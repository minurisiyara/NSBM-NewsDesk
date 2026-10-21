
<?php

require_once __DIR__ . '/backend/config/database.php';

try {
    $result = $pdo->query('SELECT DATABASE() AS database_name');
    $row = $result->fetch();

    echo 'Database connection successful!<br>';
    echo 'Connected database: ' .
        htmlspecialchars($row['database_name'], ENT_QUOTES, 'UTF-8');
} catch (PDOException $e) {
    error_log('Database test failed: ' . $e->getMessage());
    http_response_code(500);
    echo 'The database test failed.';
}
