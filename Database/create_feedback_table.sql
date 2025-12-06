-- Create feedback table for book ratings and reviews
USE `mini_project`;

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
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_feedback_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Book feedback and ratings from customers';

-- Add check constraint for rating values (1-5)
ALTER TABLE `feedback` 
ADD CONSTRAINT `chk_rating_range` CHECK (`rating` >= 1 AND `rating` <= 5);
