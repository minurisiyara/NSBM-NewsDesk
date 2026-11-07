<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}
?>
