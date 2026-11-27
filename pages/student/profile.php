<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Check student role
if (($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

// Student details
$userId = (int) $_SESSION['user_id'];
$activePage = 'profile';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

$projectRoot = dirname(__DIR__, 2);
$uploadDirectory = $projectRoot . '/uploads/profiles/';


// ==========================================
// DELETE OLD PROFILE PHOTO
// ==========================================

$deleteOldImage = static function (?string $imagePath) use ($uploadDirectory): void {

    if (
        !$imagePath ||
        strpos($imagePath, 'uploads/profiles/') !== 0
    ) {
        return;
    }

    $fileName = basename($imagePath);

    if ($fileName === '' || $fileName === '.' || $fileName === '..') {
        return;
    }

    $fullPath = $uploadDirectory . $fileName;

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
};


// ==========================================
// HANDLE PHOTO ACTIONS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {

        $error = 'Invalid request. Please refresh the page and try again.';

    } else {

        $action = $_POST['action'] ?? '';


        // ==================================
        // UPLOAD / CHANGE PROFILE PHOTO
        // ==================================

        if ($action === 'upload_photo') {

            if (
                !isset($_FILES['profile_photo']) ||
                $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE
            ) {

                $error = 'Please select a profile photo.';

            } else {

                $image = $_FILES['profile_photo'];

                if ($image['error'] !== UPLOAD_ERR_OK) {

                    $error = 'Photo upload failed. Please try again.';

                } elseif ($image['size'] > 2 * 1024 * 1024) {

                    $error = 'Photo size must be less than 2 MB.';

                } else {

                    // Allowed image types
                    $allowedTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $fileInfo->file($image['tmp_name']);

                    $imageInfo = @getimagesize($image['tmp_name']);

                    if (
                        !isset($allowedTypes[$mimeType]) ||
                        $imageInfo === false ||
                        $imageInfo['mime'] !== $mimeType
                    ) {

                        $error = 'Only valid JPG, PNG and WEBP images are allowed.';

                    } else {

                        // Create upload folder
                        if (
                            !is_dir($uploadDirectory) &&
                            !mkdir($uploadDirectory, 0755, true) &&
                            !is_dir($uploadDirectory)
                        ) {

                            $error = 'Unable to create profile upload folder.';

                        } else {

                            // Generate random file name
                            $fileName = bin2hex(random_bytes(16))
                                . '.'
                                . $allowedTypes[$mimeType];

                            $destination = $uploadDirectory . $fileName;

                            if (
                                move_uploaded_file(
                                    $image['tmp_name'],
                                    $destination
                                )
                            ) {

                                $newImagePath = 'uploads/profiles/' . $fileName;

                                try {

                                    // Get previous photo
                                    $oldStmt = $pdo->prepare("
                                        SELECT profile_image
                                        FROM users
                                        WHERE id = ?
                                    ");

                                    $oldStmt->execute([$userId]);

                                    $oldImage = $oldStmt->fetchColumn();

                                    // Update database
                                    $update = $pdo->prepare("
                                        UPDATE users
                                        SET profile_image = ?
                                        WHERE id = ?
                                    ");

                                    $update->execute([
                                        $newImagePath,
                                        $userId
                                    ]);

                                    // Delete previous photo
                                    $deleteOldImage($oldImage ?: null);

                                    $message = 'Profile photo updated successfully!';

                                } catch (PDOException $e) {

                                    error_log('Profile photo error: ' . $e->getMessage());

                                    if (is_file($destination)) {
                                        unlink($destination);
                                    }

                                    $error = 'Unable to update profile photo. Please try again.';

                                }

                            } else {

                                $error = 'Unable to upload profile photo.';

                            }
                        }
                    }
                }
            }
        }


        // ==================================
        // REMOVE PROFILE PHOTO
        // ==================================

        elseif ($action === 'remove_photo') {

            try {

                // Get current photo
                $stmt = $pdo->prepare("
                    SELECT profile_image
                    FROM users
                    WHERE id = ?
                ");

                $stmt->execute([$userId]);

                $oldImage = $stmt->fetchColumn();

                // Remove image from database
                $update = $pdo->prepare("
                    UPDATE users
                    SET profile_image = NULL
                    WHERE id = ?
                ");

                $update->execute([$userId]);

                // Delete image file
                $deleteOldImage($oldImage ?: null);

                $message = 'Profile photo removed successfully!';

            } catch (PDOException $e) {

                error_log('Profile photo removal error: ' . $e->getMessage());

                $error = 'Unable to remove profile photo. Please try again.';

            }

        }

        else {

            $error = 'Invalid action.';

        }


        // Generate new CSRF token after success
        if ($message !== '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

    }
}


// ==========================================
// GET STUDENT INFORMATION
// ==========================================

$stmt = $pdo->prepare("
    SELECT
        name,
        email,
        profile_image,
        admission_no,
        faculty,
        degree_program,
        batch,
        academic_year
    FROM users
    WHERE id = ?
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}


// ==========================================
// PROFILE PHOTO URL
// ==========================================

$profileImageUrl = null;

if (
    !empty($user['profile_image']) &&
    strpos($user['profile_image'], 'uploads/profiles/') === 0
) {

    $fileName = basename($user['profile_image']);

    $profileFile = $uploadDirectory . $fileName;

    if (is_file($profileFile)) {

        $profileImageUrl = '../../uploads/profiles/'
            . rawurlencode($fileName)
            . '?v='
            . filemtime($profileFile);

    }
}

// Default avatar letter
$firstLetter = strtoupper(substr($user['name'], 0, 1));

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | NSBM NewsDesk</title>

    <!-- Main Student CSS -->
    <link rel="stylesheet" href="../../assets/css/student.css">

    <!-- Profile CSS -->
    <link rel="stylesheet" href="../../assets/css/profile.css?v=6">

    <!-- Student Logo CSS -->
    <link rel="stylesheet" href="../../assets/css/student-logo.css?v=3">

    <!-- Student Topbar CSS -->
    <link rel="stylesheet" href="../../assets/css/student-topbar.css?v=2">

</head>

<body>

    <div class="dashboard-container">


        <!-- Common Student Sidebar -->

        <?php
        include dirname(__DIR__, 2) . '/components/student-sidebar.php';
        ?>


        <!-- Main Content -->

        <main class="main-content">


            <!-- Common Student Topbar -->

            <?php
            include dirname(__DIR__, 2) . '/components/student-topbar.php';
            ?>


            <!-- Profile Content -->

            <section class="profile-content">

                <div class="profile-card">


                    <!-- Profile Heading -->

                    <div class="profile-heading">

                        <h2>My Profile</h2>

                        <p>
                            View your personal and academic information.
                        </p>

                    </div>


                    <!-- Success Message -->

                    <?php if ($message !== ''): ?>

                        <div class="success-message">

                            <?= htmlspecialchars(
                                $message,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <!-- Error Message -->

                    <?php if ($error !== ''): ?>

                        <div class="error-message">

                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <!-- ==================================
                         PROFILE PHOTO
                    =================================== -->

                    <div class="profile-photo-section">

                        <h3>Profile Photo</h3>


                        <div class="profile-photo-wrapper">

                            <?php if ($profileImageUrl): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                        $profileImageUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt="Student Profile Photo"
                                    class="profile-photo"
                                    id="profilePreview"
                                >

                            <?php else: ?>

                                <div class="profile-avatar" id="profileAvatar">

                                    <?= htmlspecialchars(
                                        $firstLetter,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                            <?php endif; ?>

                        </div>


                        <p class="photo-help">

                            Upload a JPG, PNG or WEBP image.
                            Maximum size: 2 MB.

                        </p>


                        <!-- Upload / Change Photo -->

                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            class="photo-upload-form"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="upload_photo"
                            >

                            <input
                                type="file"
                                name="profile_photo"
                                id="profilePhotoInput"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                            >

                            <button
                                type="submit"
                                class="profile-save-btn"
                            >
                                <?= $profileImageUrl ? 'Change Profile Photo' : 'Upload Profile Photo' ?>
                            </button>

                        </form>


                        <!-- Remove Photo -->

                        <?php if ($profileImageUrl): ?>

                            <form method="POST" class="remove-photo-form">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(
                                        $_SESSION['csrf_token'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="remove_photo"
                                >

                                <button
                                    type="submit"
                                    class="remove-photo-btn"
                                    onclick="return confirm('Are you sure you want to remove your profile photo?');"
                                >
                                    Remove Photo
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>


                    <hr class="profile-divider">


                    <!-- ==================================
                         PERSONAL INFORMATION
                    =================================== -->

                    <div class="information-section">

                        <div class="section-title">

                            <h3>Personal Information</h3>

                            <p>
                                Your registered personal details.
                            </p>

                        </div>


                        <div class="profile-fields-grid">


                            <div class="profile-info-item">

                                <span>Full Name</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                            <div class="profile-info-item">

                                <span>Email Address</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['email'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                            <div class="profile-info-item">

                                <span>Admission Number</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['admission_no'] ?: 'Not assigned',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                        </div>

                    </div>


                    <hr class="profile-divider">


                    <!-- ==================================
                         ACADEMIC INFORMATION
                    =================================== -->

                    <div class="information-section">

                        <div class="section-title">

                            <h3>Academic Information</h3>

                            <p>
                                Your university and degree programme details.
                            </p>

                        </div>


                        <div class="profile-fields-grid">


                            <div class="profile-info-item">

                                <span>Faculty</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['faculty'] ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                            <div class="profile-info-item">

                                <span>Degree Programme</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['degree_program'] ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                            <div class="profile-info-item">

                                <span>Batch</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['batch'] ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                            <div class="profile-info-item">

                                <span>Academic Year</span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $user['academic_year'] ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>


                        </div>

                    </div>


                    <!-- Admin Notice -->

                    <div class="profile-notice">

                        <strong>Personal and Academic Information</strong>

                        <p>
                            Your personal and academic information is managed
                            by the university administrator. Please contact
                            the administrator if any details need to be changed.
                            You can update your profile photo at any time.
                        </p>

                    </div>


                    <!-- Back Button -->

                    <div class="profile-buttons">

                        <a
                            href="dashboard.php"
                            class="profile-cancel-btn"
                        >
                            Back to Dashboard
                        </a>

                    </div>


                </div>

            </section>

        </main>

    </div>


    <!-- Image Preview Script -->

    <script>

        const photoInput = document.getElementById('profilePhotoInput');

        if (photoInput) {

            photoInput.addEventListener('change', function () {

                const file = this.files[0];

                if (!file) {
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {

                    alert('Please select a JPG, PNG or WEBP image.');

                    this.value = '';

                    return;
                }

                if (file.size > 2 * 1024 * 1024) {

                    alert('Photo size must be less than 2 MB.');

                    this.value = '';

                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {

                    let preview = document.getElementById('profilePreview');

                    if (!preview) {

                        const avatar = document.getElementById('profileAvatar');

                        preview = document.createElement('img');

                        preview.id = 'profilePreview';

                        preview.className = 'profile-photo';

                        preview.alt = 'Profile Photo Preview';

                        if (avatar) {
                            avatar.replaceWith(preview);
                        }

                    }

                    preview.src = event.target.result;

                };

                reader.readAsDataURL(file);

            });

        }

    </script>

</body>

</html>