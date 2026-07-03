<?php
/**
 * PDO connection (singleton) + tiny query helpers.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, $port, DB_NAME, DB_CHARSET);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        if (APP_DEBUG) {
            http_response_code(500);
            exit('Database connection failed: ' . $e->getMessage());
        }
        http_response_code(500);
        exit('Service temporarily unavailable.');
    }
    return $pdo;
}

/** Run a prepared statement and return the PDOStatement. */
function db_run(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Fetch a single row (or null). */
function db_one(string $sql, array $params = []): ?array
{
    $row = db_run($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** Fetch all rows. */
function db_all(string $sql, array $params = []): array
{
    return db_run($sql, $params)->fetchAll();
}

/** Fetch a single scalar value (first column of first row), or null. */
function db_val(string $sql, array $params = [])
{
    $v = db_run($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** Last inserted id. */
function db_insert_id(): string
{
    return db()->lastInsertId();
}
