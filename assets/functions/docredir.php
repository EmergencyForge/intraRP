<?php
require_once __DIR__ . '/../../assets/config/config.php';
require __DIR__ . '/../../assets/config/database.php';
$openedID = $_GET['docid'];

$stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dokumente WHERE docid = :docid");
$stmt->execute(['docid' => $_GET['docid']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$docType = $row['type'];

// Abmahnungen
if ($docType == 10) {
    header("Location: /dokumente/schreiben/abmahnung.php?dok=" . $openedID);
}
// Dienstenthebungen
if ($docType == 11) {
    header("Location: /dokumente/schreiben/dienstenthebung.php?dok=" . $openedID);
}
// Dienstentfernungen
if ($docType == 12) {
    header("Location: /dokumente/schreiben/dienstentfernung.php?dok=" . $openedID);
}
// Kündigung
if ($docType == 13) {
    header("Location: /dokumente/schreiben/kuendigung.php?dok=" . $openedID);
}
// Ernennungsurkunde
if ($docType == 0) {
    header("Location: /dokumente/urkunden/ernennung.php?dok=" . $openedID);
}
// Beförderungsurkunde
if ($docType == 1) {
    header("Location: /dokumente/urkunden/befoerderung.php?dok=" . $openedID);
}
// Entlassungsurkunde
if ($docType == 2) {
    header("Location: /dokumente/urkunden/entlassung.php?dok=" . $openedID);
}
// Ausbildungszertifikat
if ($docType == 5) {
    header("Location: /dokumente/zertifikate/ausbildung.php?dok=" . $openedID);
}
// Lehrgangszertifikat
if ($docType == 6) {
    header("Location: /dokumente/zertifikate/lehrgang.php?dok=" . $openedID);
}
// Fachlehrgangszertifikat
if ($docType == 7) {
    header("Location: /dokumente/zertifikate/fachlehrgang.php?dok=" . $openedID);
}
