<?php
require('connection.php');

echo "<h2>🔍 Session Table Investigation</h2>";

// Check session table structure
echo "<h3>📊 Session Table Structure:</h3>";
$sql = "SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'session' 
        ORDER BY ordinal_position";

$result = pg_query($con, $sql);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th></tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr><td>{$row['column_name']}</td><td>{$row['data_type']}</td><td>{$row['is_nullable']}</td></tr>";
    }
    echo "</table>";
    
    // Check current session data in table
    echo "<h3>📊 Current Session Data in Table:</h3>";
    $dataSql = "SELECT * FROM session";
    $dataResult = pg_query($con, $dataSql);
    
    if (pg_num_rows($dataResult) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Session ID</th><th>Data</th><th>Expires</th></tr>";
        while ($row = pg_fetch_assoc($dataResult)) {
            echo "<tr><td>{$row['session_id']}</td><td>" . substr($row['data'], 0, 50) . "...</td><td>{$row['expires_at']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No session data found in table";
    }
} else {
    echo "Error: " . pg_last_error($con);
}

// Check PHP session configuration
echo "<h3>🔧 PHP Session Configuration:</h3>";
echo "<table border='1'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>session.save_handler</td><td>" . ini_get('session.save_handler') . "</td></tr>";
echo "<tr><td>session.save_path</td><td>" . ini_get('session.save_path') . "</td></tr>";
echo "</table>";

// Test current session
echo "<h3>🧪 Current Session Test:</h3>";
session_start();
echo "Current Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

if (!empty($_SESSION)) {
    echo "Session Data:<br>";
    foreach ($_SESSION as $key => $value) {
        echo "$key = $value<br>";
    }
} else {
    echo "No session data currently";
}

echo "<h3>💡 Analysis:</h3>";
if (ini_get('session.save_handler') == 'files') {
    echo "✅ PHP is using FILE-based sessions (default)<br>";
    echo "❌ Session table exists but NOT being used<br>";
    echo "🔧 To use database sessions, need custom session handler<br>";
} else {
    echo "✅ PHP is using custom session handler<br>";
    echo "✅ Session table IS being used<br>";
}

pg_close($con);
?>
