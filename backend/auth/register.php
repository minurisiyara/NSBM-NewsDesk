<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/database.php';

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/auth/register.php');
    exit;
}


// CSRF Validation
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submittedToken)
) {
    $_SESSION['register_error'] = 'Invalid request. Please try again.';
    header('Location: ../../pages/auth/register.php');
    exit;
}


// Get form values
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$admissionNo = trim($_POST['admission_no'] ?? '');

$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$faculty = trim($_POST['faculty'] ?? '');
$degreeProgram = trim($_POST['degree_program'] ?? '');

$batch = trim($_POST['batch'] ?? '');
$academicYear = trim($_POST['academic_year'] ?? '');


// Preserve form values if registration fails
$_SESSION['register_old'] = [
    'name' => $name,
    'email' => $email,
    'admission_no' => $admissionNo,
    'faculty' => $faculty,
    'degree_program' => $degreeProgram,
    'batch' => $batch,
    'academic_year' => $academicYear
];


// Faculty and Degree Program list
$degreePrograms = [

    'Faculty of Computing' => [
        'BSc (Hons) in Software Engineering',
        'BSc (Hons) in Computer Science',
        'BSc (Hons) in Data Science',
        'BSc (Hons) in Computer Networks',
        'BSc in Management Information Systems (Special)',
        'BSc (Hons) Artificial Intelligence',
        'BSc (Hons) Computer Security',
        'BSc (Hons) Technology Management',
        'Bachelor of Information Technology (Major in Cyber Security)'
    ],

    'Faculty of Business' => [
        'BM (Honors) in Business Analytics',
        'BM (Honors) in Applied Economics',
        'BBM (Hons) Tourism, Hospitality & Events',
        'BSc in Multimedia',
        'BA in Business Communication',
        'BM (Hons) in Accounting and Finance',
        'BMgtHons (International Business)',
        'BSc in Business Management (Industrial Management) (Special)',
        'BSc in Business Management (Project Management) (Special)',
        'BSc in Business Management (Logistics Management) (Special)',
        'BSc in Business Management (Human Resource Management) (Special)',
        'Bachelor of Laws (Honours)',
        'BM (Hons) in Marketing Management',
        'BM (Hons) in Law and Business Studies',
        'BM (Hons.) in Law and International Trade',
        'BM (Hons) in Law and E-Commerce',
        'BSc (Hons) International Management and Business',
        'BSc (Hons) Operations and Logistics Management',
        'BSc (Hons) Events, Tourism and Hospitality Management'
    ],

    'Faculty of Engineering' => [
        'BSc Engineering Honours in Mechatronic Engineering',
        'BSc Engineering Honours in Electrical and Electronic Engineering',
        'BSc Engineering Honours in Computer System Engineering',
        'BSc Engineering Honours in Civil Engineering',
        'Bachelor of Interior Design',
        'BSc (Hons) Quantity Surveying',
        'BEng (Hons) Civil and Structural Engineering',
        'BEng (Hons) Mechanical and Mechatronics Engineering',
        'BEng (Hons) Robotics and Automation Engineering',
        'BEng (Hons) Electronic and Computer Engineering',
        'BEng (Hons) Automotive Engineering',
        'BEng (Hons) Naval Architecture and Marine Engineering'
    ],

    'Faculty of Science' => [
        'BSc (Hons) in Biomedical Science',
        'BSc (Hons) in Pharmaceutical Science',
        'BSc (Hons) Nutrition and Health',
        'BSc (Hons) Psychology',
        'BSc (Hons) Nursing'
    ],

    'Faculty of Postgraduate Studies and Professional Advancement' => [
        'Postgraduate / Professional Programme'
    ]

];


// Validate registration details
$error = '';

if ($name === '' || strlen($name) > 100) {

    $error = 'Please enter a valid full name.';

} elseif (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 255
) {

    $error = 'Please enter a valid email address.';

} elseif ($admissionNo === '' || strlen($admissionNo) > 30) {

    $error = 'Please enter a valid admission number.';

} elseif (
    !is_string($password) ||
    strlen($password) < 8 ||
    strlen($password) > 72
) {

    $error = 'Password must contain 8 to 72 characters.';

} elseif (
    !is_string($confirmPassword) ||
    $password !== $confirmPassword
) {

    $error = 'Passwords do not match.';

} elseif (!isset($degreePrograms[$faculty])) {

    $error = 'Please select a valid faculty.';

} elseif (
    !in_array($degreeProgram, $degreePrograms[$faculty], true)
) {

    $error = 'Please select a valid degree program.';

} elseif (!preg_match('/^2[3-7]\.[1-3]$/', $batch)) {

    $error = 'Please select a valid batch.';

} elseif (
    !in_array(
        $academicYear,
        ['Year 1', 'Year 2', 'Year 3', 'Year 4'],
        true
    )
) {

    $error = 'Please select a valid academic year.';

}


// Stop if validation fails
if ($error !== '') {

    $_SESSION['register_error'] = $error;

    header('Location: ../../pages/auth/register.php');
    exit;

}


// Save student account
try {

    // Check duplicate email or admission number
    $checkStmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE email = :email
        OR admission_no = :admission_no
        LIMIT 1
    ");

    $checkStmt->execute([
        ':email' => $email,
        ':admission_no' => $admissionNo
    ]);


    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {

        $_SESSION['register_error'] =
            'Email address or Admission Number is already registered.';

        header('Location: ../../pages/auth/register.php');
        exit;

    }


    // Securely hash password
    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    // Insert student details into database
    $insertStmt = $pdo->prepare("
        INSERT INTO users (
            name,
            email,
            password,
            role,
            admission_no,
            faculty,
            degree_program,
            batch,
            academic_year
        )
        VALUES (
            :name,
            :email,
            :password,
            :role,
            :admission_no,
            :faculty,
            :degree_program,
            :batch,
            :academic_year
        )
    ");


    $insertStmt->execute([

        ':name' => $name,

        ':email' => $email,

        ':password' => $hashedPassword,

        ':role' => 'student',

        ':admission_no' => $admissionNo,

        ':faculty' => $faculty,

        ':degree_program' => $degreeProgram,

        ':batch' => $batch,

        ':academic_year' => $academicYear

    ]);


    // Registration successful
    unset($_SESSION['register_old']);

    $_SESSION['register_success'] =
        'Registration successful! You can now log in.';

    // Generate a new CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: ../../pages/auth/login.php');
    exit;


} catch (PDOException $e) {

    error_log('Registration Error: ' . $e->getMessage());

    $_SESSION['register_error'] =
        'Registration failed. Please try again.';

    header('Location: ../../pages/auth/register.php');
    exit;

}