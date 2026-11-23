<?php
declare(strict_types=1);

require_once '../../backend/config/session.php';
require_once '../../backend/config/database.php';

// Enforce Admin Authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$activePage = 'users';
$currentAdminId = (int) $_SESSION['user_id'];
$loggedInUserName = $_SESSION['user_name'] ?? 'Admin User';
$firstLetter = strtoupper(substr($loggedInUserName, 0, 1));

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle POST Actions (Add User, Change Role, Toggle Status, Delete User)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
        header('Location: users.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    try {
        // 1. Add New User (Admin / Moderator / Student)
        if ($action === 'add_user') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = in_array($_POST['role'] ?? '', ['student', 'moderator', 'admin'], true) ? $_POST['role'] : 'student';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
                $_SESSION['flash_error'] = 'Please enter a valid name, email address, and a password (minimum 6 characters).';
                header('Location: users.php');
                exit;
            }

            // Duplicate Email Check
            $dupStmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
            $dupStmt->execute(['email' => $email]);
            if ($dupStmt->fetch()) {
                $_SESSION['flash_error'] = "An account with email '{$email}' already exists.";
                header('Location: users.php');
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, is_active)
                VALUES (:name, :email, :password, :role, :is_active)
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'is_active' => $isActive
            ]);

            $_SESSION['flash_success'] = "New {$role} account created for {$name}.";
        }

        // 2. Update User Role
        if ($action === 'update_role') {
            $targetUserId = (int) ($_POST['user_id'] ?? 0);
            $newRole = $_POST['role'] ?? '';

            if ($targetUserId === $currentAdminId) {
                $_SESSION['flash_error'] = 'You cannot change your own administrator role.';
            } elseif (!in_array($newRole, ['student', 'moderator', 'admin'], true)) {
                $_SESSION['flash_error'] = 'Invalid role selected.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
                $stmt->execute(['role' => $newRole, 'id' => $targetUserId]);
                $_SESSION['flash_success'] = 'User role updated successfully.';
            }
        }

        // 3. Toggle Active / Suspended Status
        if ($action === 'toggle_status') {
            $targetUserId = (int) ($_POST['user_id'] ?? 0);

            if ($targetUserId === $currentAdminId) {
                $_SESSION['flash_error'] = 'You cannot suspend your own logged-in account.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
                $stmt->execute(['id' => $targetUserId]);
                $_SESSION['flash_success'] = 'User account status updated.';
            }
        }

        // 4. Delete User (With Foreign Key Protection)
        if ($action === 'delete_user') {
            $targetUserId = (int) ($_POST['user_id'] ?? 0);

            if ($targetUserId === $currentAdminId) {
                $_SESSION['flash_error'] = 'You cannot delete your own logged-in account.';
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                    $stmt->execute(['id' => $targetUserId]);
                    $_SESSION['flash_success'] = 'User account deleted permanently.';
                } catch (PDOException $fkError) {
                    // Schema enforces ON DELETE RESTRICT if user has authored articles, comments, or reports
                    $_SESSION['flash_error'] = 'Cannot delete this user because they have linked articles, comments, or reports. Please suspend the account instead.';
                }
            }
        }

    } catch (PDOException $e) {
        error_log('User management error: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Database operation failed.';
    }

    header('Location: users.php');
    exit;
}

// Search & Role Filter
$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$sql = "SELECT id, name, email, role, is_active, created_at FROM users WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE :search OR email LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
if (in_array($roleFilter, ['student', 'moderator', 'admin'], true)) {
    $sql .= " AND role = :role";
    $params['role'] = $roleFilter;
}

$sql .= " ORDER BY FIELD(role, 'admin', 'moderator', 'student'), created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | NSBM NewsDesk Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=8">
    <script>
        if (localStorage.getItem('nsbm-admin-sidebar-collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
</head>
<body>

    <?php include '../../components/admin-sidebar.php'; ?>

    <main class="admin-main">
        <!-- Top Header -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">☰</button>
                <h1>Users</h1>
            </div>
            <div class="top-bar-actions">
                <div class="avatar-circle" title="<?= htmlspecialchars($loggedInUserName, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($firstLetter, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <a href="../home.php" class="btn btn-outline">View Site</a>
                <form action="../../backend/auth/logout.php" method="POST" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Flash Feedback Messages -->
            <?php if ($flashSuccess !== ''): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($flashError !== ''): ?>
                <div class="alert alert-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <!-- Search, Role Filter & Add User Bar -->
            <form method="GET" action="users.php" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; margin-bottom: 20px;">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">

                <select name="role" class="form-control" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="moderator" <?= $roleFilter === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                    <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Student</option>
                </select>

                <button type="button" class="btn btn-primary" onclick="openAddUserModal()">+ Add User</button>
            </form>

            <!-- Users Data Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <?php
                                    $uId = (int) $u['id'];
                                    $isSelf = ($uId === $currentAdminId);
                                    $isActive = ((int) $u['is_active'] === 1);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if ($isSelf): ?>
                                            <span style="font-size: 11px; color: #09482e; font-weight: 600;">(You)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #6b7280;"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <!-- Inline Role Selector -->
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="user_id" value="<?= $uId ?>">
                                            <select name="role" class="form-control" style="padding: 6px 10px; width: auto; min-width: 120px;" onchange="this.form.submit()" <?= $isSelf ? 'disabled' : '' ?>>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="moderator" <?= $u['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge <?= $isActive ? 'badge-published' : 'badge-draft' ?>">
                                            <?= $isActive ? 'Active' : 'Suspended' ?>
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <?php if (!$isSelf): ?>
                                            <!-- Toggle Active / Suspend Button -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                <button type="submit" class="btn btn-outline btn-sm">
                                                    <?= $isActive ? 'Suspend' : 'Activate' ?>
                                                </button>
                                            </form>

                                            <!-- Delete User Button -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: #9ca3af;">Current Session</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No users found matching your filter.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add New User Popup Modal -->
    <div class="modal-backdrop" id="userModal">
        <div class="modal-box" style="max-width: 480px;">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeUserModal()">✕</button>
            </div>

            <form method="POST" action="users.php" onsubmit="return validateUserForm()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="add_user">

                <div class="form-group">
                    <label for="userName">Full Name *</label>
                    <input type="text" name="name" id="userName" class="form-control" placeholder="e.g., Kasun Silva" required>
                </div>

                <div class="form-group">
                    <label for="userEmail">University Email *</label>
                    <input type="email" name="email" id="userEmail" class="form-control" placeholder="e.g., kasun@students.nsbm.ac.lk" required>
                </div>

                <div class="form-group">
                    <label for="userPassword">Initial Password (Min. 6 characters) *</label>
                    <input type="password" name="password" id="userPassword" class="form-control" minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="userRole">Account Role *</label>
                    <select name="role" id="userRole" class="form-control">
                        <option value="student">Student</option>
                        <option value="moderator">Moderator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" name="is_active" checked> Activate account immediately
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/admin.js?v=6"></script>
</body>
</html>