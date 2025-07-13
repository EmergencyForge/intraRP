<?php
try {
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS `intra_bewerbung_statuslog` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bewerbungid` int(11) NOT NULL,
        `status_alt` tinyint(1) DEFAULT NULL,
        `status_neu` tinyint(1) DEFAULT NULL,
        `user` varchar(255) DEFAULT NULL,
        `discordid` varchar(255) NOT NULL,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
  SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
