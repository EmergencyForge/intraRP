<?php
try {
  $sql = <<<SQL
  CREATE TABLE IF NOT EXISTS `intra_antrag_bef` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uniqueid` int(11) NOT NULL,
    `name_dn` varchar(255) NOT NULL,
    `dienstgrad` varchar(255) NOT NULL,
    `time_added` datetime NOT NULL DEFAULT current_timestamp(),
    `freitext` text NOT NULL,
    `cirs_manager` varchar(255) DEFAULT NULL,
    `cirs_time` datetime DEFAULT NULL,
    `cirs_status` tinyint(3) NOT NULL DEFAULT 0,
    `cirs_text` text DEFAULT NULL,
    `discordid` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
  SQL;

  $pdo->exec($sql);
} catch (PDOException $e) {
  $message = $e->getMessage();
  echo $message;
}
