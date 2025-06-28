<?php

use App\Auth\Permissions; ?>

<!-- MODAL -->
<div class="modal fade" id="modalFDQuali" tabindex="-1" aria-labelledby="modalFDQualiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFDQualiLabel">Fachdienste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="fdqualiForm" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <?php
                        $fdqualis = json_decode($row['fachdienste'], true) ?? [];
                        if (Permissions::check(['admin', 'personnel.edit'])) {
                            $stmtfdc = $pdo->query("SELECT sgnr, sgname FROM intra_mitarbeiter_fdquali ORDER BY sgnr ASC");
                            $fachdienste = $stmtfdc->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <input type="hidden" name="new" value="4" />
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ja/Nein</th>
                                        <th colspan="2">Bezeichnung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fachdienste as $fd): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="fachdienste[]" value="<?= htmlspecialchars($fd['sgnr']) ?>"
                                                    <?php if (in_array($fd['sgnr'], $fdqualis)) echo 'checked'; ?>>
                                            </td>
                                            <td><?= htmlspecialchars($fd['sgnr']) ?></td>
                                            <td><?= htmlspecialchars($fd['sgname']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                    <?php if (Permissions::check(['admin', 'personnel.edit'])) { ?>
                        <button type="button" class="btn btn-success" id="fdq-save" onclick="document.getElementById('fdqualiForm').submit()">Speichern</button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- MODAL ENDE -->

<!-- MODAL -->
<div class="modal fade" id="modalNewComment" tabindex="-1" aria-labelledby="modalNewCommentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNewCommentLabel">Neue Notiz erstellen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newNoteForm" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" name="new" value="5" />
                        <select class="form-select mb-2" name="noteType" id="noteType">
                            <option value="0">Allgemein</option>
                            <option value="1">Positiv</option>
                            <option value="2">Negativ</option>
                        </select>
                        <textarea class="form-control" name="content" id="content" rows="3" placeholder="Notiztext" style="resize:none"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                    <?php if (Permissions::check(['admin', 'personnel.view'])) { ?>
                        <button type="button" class="btn btn-success" id="fdq-save" onclick="document.getElementById('newNoteForm').submit()">Speichern</button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- MODAL ENDE -->

<!-- MODAL -->
<?php if (Permissions::check(['admin', 'personnel.delete'])) { ?>
    <div class="modal fade" id="modalPersoDelete" tabindex="-1" aria-labelledby="modalPersoDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPersoDeleteLabel">Mitarbeiterakte löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newNoteForm" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <p>Die Mitarbeiterakte von <strong><?= $row['fullname'] ?></strong> wird mit der Bestätigung <strong>unwiderruflich gelöscht</strong>. Es ist nicht möglich diese im Nachhinein wiederherzustellen.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <a href="<?= BASE_PATH ?>admin/personal/delete.php?id=<?= $row['id'] ?>" type="button" class="btn btn-danger" id="complete-delete">Endgültig löschen</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>
<!-- MODAL ENDE -->

<!-- MODAL -->
<?php if (Permissions::check(['admin', 'personnel.documents.manage'])) { ?>
    <div class="modal fade" id="modalDokuCreate" tabindex="-1" aria-labelledby="modalDokuCreateLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDokuCreateLabel">Dokument anlegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newDocForm" method="post">
                    <div class="modal-body">
                        <?php if (!$editdg) { ?>
                            <div class="alert alert-danger" role="alert">
                                <h4 class="fw-bold">Achtung!</h4> Es sind keine Profildaten hinterlegt. Dokumente können Fehlerhaft sein.<br>Bitte erstelle erst ein <a href="<?= BASE_PATH ?>admin/personal/create.php">eigenes Mitarbeiterprofil</a> (mit deiner Discord-ID).
                            </div>
                        <?php } ?>
                        <div class="mb-3">
                            <input type="hidden" name="new" value="6" />
                            <input type="hidden" name="erhalter" value="<?= $row['fullname'] ?>" />
                            <input type="hidden" name="erhalter_gebdat" value="<?= $row['gebdatum'] ?>" />
                            <input type="hidden" name="ausstellerid" value="<?= $_SESSION['discordtag'] ?>" />
                            <input type="hidden" name="aussteller_name" value="<?= $edituseric ?>" />
                            <input type="hidden" name="aussteller_rang" value="<?= $editdg ?>" />
                            <input type="hidden" name="profileid" value="<?= $openedID ?>" />
                            <label for="docType">Dokumenten-Typ</label>
                            <select class="form-select mb-2" name="docType" id="docType">
                                <option disabled hidden selected>Bitte wählen</option>
                                <option value="0">Ernennungsurkunde</option>
                                <option value="1">Beförderungsurkunde</option>
                                <option value="2">Entlassungsurkunde</option>
                                <!-- <option value="3">Ausbildungsvertrag</option> -->
                                <option value="5">Ausbildungszertifikat</option>
                                <option value="6">Lehrgangszertifikat</option>
                                <option value="7">Lehrgangszertifikat (Fachdienste)</option>
                                <option value="10">Schriftliche Abmahnung</option>
                                <option value="11">Vorläufige Dienstenthebung</option>
                                <option value="12">Dienstentfernung</option>
                                <option value="13">Außerordentliche Kündigung</option>
                            </select>
                            <hr>
                            <div id="form-0" style="display: none;">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $stmt = $pdo->prepare("SELECT id,name,priority FROM intra_mitarbeiter_dienstgrade WHERE archive = 0 ORDER BY priority ASC");
                                $stmt->execute();
                                $dgsel = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $stmt2 = $pdo->prepare("SELECT id,name,priority FROM intra_mitarbeiter_rdquali WHERE trainable = 1 ORDER BY priority ASC");
                                $stmt2->execute();
                                $rddgsel = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <label for="erhalter_rang">Neuer Dienstgrad</label>
                                <select class="form-select" name="erhalter_rang" id="erhalter_rang">
                                    <option disabled hidden selected>Bitte wählen</option>
                                    <?php foreach ($dgsel as $data) {
                                        echo "<option value='{$data['id']}'>{$data['name']}</option>";
                                    } ?>
                                </select>
                                <label for="ausstellungsdatum_0">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_0" id="ausstellungsdatum_0" class="form-control">
                            </div>
                            <div id="form-1" style="display: none;">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <label for="ausstellungsdatum_2">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_2" id="ausstellungsdatum_2" class="form-control">
                            </div>
                            <div id="form-2" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <label for="erhalter_rang_rd">Qualifikation</label>
                                <select class="form-select" name="erhalter_rang_rd" id="erhalter_rang_rd">
                                    <option disabled hidden selected>Bitte wählen</option>
                                    <?php foreach ($rddgsel as $data2) {
                                        echo "<option value='{$data2['id']}'>{$data2['name']}</option>";
                                    } ?>
                                </select>
                                <label for="ausstellungsdatum_5">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_5" id="ausstellungsdatum_5" class="form-control">
                            </div>
                            <div id="form-3" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $qoptions = [
                                    4 => 'Sonderfahrzeug-Maschinist/-in',
                                    2 => 'Zugführer/-in',
                                    1 => 'Gruppenführer/-in',
                                    0 => 'Brandmeister/-in',
                                ];
                                ?>
                                <label for="erhalter_quali">Qualifikation</label>
                                <select class="form-select" name="erhalter_quali" id="erhalter_quali">
                                    <option disabled hidden selected>Bitte wählen</option>
                                    <?php foreach ($qoptions as $qvalue => $qlabel) : ?>
                                        <option value="<?php echo $qvalue; ?>">
                                            <?php echo $qlabel; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ausstellungsdatum_6">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_6" id="ausstellungsdatum_6" class="form-control">
                            </div>
                            <div id="form-7" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $qoptions2 = [
                                    9 => 'Luftrettungspilot/-in',
                                    8 => 'HEMS-TC',
                                    3 => 'Leitstellen-Disponent/-in',
                                    5 => 'Helfergrundmodul (SEG)',
                                    6 => 'SEG-Sanitäter/-in',
                                    7 => 'Gruppenführer/-in-BevS',
                                ];
                                ?>
                                <label for="erhalter_quali">Qualifikation</label>
                                <select class="form-select" name="erhalter_quali" id="erhalter_quali">
                                    <option disabled hidden selected>Bitte wählen</option>
                                    <?php foreach ($qoptions2 as $qvalue2 => $qlabel2) : ?>
                                        <option value="<?php echo $qvalue2; ?>">
                                            <?php echo $qlabel2; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ausstellungsdatum_7">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_7" id="ausstellungsdatum_7" class="form-control">
                            </div>
                            <div id="form-4" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <label for="ausstellungsdatum_10">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_10" id="ausstellungsdatum_10" class="form-control">
                                <div id="form-5" style="display:none">
                                    <label for="suspendtime">Suspendiert bis <small>(leer lassen für unbestimmt)</small></label>
                                    <input type="date" name="suspendtime" id="suspendtime" class="form-control">
                                </div>
                                <label for="inhalt">Begründung</label>
                                <textarea name="inhalt" id="inhalt" style="resize:none"></textarea>
                            </div>
                            <div id="form-6" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $rdoptions2 = [
                                    2 => 'Notfallsanitäter/-in',
                                    1 => 'Rettungssanitäter/-in',
                                    0 => 'Rettungssanitäter/-in in Ausbildung',
                                ];
                                ?>
                                <label for="erhalter_rang_rd_2">Qualifikation</label>
                                <select class="form-select" name="erhalter_rang_rd_2" id="erhalter_rang_rd_2">
                                    <option disabled hidden selected>Bitte wählen</option>
                                    <?php foreach ($rdoptions2 as $rdvalue => $rdlabel) : ?>
                                        <option value="<?php echo $rdvalue; ?>">
                                            <?php echo $rdlabel; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ausstellungsdatum_3">Ausstellungsdatum</label>
                                <input type="date" name="ausstellungsdatum_3" id="ausstellungsdatum_3" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="button" class="btn btn-success" id="fdq-save" onclick="document.getElementById('newDocForm').submit()">Erstellen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const docTypeSelect = document.getElementById('docType');
        const form0 = document.getElementById('form-0');
        const form1 = document.getElementById('form-1');
        const form2 = document.getElementById('form-2');
        const form3 = document.getElementById('form-3');
        const form4 = document.getElementById('form-4');
        const form5 = document.getElementById('form-5');
        const form6 = document.getElementById('form-6');
        const form7 = document.getElementById('form-7');

        docTypeSelect.addEventListener('change', function() {
            const selectedValue = docTypeSelect.value;

            if (selectedValue === '0' ||
                selectedValue === '1') {
                form0.style.display = 'block';
                form1.style.display = 'none';
                form2.style.display = 'none';
                form3.style.display = 'none';
                form4.style.display = 'none';
                form5.style.display = 'none';
                form6.style.display = 'none';
                form7.style.display = 'none';
            } else if (selectedValue === '2') {
                form0.style.display = 'none';
                form1.style.display = 'block';
                form2.style.display = 'none';
                form3.style.display = 'none';
                form4.style.display = 'none';
                form5.style.display = 'none';
                form6.style.display = 'none';
                form7.style.display = 'none';
            } else if (selectedValue === '3') {
                form0.style.display = 'none';
                form1.style.display = 'none';
                form2.style.display = 'none';
                form3.style.display = 'none';
                form4.style.display = 'none';
                form5.style.display = 'none';
                form6.style.display = 'block';
                form7.style.display = 'none';
            } else if (selectedValue === '5') {
                form0.style.display = 'none';
                form1.style.display = 'none';
                form2.style.display = 'block';
                form3.style.display = 'none';
                form4.style.display = 'none';
                form5.style.display = 'none';
                form6.style.display = 'none';
                form7.style.display = 'none';
            } else if (selectedValue === '6') {
                form0.style.display = 'none';
                form1.style.display = 'none';
                form2.style.display = 'none';
                form3.style.display = 'block';
                form4.style.display = 'none';
                form5.style.display = 'none';
                form6.style.display = 'none';
                form7.style.display = 'none';
            } else if (selectedValue === '7') {
                form0.style.display = 'none';
                form1.style.display = 'none';
                form2.style.display = 'none';
                form3.style.display = 'none';
                form4.style.display = 'none';
                form5.style.display = 'none';
                form6.style.display = 'none';
                form7.style.display = 'block';
            } else if (selectedValue === '10' || selectedValue === '11' || selectedValue === '12' || selectedValue === '13') {
                form0.style.display = 'none';
                form1.style.display = 'none';
                form2.style.display = 'none';
                form3.style.display = 'none';
                form4.style.display = 'block';
                if (selectedValue === '11') {
                    form5.style.display = 'block';
                } else {
                    form5.style.display = 'none';
                }
                form6.style.display = 'none';
            }
        });
    </script>
    <script type="importmap">
        {
			"imports": {
				"ckeditor5": "<?= BASE_PATH ?>assets/_ext/ckeditor5/ckeditor5.js",
				"ckeditor5/": "<?= BASE_PATH ?>assets/_ext/ckeditor5/"
			}
		}
		</script>
    <script src="<?= BASE_PATH ?>assets/_ext/ckeditor5/ckeditor5.js"></script>
    <script type="module" src="<?= BASE_PATH ?>assets/js/ckmain.js"></script>
<?php } ?>
<!-- MODAL ENDE -->