<?php
require __DIR__ . '/../../../../assets/config/database.php';

use App\Auth\Permissions;

$commentsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $commentsPerPage;

// WICHTIG: Erst die Gesamtanzahl der Kommentare ermitteln
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_mitarbeiter_log WHERE profilid = ?");
$countStmt->execute([$_GET['id']]);
$totalComments = $countStmt->fetchColumn();

// Dann die Kommentare für die aktuelle Seite laden
$stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_log WHERE profilid = ? ORDER BY datetime DESC LIMIT ?, ?");
$stmt->execute([$_GET['id'], $offset, $commentsPerPage]);
$comments = $stmt->fetchAll();

foreach ($comments as $comment) {
    $commentType = '';
    switch ($comment['type']) {
        case 0:
            $commentType = 'note';
            break;
        case 1:
            $commentType = 'positive';
            break;
        case 2:
            $commentType = 'negative';
            break;
        case 4:
            $commentType = 'rank';
            break;
        case 5:
            $commentType = 'modify';
            break;
        case 6:
            $commentType = 'created';
            break;
        case 7:
            $commentType = 'document';
            break;
    }

    echo "<div class='comment $commentType border shadow-sm'>";
    $comtime = date("d.m.Y H:i", strtotime($comment['datetime']));
    echo "<p>{$comment['content']}<br><small><span><i class='las la-user'></i> {$comment['paneluser']} <i class='las la-clock'></i> $comtime";

    if (Permissions::check('admin') && $comment['type'] <= 3) {
        echo " / <a href='" . BASE_PATH . "admin/personal/comment-delete.php?id={$comment['logid']}&pid={$comment['profilid']}'><i class='las la-trash' style='color:red;margin-left:5px'></i></a></span></small></p>";
    } else {
        echo "</span></small></p>";
    }
    echo "</div>";
}

$totalPages = ceil($totalComments / $commentsPerPage);
if ($totalPages > 1) {
    echo '<nav aria-label="Comment Pagination">';
    echo '<ul class="pagination justify-content-center">';
    $editArgument = isset($_GET['edit']) ? '&edit' : '';

    if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . ($page - 1) . $editArgument . '">Zurück</a></li>';
    }

    if ($totalPages <= 10) {
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . $i . $editArgument . '">' . $i . '</a></li>';
            }
        }
    } else {
        $maxButtons = 5;
        $startPage = max(1, $page - floor($maxButtons / 2));
        $endPage = min($totalPages, $startPage + $maxButtons - 1);

        if ($endPage - $startPage < $maxButtons - 1) {
            $startPage = max(1, $endPage - $maxButtons + 1);
        }

        if ($startPage > 1) {
            echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=1' . $editArgument . '">1</a></li>';
            if ($startPage > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $page) {
                echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . $i . $editArgument . '">' . $i . '</a></li>';
            }
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . $totalPages . $editArgument . '">' . $totalPages . '</a></li>';
        }
    }

    if ($page < $totalPages) {
        echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . ($page + 1) . $editArgument . '">Weiter</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
}
