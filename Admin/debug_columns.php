<?php
require('connection.php');

echo "<h2>PostgreSQL Column Name Debug</h2>";

// Test 1: Check information_schema
echo "<h3>1. Information Schema Columns</h3>";
$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = 'books' ORDER BY ordinal_position";
$result = pg_query($con, $sql);

if ($result) {
    echo "<table border='1'><tr><th>Column Name</th></tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr><td>" . htmlspecialchars($row['column_name']) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . pg_last_error($con);
}

// Test 2: Try different column name variations
echo "<h3>2. Testing Different Column Names</h3>";
$testColumns = ['isbn', 'ISBN', '"ISBN"', '"isbn"', 'books.isbn', 'books."ISBN"'];

foreach ($testColumns as $col) {
    echo "<p><strong>Testing: $col</strong><br>";
    $sql = "SELECT $col as test_col FROM books LIMIT 1";
    $result = pg_query($con, $sql);
    if ($result) {
        $row = pg_fetch_assoc($result);
        echo "✅ SUCCESS: " . htmlspecialchars($row['test_col']) . "</p>";
    } else {
        echo "❌ FAILED: " . pg_last_error($con) . "</p>";
    }
}

// Test 3: Get all columns with *
echo "<h3>3. SELECT * Test</h3>";
$sql = "SELECT * FROM books LIMIT 1";
$result = pg_query($con, $sql);

if ($result) {
    $row = pg_fetch_assoc($result);
    echo "<table border='1'><tr><th>Available Column</th><th>Sample Value</th></tr>";
    foreach ($row as $col => $val) {
        echo "<tr><td>" . htmlspecialchars($col) . "</td><td>" . htmlspecialchars(substr($val, 0, 50)) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . pg_last_error($con);
}

pg_close($con);
?>
