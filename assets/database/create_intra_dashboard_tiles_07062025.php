<?php
try {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `intra_dashboard_tiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '#',
  `icon` varchar(255) NOT NULL DEFAULT 'las la-external-link-alt',
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_intra_dashboard_tiles_intra_dashboard_categories` (`category`),
  CONSTRAINT `FK_intra_dashboard_tiles_intra_dashboard_categories` FOREIGN KEY (`category`) REFERENCES `intra_dashboard_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
