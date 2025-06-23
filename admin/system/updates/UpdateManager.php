<?php
class UpdateManager
{
    private $pdo;
    private $currentVersion;
    private $githubRepo;
    private $githubToken;
    private $updateDir;
    private $backupDir;
    private $updateSourceDir;

    public function __construct($pdo, $githubRepo, $currentVersion = null, $githubToken = null)
    {
        $this->pdo = $pdo;
        $this->githubRepo = $githubRepo;
        $this->currentVersion = $currentVersion ?: $this->getCurrentVersion();
        $this->githubToken = $githubToken;

        $this->updateDir = __DIR__ . '/temp_update';
        $this->backupDir = __DIR__ . '/backups';

        $appRoot = dirname(__DIR__, 3);
        $this->log('UpdateManager initialisiert:');
        $this->log('- UpdateManager Pfad: ' . __DIR__);
        $this->log('- App Root: ' . $appRoot);
        $this->log('- Update Dir: ' . $this->updateDir);
        $this->log('- Backup Dir: ' . $this->backupDir);
        $this->log('- Aktuelle Version: ' . $this->currentVersion);

        $this->ensureDirectories();
    }

    private function ensureDirectories()
    {
        $directories = [
            $this->updateDir,
            $this->backupDir
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Verzeichnis konnte nicht erstellt werden: $dir");
                }
                $this->log("Verzeichnis erstellt: $dir");
            }

