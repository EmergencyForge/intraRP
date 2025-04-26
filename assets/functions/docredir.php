<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
$openedID = $_GET['docid'];

$stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dokumente WHERE docid = :docid");
$stmt->execute(['docid' => $_GET['docid']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$docType = $row['type'];

// Abmahnungen
if ($docType == 10) {
    header("Location: /dokumente/schreiben/abmahnung/" . $openedID);
}
// Dienstenthebungen
if ($docType == 11) {
    header("Location: /dokumente/schreiben/dienstenthebung/" . $openedID);
}
// Dienstentfernungen
if ($docType == 12) {
    header("Location: /dokumente/schreiben/dienstentfernung/" . $openedID);
}
// Kündigung
if ($docType == 13) {
    header("Location: /dokumente/schreiben/kuendigung/" . $openedID);
}
// Ernennungsurkunde
if ($docType == 0) {
    header("Location: /dokumente/urkunden/ernennung/" . $openedID);
}
// Beförderungsurkunde
if ($docType == 1) {
    header("Location: /dokumente/urkunden/befoerderung/" . $openedID);
}
// Entlassungsurkunde
if ($docType == 2) {
    header("Location: /dokumente/urkunden/entlassung/" . $openedID);
}
// Ausbildungszertifikat
if ($docType == 5) {
    header("Location: /dokumente/zertifikate/ausbildung/" . $openedID);
}
// Lehrgangszertifikat
if ($docType == 6) {
    header("Location: /dokumente/zertifikate/lehrgang/" . $openedID);
}
// Fachlehrgangszertifikat
if ($docType == 7) {
    header("Location: /dokumente/zertifikate/fachlehrgang/" . $openedID);
}
