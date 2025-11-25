<?php
require('connection.php');

echo "<h2>🔍 Admin_tokens Table Structure</h2>";

// Check table structure
$sql = "SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'admin_tokens' 
        ORDER BY ordinal_position";

$result = pg_query($con, $sql);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th></tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr><td>{$row['column_name']}</td><td>{$row['data_type']}</td><td>{$row['is_nullable']}</td></tr>";
    }
    echo "</table>";
    
    // Check current data
    echo "<h3>📊 Current Data:</h3>";
    $dataSql = "SELECT * FROM admin_tokens";
    $dataResult = pg_query($con, $dataSql);
    
    if (pg_num_rows($dataResult) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Admin ID</th><th>Token</th><th>Expires</th></tr>";
        while ($row = pg_fetch_assoc($dataResult)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['admin_id']}</td><td>{$row['token']}</td><td>{$row['expires_at']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No tokens found in table";
    }
} else {
    echo "Error: " . pg_last_error($con);
}

pg_close($con);
?>
