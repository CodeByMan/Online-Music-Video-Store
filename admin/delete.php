<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$currentUser = require_admin($conn);
require_post_request();

$redirect = 'index.php';
try {
    verify_csrf();
    $entity = input_text($_POST, 'entity', 4, 20);
    $id = request_positive_int($_POST, 'id');
    $redirects = ['user' => 'view_user.php', 'audio' => 'view_music.php', 'video' => 'view_video.php', 'category' => 'view_category.php', 'message' => 'message.php'];
    if (!isset($redirects[$entity])) {
        throw new InvalidArgumentException('Invalid delete request.');
    }
    $redirect = $redirects[$entity];

    if ($entity === 'user') {
        if ($id === (int) $currentUser['user_id']) {
            throw new RuntimeException('You cannot delete your own account.');
        }
        $target = null;
        $conn->begin_transaction();
        try {
            $target = db_fetch_one($conn, 'SELECT user_id, role_id, account_status, user_image FROM users WHERE user_id = ? FOR UPDATE', 'i', [$id]);
            if ($target === null) {
                throw new RuntimeException('The selected user was not found.');
            }
            if ((int) $target['role_id'] === ROLE_ADMIN && $target['account_status'] === 'active') {
                $admins = db_fetch_all($conn, "SELECT user_id FROM users WHERE role_id = ? AND account_status = 'active' FOR UPDATE", 'i', [ROLE_ADMIN]);
                if (count($admins) <= 1) {
                    throw new RuntimeException('The final active administrator account cannot be deleted.');
                }
            }
            db_execute($conn, 'DELETE FROM users WHERE user_id = ?', 'i', [$id]);
            $conn->commit();
        } catch (Throwable $exception) {
            $conn->rollback();
            throw $exception;
        }
        $profileDirectory = (int) $target['role_id'] === ROLE_ADMIN ? __DIR__ . '/img/profile-img' : dirname(__DIR__) . '/frontend/img/banner';
        delete_file_if_safe($profileDirectory, (string) $target['user_image'], 'user_');
    } elseif ($entity === 'audio') {
        $audio = db_fetch_one($conn, 'SELECT audio_name FROM audio WHERE audio_id = ? LIMIT 1', 'i', [$id]);
        if ($audio === null || db_execute($conn, 'DELETE FROM audio WHERE audio_id = ?', 'i', [$id]) < 1) {
            throw new RuntimeException('Audio file not found.');
        }
        delete_file_if_safe(media_storage_directory('audio'), (string) $audio['audio_name']);
    } elseif ($entity === 'video') {
        $video = db_fetch_one($conn, 'SELECT video_name FROM videos WHERE video_id = ? LIMIT 1', 'i', [$id]);
        if ($video === null || db_execute($conn, 'DELETE FROM videos WHERE video_id = ?', 'i', [$id]) < 1) {
            throw new RuntimeException('Video file not found.');
        }
        delete_file_if_safe(media_storage_directory('video'), (string) $video['video_name']);
    } elseif ($entity === 'category') {
        $category = db_fetch_one($conn, 'SELECT category_image FROM categories WHERE category_id = ? LIMIT 1', 'i', [$id]);
        if ($category === null) {
            throw new RuntimeException('Category not found.');
        }
        $audioFiles = db_fetch_all($conn, 'SELECT audio_name FROM audio WHERE category_id = ?', 'i', [$id]);
        $videoFiles = db_fetch_all($conn, 'SELECT video_name FROM videos WHERE category_id = ?', 'i', [$id]);
        db_execute($conn, 'DELETE FROM categories WHERE category_id = ?', 'i', [$id]);
        foreach ($audioFiles as $file) {
            delete_file_if_safe(media_storage_directory('audio'), (string) $file['audio_name']);
        }
        foreach ($videoFiles as $file) {
            delete_file_if_safe(media_storage_directory('video'), (string) $file['video_name']);
        }
        if (db_fetch_one($conn, 'SELECT category_id FROM categories WHERE category_image = ? LIMIT 1', 's', [$category['category_image']]) === null) {
            delete_file_if_safe(__DIR__ . '/uploads/images', (string) $category['category_image']);
        }
    } else {
        if (db_execute($conn, 'DELETE FROM messages WHERE message_id = ?', 'i', [$id]) < 1) {
            throw new RuntimeException('Message not found.');
        }
    }

    redirect_with_flash($redirect, ucfirst($entity) . ' deleted successfully.');
} catch (Throwable $exception) {
    error_log('[One Sound] Delete error: ' . $exception->getMessage());
    redirect_with_flash($redirect, $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to delete the selected record.', 'danger');
}
