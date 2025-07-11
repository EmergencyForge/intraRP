<?php
if (getenv('GITHUB_ACTIONS') === 'true' || getenv('CI') === 'true') {
    echo "⚠️  Skipping database setup in CI (GitHub Actions).\n";
    exit(0);
}

function findProjectRoot()
{
    $currentDir = __DIR__;
    $workingDir = getcwd();

    echo "DEBUG: Script-Pfad: $currentDir\n";
    echo "DEBUG: Arbeitsverzeichnis: $workingDir\n";

    $candidates = [
        dirname($currentDir),

        dirname($workingDir),
        dirname($workingDir, 2),
        dirname($workingDir, 3),
        dirname($workingDir, 4),

        $workingDir,
        dirname($currentDir, 2),
        dirname($currentDir, 3),
        dirname($currentDir, 4),
    ];

    foreach ($candidates as $candidate) {
        $candidate = realpath($candidate);
        if (!$candidate) continue;

        echo "DEBUG: Teste Root-Kandidat: $candidate\n";

        if (
            file_exists($candidate . '/composer.json') &&
            file_exists($candidate . '/.env') &&
            is_dir($candidate . '/vendor')
        ) {
            echo "✓ Project-Root gefunden: $candidate\n";
            return $candidate;
        }
    }

    throw new Exception("Konnte Project-Root nicht finden. Getestete Pfade: " . implode(', ', $candidates));
}

try {
    $projectRoot = findProjectRoot();
} catch (Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
    exit(1);
}

$autoloadPath = $projectRoot . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo "❌ Autoloader nicht gefunden: $autoloadPath\n";
    exit(1);
}

require_once $autoloadPath;
echo "✓ Autoloader geladen von: $autoloadPath\n";

try {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot, null, false);
    $dotenv->load();
    echo "✓ .env geladen von: $projectRoot/.env\n";
} catch (Exception $e) {
    echo "❌ Konnte .env nicht laden: " . $e->getMessage() . "\n";
    echo "Suchpfad war: $projectRoot/.env\n";
    exit(1);
}

$requiredVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
foreach ($requiredVars as $var) {
    if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
        echo "❌ Umgebungsvariable $var nicht gesetzt\n";
        exit(1);
    }
}

$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_pass = $_ENV['DB_PASS'];
$db_name = $_ENV['DB_NAME'];

$dsn = "mysql:host=" . $db_host . ";dbname=" . $db_name . ";charset=utf8";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "✓ Datenbankverbindung erfolgreich\n";
} catch (PDOException $e) {
    echo "❌ Datenbankverbindung fehlgeschlagen: " . $e->getMessage() . "\n";
    exit(1);
}

$migrationPath = $projectRoot . '/assets/database';
if (!is_dir($migrationPath)) {
    echo "❌ Migration-Verzeichnis nicht gefunden: $migrationPath\n";
    exit(1);
}

echo "✓ Migration-Verzeichnis: $migrationPath\n";

$migrationFiles = [
    // 07.06.2025
    'create_intra_antrag_bef_07062025.php',
    'create_intra_users_roles_07062025.php',
    'insert_intra_users_roles_07062025.php',
    'create_intra_users_07062025.php',
    'create_intra_audit_log_07062025.php',
    'add_foreign_keys_07062025.php',
    'create_intra_dashboard_categories_07062025.php',
    'create_intra_dashboard_tiles_07062025.php',
    'create_intra_edivi_07062025.php',
    'create_intra_edivi_fahrzeuge_07062025.php',
    'create_intra_edivi_qmlog_07062025.php',
    'create_intra_edivi_ziele_07062025.php',
    'insert_intra_edivi_ziele_07062025.php',
    'create_intra_mitarbeiter_dienstgrade_07062025.php',
    'insert_intra_mitarbeiter_dienstgrade_07062025.php',
    'create_intra_mitarbeiter_fwquali_07062025.php',
    'insert_intra_mitarbeiter_fwquali_07062025.php',
    'create_intra_mitarbeiter_log_07062025.php',
    'create_intra_mitarbeiter_rdquali_07062025.php',
    'insert_intra_mitarbeiter_rdquali_07062025.php',
    'create_intra_mitarbeiter_07062025.php',
    'create_intra_mitarbeiter_dokumente_07062025.php',
    'create_intra_uploads_07062025.php',
    // 13.06.2025
    'create_intra_mitarbeiter_fdquali_13062025.php',
    'insert_intra_mitarbeiter_fdquali_13062025.php',
    // 18.06.2025
    'create_intra_edivi_prereg_18062025.php',
    // 23.06.2025
    'update_intra_edivi_fahrzeuge_23062025.php',
    'create_intra_fahrzeuge_beladung_categories_23062025.php',
    'create_intra_fahrzeuge_beladung_tiles_23062025.php',
    'update_intra_mitarbeiter_23062025.php',
    'update_intra_mitarbeiter_dokumente_23062025.php',
    // 06.07.2025
    'update_intra_edivi_06072025.php',
    'create_intra_edivi_vitalparameter_einzelwerte_06072025.php',
    // 11.07.2025
    'create_intra_bewerbung_11072025.php',
    'create_intra_bewerbung_messages_11072025.php',
    'create_intra_bewerbung_statuslog_11072025.php'
];

$executed = 0;
$skipped = 0;

foreach ($migrationFiles as $file) {
    $fullPath = $migrationPath . '/' . $file;

    if (file_exists($fullPath)) {
        try {
            include $fullPath;
            $executed++;
            echo "✓ Migration ausgeführt: $file\n";
        } catch (Exception $e) {
            echo "⚠️  Migration fehlgeschlagen: $file - " . $e->getMessage() . "\n";
        }
    } else {
        $skipped++;
        echo "⚠️  Migration nicht gefunden: $file\n";
    }
}

echo "\n=== Database Setup Abgeschlossen ===\n";
echo "Project Root: $projectRoot\n";
echo "Migrationen ausgeführt: $executed\n";
echo "Migrationen übersprungen: $skipped\n";

exit(0);
