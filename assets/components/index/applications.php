<table class="table table-striped" id="documentTable">
    <thead>
        <th scope="col">Status</th>
        <th scope="col">#</th>
        <th scope="col">Bearbeiter</th>
        <th scope="col">Am</th>
        <th scope="col"></th>
    </thead>
    <tbody>
        <?php
        $query = "SELECT * FROM intra_antrag_bef WHERE discordid = :discordtag ORDER BY time_added DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['discordtag' => $_SESSION['discordtag']]);
        $appresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($appresult)) {
            echo "<tr><td colspan='5' class='text-center'>Es sind keine Anträge hinterlegt.</td></tr>";
        } else {
            foreach ($appresult as $row) {
                $adddat = date("d.m.Y | H:i", strtotime($row['time_added']));
                $cirs_state = "Unbekannt";
                $bgColor = "";
                switch ($row['cirs_status']) {
                    case 0:
                        $cirs_state = "In Bearbeitung";
                        $badge_color = "text-bg-info";
                        break;
                    case 1:
                        $cirs_state = "Abgelehnt";
                        $badge_color = "text-bg-danger";
                        $badge_text = "Abgelehnt";
                        break;
                    case 2:
                        $cirs_state = "Aufgeschoben";
                        $badge_color = "text-bg-warning";
                        break;
                    case 3:
                        $cirs_state = "Angenommen";
                        $badge_color = "text-bg-success";
                        break;
                    default:
                        $cirs_state = "Unbekannt";
                        $badge_color = "text-bg-dark";
                        break;
                }



                echo "<tr>
                <td><span class='badge {$badge_color}'>" . $cirs_state . "</span></td>
                <td>{$row['uniqueid']}</td>
                <td>" . (!empty($row['cirs_manager']) ? htmlspecialchars($row['cirs_manager']) : '---') . "</td>
                <td><span style='display:none'>{$row['time_added']}</span>{$adddat}</td>
                <td><a class='btn btn-primary btn-sm' href='/antraege/view.php?antrag={$row['uniqueid']}'>Ansehen</a></td>
                </tr>";
            }
        }
        ?>
    </tbody>
</table>