<?php

use App\Auth\Permissions;
?>

<table class="table table-striped" id="documentTable">
    <thead>
        <th scope="col"><?= __('personnel.profile.documents.type') ?></th>
        <th scope="col"><?= __('personnel.profile.documents.id') ?></th>
        <th scope="col"><?= __('personnel.profile.documents.created_by') ?></th>
        <th scope="col"><?= __('personnel.profile.documents.created_at') ?></th>
        <th scope="col"></th>
    </thead>
    <tbody>
        <?php
        $query = "SELECT pd.docid, pd.ausstellerid, pd.ausstellungsdatum, pd.type, u.id AS user_id, u.fullname, u.aktenid FROM intra_mitarbeiter_dokumente pd JOIN intra_users u ON pd.ausstellerid = u.id WHERE pd.profileid = :profileid ORDER BY pd.ausstellungsdatum DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['profileid' => $openedID]);
        $dokuresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arten = larray('personnel.profile.documents.types');

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
            echo "<a href='$path' class='btn btn-sm btn-primary' target='_blank'>" . __('personnel.profile.documents.view') . "</a>";

            if (Permissions::check('admin')) {
                echo " <a href='/admin/personal/dokument-delete.php?id={$doks['docid']}&pid=$openedID' class='btn btn-sm btn-danger'><i class='las la-trash'></i></a>";
            }

            echo "</td>";
            echo "</tr>";
        }
        ?>

    </tbody>
</table>
<script src="/vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
<script src="/vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#documentTable').DataTable({
            stateSave: true,
            paging: true,
            lengthMenu: [5, 10, 20],
            pageLength: 10,
            columnDefs: [{
                orderable: false,
                targets: -1
            }],
            language: {
                "decimal": "",
                "emptyTable": <?= json_encode(__('datatable.emptytable')) ?>,
                "info": <?= json_encode(__('datatable.info')) ?>,
                "infoEmpty": <?= json_encode(__('datatable.infoempty')) ?>,
                "infoFiltered": <?= json_encode(__('personnel.profile.documents.datatable.infofiltered')) ?>,
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": <?= json_encode(__('personnel.profile.documents.datatable.lengthmenu')) ?>,
                "loadingRecords": <?= json_encode(__('datatable.loadingrecords')) ?>,
                "processing": <?= json_encode(__('datatable.processing')) ?>,
                "search": <?= json_encode(__('personnel.profile.documents.datatable.search')) ?>,
                "zeroRecords": <?= json_encode(__('datatable.zerorecords')) ?>,
                "paginate": {
                    "first": <?= json_encode(__('datatable.paginate.first')) ?>,
                    "last": <?= json_encode(__('datatable.paginate.last')) ?>,
                    "next": <?= json_encode(__('datatable.paginate.next')) ?>,
                    "previous": <?= json_encode(__('datatable.paginate.previous')) ?>
                },
                "aria": {
                    "sortAscending": <?= json_encode(__('datatable.aria.sortascending')) ?>,
                    "sortDescending": <?= json_encode(__('datatable.aria.sortdescending')) ?>
                }
            }
        });
    });
</script>