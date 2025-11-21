<?php
// Test mod_rewrite configuration
echo "<h1>Apache & mod_rewrite Test</h1>";

echo "<h2>Apache Version:</h2>";
echo $_SERVER['SERVER_SOFTWARE'] ?? 'Not available';

echo "<h2>mod_rewrite Status:</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ mod_rewrite is ENABLED";
    } else {
        echo "❌ mod_rewrite is NOT LOADED";
    }
} else {
    echo "ℹ️ Cannot check modules (possibly running as CGI/FastCGI)";
}

echo "<h2>RewriteRule Test URLs:</h2>";
echo "<p>Current URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";

echo "<h2>Available Test URLs:</h2>";
echo "<ul>";
echo "<li><a href='../index.php'>Root index.php</a></li>";
echo "<li><a href='bookCategory.php?id=1'>Direct bookCategory.php access</a></li>";
echo "<li><a href='../pages/bookCategory.php?id=1'>Pages directory access</a></li>";
echo "<li><a href='aboutUs.php'>About Us page</a></li>";
echo "<li><a href='book.php?id=1'>Book page</a></li>";
echo "</ul>";

echo "<h2>Directory Structure Test:</h2>";
if (file_exists(__DIR__ . '/pages/index.php')) {
    echo "✅ pages/index.php exists";
} else {
    echo "❌ pages/index.php missing";
}
echo "<br>";
if (file_exists(__DIR__ . '/pages/bookCategory.php')) {
    echo "✅ pages/bookCategory.php exists";
} else {
    echo "❌ pages/bookCategory.php missing";
}
?>