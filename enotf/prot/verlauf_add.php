<?php
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_samesite', 'None');
    ini_set('session.cookie_secure', '1');
}

session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

use App\Auth\Permissions;

$daten = array();
$message = '';
$messageType = '';

if (isset($_GET['enr'])) {
    $queryget = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($queryget);
    $stmt->execute(['enr' => $_GET['enr']]);
    $daten = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($daten) == 0) {
        header("Location: " . BASE_PATH . "enotf/");
        exit();
    }
} else {
    header("Location: " . BASE_PATH . "enotf/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

// Prüfung ob freigegeben
if ($ist_freigegeben) {
    header("Location: verlauf.php?enr=" . $_GET['enr']);
    exit();
}

$enr = $daten['enr'];

// Form-Verarbeitung - GEÄNDERT: Einzelne Werte separat speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_vitals'])) {
    try {
        $zeitpunkt = $_POST['zeitpunkt'];
        $erstellt_von = $_SESSION['username'] ?? 'Unbekannt';
        $gespeicherte_werte = 0;

        // Array der möglichen Vitalparameter
        $vitalparameter = [
            'spo2' => ['name' => 'SpO₂', 'einheit' => '%'],
            'atemfreq' => ['name' => 'Atemfrequenz', 'einheit' => '/min'],
            'etco2' => ['name' => 'etCO₂', 'einheit' => 'mmHg'],
            'herzfreq' => ['name' => 'Herzfrequenz', 'einheit' => '/min'],
            'rrsys' => ['name' => 'RR systolisch', 'einheit' => 'mmHg'],
            'rrdias' => ['name' => 'RR diastolisch', 'einheit' => 'mmHg'],
            'bz' => ['name' => 'Blutzucker', 'einheit' => 'mg/dl'],
            'temp' => ['name' => 'Temperatur', 'einheit' => '°C']
        ];

        // Prepare Statement für Einzelwerte
        $query = "INSERT INTO intra_edivi_vitalparameter_einzelwerte (enr, zeitpunkt, parameter_name, parameter_wert, parameter_einheit, erstellt_von) 
                  VALUES (:enr, :zeitpunkt, :parameter_name, :parameter_wert, :parameter_einheit, :erstellt_von)";
        $stmt = $pdo->prepare($query);

        // Jeden Vitalparameter einzeln speichern (nur wenn Wert vorhanden)
        foreach ($vitalparameter as $param_key => $param_info) {
            if (!empty($_POST[$param_key])) {
                $wert = $_POST[$param_key];

                $result = $stmt->execute([
                    'enr' => $enr,
                    'zeitpunkt' => $zeitpunkt,
                    'parameter_name' => $param_info['name'],
                    'parameter_wert' => $wert,
                    'parameter_einheit' => $param_info['einheit'],
                    'erstellt_von' => $erstellt_von
                ]);

                if ($result) {
                    $gespeicherte_werte++;
                }
            }
        }

        // Bemerkung separat speichern (falls vorhanden)
        if (!empty($_POST['bemerkung'])) {
            $result = $stmt->execute([
                'enr' => $enr,
                'zeitpunkt' => $zeitpunkt,
                'parameter_name' => 'Bemerkung',
                'parameter_wert' => $_POST['bemerkung'],
                'parameter_einheit' => '',
                'erstellt_von' => $erstellt_von
            ]);

            if ($result) {
                $gespeicherte_werte++;
            }
        }

        if ($gespeicherte_werte > 0) {
            header("Location: verlauf.php?enr=" . $enr);
            exit();
        } else {
            $message = 'Keine Werte zum Speichern gefunden oder Fehler beim Speichern.';
            $messageType = 'warning';
        }
    } catch (Exception $e) {
        $message = 'Fehler: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$prot_url = "https://" . SYSTEM_URL . "/enotf/prot/index.php?enr=" . $enr;

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');
$currentDateTime = date('Y-m-d\TH:i');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/divi.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] Vitalparameter hinzufügen &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body data-page="verlauf">
    <div class="container-fluid" id="edivi__container">
        <div class="row h-100">
            <div class="col" id="edivi__content">
                <!-- Erfolg/Fehler-Meldung -->
                <?php if (!empty($message)): ?>
                    <div class="row mb-3">
                        <div class="col">
                            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="vitalsForm" method="post" action="">
                    <input type="hidden" name="save_vitals" value="1" />
                    <input type="hidden" name="zeitpunkt" id="zeitpunkt" value="<?= $currentDateTime ?>" required>
                    <div class="row">
                        <div class="col position-relative">
                            <div class="row my-3">
                                <div class="col edivi__vitalparam-box" data-before="SpO₂" data-after="%">
                                    <input type="number" name="spo2" id="spo2" class="form-control edivi__vitalparam keypad-input" min="0" max="100" placeholder="96">
                                </div>
                                <div class="col edivi__vitalparam-box" data-before="AF" data-after="/min">
                                    <input type="number" name="atemfreq" id="atemfreq" class="form-control edivi__vitalparam keypad-input" min="0" max="40" placeholder="16">
                                </div>
                                <div class="col edivi__vitalparam-box" data-before="etCO₂" data-after="mmHg">
                                    <input type="number" name="etco2" id="etco2" class="form-control edivi__vitalparam keypad-input" min="0" max="100" placeholder="35">
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col edivi__vitalparam-box" data-before="HF" data-after="/min">
                                    <input type="number" name="herzfreq" id="herzfreq" class="form-control edivi__vitalparam keypad-input" min="0" max="300" placeholder="80">
                                </div>
                                <div class="col edivi__vitalparam-box" data-before="NIBP/RR" data-after="mmHg">
                                    <input type="number" name="rrsys" id="rrsys" class="form-control edivi__vitalparam-shared keypad-input" min="0" max="300" placeholder="120" style="border-right:0!important">
                                    <div class="edivi_vitalparam-spacer">/</div>
                                    <input type="number" name="rrdias" id="rrdias" class="form-control edivi__vitalparam-shared keypad-input" min="0" max="300" placeholder="80" style="border-left:0!important">
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col edivi__vitalparam-box" data-before="BZ" data-after="mg/dl">
                                    <input type="number" name="bz" id="bz" class="form-control edivi__vitalparam keypad-input" min="0" max="700" placeholder="90">
                                </div>
                                <div class="col edivi__vitalparam-box" data-before="Temperatur" data-after="°C">
                                    <input type="number" name="temp" id="temp" class="form-control edivi__vitalparam keypad-input" min="10" max="45" placeholder="36,5">
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col edivi__vitalparam-box">
                                    <textarea name="bemerkung" id="bemerkung" rows="3"
                                        class="form-control edivi__vitalparam"
                                        placeholder="Optionale Bemerkung" style="color:#fff !important"></textarea>
                                </div>
                            </div>
                            <div class="row edivi__vitalparam-mainbuttons">
                                <div class="col">
                                    <a href="verlauf.php?enr=<?= $enr ?>">Abbrechen</a>
                                </div>
                                <div class="col" style="border-left: 2px solid #191919;">
                                    <button type="submit" form="vitalsForm">
                                        Speichern
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="keypad-container">
                                <!-- Zahlen-Tastenfeld -->
                                <div class="keypad-grid">
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('7')">7</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('8')">8</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('9')">9</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('4')">4</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('5')">5</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('6')">6</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('1')">1</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('2')">2</button>
                                    <button type="button" class="keypad-btn" onclick="keypadAddDigit('3')">3</button>
                                    <button type="button" class="keypad-btn wide" onclick="keypadAddDigit('0')">0</button>
                                    <button type="button" class="keypad-btn special" onclick="keypadAddDigit(',')">,</button>
                                </div>

                                <!-- Untere Funktions-Buttons -->
                                <div class="function-grid">
                                    <button type="button" class="keypad-btn danger" onclick="keypadClearField()">
                                        Löschen
                                    </button>
                                    <button type="button" class="keypad-btn danger" onclick="keypadBackspace()">⌫</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Keypad Funktionalität
        let keypadCurrentField = null;

        // Feld-Namen Mapping für Anzeige
        const fieldNames = {
            'spo2': 'SpO₂ (%)',
            'atemfreq': 'Atemfrequenz (/min)',
            'etco2': 'etCO₂ (mmHg)',
            'herzfreq': 'Herzfrequenz (/min)',
            'rrsys': 'RR systolisch (mmHg)',
            'rrdias': 'RR diastolisch (mmHg)',
            'bz': 'Blutzucker (mg/dl)',
            'temp': 'Temperatur (°C)'
        };

        // Event Listener für Eingabefelder - nach dem DOM geladen ist
        document.addEventListener('DOMContentLoaded', function() {
            // Keypad Event Listener
            const keypadInputs = document.querySelectorAll('.keypad-input');
            keypadInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    selectField(this);
                });
                input.addEventListener('click', function() {
                    selectField(this);
                });
            });

            // Bestehende Validierung für Vitalparameter - funktioniert für alle Eingabearten
            const vitalInputs = document.querySelectorAll('.edivi__vitalparam, .edivi__vitalparam-shared');
            vitalInputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateField(this);
                });
            });
        });

        // Feld-Validierung (für alle Eingabearten)
        function validateField(field) {
            const value = parseFloat(field.value.replace(',', '.'));
            const fieldName = field.name;

            // Alle Klassen zurücksetzen
            field.classList.remove('text-warning', 'text-danger', 'text-success');

            if (isNaN(value) || field.value === '') {
                return; // Kein Wert oder ungültiger Wert
            }

            let isWarning = false;
            let isDanger = false;
            let isSuccess = false;

            // Feldspezifische Validierung
            switch (fieldName) {
                case 'spo2':
                    if (value < 88 || value > 100) {
                        isDanger = true;
                    } else if (value < 95) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'atemfreq':
                    if (value < 9 || value > 27) {
                        isDanger = true;
                    } else if (value < 12 || value > 20) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'etco2':
                    if (value < 25 || value > 50) {
                        isDanger = true;
                    } else if (value < 33 || value > 43) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'rrsys':
                    if (value < 81 || value > 179) {
                        isDanger = true;
                    } else if (value < 110 || value > 139) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'rrdias':
                    if (value < 51 || value > 119) {
                        isDanger = true;
                    } else if (value < 80 || value > 99) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'herzfreq':
                    if (value < 51 || value > 300) {
                        isDanger = true;
                    } else if (value < 61 || value > 99) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'bz':
                    if (value < 61 || value > 199) {
                        isDanger = true;
                    } else if (value < 71 || value > 139) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
                case 'temp':
                    if (value < 35 || value > 40) {
                        isDanger = true;
                    } else if (value < 36.1 || value > 37.5) {
                        isWarning = true;
                    } else {
                        isSuccess = true;
                    }
                    break;
            }

            if (isDanger) {
                field.classList.add('text-danger');
            } else if (isWarning) {
                field.classList.add('text-warning');
            } else if (isSuccess) {
                field.classList.add('text-success');
            }
        }

        // Feld auswählen
        function selectField(field) {
            // Alle aktiven Markierungen entfernen
            document.querySelectorAll('.keypad-input').forEach(input => {
                input.classList.remove('active-field');
                // Zurück zu number type setzen
                if (input.dataset.originalType) {
                    input.type = input.dataset.originalType;
                    delete input.dataset.originalType;
                }
            });

            // Neues Feld markieren
            field.classList.add('active-field');
            keypadCurrentField = field;

            // Field type auf text setzen um Kommas zu erlauben
            if (field.type === 'number') {
                field.dataset.originalType = 'number';
                field.type = 'text';
            }

            // Cursor ans Ende setzen
            setTimeout(() => {
                const length = field.value.length;
                field.setSelectionRange(length, length);
                field.focus();
            }, 0);

            // Feld-Info aktualisieren
            updateFieldInfo();
        }

        // Feld-Info aktualisieren
        function updateFieldInfo() {
            const info = document.getElementById('fieldInfo');
            if (keypadCurrentField && info) {
                const fieldName = fieldNames[keypadCurrentField.id] || keypadCurrentField.id;
                info.textContent = `Aktives Feld: ${fieldName}`;
            } else if (info) {
                info.textContent = 'Feld auswählen für Eingabe';
            }
        }

        // Ziffer direkt hinzufügen - sofortige Übertragung
        function keypadAddDigit(digit) {
            if (!keypadCurrentField) {
                alert('Bitte wählen Sie zuerst ein Eingabefeld aus.');
                return;
            }

            let currentValue = keypadCurrentField.value || '';

            // Komma-Behandlung
            if (digit === ',') {
                if (currentValue.includes(',')) {
                    return; // Nur ein Komma erlaubt
                }
                if (currentValue === '') {
                    currentValue = '0,';
                } else {
                    currentValue += ',';
                }
            } else {
                // Normale Ziffer hinzufügen
                if (currentValue.includes(',')) {
                    // Es gibt bereits ein Komma
                    const parts = currentValue.split(',');
                    const nachKomma = parts[1] || '';

                    if (nachKomma.length >= 2) {
                        return; // Maximal 2 Nachkommastellen
                    }

                    // Ziffer nach dem Komma hinzufügen
                    currentValue = parts[0] + ',' + nachKomma + digit;
                } else {
                    // Kein Komma vorhanden
                    if (currentValue.length >= 3) {
                        return; // Maximal 3 Stellen vor dem Komma
                    }

                    // Ziffer vor dem Komma hinzufügen
                    currentValue += digit;
                }
            }

            // Sofort ins Feld übertragen
            keypadUpdateFieldValue(currentValue);
        }

        // Rückgängig/Löschen - sofortige Übertragung
        function keypadBackspace() {
            if (!keypadCurrentField) {
                return;
            }

            let currentValue = keypadCurrentField.value || '';
            if (currentValue.length > 0) {
                currentValue = currentValue.slice(0, -1);
                keypadUpdateFieldValue(currentValue);
            }
        }

        // Aktuelles Feld komplett löschen
        function keypadClearField() {
            if (keypadCurrentField) {
                keypadUpdateFieldValue('');
            }
        }

        // Wert direkt ins Feld setzen
        function keypadUpdateFieldValue(value) {
            if (!keypadCurrentField) return;

            const fieldId = keypadCurrentField.id;

            if (value === '') {
                keypadCurrentField.value = '';
                keypadCurrentField.classList.remove('text-danger', 'text-warning', 'text-success');
                keypadCurrentField.dispatchEvent(new Event('input'));
                return;
            }

            // Komma für Validierung in Punkt umwandeln, aber echten Wert mit Komma im Feld belassen
            const numericValue = parseFloat(value.replace(',', '.'));

            if (!isNaN(numericValue)) {
                // Feldspezifische Grundvalidierung (nur für unmögliche Werte)
                let isValid = true;

                switch (fieldId) {
                    case 'spo2':
                        isValid = numericValue >= 0 && numericValue <= 100;
                        break;
                    case 'atemfreq':
                        isValid = numericValue >= 0 && numericValue <= 40;
                        break;
                    case 'etco2':
                        isValid = numericValue >= 0 && numericValue <= 100;
                        break;
                    case 'herzfreq':
                        isValid = numericValue >= 0 && numericValue <= 300;
                        break;
                    case 'rrsys':
                    case 'rrdias':
                        isValid = numericValue >= 0 && numericValue <= 300;
                        break;
                    case 'bz':
                        isValid = numericValue >= 0 && numericValue <= 700;
                        break;
                    case 'temp':
                        isValid = numericValue >= 10 && numericValue <= 45;
                        break;
                }

                // Wert immer setzen, Validierung erfolgt über Event-Handler
                keypadCurrentField.value = value;

                // Event-Handler auslösen für detaillierte Validierung (Warning/Danger/Success)
                keypadCurrentField.dispatchEvent(new Event('input'));
            }
        }

        // Hilfsfunktionen für Kompatibilität mit bestehenden Includes
        function setCurrentTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            const dateTimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
            const zeitpunktElement = document.getElementById('zeitpunkt');
            if (zeitpunktElement) {
                zeitpunktElement.value = dateTimeString;
            }
        }
    </script>
</body>

</html>