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
        $query = "SELECT pd.docid, pd.ausstellerid, pd.ausstellungsdatum, pd.type, u.id AS user_id, u.fullname, u.aktenid FROM intra_mitarbeiter_dokumente pd JOIN intra_users u ON pd.ausstellerid = u.id WHERE pd.discordid = :discordtag ORDER BY pd.ausstellungsdatum DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['discordtag' => $_SESSION['discordtag']]);
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

        if (empty($dokuresult)) {
            echo "<tr><td colspan='5' class='text-center'>Es sind keine Dokumente für dich hinterlegt.</td></tr>";
        } else {
            foreach ($dokuresult as $doks) {
                $austdatum = date("d.m.Y", strtotime($doks['ausstellungsdatum']));
                $docart = isset($arten[$doks['type']]) ? $arten[$doks['type']] : '';
                $path = "/assets/functions/docredir.php?docid=" . $doks['docid'];

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
                echo "<td>" . $doks['fullname'] . "</td>";
                echo "<td>" . $austdatum . "</td>";
                echo "<td>";
                echo "<a href='$path' class='btn btn-sm btn-primary' target='_blank'>Ansehen</a>";
                echo "</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>