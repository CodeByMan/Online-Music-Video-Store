<?php

declare(strict_types=1);

function database_connect(array $config, bool $debug = false): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $connection = new mysqli(
            (string) $config['host'],
            (string) $config['user'],
            (string) $config['password'],
            (string) $config['name'],
            (int) $config['port']
        );
        $connection->set_charset('utf8mb4');
        $connection->query("SET time_zone = '" . DATABASE_TIMEZONE_OFFSET . "'");
        return $connection;
    } catch (mysqli_sql_exception $exception) {
        error_log('[One Sound] Database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        exit($debug ? 'Database connection failed: ' . e($exception->getMessage()) : 'Database connection failed.');
    }
}

function db_statement(mysqli $connection, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $statement = $connection->prepare($sql);

    if ($types !== '') {
        if (strlen($types) !== count($params)) {
            throw new InvalidArgumentException('Database parameter count does not match its type definition.');
        }

        $bindArguments = [$types];
        foreach ($params as $index => $value) {
            $params[$index] = $value;
            $bindArguments[] = &$params[$index];
        }
        call_user_func_array([$statement, 'bind_param'], $bindArguments);
    }

    $statement->execute();
    return $statement;
}

function db_fetch_one(mysqli $connection, string $sql, string $types = '', array $params = []): ?array
{
    $statement = db_statement($connection, $sql, $types, $params);
    $result = $statement->get_result();
    $row = $result->fetch_assoc() ?: null;
    $statement->close();
    return $row;
}

function db_fetch_all(mysqli $connection, string $sql, string $types = '', array $params = []): array
{
    $statement = db_statement($connection, $sql, $types, $params);
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}

function db_execute(mysqli $connection, string $sql, string $types = '', array $params = []): int
{
    $statement = db_statement($connection, $sql, $types, $params);
    $affectedRows = $statement->affected_rows;
    $statement->close();
    return $affectedRows;
}

function db_last_insert_id(mysqli $connection): int
{
    return (int) $connection->insert_id;
}
