<?php
require('connection.php');

echo "<h2>🔍 Sessions Table Investigation</h2>";

// Check sessions table structure
echo "<h3>📊 Sessions Table Structure:</h3>";
$sql = "SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'sessions' 
        ORDER BY ordinal_position";

$result = pg_query($con, $sql);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th></tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr><td>{$row['column_name']}</td><td>{$row['data_type']}</td><td>{$row['is_nullable']}</td></tr>";
    }
    echo "</table>";
    
    // Check current data in sessions table
    echo "<h3>📊 Current Sessions Data:</h3>";
    $dataSql = "SELECT * FROM sessions LIMIT 5";
    $dataResult = pg_query($con, $dataSql);
    
    if ($dataResult && pg_num_rows($dataResult) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Session ID</th><th>Data Sample</th><th>Expires</th></tr>";
        while ($row = pg_fetch_assoc($dataResult)) {
            $dataSample = isset($row['data']) ? substr($row['data'], 0, 50) . '...' : 'N/A';
            $expires = isset($row['expires_at']) ? $row['expires_at'] : 'N/A';
            echo "<tr><td>{$row['session_id']}</td><td>$dataSample</td><td>$expires</td></tr>";
        }
        echo "</table>";
        echo "✅ Sessions table IS being used!";
    } else {
        echo "❌ No data in sessions table - table exists but not used";
    }
} else {
    echo "Error: " . pg_last_error($con);
}

// Check for user_tokens table structure
echo "<h3>📊 User_tokens Table Structure:</h3>";
$sql = "SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'user_tokens' 
        ORDER BY ordinal_position";

$result = pg_query($con, $sql);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th></tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr><td>{$row['column_name']}</td><td>{$row['data_type']}</td></tr>";
    }
    echo "</table>";
}

echo "<h3>🎯 Analysis:</h3>";
echo "<ul>";
echo "<li><strong>Admin Authentication:</strong> Sessions (files) + admin_tokens (Remember Me)</li>";
echo "<li><strong>User Authentication:</strong> Possibly sessions (database) + user_tokens (Remember Me)</li>";
echo "<li><strong>Dual System:</strong> Admin uses file sessions, Users might use database sessions</li>";
echo "</ul>";

pg_close($con);
?>
