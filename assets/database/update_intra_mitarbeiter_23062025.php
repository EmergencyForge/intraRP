<?php
try {
    $sql = <<<SQL
        ALTER TABLE IF EXISTS `intra_mitarbeiter` 
        MODIFY COLUMN `charakterid` VARCHAR(255) NULL DEFAULT NULL;
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
