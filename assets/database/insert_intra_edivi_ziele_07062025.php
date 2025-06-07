<?php
try {
    $sql = <<<SQL
    INSERT IGNORE INTO `intra_edivi_ziele` (`id`, `priority`, `identifier`, `name`, `transport`, `active`, `created_at`) VALUES
        (2, 98, 'amb', 'Ambulante Versorgung', 0, 1, '2025-03-19 22:32:15'),
        (3, 99, 'ubg', 'Übergabe Notfallteam', 0, 1, '2025-03-19 22:32:22'),
        (4, 96, 'kp', 'Kein Patient', 0, 1, '2025-03-19 22:32:36'),
        (5, 97, 'sf', 'Sozialfahrt', 0, 1, '2025-03-19 22:32:42');
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    $message = $e->getMessage();
    echo $message;
}