            if (!is_writable($dir)) {
                throw new Exception("Verzeichnis ist nicht beschreibbar: $dir");
            }
        }
    }

    private function getCurrentVersion()
    {
        $versionFile = __DIR__ . '/version.json';
        if (file_exists($versionFile)) {
            $version = json_decode(file_get_contents($versionFile), true);
            return $version['version'] ?? '1.0.0';
        }

        try {
            $stmt = $this->pdo->prepare("SELECT value FROM system_settings WHERE setting_key = 'system_version'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['value'] ?? '1.0.0';
        } catch (Exception $e) {
            return '1.0.0';
        }
    }

    public function checkForUpdates()
    {
        try {
            $headers = ['User-Agent: Update-Manager'];
            if ($this->githubToken) {
                $headers[] = 'Authorization: token ' . $this->githubToken;
            }

            $context = stream_context_create([
                'http' => [
                    'header' => implode("\r\n", $headers),
                    'timeout' => 10
                ]
            ]);

            $url = "https://api.github.com/repos/{$this->githubRepo}/releases/latest";
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new Exception('GitHub API nicht erreichbar');
            }

            $release = json_decode($response, true);

            if (!$release || !isset($release['tag_name'])) {
                throw new Exception('Ungültige API-Antwort');
            }

            $currentVersion = ltrim($this->currentVersion, 'v');
            $latestVersion = ltrim($release['tag_name'], 'v');

            return [
                'has_update' => version_compare($latestVersion, $currentVersion, '>'),
                'latest_version' => $release['tag_name'],
                'current_version' => $this->currentVersion,
                'release_notes' => $release['body'] ?? '',
                'published_at' => $release['published_at'] ?? '',
                'download_url' => $release['zipball_url'] ?? null,
                'debug_comparison' => [
                    'current_normalized' => $currentVersion,
                    'latest_normalized' => $latestVersion,
                    'comparison_result' => version_compare($latestVersion, $currentVersion, '>')
                ]
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'has_update' => false,
                'current_version' => $this->currentVersion
            ];
        }
    }

    public function performUpdate()
    {
        try {
            $updateInfo = $this->checkForUpdates();

            if (!$updateInfo['has_update']) {
                throw new Exception('Keine Updates verfügbar');
            }

            $this->log('=== UPDATE GESTARTET ===');
            $this->log('Von Version: ' . $this->currentVersion);
            $this->log('Zu Version: ' . $updateInfo['latest_version']);
            $this->log('Download URL: ' . $updateInfo['download_url']);

            $this->log('Schritt 1: Backup erstellen...');
            $this->createBackup();

            $this->log('Schritt 2: Update herunterladen...');
            $updateFile = $this->downloadUpdate($updateInfo['download_url']);

            $this->log('Schritt 3: Update extrahieren...');
            $this->extractUpdate($updateFile);

            $this->log('Schritt 4: Dateien kopieren...');
            $this->copyFiles();

            $this->log('Schritt 5: Composer Update...');
            try {
                $this->runComposerUpdate();
            } catch (Exception $e) {
                $this->log('Composer Update Warnung: ' . $e->getMessage());
            }

            $this->log('Schritt 6: Version aktualisieren...');
            $this->updateVersion($updateInfo['latest_version']);

            $this->log('Schritt 7: Aufräumen...');
            $this->cleanup();

            $this->log('=== UPDATE ERFOLGREICH ABGESCHLOSSEN ===');

            return [
                'success' => true,
                'message' => 'Update erfolgreich installiert',
                'new_version' => $updateInfo['latest_version'],
                'previous_version' => $this->currentVersion
            ];
        } catch (Exception $e) {
            $this->log('=== UPDATE FEHLGESCHLAGEN ===');
            $this->log('Fehler: ' . $e->getMessage());
            $this->log('Stack Trace: ' . $e->getTraceAsString());

            if (strpos($e->getMessage(), 'Composer') === false) {
                $this->rollback();
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'debug_info' => [
                    'current_version' => $this->currentVersion,
                    'error_class' => get_class($e),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]
            ];
        }
    }

    private function checkSystemRequirements()
    {
        $errors = [];

        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $errors[] = 'PHP 7.4+ erforderlich, aktuell: ' . PHP_VERSION;
        }

        $requiredExtensions = ['zip', 'curl', 'json'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = "PHP Extension '$ext' nicht verfügbar";
            }
        }

        $rootDir = dirname(__DIR__, 3);
        if (!is_writable($rootDir)) {
            $errors[] = 'Keine Schreibrechte im Root-Verzeichnis: ' . $rootDir;
        }

        $freeSpace = disk_free_space($rootDir);
        if ($freeSpace < 100 * 1024 * 1024) {
            $errors[] = 'Nicht genügend Speicherplatz (< 100MB verfügbar)';
        }

        if (!empty($errors)) {
            throw new Exception('Systemvoraussetzungen nicht erfüllt: ' . implode(', ', $errors));
        }

        $this->log('Systemvoraussetzungen erfüllt');
    }

    private function createBackup()
    {
        $appRoot = dirname(__DIR__, 3);
        $backupName = 'backup_' . $this->currentVersion . '_' . date('Y-m-d_H-i-s');
        $backupPath = $this->backupDir . '/' . $backupName . '.tar.gz';

        $this->log('Erstelle Backup: ' . $backupName);
        $this->log('App Root für Backup: ' . $appRoot);
        $this->log('Backup Pfad: ' . $backupPath);

        $excludePatterns = [
            '--exclude=node_modules',
            '--exclude=vendor',
            '--exclude=*.log',
            '--exclude=admin/system/updates/temp_update',
            '--exclude=admin/system/updates/backups',
            '--exclude=.git',
            '--exclude=.github',
            '--exclude=storage/logs/*',
            '--exclude=storage/cache/*'
        ];

        $command = sprintf(
            'tar %s -czf "%s" -C "%s" .',
            implode(' ', $excludePatterns),
            $backupPath,
            $appRoot
        );

        $this->log('Backup-Befehl: ' . $command);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMsg = 'Backup konnte nicht erstellt werden. Return Code: ' . $returnCode;
            if (!empty($output)) {
                $errorMsg .= '. Output: ' . implode("\n", $output);
            }
            throw new Exception($errorMsg);
        }

        if (!file_exists($backupPath)) {
            throw new Exception('Backup-Datei wurde nicht erstellt: ' . $backupPath);
        }

        $backupSize = filesize($backupPath);
        $this->log('Backup erfolgreich erstellt: ' . $backupName . ' (' . $this->formatBytes($backupSize) . ')');

        $this->cleanupOldBackups();
    }

    private function cleanupOldBackups()
    {
        $backupFiles = glob($this->backupDir . '/backup_*.tar.gz');

        if (count($backupFiles) <= 5) {
            return;
        }

        usort($backupFiles, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $toDelete = array_slice($backupFiles, 5);
        foreach ($toDelete as $file) {
            if (unlink($file)) {
                $this->log('Altes Backup gelöscht: ' . basename($file));
            }
        }
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }

    private function downloadUpdate($downloadUrl)
    {
        $headers = ['User-Agent: Update-Manager'];
        if ($this->githubToken) {
            $headers[] = 'Authorization: token ' . $this->githubToken;
        }

        $context = stream_context_create([
            'http' => [
                'header' => implode("\r\n", $headers),
                'timeout' => 300
            ]
        ]);

        $updateFile = $this->updateDir . '/update.zip';
        $data = file_get_contents($downloadUrl, false, $context);

        if ($data === false) {
            throw new Exception('Update konnte nicht heruntergeladen werden');
        }

        file_put_contents($updateFile, $data);
        $this->log('Update heruntergeladen: ' . filesize($updateFile) . ' Bytes');

        return $updateFile;
    }

    private function extractUpdate($updateFile)
    {
        $zip = new ZipArchive();
        $result = $zip->open($updateFile);

        if ($result !== TRUE) {
            throw new Exception('Update-Archiv konnte nicht geöffnet werden');
        }

        $extractPath = $this->updateDir . '/extracted';
        $zip->extractTo($extractPath);
        $zip->close();

        $dirs = glob($extractPath . '/*', GLOB_ONLYDIR);
        if (empty($dirs)) {
            throw new Exception('Ungültiges Update-Archiv');
        }

        $this->updateSourceDir = $dirs[0];
        $this->log('Update extrahiert nach: ' . $this->updateSourceDir);
    }

    private function copyFiles()
    {
        $appRoot = dirname(__DIR__, 3);

        $this->log('App Root: ' . $appRoot);
        $this->log('Update Source: ' . $this->updateSourceDir);

        $excludeFiles = [
            'assets/config/config.php',
            'assets/config/database.php',
            'admin/system/updates/version.json',
            '.env',
            '.htaccess'
        ];

        $excludeDirs = [
            'node_modules',
            'vendor',
            '.git',
            '.github',
            'backups',
            'temp_update',
            'logs'
        ];

        $updatesDir = $appRoot . '/admin/system/updates';
        $excludeDirs[] = 'admin/system/updates/backups';
        $excludeDirs[] = 'admin/system/updates/temp_update';

        $this->recursiveCopy($this->updateSourceDir, $appRoot, $excludeFiles, $excludeDirs);
        $this->log('Dateien erfolgreich kopiert von ' . $this->updateSourceDir . ' nach ' . $appRoot);
    }

    private function recursiveCopy($src, $dst, $excludeFiles = [], $excludeDirs = [])
    {
        if (!is_dir($src)) {
            throw new Exception("Quellverzeichnis existiert nicht: $src");
        }

        if (!is_dir($dst)) {
            if (!mkdir($dst, 0755, true)) {
                throw new Exception("Zielverzeichnis konnte nicht erstellt werden: $dst");
            }
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $copiedFiles = 0;
        $skippedFiles = 0;

        foreach ($iterator as $item) {
            $srcPath = $item->getPathname();
            $relativePath = substr($srcPath, strlen($src) + 1);
            $dstPath = $dst . DIRECTORY_SEPARATOR . $relativePath;

            $normalizedPath = str_replace('\\', '/', $relativePath);

            $shouldExclude = false;

            foreach ($excludeFiles as $excludeFile) {
                if ($normalizedPath === $excludeFile || basename($normalizedPath) === $excludeFile) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (!$shouldExclude) {
                $pathParts = explode('/', $normalizedPath);
                foreach ($excludeDirs as $excludeDir) {
                    if (in_array($excludeDir, $pathParts) || strpos($normalizedPath, $excludeDir . '/') === 0) {
                        $shouldExclude = true;
                        break;
                    }
                }
            }

            if ($shouldExclude) {
                $skippedFiles++;
                $this->log("Übersprungen: $normalizedPath");
                continue;
            }

            if ($item->isDir()) {
                if (!is_dir($dstPath)) {
                    if (!mkdir($dstPath, 0755, true)) {
                        throw new Exception("Verzeichnis konnte nicht erstellt werden: $dstPath");
                    }
                }
            } else {
                $dstDir = dirname($dstPath);
                if (!is_dir($dstDir)) {
                    if (!mkdir($dstDir, 0755, true)) {
                        throw new Exception("Zielverzeichnis konnte nicht erstellt werden: $dstDir");
                    }
                }

                if (!copy($srcPath, $dstPath)) {
                    throw new Exception("Datei konnte nicht kopiert werden: $srcPath -> $dstPath");
                }

                $copiedFiles++;

                chmod($dstPath, 0644);

                if ($copiedFiles % 100 === 0) {
                    $this->log("$copiedFiles Dateien kopiert...");
                }
            }
        }

        $this->log("Kopiervorgang abgeschlossen: $copiedFiles Dateien kopiert, $skippedFiles übersprungen");
    }

    private function runComposerUpdate()
    {
        $rootDir = dirname(__DIR__, 3);

        if (!file_exists($rootDir . '/composer.json')) {
            $this->log('Kein composer.json gefunden - Composer Update übersprungen');
            return;
        }

        $originalDir = getcwd();
        chdir($rootDir);
        $this->log('Arbeitsverzeichnis geändert nach: ' . getcwd());

        $envVars = [
            'COMPOSER_ROOT_VERSION=' . ltrim($this->currentVersion, 'v'),
            'COMPOSER_ALLOW_SUPERUSER=1',
            'COMPOSER_NO_INTERACTION=1',
            'COMPOSER_DISABLE_XDEBUG_WARN=1'
        ];

        $composerCommands = [
            'composer update --no-dev --optimize-autoloader',
            'php composer.phar update --no-dev --optimize-autoloader',
            '/usr/local/bin/composer update --no-dev --optimize-autoloader'
        ];

        $fallbackCommands = [
            'composer install --no-dev --optimize-autoloader',
            'php composer.phar install --no-dev --optimize-autoloader'
        ];

        if (file_exists($rootDir . '/composer.phar')) {
            array_unshift($composerCommands, 'php composer.phar update --no-dev --optimize-autoloader');
            array_unshift($fallbackCommands, 'php composer.phar install --no-dev --optimize-autoloader');
        }

        $success = false;
        $lastError = '';

        try {
            foreach ($composerCommands as $command) {
                $fullCommand = sprintf(
                    '%s %s 2>&1',
                    implode(' ', $envVars),
                    $command
                );

                $this->log('Versuche Composer UPDATE: ' . $command);

                $output = [];
                $returnCode = 0;
                exec($fullCommand, $output, $returnCode);

                $outputString = implode("\n", $output);

                if ($this->isComposerSuccessful($outputString, $returnCode)) {
                    $success = true;
                    $this->log('✓ Composer UPDATE erfolgreich: ' . $command);
                    break;
                } else {
                    $this->log('Composer UPDATE fehlgeschlagen: ' . $command);
                    $lastError = $outputString;
                }
            }

            if (!$success) {
                $this->log('UPDATE fehlgeschlagen, versuche INSTALL als Fallback...');

                foreach ($fallbackCommands as $command) {
                    $fullCommand = sprintf(
                        '%s %s 2>&1',
                        implode(' ', $envVars),
                        $command
                    );

                    $this->log('Versuche Composer INSTALL: ' . $command);

                    $output = [];
                    $returnCode = 0;
                    exec($fullCommand, $output, $returnCode);

                    $outputString = implode("\n", $output);

                    if ($this->isComposerSuccessful($outputString, $returnCode)) {
                        $success = true;
                        $this->log('✓ Composer INSTALL erfolgreich: ' . $command);
                        break;
                    } else {
                        $lastError = $outputString;
                    }
                }
            }
        } finally {
            chdir($originalDir);
        }

        if (!$success) {
            if ($this->isVendorDirectoryFunctional($rootDir)) {
                $this->log('Composer fehlgeschlagen, aber vendor-Verzeichnis ist funktional');
                return;
            }

            throw new Exception('Composer update/install fehlgeschlagen: ' . $lastError);
        }
    }

    private function isVendorDirectoryFunctional($rootDir)
    {
        $vendorDir = $rootDir . '/vendor';

        if (!is_dir($vendorDir)) {
            return false;
        }

        if (!file_exists($vendorDir . '/autoload.php')) {
            return false;
        }

        if (!is_dir($vendorDir . '/composer')) {
            return false;
        }

        return true;
    }

    private function isComposerSuccessful($output, $returnCode)
    {
        $successIndicators = [
            'Writing lock file',
            'Installing dependencies from lock file',
            'Generating optimized autoload files',
            'Nothing to install, update or remove',
            'Nothing to modify in lock file'
        ];

        $hasSuccess = false;
        foreach ($successIndicators as $indicator) {
            if (stripos($output, $indicator) !== false) {
                $hasSuccess = true;
                break;
            }
        }

        return ($returnCode === 0) || $hasSuccess;
    }

    private function updateVersion($newVersion)
    {
        $versionData = [
            'version' => $newVersion,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents(__DIR__ . '/version.json', json_encode($versionData, JSON_PRETTY_PRINT));

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO system_settings (setting_key, value) 
                VALUES ('system_version', ?) 
                ON DUPLICATE KEY UPDATE value = ?
            ");
            $stmt->execute([$newVersion, $newVersion]);
        } catch (Exception $e) {
            $this->log('Warnung: Version konnte nicht in Datenbank gespeichert werden: ' . $e->getMessage());
        }

        $this->currentVersion = $newVersion;
    }

    private function cleanup()
    {
        $this->deleteDirectory($this->updateDir);
        mkdir($this->updateDir, 0755, true);
        $this->log('Temporäre Dateien bereinigt');
    }

    private function rollback()
    {
        $this->log('Rollback würde hier ausgeführt');
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function log($message)
    {
        $logFile = __DIR__ . '/update.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO system_logs (level, message, created_at) VALUES ('info', ?, NOW())");
            $stmt->execute([$message]);
        } catch (Exception $e) {
        }
    }

    public function getUpdateInfo()
    {
        $updateInfo = $this->checkForUpdates();

        return [
            'current_version' => $this->currentVersion,
            'has_update' => $updateInfo['has_update'] ?? false,
            'latest_version' => $updateInfo['latest_version'] ?? null,
            'release_notes' => $updateInfo['release_notes'] ?? '',
            'error' => $updateInfo['error'] ?? false,
            'error_message' => $updateInfo['message'] ?? null
        ];
    }

    public function checkRequirements()
    {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'zip_extension' => extension_loaded('zip'),
            'curl_extension' => extension_loaded('curl'),
            'writable_root' => is_writable(dirname(__DIR__)),
            'git_available' => $this->isCommandAvailable('git'),
            'composer_available' => $this->isCommandAvailable('composer') || file_exists(dirname(__DIR__) . '/composer.phar')
        ];

        return $requirements;
    }

    private function isCommandAvailable($command)
    {
        $output = shell_exec("which $command 2>/dev/null");
        return !empty($output);
    }
}
