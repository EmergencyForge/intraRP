<?php
if (getenv('GITHUB_ACTIONS') === 'true' || getenv('CI') === 'true') {
    echo "⚠️  Skipping database setup in CI (GitHub Actions).\n";
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', null, false);
$dotenv->load();
// Verbindungsdaten
$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_pass = $_ENV['DB_PASS'];
$db_name = $_ENV['DB_NAME'];
$dsn = "mysql:host=" . $db_host . ";dbname=" . $db_name . ";charset=utf8";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $db_user, $db_pass, $options);

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}


include __DIR__ . '/../' . '/assets/database/create_intra_antrag_bef_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_users_roles_07062025.php';
include __DIR__ . '/../' . '/assets/database/insert_intra_users_roles_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_users_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_audit_log_07062025.php';
include __DIR__ . '/../' . '/assets/database/add_foreign_keys_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_dashboard_categories_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_dashboard_tiles_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_edivi_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_edivi_fahrzeuge_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_edivi_qmlog_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_edivi_ziele_07062025.php';
include __DIR__ . '/../' . '/assets/database/insert_intra_edivi_ziele_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_dienstgrade_07062025.php';
include __DIR__ . '/../' . '/assets/database/insert_intra_mitarbeiter_dienstgrade_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_fwquali_07062025.php';
include __DIR__ . '/../' . '/assets/database/insert_intra_mitarbeiter_fwquali_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_log_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_rdquali_07062025.php';
include __DIR__ . '/../' . '/assets/database/insert_intra_mitarbeiter_rdquali_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_dokumente_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_uploads_07062025.php';
include __DIR__ . '/../' . '/assets/database/create_intra_mitarbeiter_fdquali_13062025.php';
include __DIR__ . '/../' . '/assets/database/insert_intra_mitarbeiter_fdquali_13062025.php';
