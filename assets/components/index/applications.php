<table class="table table-striped" id="documentTable">
    <thead>
        <th scope="col">ID</th>
        <th scope="col">Status</th>
        <th scope="col">Bearbeiter</th>
        <th scope="col">Datum</th>
        <th scope="col"></th>
    </thead>
    <tbody>
        <?php
        $query = "SELECT * FROM intra_antrag_bef WHERE discordid = :discordtag ORDER BY time_added DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['discordtag' => $_SESSION['discordtag']]);
        $appresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($appresult)) {
            echo "<tr><td colspan='5' class='text-center'>Es sind keine Dokumente für dich hinterlegt.</td></tr>";
        } else {
            foreach ($appresult as $row) {
                $adddat = date("d.m.Y | H:i", strtotime($row['time_added']));
                $cirs_state = "Unbekannt";
                $bgColor = "";
                switch ($row['cirs_status']) {
                    case 0:
                        $cirs_state = "In Bearbeitung";
                        break;
                    case 1:
                        $bgColor = "rgba(255,0,0,.05)";
                        $cirs_state = "Abgelehnt";
                        break;
                    case 2:
                        $cirs_state = "Aufgeschoben";
                        break;
                    case 3:
                        $bgColor = "rgba(0,255,0,.05)";
                        $cirs_state = "Angenommen";
                        break;
                }

                echo "<tr";
                if (!empty($bgColor)) {
                    echo " style='--bs-table-striped-bg: {$bgColor}; --bs-table-bg: {$bgColor};'";
                }
                echo ">
                <td>{$row['uniqueid']}</td>
                <td>{$cirs_state}</td>
                <td>{$row['cirs_manager']}</td>
                <td><span style='display:none'>{$row['time_added']}</span>{$adddat}</td>
                <td><a class='btn btn-primary btn-sm' href='/antraege/view.php?antrag={$row['uniqueid']}'>Ansehen</a></td>
                </tr>";
            }
        }
        ?>
    </tbody>
</table>