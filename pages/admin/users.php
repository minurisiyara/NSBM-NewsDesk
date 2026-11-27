<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$message = '';
$error = '';

/**
 * Inspect existing columns of `users` table so queries adapt cleanly.
 *
 * @return array<string, array<string, mixed>>
 */
function admin_get_users_schema(PDO $pdo): array
{
    $schema = [];
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users");
        foreach ($stmt->fetchAll() as $row) {
            if (!empty($row['Field'])) {
                $schema[(string) $row['Field']] = $row;
            }
        }
    } catch (Throwable $e) {
        // Fallback if SHOW COLUMNS is restricted
    }
    return $schema;
}

/**
 * Resolve role column name in `users` table.
 */
function admin_role_column(array $schema): string
{
    if (isset($schema['role'])) {
        return 'role';
    }
    if (isset($schema['user_role'])) {
        return 'user_role';
    }
    return 'role';
}

/**
 * Determine whether a user row represents a suspended state.
 */
function admin_user_is_suspended(array $user, array $schema): bool
{
    if (isset($schema['status'])) {
        return strtolower((string) ($user['status'] ?? 'active')) === 'suspended';
    }
    if (isset($schema['is_active'])) {
        return (int) ($user['is_active'] ?? 1) === 0;
    }
    return false;
}

/**
 * Persist suspended/active state safely across schemas.
 */
function admin_set_user_suspended(PDO $pdo, array $schema, int $userId, bool $suspend): void
{
    $roleCol = admin_role_column($schema);

    if (isset($schema['status'])) {
        $type = strtolower((string) ($schema['status']['Type'] ?? ''));
        $targetValue = 'suspended';

        if (str_contains($type, 'enum(')) {
            preg_match_all("/'([^']+)'/", $type, $matches);
            $allowedEnum = array_map('strtolower', $matches[1] ?? []);
            if ($suspend) {
                foreach (['suspended', 'inactive', 'blocked', 'banned'] as $candidate) {
                    if (in_array($candidate, $allowedEnum, true)) {
                        $targetValue = $candidate;
                        break;
                    }
                }
            } else {
                foreach (['active', 'approved', 'enabled'] as $candidate) {
                    if (in_array($candidate, $allowedEnum, true)) {
                        $targetValue = $candidate;
                        break;
                    }
                }
            }
        } elseif (!$suspend) {
            $targetValue = 'active';
        }

        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$targetValue, $userId]);
        return;
    }

    if (isset($schema['is_active'])) {
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([$suspend ? 0 : 1, $userId]);
        return;
    }

    // Fallback if neither `status` nor `is_active` exists: toggle between suspended/student role
    if ($suspend) {
        $stmt = $pdo->prepare("UPDATE users SET {$roleCol} = 'suspended' WHERE id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET {$roleCol} = 'student' WHERE id = ? AND LOWER({$roleCol}) = 'suspended'");
        $stmt->execute([$userId]);
    }
}

$schema = admin_get_users_schema($pdo);
$roleCol = admin_role_column($schema);
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $fullName = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = strtolower(trim((string) ($_POST['role'] ?? 'student')));

        $allowedRoles = ['admin', 'moderator', 'student'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'student';
        }

        if ($fullName === '' || $email === '' || $password === '') {
            $error = 'Name, email, and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $checkStmt->execute([$email]);
                if ((int) $checkStmt->fetchColumn() > 0) {
                    $error = 'A user with this email already exists.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $insertCols = [];
                    $placeholders = [];
                    $params = [];

                    if (isset($schema['full_name'])) {
                        $insertCols[] = 'full_name';
                        $placeholders[] = '?';
                        $params[] = $fullName;
                    }
                    if (isset($schema['name'])) {
                        $insertCols[] = 'name';
                        $placeholders[] = '?';
                        $params[] = $fullName;
                    }
                    if (empty($insertCols)) {
                        $insertCols[] = 'name';
                        $placeholders[] = '?';
                        $params[] = $fullName;
                    }

                    $insertCols[] = 'email';
                    $placeholders[] = '?';
                    $params[] = $email;

                    if (isset($schema['password_hash'])) {
                        $insertCols[] = 'password_hash';
                        $placeholders[] = '?';
                        $params[] = $hash;
                    } elseif (isset($schema['password'])) {
                        $insertCols[] = 'password';
                        $placeholders[] = '?';
                        $params[] = $hash;
                    } else {
                        $insertCols[] = 'password_hash';
                        $placeholders[] = '?';
                        $params[] = $hash;
                    }

                    $insertCols[] = $roleCol;
                    $placeholders[] = '?';
                    $params[] = $role;

                    if (isset($schema['status'])) {
                        $insertCols[] = 'status';
                        $placeholders[] = '?';
                        $params[] = 'active';
                    } elseif (isset($schema['is_active'])) {
                        $insertCols[] = 'is_active';
                        $placeholders[] = '?';
                        $params[] = 1;
                    }

                    $sql = sprintf(
                        "INSERT INTO users (%s) VALUES (%s)",
                        implode(', ', $insertCols),
                        implode(', ', $placeholders)
                    );
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    $message = 'New user added successfully.';
                }
            } catch (Throwable $e) {
                $error = 'Could not add user: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_role') {
        $targetId = (int) ($_POST['id'] ?? 0);
        $newRole = strtolower(trim((string) ($_POST['role'] ?? 'student')));
        $allowedRoles = ['admin', 'moderator', 'student'];

        if ($targetId <= 0 || !in_array($newRole, $allowedRoles, true)) {
            $error = 'Invalid user or role selection.';
        } elseif ($targetId === $currentUserId && $newRole !== 'admin') {
            $error = 'You cannot demote your own currently logged-in admin account.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET {$roleCol} = ? WHERE id = ?");
                $stmt->execute([$newRole, $targetId]);
                $message = 'User role updated successfully.';
            } catch (Throwable $e) {
                $error = 'Could not update role: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_suspend') {
        $targetId = (int) ($_POST['id'] ?? 0);
        $suspend = (string) ($_POST['suspend'] ?? '1') === '1';

        if ($targetId <= 0) {
            $error = 'Invalid user selected.';
        } elseif ($targetId === $currentUserId && $suspend) {
            $error = 'You cannot suspend your own currently logged-in account.';
        } else {
            try {
                admin_set_user_suspended($pdo, $schema, $targetId, $suspend);
                $message = $suspend ? 'User account suspended.' : 'User account activated.';
            } catch (Throwable $e) {
                $error = 'Could not update account status: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $targetId = (int) ($_POST['id'] ?? 0);

        if ($targetId <= 0) {
            $error = 'Invalid user selected.';
        } elseif ($targetId === $currentUserId) {
            $error = 'You cannot delete your own currently logged-in account.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$targetId]);
                $message = 'User deleted successfully.';
            } catch (Throwable $e) {
                $error = 'Could not delete user: ' . $e->getMessage();
            }
        }
    }
}

$users = [];
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
} catch (Throwable $e) {
    $error = $error ?: ('Could not load users: ' . $e->getMessage());
}

