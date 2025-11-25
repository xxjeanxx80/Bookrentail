<?php
echo "<h2>🔍 PHP Session Configuration</h2>";

echo "<h3>📊 Session Settings:</h3>";
echo "<table border='1'>";
echo "<tr><th>Setting</th><th>Value</th><th>Explanation</th></tr>";

echo "<tr><td>session.save_handler</td><td>" . ini_get('session.save_handler') . "</td><td>How sessions are stored (files=server files)</td></tr>";
echo "<tr><td>session.save_path</td><td>" . ini_get('session.save_path') . "</td><td>Directory where session files are stored</td></tr>";
echo "<tr><td>session.gc_maxlifetime</td><td>" . ini_get('session.gc_maxlifetime') . " seconds</td><td>How long sessions last (seconds)</td></tr>";
echo "<tr><td>session.use_cookies</td><td>" . ini_get('session.use_cookies') . "</td><td>Whether to use cookies for session ID</td></tr>";
echo "<tr><td>session.name</td><td>" . ini_get('session.name') . "</td><td>Session cookie name (usually PHPSESSID)</td></tr>";

echo "</table>";

echo "<h3>🍪 Current Session Info:</h3>";
session_start();
echo "<table border='1'>";
echo "<tr><th>Session ID</th><td>" . session_id() . "</td></tr>";
echo "<tr><th>Session Status</th><td>" . session_status() . "</td></tr>";

echo "<tr><th>Session Data</th><td>";
if (!empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        echo "$key = $value<br>";
    }
} else {
    echo "No session data";
}
echo "</td></tr>";

echo "</table>";

echo "<h3>🔍 Test Session Creation:</h3>";
$_SESSION['test'] = 'Session working at ' . date('Y-m-d H:i:s');
echo "✅ Test session data set. Refresh page to see if it persists.";

echo "<h3>💡 What You're Seeing:</h3>";
echo "<ul>";
echo "<li><strong>PHP Sessions</strong> = Server files (NOT database)</li>";
echo "<li><strong>Database Tokens</strong> = admin_tokens table (for Remember Me)</li>";
echo "<li><strong>Two Different Systems!</strong></li>";
echo "</ul>";
?>
