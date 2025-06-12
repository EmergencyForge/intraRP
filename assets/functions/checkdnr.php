<?php
require_once __DIR__ . '/../../assets/config/config.php';
require __DIR__ . '/../../assets/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dienstnr = $_POST['dienstnr'];

    try {
        $query = "SELECT COUNT(*) as count FROM intra_mitarbeiter WHERE dienstnr = :dienstnr";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':dienstnr' => $dienstnr]);
        $count = $stmt->fetchColumn();
        echo ($count > 0) ? 'exists' : 'not_exists';
    } catch (PDOException $e) {
        echo 'error';
    }
}
