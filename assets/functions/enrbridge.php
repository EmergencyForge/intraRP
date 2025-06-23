<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require __DIR__ . '/../../assets/config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"];
    $enr = $_POST["enr"];
    $prot_by = isset($_POST["prot_by"]) ? (int)$_POST["prot_by"] : 0;

    $stmt = $pdo->prepare("SELECT 1 FROM intra_edivi WHERE enr = :enr");
    $stmt->execute(['enr' => $enr]);
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
        header("Location: " . BASE_PATH . "enotf/prot/index.php?enr=" . urlencode($enr));
        exit();
    }

    $fahrzeugId = $_SESSION['protfzg'] ?? null;
    $stmtFzg = $pdo->prepare("SELECT identifier, rd_type FROM intra_fahrzeuge WHERE identifier = :id");
    $stmtFzg->execute(['id' => $fahrzeugId]);
    $fahrzeug = $stmtFzg->fetch(PDO::FETCH_ASSOC);

    $isDoctorVehicle = ($fahrzeug && $fahrzeug['rd_type'] == 1);
    $fzgField = $isDoctorVehicle ? 'fzg_na' : 'fzg_transp';
    $persoField1 = $isDoctorVehicle ? 'fzg_na_perso' : 'fzg_transp_perso';
    $persoField2 = $isDoctorVehicle ? 'fzg_na_perso_2' : 'fzg_transp_perso_2';

    $fahrer = (!empty($_SESSION['fahrername']) && !empty($_SESSION['fahrerquali']))
        ? $_SESSION['fahrername'] . " (" . $_SESSION['fahrerquali'] . ")"
        : null;

    $beifahrer = (!empty($_SESSION['beifahrername']) && !empty($_SESSION['beifahrerquali']))
        ? $_SESSION['beifahrername'] . " (" . $_SESSION['beifahrerquali'] . ")"
        : null;

    $columns = ['enr', 'prot_by', $fzgField];
    $placeholders = [':enr', ':prot_by', ':fahrzeug'];
    $params = [
        ':enr' => $enr,
        ':prot_by' => $prot_by,
        ':fahrzeug' => $fahrzeugId
    ];

    if ($beifahrer !== null) {
        $columns[] = $persoField1;
        $placeholders[] = ':beifahrer';
        $params[':beifahrer'] = $beifahrer;
    }

    if ($fahrer !== null) {
        $columns[] = $persoField2;
        $placeholders[] = ':fahrer';
        $params[':fahrer'] = $fahrer;
    }

    $sql = "INSERT INTO intra_edivi (" . implode(", ", $columns) . ")
            VALUES (" . implode(", ", $placeholders) . ")";

    $insert = $pdo->prepare($sql);
    $insert->execute($params);

    header("Location: " . BASE_PATH . "enotf/prot/index.php?enr=" . urlencode($enr));
    exit();
}
