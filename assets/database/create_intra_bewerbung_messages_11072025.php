<?php
try {
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS `intra_bewerbung_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bewerbungid` int(11) NOT NULL,
        `text` text DEFAULT NULL,
        `user` varchar(255) DEFAULT NULL,
        `discordid` varchar(255) NOT NULL,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
  SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
