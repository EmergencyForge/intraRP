<?php
try {
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS `intra_bewerbung` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `discordid` varchar(255) NOT NULL,
        `fullname` varchar(255) DEFAULT NULL,
        `gebdatum` date DEFAULT NULL,
        `charakterid` varchar(255) DEFAULT NULL,
        `geschlecht` tinyint(1) DEFAULT NULL,
        `telefonnr` varchar(255) DEFAULT NULL,
        `dienstnr` varchar(255) DEFAULT NULL,
        `closed` tinyint(1) NOT NULL DEFAULT 0,
        `deleted` tinyint(1) NOT NULL DEFAULT 0,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
  SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
