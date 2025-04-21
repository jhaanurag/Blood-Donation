CREATE TABLE IF NOT EXISTS `ai_response_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(32) NOT NULL,
  `prompt` text NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;