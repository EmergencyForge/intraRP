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

        $this->ensureDirectories();
    }

    private function ensureDirectories()
    {
        if (!is_dir($this->updateDir)) {
            mkdir($this->updateDir, 0755, true);
        }
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
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

            $this->log('Update gestartet: ' . $updateInfo['latest_version']);

            $this->createBackup();
            $updateFile = $this->downloadUpdate($updateInfo['download_url']);
            $this->extractUpdate($updateFile);
            $this->copyFiles();
            $this->runComposerUpdate();
            $this->updateVersion($updateInfo['latest_version']);
            $this->cleanup();

            $this->log('Update erfolgreich abgeschlossen: ' . $updateInfo['latest_version']);

            return [
                'success' => true,
                'message' => 'Update erfolgreich installiert',
                'new_version' => $updateInfo['latest_version']
            ];
        } catch (Exception $e) {
            $this->log('Update fehlgeschlagen: ' . $e->getMessage());
            $this->rollback();

            return [
                'success' => false,
                'message' => 'Update fehlgeschlagen: ' . $e->getMessage()
            ];
        }
    }

    private function createBackup()
    {
        $backupName = 'backup_' . $this->currentVersion . '_' . date('Y-m-d_H-i-s');
        $backupPath = $this->backupDir . '/' . $backupName . '.tar.gz';

        $excludePatterns = [
            '--exclude=node_modules',
            '--exclude=vendor',
            '--exclude=*.log',
            '--exclude=temp_update',
            '--exclude=backups',
            '--exclude=.git'
        ];

        $command = 'tar ' . implode(' ', $excludePatterns) . ' -czf "' . $backupPath . '" -C "' . dirname(__DIR__) . '" .';
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception('Backup konnte nicht erstellt werden');
        }

        $this->log('Backup erstellt: ' . $backupName);
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
        $excludeFiles = [
            'assets/config/config.php',
            'assets/config/database.php',
            '.env',
            'version.json'
        ];

        $excludeDirs = [
            'node_modules',
            'vendor',
            'backups',
            'temp_update',
            '.git'
        ];

        $this->recursiveCopy($this->updateSourceDir, dirname(__DIR__), $excludeFiles, $excludeDirs);
        $this->log('Dateien kopiert');
    }

    private function recursiveCopy($src, $dst, $excludeFiles = [], $excludeDirs = [])
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $src . '/' . $file;
                $dstPath = $dst . '/' . $file;
                $relativePath = str_replace(dirname(__DIR__) . '/', '', $dstPath);

                if (in_array($relativePath, $excludeFiles) || in_array($file, $excludeDirs)) {
                    continue;
                }

                if (is_dir($srcPath)) {
                    $this->recursiveCopy($srcPath, $dstPath, $excludeFiles, $excludeDirs);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
        closedir($dir);
    }

    private function runComposerUpdate()
    {
        $composerPath = dirname(__DIR__) . '/composer.phar';

        $composerCommands = [
            'composer',
            'php composer.phar',
            '/usr/local/bin/composer',
            'php ' . $composerPath
        ];

        $success = false;
        foreach ($composerCommands as $command) {
            $fullCommand = 'cd "' . dirname(__DIR__) . '" && ' . $command . ' update --no-dev --optimize-autoloader 2>&1';
            exec($fullCommand, $output, $returnCode);

            if ($returnCode === 0) {
                $success = true;
                $this->log('Composer update erfolgreich: ' . $command);
                break;
            }
        }

        if (!$success) {
            $this->log('Composer update fehlgeschlagen. Output: ' . implode("\n", $output));
            throw new Exception('Composer update fehlgeschlagen');
        }
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
