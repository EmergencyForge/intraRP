<?php
$stmt = $pdo->prepare("SELECT id,name,shortname,priority FROM intra_mitarbeiter_fwquali ORDER BY priority ASC");
$stmt->execute();
$bfqsel = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-floating">
    <select class="form-select mt-3" name="qualifw2" id="qualifw2">
        <?php foreach ($bfqsel as $data) {
            if ($bfq2 == $data['id']) {
                echo "<option value='{$data['id']}' selected='selected'>{$data['name']}</option>";
            } else {
                echo "<option value='{$data['id']}'>{$data['name']}</option>";
            }
        } ?>
    </select>
    <label for="qualifw2"><?= __('personnel.selectors.qualification_fire') ?></label>
</div>