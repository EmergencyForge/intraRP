<table class="table table-striped" id="documentTable">
    <thead>
        <th scope="col">Status</th>
        <th scope="col">#</th>
        <th scope="col">Bearbeiter</th>
        <th scope="col">Datum</th>
        <th scope="col"></th>
    </thead>
    <tbody>
        <?php
        $stmtdivi = $pdo->prepare("
    SELECT 
        e.enr, 
        e.sendezeit, 
        e.protokoll_status, 
        e.bearbeiter, 
        e.freigegeben,
        e.freigeber_name
    FROM intra_edivi e
    JOIN intra_mitarbeiter m ON e.pfname = m.fullname
    WHERE m.discordtag = :discordtag
    ORDER BY e.sendezeit DESC
    LIMIT 1");
        $stmtdivi->execute(['discordtag' => $_SESSION['discordtag']]);
        $ediviRows = $stmtdivi->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ediviRows)) {
            echo "<tr><td colspan='5' class='text-center'>Es sind keine Protokolle hinterlegt.</td></tr>";
        } else {
            foreach ($ediviRows as $row) {
                $datetime = new DateTime($row['sendezeit']);
                $date = $datetime->format('d.m.Y | H:i');
                switch ($row['protokoll_status']) {
                    case 0:
                        $status = "<span class='badge text-bg-secondary'>Ungesehen</span>";
                        break;
                    case 1:
                        $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge text-bg-warning'>in Prüfung</span>";
                        break;
                    case 2:
                        $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge text-bg-success'>Geprüft</span>";
                        break;
                    default:
                        $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge text-bg-danger'>Ungenügend</span>";
                        break;
                }

                switch ($row['freigegeben']) {
                    default:
                        $freigabe_status = "";
                        break;
                    case 1:
                        $freigabe_status = "<span title='Freigegeben von: " . $row['freigeber_name'] . "' class='badge text-bg-success'>F</span>";
                        break;
                }

                echo "<tr>";
                echo "<td>" . $status . "</td>";
                echo "<td >" . $row['enr'] . " " . $freigabe_status . "</td>";
                echo "<td>" . (!empty($row['bearbeiter']) ? htmlspecialchars($row['bearbeiter']) : '---') . "</td>";
                echo "<td><span style='display:none'>" . $row['sendezeit'] . "</span>" . $date . "</td>";
                echo "<td><a href='" . BASE_PATH . "enotf/prot/index.php?enr={$row['enr']}' class='btn btn-sm btn-primary'>Ansehen</a></td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>