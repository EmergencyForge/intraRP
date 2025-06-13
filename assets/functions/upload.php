<?php
require_once __DIR__ . '/../../assets/config/config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

$upload_dir = realpath(__DIR__ . '/../../assets/upload') . '/';
$max_file_size = 10 * 1024 * 1024; // 10 MB

$allowed_mime_types = [
    'image/png'                  => 'png',
    'image/jpeg'                 => 'jpg',
    'image/gif'                  => 'gif',
    'image/bmp'                  => 'bmp',
    'image/webp'                 => 'webp',
    'image/svg+xml'              => 'svg',
    'image/tiff'                 => 'tiff',
    'image/heic'                 => 'heic',
    'image/heif'                 => 'heif',
    'application/pdf'            => 'pdf',
    'application/msword'                         => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel'                   => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'xlsx',
    'application/vnd.ms-powerpoint'              => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    'application/vnd.oasis.opendocument.text'    => 'odt',
    'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
    'application/vnd.oasis.opendocument.presentation' => 'odp',
];


if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Keine Datei empfangen oder Fehler beim Upload.');
}

$file = $_FILES['file'];
$file_mime = mime_content_type($file['tmp_name']);
$file_size = $file['size'];

if (!array_key_exists($file_mime, $allowed_mime_types)) {
    http_response_code(415);
    exit('Ungültiger Datei-Typ');
}

if ($file_size > $max_file_size) {
    http_response_code(413);
    exit('Datei ist zu groß. Maximal erlaubt: 10 MB.');
}

$extension = $allowed_mime_types[$file_mime];
$random_name = bin2hex(random_bytes(12)) . '.' . $extension;
$destination = $upload_dir . $random_name;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    exit('Fehler beim Speichern der Datei.');
}

$sql = "INSERT INTO intra_uploads (file_name, file_type, file_size, user_name, upload_time)
        VALUES (:file_name, :file_type, :file_size, :user_name, :upload_time)";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    'file_name' => $random_name,
    'file_type' => $file_mime,
    'file_size' => $file_size,
    'user_name' => $_SESSION['cirs_user'] ?? 'unbekannt',
    'upload_time' => date('Y-m-d H:i:s')
]);

if ($success) {
    echo BASE_PATH . 'assets/upload/' . $random_name;
} else {
    http_response_code(500);
    exit('Fehler beim Speichern in der Datenbank.');
}
