<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

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

$userId = (int) $_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? 'Student';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

// Get categories
$stmt = $pdo->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

$categories = $stmt->fetchAll();

$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check CSRF token
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        $errorMessage = 'Invalid request. Please refresh the page and try again.';
    } else {

        $title = trim($_POST['title'] ?? '');
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $content = trim($_POST['content'] ?? '');
        $action = $_POST['action'] ?? 'submit';

        // Validate input
        if ($title === '' || strlen($title) > 255) {

            $errorMessage = 'Please enter a valid article title (maximum 255 characters).';

        } elseif (!$categoryId || $categoryId < 1) {

            $errorMessage = 'Please select a category.';

        } elseif ($content === '') {

            $errorMessage = 'Please enter your article content.';

        } elseif (!in_array($action, ['draft', 'submit'], true)) {

            $errorMessage = 'Invalid submission action.';

        } else {

            // Check category exists
            $categoryCheck = $pdo->prepare("
                SELECT id
                FROM categories
                WHERE id = ?
            ");

            $categoryCheck->execute([$categoryId]);

            if (!$categoryCheck->fetch()) {

                $errorMessage = 'Selected category does not exist.';

            } else {

                $imagePath = null;

                // Handle image upload
                if (
                    isset($_FILES['image']) &&
                    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ) {

                    $image = $_FILES['image'];

                    if ($image['error'] !== UPLOAD_ERR_OK) {

                        $errorMessage = 'Image upload failed. Please try again.';

                    } elseif ($image['size'] > 5 * 1024 * 1024) {

                        $errorMessage = 'Image size must be less than 5 MB.';

                    } else {

                        $allowedTypes = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp'
                        ];

                        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $fileInfo->file($image['tmp_name']);

                        if (!isset($allowedTypes[$mimeType])) {

                            $errorMessage = 'Only JPG, PNG and WEBP images are allowed.';

                        } else {

                            $uploadDirectory = dirname(__DIR__, 2)
                                . '/uploads/articles/';

                            if (
                                !is_dir($uploadDirectory) &&
                                !mkdir($uploadDirectory, 0755, true)
                            ) {
                                $errorMessage = 'Unable to create upload directory.';
                            } else {

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

                                    $imagePath = 'uploads/articles/' . $fileName;

                                } else {

                                    $errorMessage = 'Unable to save uploaded image.';

                                }
                            }
                        }
                    }
                }

                // Save article if there are no errors
                if ($errorMessage === '') {

                    $status = ($action === 'draft') ? 'draft' : 'pending';

                    try {

                        $stmt = $pdo->prepare("
                            INSERT INTO articles
                            (
                                title,
                                content,
                                author_id,
                                category_id,
                                status,
                                image_path
                            )
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");

                        $stmt->execute([
                            $title,
                            $content,
                            $userId,
                            $categoryId,
                            $status,
                            $imagePath
                        ]);

                        if ($action === 'draft') {

                            $successMessage = 'Your article has been saved as a draft.';

                        } else {

                            $successMessage = 'Your article has been submitted for review successfully.';

                        }

                        // Clear form values
                        $_POST = [];

                        // Generate a new CSRF token
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    } catch (PDOException $e) {

                        // Remove uploaded file if database save fails
                        if ($imagePath !== null) {

                            $savedImage = dirname(__DIR__, 2)
                                . '/' . $imagePath;

                            if (is_file($savedImage)) {
                                unlink($savedImage);
                            }
                        }

                        error_log('Article submission failed: ' . $e->getMessage());

                        $errorMessage = 'Unable to save your article. Please try again.';
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Submit Article | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../../assets/css/student.css">

    <link rel="stylesheet" href="../../assets/css/submit-article.css?v=2">
</head>

<body>

    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-icon">N</div>

            <div>
                <h2>NSBM</h2>
                <span>NewsDesk Portal</span>
            </div>

        </div>

        <nav class="sidebar-nav">

            <a href="dashboard.php">Dashboard</a>

            <a href="my-submissions.php">My Submissions</a>

            <a href="submit-article.php" class="active">Submit Article</a>

            <a href="bookmarks.php">Saved Research</a>

            <a href="profile.php">Scholar Profile</a>

        </nav>

        <div class="sidebar-bottom">

            <a href="../../index.php">Back to Journal</a>

            <span>NSBM Green University</span>

        </div>

    </aside>


    <!-- Main Content -->

    <div class="main-content">

        <!-- Topbar -->

        <header class="topbar">

            <span class="portal-label">STUDENT PORTAL</span>

            <div class="topbar-right">

                <span>
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </span>

                <form action="../../backend/auth/logout.php" method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <button type="submit" class="logout-button">
                        Log Out
                    </button>

                </form>

            </div>

        </header>


        <!-- Submit Article Content -->

        <main class="dashboard-content">

            <section class="welcome-banner">

                <span class="welcome-label">ARTICLE CONTRIBUTION</span>

                <h1>Submit New Article</h1>

                <p>
                    Share your knowledge and ideas with the NSBM NewsDesk community.
                    Your article will be reviewed by the editorial team before publication.
                </p>

            </section>


            <section class="article-form-container">

                <div class="form-heading">

                    <h2>Article Information</h2>

                    <p>Complete the following details to submit your article.</p>

                </div>


                <?php if ($successMessage !== ''): ?>

                    <div class="form-message success-message">

                        <?= htmlspecialchars(
                            $successMessage,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        <br>

                        <a href="my-submissions.php">
                            View My Submissions
                        </a>

                    </div>

                <?php endif; ?>


                <?php if ($errorMessage !== ''): ?>

                    <div class="form-message error-message">

                        <?= htmlspecialchars(
                            $errorMessage,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                <?php endif; ?>


                <form
                    id="articleForm"
                    action="submit-article.php"
                    method="POST"
                    enctype="multipart/form-data"
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


                    <!-- Article Title -->

                    <div class="article-group">

                        <label for="title">Article Title</label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            maxlength="255"
                            placeholder="Enter your article title"
                            value="<?= htmlspecialchars(
                                $_POST['title'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- Category -->

                    <div class="article-group">

                        <label for="category_id">Select Category</label>

                        <select
                            id="category_id"
                            name="category_id"
                            required
                        >

                            <option value="">Choose a category</option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= (int) $category['id'] ?>"
                                    <?= (
                                        (string) ($_POST['category_id'] ?? '') ===
                                        (string) $category['id']
                                    ) ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars(
                                        $category['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Article Content -->

                    <div class="article-group">

                        <label for="content">Article Content</label>

                        <textarea
                            id="content"
                            name="content"
                            rows="12"
                            placeholder="Write your full article here..."
                            required
                        ><?= htmlspecialchars(
                            $_POST['content'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></textarea>

                    </div>


                    <!-- Featured Image -->

                    <div class="article-group">

                        <label for="image">Featured Image (Optional)</label>

                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >

                        <small>
                            Allowed formats: JPG, PNG, WEBP. Maximum size: 5 MB.
                        </small>

                        <div class="image-preview-container">

                            <img
                                id="imagePreview"
                                src=""
                                alt="Selected image preview"
                                style="display: none;"
                            >

                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="form-buttons">

                        <button
                            type="submit"
                            name="action"
                            value="draft"
                            class="draft-button"
                        >
                            Save as Draft
                        </button>

                        <button
                            type="submit"
                            name="action"
                            value="submit"
                            class="submit-btn"
                        >
                            Submit for Review
                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>


    <!-- Image Preview -->

    <script>

        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');

        imageInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
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
                imagePreview.style.display = 'none';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must be less than 5 MB.');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                imagePreview.src = event.target.result;
                imagePreview.style.display = 'block';
            };

            reader.readAsDataURL(file);

        });

    </script>

</body>

</html>