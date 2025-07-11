<?php
require_once __DIR__ . '/../../assets/config/config.php';
require __DIR__ . '/../../assets/config/database.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dienstnr = $_POST['dienstnr'] ?? '';

    if (empty($dienstnr)) {
        echo 'error';
        exit;
    }

    if (!is_numeric($dienstnr)) {
        echo 'error';
        exit;
    }

    try {
        if (!isset($pdo)) {
            echo 'error';
            exit;
        }

        $query = "SELECT COUNT(*) as count FROM intra_mitarbeiter WHERE dienstnr = :dienstnr";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['dienstnr' => $dienstnr]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo 'exists';
        } else {
            echo 'not_exists';
        }
    } catch (PDOException $e) {
        error_log("Fehler bei Dienstnummer-Überprüfung: " . $e->getMessage());
        echo 'error';
    } catch (Exception $e) {
        error_log("Allgemeiner Fehler: " . $e->getMessage());
        echo 'error';
    }
} else {
    echo 'error';
}