admin_render_start('users.php', 'Users');
?>

<?php if ($message !== ''): ?>
    <div class="alert ok"><?= admin_h($message) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="alert"><?= admin_h($error) ?></div>
<?php endif; ?>

<div class="bar">
    <input type="text" id="adminSearchInput" class="inp" placeholder="Search users by name, email, or role...">
    <button type="button" class="btn" onclick="AdminUI.openModal('addUserModal')">+ Add User</button>
</div>

<div class="card tw">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="adminFilterBody">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $u): ?>
                <?php
                $uid = (int) ($u['id'] ?? 0);
                $uName = (string) ($u['full_name'] ?? $u['name'] ?? 'User');
                $uEmail = (string) ($u['email'] ?? '');
                $rawRole = strtolower((string) ($u[$roleCol] ?? $u['role'] ?? $u['user_role'] ?? 'student'));
                $isSuspended = admin_user_is_suspended($u, $schema) || $rawRole === 'suspended';
                $displayRole = $rawRole === 'suspended' ? 'student' : $rawRole;
                $isSelf = ($uid > 0 && $uid === $currentUserId);
                ?>
                <tr data-filter-row data-search="<?= admin_h($uName . ' ' . $uEmail . ' ' . $displayRole . ' ' . ($isSuspended ? 'suspended' : 'active')) ?>">
                    <td><b><?= admin_h($uName) ?></b></td>
                    <td class="meta"><?= admin_h($uEmail) ?></td>
                    <td>
                        <form method="POST" style="display:inline-flex;align-items:center;gap:6px">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="id" value="<?= $uid ?>">
                            <select name="role" class="inp" style="width:auto;padding:5px 8px" onchange="this.form.submit()" <?= $isSelf ? 'disabled title="Cannot change own role here"' : '' ?>>
                                <?php foreach (['student' => 'Student', 'moderator' => 'Moderator', 'admin' => 'Admin'] as $rVal => $rLabel): ?>
                                    <option value="<?= $rVal ?>" <?= $displayRole === $rVal ? 'selected' : '' ?>>
                                        <?= $rLabel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <?php if ($isSuspended): ?>
                            <span class="pill am">Suspended</span>
                        <?php else: ?>
                            <span class="pill ok">Active</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;display:inline-flex;gap:6px">
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="toggle_suspend">
                            <input type="hidden" name="id" value="<?= $uid ?>">
                            <input type="hidden" name="suspend" value="<?= $isSuspended ? '0' : '1' ?>">
                            <button type="submit" class="btn o s" <?= $isSelf ? 'disabled title="Cannot suspend own account"' : '' ?>>
                                <?= $isSuspended ? 'Activate' : 'Suspend' ?>
                            </button>
                        </form>

                        <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $uid ?>">
                            <button type="submit" class="btn r s" <?= $isSelf ? 'disabled title="Cannot delete own account"' : '' ?>>
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="meta">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mod" id="addUserModal">
    <div class="card">
        <h3 style="font-family:Inter;margin-bottom:8px">Add New User</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">

            <label for="userName">Full Name</label>
            <input type="text" id="userName" name="name" class="inp" placeholder="e.g. Nimal Perera" required>

            <label for="userEmail">Email Address</label>
            <input type="email" id="userEmail" name="email" class="inp" placeholder="e.g. nimal@students.nsbm.ac.lk" required>

            <label for="userPassword">Password (min 6 characters)</label>
            <input type="password" id="userPassword" name="password" class="inp" minlength="6" required>

            <label for="userRole">Role</label>
            <select id="userRole" name="role" class="inp">
                <option value="student">Student</option>
                <option value="moderator">Moderator</option>
                <option value="admin">Admin</option>
            </select>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
                <button type="button" class="btn o" onclick="AdminUI.closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn">Save User</button>
            </div>
        </form>
    </div>
</div>

<?php admin_render_end(false); ?>