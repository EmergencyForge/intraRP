<?php
try {
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS `intra_edivi_vitalparameter` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `enr` varchar(255) NOT NULL,
        `zeitpunkt` datetime NOT NULL DEFAULT current_timestamp(),
        `spo2` varchar(255) DEFAULT NULL,
        `atemfreq` varchar(255) DEFAULT NULL,
        `etco2` varchar(255) DEFAULT NULL,
        `rrsys` varchar(255) DEFAULT NULL,
        `rrdias` varchar(255) DEFAULT NULL,
        `herzfreq` varchar(255) DEFAULT NULL,
        `bz` varchar(255) DEFAULT NULL,
        `temp` varchar(255) DEFAULT NULL,
        `bemerkung` text DEFAULT NULL,
        `erstellt_von` varchar(255) DEFAULT NULL,
        `erstellt_am` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `FK_intra_edivi_vitalparameter_intra_edivi` (`enr`),
        CONSTRAINT `FK_intra_edivi_vitalparameter_intra_edivi` FOREIGN KEY (`enr`) REFERENCES `intra_edivi` (`enr`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
    ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
  SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
