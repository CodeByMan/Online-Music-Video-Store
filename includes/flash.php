<?php

declare(strict_types=1);

function set_flash(string $type, string $message): void
{
    $allowed = ['success', 'danger', 'warning', 'info'];
    $_SESSION['flash_messages'][] = [
        'type' => in_array($type, $allowed, true) ? $type : 'info',
        'message' => $message,
    ];
}

function consume_flash_messages(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return is_array($messages) ? $messages : [];
}

function render_flash_messages(): string
{
    $html = '';
    foreach (consume_flash_messages() as $message) {
        if (!is_array($message)) {
            continue;
        }
        $type = (string) ($message['type'] ?? 'info');
        $text = (string) ($message['message'] ?? '');
        $html .= '<div class="container mt-3"><div class="alert alert-' . e($type) . '" role="alert">' . e($text) . '</div></div>';
    }
    return $html;
}

function redirect(string $location, int $status = 303): never
{
    header('Location: ' . $location, true, $status);
    exit;
}

function redirect_with_flash(string $location, string $message, string $type = 'success'): never
{
    set_flash($type, $message);
    redirect($location);
}
