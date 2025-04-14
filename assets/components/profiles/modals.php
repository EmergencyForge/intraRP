<?php

use App\Auth\Permissions; ?>

<!-- MODAL -->
<div class="modal fade" id="modalFDQuali" tabindex="-1" aria-labelledby="modalFDQualiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFDQualiLabel"><?= __('personnel.modals.specialities.title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="fdqualiForm" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <?php
                        $fdqualis = json_decode($row['fachdienste'], true) ?? [];
                        if (Permissions::check(['admin', 'personnel.edit'])) { ?>
                            <input type="hidden" name="new" value="4" />
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('personnel.modals.specialities.yes_no') ?></th>
                                        <th colspan="2"><?= __('personnel.modals.specialities.name') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="211" <?php if (in_array('211', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>211</td>
                                        <td><?= __('personnel.specialities.names.211') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="212" <?php if (in_array('212', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>212</td>
                                        <td><?= __('personnel.specialities.names.212') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="213" <?php if (in_array('213', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>213</td>
                                        <td><?= __('personnel.specialities.names.213') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="221" <?php if (in_array('221', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>221</td>
                                        <td><?= __('personnel.specialities.names.221') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="222" <?php if (in_array('222', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>222</td>
                                        <td><?= __('personnel.specialities.names.222') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="223" <?php if (in_array('223', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>223</td>
                                        <td><?= __('personnel.specialities.names.223') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="231" <?php if (in_array('231', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>231</td>
                                        <td><?= __('personnel.specialities.names.231') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="232" <?php if (in_array('232', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>232</td>
                                        <td><?= __('personnel.specialities.names.232') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="233" <?php if (in_array('233', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>233</td>
                                        <td><?= __('personnel.specialities.names.233') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="411" <?php if (in_array('411', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>411</td>
                                        <td><?= __('personnel.specialities.names.411') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="412" <?php if (in_array('412', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>412</td>
                                        <td><?= __('personnel.specialities.names.412') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="413" <?php if (in_array('413', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>413</td>
                                        <td><?= __('personnel.specialities.names.413') ?></td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" name="fachdienste[]" value="414" <?php if (in_array('414', $fdqualis)) echo 'checked'; ?>></td>
                                        <td>414</td>
                                        <td><?= __('personnel.specialities.names.414') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('personnel.modals.close') ?></button>
                    <?php if (Permissions::check(['admin', 'personnel.edit'])) { ?>
                        <button type="button" class="btn btn-success" id="fdq-save" onclick="document.getElementById('fdqualiForm').submit()"><?= __('personnel.modals.save') ?></button>
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
                <h5 class="modal-title" id="modalNewCommentLabel"><?= __('personnel.modals.notes.title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newNoteForm" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" name="new" value="5" />
                        <select class="form-select mb-2" name="noteType" id="noteType">
                            <option value="0"><?= __('personnel.modals.notes.options.0') ?></option>
                            <option value="1"><?= __('personnel.modals.notes.options.1') ?></option>
                            <option value="2"><?= __('personnel.modals.notes.options.2') ?></option>
                        </select>
                        <textarea class="form-control" name="content" id="content" rows="3" placeholder="<?= __('personnel.modals.notes.note_text') ?>" style="resize:none"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('personnel.modals.close') ?></button>
                    <?php if (Permissions::check(['admin', 'personnel.view'])) { ?>
                        <button type="button" class="btn btn-success" id="fdq-save" onclick="document.getElementById('newNoteForm').submit()"><?= __('personnel.modals.save') ?></button>
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
                            <p><?= __('personnel.modals.delete.text', [$row['fullname']]) ?></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('personnel.modals.delete.abort') ?></button>
                        <a href="/admin/personal/delete.php?id=<?= $row['id'] ?>" type="button" class="btn btn-danger" id="complete-delete"><?= __('personnel.modals.delete.confirm') ?></a>
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
                    <h5 class="modal-title" id="modalDokuCreateLabel"><?= __('personnel.modals.documents.title') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newDocForm" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="hidden" name="new" value="6" />
                            <input type="hidden" name="erhalter" value="<?= $row['fullname'] ?>" />
                            <input type="hidden" name="erhalter_gebdat" value="<?= $row['gebdatum'] ?>" />
                            <input type="hidden" name="ausstellerid" value="<?= $userid ?>" />
                            <input type="hidden" name="aussteller_name" value="<?= $edituseric ?>" />
                            <input type="hidden" name="aussteller_rang" value="<?= $editdg ?>" />
                            <input type="hidden" name="profileid" value="<?= $openedID ?>" />
                            <label for="docType"><?= __('personnel.modals.documents.type') ?></label>
                            <select class="form-select mb-2" name="docType" id="docType">
                                <option disabled hidden selected><?= __('personnel.modals.documents.please_select') ?></option>
                                <option value="0"><?= __('personnel.profile.documents.types.0') ?></option>
                                <option value="1"><?= __('personnel.profile.documents.types.1') ?></option>
                                <option value="2"><?= __('personnel.profile.documents.types.2') ?></option>
                                <!-- <option value="3">Ausbildungsvertrag</option> -->
                                <option value="5"><?= __('personnel.profile.documents.types.5') ?></option>
                                <option value="6"><?= __('personnel.profile.documents.types.6') ?></option>
                                <option value="7"><?= __('personnel.profile.documents.types.7') ?></option>
                                <option value="10"><?= __('personnel.profile.documents.types.10') ?></option>
                                <option value="11"><?= __('personnel.profile.documents.types.11') ?></option>
                                <option value="12"><?= __('personnel.profile.documents.types.12') ?></option>
                                <option value="13"><?= __('personnel.profile.documents.types.13') ?></option>
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
                                <label for="erhalter_rang"><?= __('personnel.modals.documents.new_rank') ?></label>
                                <select class="form-select" name="erhalter_rang" id="erhalter_rang">
                                    <option disabled hidden selected><?= __('personnel.modals.documents.please_select') ?></option>
                                    <?php foreach ($dgsel as $data) {
                                        echo "<option value='{$data['id']}'>{$data['name']}</option>";
                                    } ?>
                                </select>
                                <label for="ausstellungsdatum_0"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_0" id="ausstellungsdatum_0" class="form-control">
                            </div>
                            <div id="form-1" style="display: none;">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <label for="ausstellungsdatum_2"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_2" id="ausstellungsdatum_2" class="form-control">
                            </div>
                            <div id="form-2" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <label for="erhalter_rang_rd"><?= __('personnel.modals.documents.qualification') ?></label>
                                <select class="form-select" name="erhalter_rang_rd" id="erhalter_rang_rd">
                                    <option disabled hidden selected><?= __('personnel.modals.documents.please_select') ?></option>
                                    <?php foreach ($rddgsel as $data2) {
                                        echo "<option value='{$data2['id']}'>{$data2['name']}</option>";
                                    } ?>
                                </select>
                                <label for="ausstellungsdatum_5"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_5" id="ausstellungsdatum_5" class="form-control">
                            </div>
                            <div id="form-3" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $qoptions = larray('personnel.modals.documents.qualification_options');
                                ?>
                                <label for="erhalter_quali"><?= __('personnel.modals.documents.qualification') ?></label>
                                <select class="form-select" name="erhalter_quali" id="erhalter_quali">
                                    <option disabled hidden selected><?= __('personnel.modals.documents.please_select') ?></option>
                                    <?php foreach ($qoptions as $qvalue => $qlabel) : ?>
                                        <option value="<?php echo $qvalue; ?>">
                                            <?php echo $qlabel; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ausstellungsdatum_6"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_6" id="ausstellungsdatum_6" class="form-control">
                            </div>
                            <div id="form-7" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $qoptions2 = larray('personnel.modals.documents.qualification_options_2');
                                ?>
                                <label for="erhalter_quali"><?= __('personnel.modals.documents.qualification') ?></label>
                                <select class="form-select" name="erhalter_quali" id="erhalter_quali">
                                    <option disabled hidden selected><?= __('personnel.modals.documents.please_select') ?></option>
                                    <?php foreach ($qoptions2 as $qvalue2 => $qlabel2) : ?>
                                        <option value="<?php echo $qvalue2; ?>">
                                            <?php echo $qlabel2; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ausstellungsdatum_7"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_7" id="ausstellungsdatum_7" class="form-control">
                            </div>
                            <div id="form-4" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <label for="ausstellungsdatum_10"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_10" id="ausstellungsdatum_10" class="form-control">
                                <div id="form-5" style="display:none">
                                    <label for="suspendtime"><?= __('personnel.modals.documents.suspended_until') ?></label>
                                    <input type="date" name="suspendtime" id="suspendtime" class="form-control">
                                </div>
                                <label for="inhalt"><?= __('personnel.modals.documents.reason') ?></label>
                                <textarea name="inhalt" id="inhalt" style="resize:none"></textarea>
                            </div>
                            <div id="form-6" style="display:none">
                                <input type="hidden" value=<?= $row['geschlecht'] ?> name="anrede" id="anrede">
                                <?php
                                $rdoptions2 = larray('personnel.modals.documents.qualification_options_rescue');
                                ?>
                                <label for="erhalter_rang_rd_2"><?= __('personnel.modals.documents.qualification') ?></label>
                                <select class="form-select" name="erhalter_rang_rd_2" id="erhalter_rang_rd_2">
                                    <option disabled hidden selected><?= __('personnel.modals.documents.please_select') ?></option>
                                    <?php foreach ($rdoptions2 as $rdvalue => $rdlabel) : ?>
                                        <option value="<?php echo $rdvalue; ?>">
                                            <?php echo $rdlabel; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ausstellungsdatum_3"><?= __('personnel.modals.documents.issue_date') ?></label>
                                <input type="date" name="ausstellungsdatum_3" id="ausstellungsdatum_3" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('personnel.modals.documents.abort') ?></button>
                        <button type="button" class="btn btn-success" id="fdq-save" onclick="document.getElementById('newDocForm').submit()"><?= __('personnel.modals.documents.create') ?></button>
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
				"ckeditor5": "/assets/_ext/ckeditor5/ckeditor5.js",
				"ckeditor5/": "/assets/_ext/ckeditor5/"
			}
		}
		</script>
    <script src="/assets/_ext/ckeditor5/ckeditor5.js"></script>
    <script type="module" src="/assets/js/ckmain.js"></script>
<?php } ?>
<!-- MODAL ENDE -->