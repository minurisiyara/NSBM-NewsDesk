<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

/**
 * Require the user to be logged in.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
        http_response_code(401);
        exit('Please log in to continue.');
    }
}

/**
 * Require the user to have one of the specified roles.
 */
function requireRole(array $allowedRoles): void
{
    requireLogin();

    if (!in_array($_SESSION['user_role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('You do not have permission to perform this action.');
    }
}
