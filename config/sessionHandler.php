<?php
declare(strict_types=1);

/**
 * Session handler lưu dữ liệu vào bảng Postgres để dùng được trong môi trường serverless (Vercel)
 */
class PgsqlSessionHandler implements SessionHandlerInterface
{
    private $connection;
    private $table;
    private $lifetime;

    public function __construct($connection, string $table = 'sessions')
    {
        if (!$connection instanceof \PgSql\Connection) {
            throw new InvalidArgumentException('A valid PostgreSQL connection is required.');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid sessions table name.');
        }

        $this->connection = $connection;
        $this->table = $table;
        $this->lifetime = (int)ini_get('session.gc_maxlifetime') ?: 1440;

        $this->initializeTable();
    }

    private function initializeTable(): void
    {
        $createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS {$this->table} (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
)
SQL;
        @pg_query($this->connection, $createTableSql);
        @pg_query($this->connection, "CREATE INDEX IF NOT EXISTS idx_{$this->table}_expires_at ON {$this->table} (expires_at)");
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId): string
    {
        $sql = "SELECT data FROM {$this->table} WHERE id = $1 AND expires_at > NOW()";
        $result = @pg_query_params($this->connection, $sql, [$sessionId]);

        if (!$result || pg_num_rows($result) === 0) {
            return '';
        }

        $row = pg_fetch_assoc($result);
        return $row['data'] ?? '';
    }

    public function write($sessionId, $data): bool
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $this->lifetime);

        $sql = <<<SQL
INSERT INTO {$this->table} (id, data, expires_at, updated_at)
VALUES ($1, $2, $3, NOW())
ON CONFLICT (id)
DO UPDATE SET data = EXCLUDED.data,
              expires_at = EXCLUDED.expires_at,
              updated_at = NOW()
SQL;

        $result = @pg_query_params($this->connection, $sql, [$sessionId, $data, $expiresAt]);
        return $result !== false;
    }

    public function destroy($sessionId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = $1";
        $result = @pg_query_params($this->connection, $sql, [$sessionId]);
        return $result !== false;
    }

    public function gc($max_lifetime): int|false
    {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        $result = @pg_query($this->connection, $sql);

        if ($result === false) {
            return false;
        }

        return pg_affected_rows($result);
    }
}

