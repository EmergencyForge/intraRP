<?php

use App\Auth\Permissions;
?>

<table class="table table-striped" id="documentTable">
    <thead>
        <th scope="col">Dokumenten-Typ</th>
        <th scope="col">#</th>
        <th scope="col">Ersteller</th>
        <th scope="col">Am</th>
        <th scope="col"></th>
    </thead>
    <tbody>
        <?php
        $query = "SELECT pd.docid, pd.ausstellerid, pd.ausstellungsdatum, pd.type, pd.aussteller_name, u.discord_id AS user_id, u.fullname, u.aktenid FROM intra_mitarbeiter_dokumente pd JOIN intra_users u WHERE pd.profileid = :profileid ORDER BY pd.ausstellungsdatum DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['profileid' => $openedID]);
        $dokuresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arten = [
            0 => "Ernennungsurkunde",
            1 => "Beförderungsurkunde",
            2 => "Entlassungsurkunde",
            3 => "Ausbildungsvertrag",
            5 => "Ausbildungszertifikat",
            6 => "Lehrgangszertifikat",
            7 => "Lehrgangszertifikat (Fachdienste)",
            10 => "Schriftliche Abmahnung",
            11 => "Vorläufige Dienstenthebung",
            12 => "Dienstentfernung",
            13 => "Außerordentliche Kündigung",
        ];

        foreach ($dokuresult as $doks) {
            $austdatum = date("d.m.Y", strtotime($doks['ausstellungsdatum']));
            $docart = isset($arten[$doks['type']]) ? $arten[$doks['type']] : '';
            $path = BASE_PATH . "assets/functions/docredir.php?docid=" . $doks['docid'];

            if ($doks['type'] <= 3) {
                $bg = "text-bg-secondary";
            } elseif ($doks['type'] == 5 || $doks['type'] == 6 || $doks['type'] == 7) {
                $bg = "text-bg-dark";
            } elseif ($doks['type'] >= 10 && $doks['type'] <= 12) {
                $bg = "text-bg-danger";
            }

            echo "<tr>";
            echo "<td><span class='badge $bg'>" . $docart . "</span></td>";
            echo "<td>" . $doks['docid'] .  "</td>";
            echo "<td>" . $doks['aussteller_name'] . "</td>";
            echo "<td>" . $austdatum . "</td>";
            echo "<td>";
            echo "<a href='$path' class='btn btn-sm btn-primary' target='_blank'>Ansehen</a>";

            if (Permissions::check('admin')) {
                echo " <a href='" . BASE_PATH . "admin/personal/dokument-delete.php?id={$doks['docid']}&pid=$openedID' class='btn btn-sm btn-danger'><i class='las la-trash'></i></a>";
            }

            echo "</td>";
            echo "</tr>";
        }
        ?>

    </tbody>
</table>