<?php
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

use App\Auth\Permissions;

$commentsPerPage = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $commentsPerPage;

// Get total number of comments
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_mitarbeiter_log WHERE profilid = ?");
$countStmt->execute([$_GET['id']]);
$totalComments = $countStmt->fetchColumn();

$totalPages = ceil($totalComments / $commentsPerPage);

// Get paginated comments
$stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_log WHERE profilid = ? ORDER BY datetime DESC LIMIT ?, ?");
$stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->bindValue(3, $commentsPerPage, PDO::PARAM_INT);
$stmt->execute();

$comments = $stmt->fetchAll();

foreach ($comments as $comment) {
    $commentType = match ((int) $comment['type']) {
        0 => 'note',
        1 => 'positive',
        2 => 'negative',
        4 => 'rank',
        5 => 'modify',
        6 => 'created',
        7 => 'document',
        default => '',
    };

    echo "<div class='comment $commentType border shadow-sm'>";
    $comtime = date("d.m.Y H:i", strtotime($comment['datetime']));
    echo "<p>{$comment['content']}<br><small><span><i class='las la-user'></i> {$comment['paneluser']} <i class='las la-clock'></i> $comtime";

    if (Permissions::check('admin') && $comment['type'] <= 3) {
        echo " / <a href='/admin/personal/comment-delete.php?id={$comment['logid']}&pid={$comment['profilid']}'><i class='las la-trash' style='color:red;margin-left:5px'></i></a>";
    }

    echo "</span></small></p>";
    echo "</div>";
}

if ($totalPages > 1) {
    echo '<nav aria-label="Comment Pagination">';
    echo '<ul class="pagination justify-content-center">';

    $editArgument = isset($_GET['edit']) ? '&edit' : '';

    if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . ($page - 1) . $editArgument . '">' . __('personnel.profile.comments.back') . '</a></li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $page ? ' active' : '';
        echo '<li class="page-item' . $active . '"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . $i . $editArgument . '">' . $i . '</a></li>';
    }

    if ($page < $totalPages) {
        echo '<li class="page-item"><a class="page-link" href="?id=' . $_GET['id'] . '&page=' . ($page + 1) . $editArgument . '">' . __('personnel.profile.comments.next') . '</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
}
