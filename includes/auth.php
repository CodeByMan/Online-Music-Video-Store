<?php

declare(strict_types=1);

function authenticated_user(mysqli $connection): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
    if ($lastActivity > 0 && time() - $lastActivity > AUTH_IDLE_TIMEOUT) {
        clear_authenticated_session();
        return null;
    }

    $userId = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($userId === false) {
        clear_authenticated_session();
        return null;
    }

    $user = db_fetch_one(
        $connection,
        "SELECT user_id, user_name, user_email, user_phone, user_address, user_image, role_id, account_status, session_version
         FROM users WHERE user_id = ? LIMIT 1",
        'i',
        [(int) $userId]
    );

    $sessionVersion = filter_var($_SESSION['session_version'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($user === null
        || ($user['account_status'] ?? 'active') !== 'active'
        || $sessionVersion === false
        || (int) $user['session_version'] !== (int) $sessionVersion) {
        clear_authenticated_session();
        return null;
    }

    $lastRegeneration = (int) ($_SESSION['session_regenerated_at'] ?? 0);
    if ($lastRegeneration === 0 || time() - $lastRegeneration > SESSION_REGENERATION_INTERVAL) {
        session_regenerate_id(true);
        $_SESSION['session_regenerated_at'] = time();
    }

    $_SESSION['last_activity'] = time();
    return $user;
}

function establish_authenticated_session(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['user_name'] = (string) $user['user_name'];
    $_SESSION['role_id'] = (int) $user['role_id'];
    $_SESSION['session_version'] = max(1, (int) ($user['session_version'] ?? 1));
    $_SESSION['last_activity'] = time();
    $_SESSION['session_regenerated_at'] = time();
    unset($_SESSION['csrf_token']);
}

function clear_authenticated_session(bool $preserveFlash = false): void
{
    $flash = $preserveFlash ? ($_SESSION['flash_messages'] ?? []) : [];
    $_SESSION = [];
    if ($preserveFlash && $flash !== []) {
        $_SESSION['flash_messages'] = $flash;
    }
    session_regenerate_id(true);
}

function require_admin(mysqli $connection): array
{
    $user = authenticated_user($connection);
    if ($user === null || (int) $user['role_id'] !== ROLE_ADMIN) {
        redirect('login.php', 302);
    }
    return $user;
}

function require_frontend_user(mysqli $connection): array
{
    $user = authenticated_user($connection);
    if ($user === null || (int) $user['role_id'] !== ROLE_USER) {
        redirect('signin.php', 302);
    }
    return $user;
}

function require_any_authenticated_user(mysqli $connection): array
{
    $user = authenticated_user($connection);
    if ($user === null || !in_array((int) $user['role_id'], [ROLE_ADMIN, ROLE_USER], true)) {
        http_response_code(401);
        exit('Authentication required.');
    }
    return $user;
}

function client_ip_address(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return is_string($ip) && $ip !== '' ? substr($ip, 0, 45) : 'unknown';
}

function rate_limit_key(string $identity): string
{
    return hash('sha256', strtolower(trim($identity)) . '|' . client_ip_address());
}

function rate_limit_cleanup(mysqli $connection): void
{
    // Keep the small rate-limit table tidy without adding a cron dependency.
    if (random_int(1, 100) === 1) {
        db_execute(
            $connection,
            'DELETE FROM request_limits WHERE last_attempt_at < DATE_SUB(NOW(), INTERVAL 7 DAY)'
        );
    }
}

function rate_limit_is_blocked(mysqli $connection, string $action, string $identity): bool
{
    rate_limit_cleanup($connection);
    $row = db_fetch_one(
        $connection,
        'SELECT blocked_until FROM request_limits WHERE action_name = ? AND rate_key = ? LIMIT 1',
        'ss',
        [$action, rate_limit_key($identity)]
    );
    return $row !== null && $row['blocked_until'] !== null && strtotime((string) $row['blocked_until']) > time();
}

function rate_limit_hit(mysqli $connection, string $action, string $identity, int $maxAttempts, int $windowSeconds, int $blockSeconds): void
{
    $key = rate_limit_key($identity);
    $connection->begin_transaction();
    try {
        db_execute(
            $connection,
            'INSERT IGNORE INTO request_limits (action_name, rate_key, attempt_count, window_started_at, last_attempt_at) VALUES (?, ?, 0, NOW(), NOW())',
            'ss',
            [$action, $key]
        );
        $row = db_fetch_one(
            $connection,
            'SELECT attempt_count, window_started_at, blocked_until FROM request_limits WHERE action_name = ? AND rate_key = ? FOR UPDATE',
            'ss',
            [$action, $key]
        );
        if ($row === null) {
            throw new RuntimeException('Unable to initialize request limiting.');
        }

        $now = time();
        $windowStarted = strtotime((string) $row['window_started_at']);
        $windowExpired = $windowStarted === false || $now - $windowStarted >= $windowSeconds;
        $attempts = $windowExpired ? 1 : (int) $row['attempt_count'] + 1;
        $windowDate = date('Y-m-d H:i:s', $windowExpired ? $now : $windowStarted);
        $blockedUntil = $attempts >= $maxAttempts ? date('Y-m-d H:i:s', $now + $blockSeconds) : null;

        db_execute(
            $connection,
            'UPDATE request_limits SET attempt_count = ?, window_started_at = ?, last_attempt_at = NOW(), blocked_until = ? WHERE action_name = ? AND rate_key = ?',
            'issss',
            [$attempts, $windowDate, $blockedUntil, $action, $key]
        );
        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function rate_limit_clear(mysqli $connection, string $action, string $identity): void
{
    db_execute($connection, 'DELETE FROM request_limits WHERE action_name = ? AND rate_key = ?', 'ss', [$action, rate_limit_key($identity)]);
}

function enforce_action_rate_limit(mysqli $connection, string $action, string $identity, int $maxAttempts, int $windowSeconds, int $blockSeconds): void
{
    if (rate_limit_is_blocked($connection, $action, $identity)) {
        throw new RuntimeException('Too many requests. Please wait and try again.');
    }

    // Record the current allowed request. Reaching the configured maximum marks
    // the key as blocked for the next request, so requests 1..N succeed and
    // request N+1 is rejected consistently with the login limiter.
    rate_limit_hit($connection, $action, $identity, $maxAttempts, $windowSeconds, $blockSeconds);
}
