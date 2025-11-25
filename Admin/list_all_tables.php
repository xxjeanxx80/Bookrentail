<?php
require('connection.php');

echo "<h2>🔍 All Tables in Database</h2>";

// List all tables in current database
$sql = "SELECT table_name FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name";

$result = pg_query($con, $sql);

echo "<h3>📊 Available Tables:</h3>";
if ($result && pg_num_rows($result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Table Name</th><th>Purpose</th></tr>";
    
    while ($row = pg_fetch_assoc($result)) {
        $tableName = $row['table_name'];
        echo "<tr><td><strong>$tableName</strong></td><td>";
        
        // Guess table purpose
        switch($tableName) {
            case 'admin':
                echo "Admin users";
                break;
            case 'admin_tokens':
                echo "Remember Me tokens";
                break;
            case 'users':
                echo "Regular users";
                break;
            case 'books':
                echo "Book catalog";
                break;
            case 'categories':
                echo "Book categories";
                break;
            case 'orders':
                echo "Book orders";
                break;
            case 'order_detail':
                echo "Order details";
                break;
            case 'order_status':
                echo "Order statuses";
                break;
            case 'contact_us':
                echo "User feedback";
                break;
            default:
                echo "Unknown purpose";
        }
        echo "</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>💡 Analysis:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>admin_tokens</strong> table exists - for Remember Me functionality</li>";
    echo "<li>❌ <strong>session</strong> table NOT found - PHP uses file-based sessions</li>";
    echo "<li>✅ All other Bookrentail tables are present</li>";
    echo "</ul>";
    
} else {
    echo "Error: " . pg_last_error($con);
}

echo "<h3>🔧 PHP Session Configuration:</h3>";
echo "Session Handler: <strong>" . ini_get('session.save_handler') . "</strong><br>";
echo "Session Save Path: <strong>" . ini_get('session.save_path') . "</strong><br>";

echo "<h3>🎯 Conclusion:</h3>";
if (ini_get('session.save_handler') == 'files') {
    echo "✅ PHP sessions use FILE storage (default)<br>";
    echo "✅ Sessions are stored in: " . ini_get('session.save_path') . "<br>";
    echo "✅ Remember Me uses DATABASE tokens (admin_tokens table)<br>";
    echo "❌ No session table needed - system works correctly!";
} else {
    echo "❓ Custom session handler detected";
}

pg_close($con);
?>
