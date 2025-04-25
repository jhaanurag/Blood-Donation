-- User Medications Table for Blood Donation Chatbot
-- This table stores medication information for registered users
-- Used by the chatbot to provide personalized advice

CREATE TABLE IF NOT EXISTS `user_medications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_medications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing (optional)
INSERT INTO `user_medications` (`user_id`, `name`, `dosage`, `frequency`, `purpose`, `start_date`) 
VALUES 
(1, 'Aspirin', '100mg', 'daily', 'prevent blood clots', '2025-01-15'),
(1, 'Lisinopril', '20mg', 'daily', 'high blood pressure', '2025-03-01'),
(2, 'Metformin', '500mg', 'twice daily', 'diabetes', '2025-02-10');

-- Create the AI response cache table if it doesn't exist
CREATE TABLE IF NOT EXISTS `ai_response_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(32) NOT NULL,
  `prompt` text NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;