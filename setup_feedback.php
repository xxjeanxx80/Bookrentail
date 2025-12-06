<?php
require_once(__DIR__ . '/config/connection.php');

// SQL to create feedback table
$sql = "
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_feedback` (`order_id`, `book_id`, `user_id`),
  KEY `idx_book_id` (`book_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Book feedback and ratings from customers';
";

echo "<h2>Setting up Feedback System</h2>";

if (mysqli_query($con, $sql)) {
    echo "<p style='color: green;'>✅ Feedback table created successfully!</p>";
    
    // Add check constraint for rating values (1-5)
    $constraintSql = "ALTER TABLE `feedback` ADD CONSTRAINT `chk_rating_range` CHECK (`rating` >= 1 AND `rating` <= 5)";
    
    if (mysqli_query($con, $constraintSql)) {
        echo "<p style='color: green;'>✅ Rating constraint added successfully!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️  Warning: Could not add rating constraint (may already exist): " . mysqli_error($con) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Error creating feedback table: " . mysqli_error($con) . "</p>";
}

echo "<p><a href='pages/index.php'>Back to Home</a></p>";
mysqli_close($con);
?>
