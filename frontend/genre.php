<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
$pagination = pagination_state($_GET, 20);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM audio');
$total = (int) ($totalRow['total'] ?? 0);
$songRows = db_fetch_all(
    $conn,
    'SELECT a.audio_id, a.audio_title, c.category_genre AS group_name
     FROM audio a INNER JOIN categories c ON a.category_id = c.category_id
     ORDER BY c.category_genre ASC, a.audio_title ASC LIMIT ? OFFSET ?',
    'ii',
    [$pagination['per_page'], $pagination['offset']]
);
$groupedSongs = [];
foreach ($songRows as $songRow) {
    $groupName = (string) $songRow['group_name'];
    $groupedSongs[$groupName][] = $songRow;
}
$totalPages = pagination_total_pages($total, $pagination['per_page']);
$pageTitle = 'Songs by Genre';
$emptyText = 'No songs found by genre.';
include 'header.php';
include 'partials/grouped-songs.php';
include 'footer.php';
