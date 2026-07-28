<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
require_post_request();
try {
    verify_csrf();
    $messageId = request_positive_int($_POST, 'message_id');
    $status = input_text($_POST, 'message_status', 4, 7);
    if (!in_array($status, ['seen', 'pending'], true)) {
        throw new InvalidArgumentException('Invalid message status.');
    }
    $affected = db_execute(
        $conn,
        "UPDATE messages
         SET message_status = ?, read_at = IF(? = 'seen', NOW(), NULL), read_by = IF(? = 'seen', ?, NULL)
         WHERE message_id = ?",
        'sssii',
        [$status, $status, $status, (int) $current_user['user_id'], $messageId]
    );
    if ($affected < 1 && db_fetch_one($conn, 'SELECT message_id FROM messages WHERE message_id = ? LIMIT 1', 'i', [$messageId]) === null) {
        throw new RuntimeException('Message not found.');
    }
    redirect_with_flash('message.php', 'Message status updated.');
} catch (Throwable $exception) {
    error_log('[One Sound] Message status update error: ' . $exception->getMessage());
    redirect_with_flash('message.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the message status.', 'danger');
}
