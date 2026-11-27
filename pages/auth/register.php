<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Messages
$errorMessage = $_SESSION['register_error'] ?? '';
$successMessage = $_SESSION['register_success'] ?? '';

$old = $_SESSION['register_old'] ?? [];

unset($_SESSION['register_error']);
unset($_SESSION['register_success']);
unset($_SESSION['register_old']);


// Faculty and Degree Programs
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


// Previous selected values
$selectedFaculty = $old['faculty'] ?? '';
$selectedProgram = $old['degree_program'] ?? '';


// Escape output
function escapeValue($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration | NSBM NewsDesk</title>

    <style>

        /* =========================
           GENERAL
        ========================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7f5;
            color: #26352b;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }


        /* =========================
           MAIN LAYOUT
        ========================= */

        .register-page {
            display: grid;
            grid-template-columns: 40% 60%;
            min-height: 100vh;
        }


        /* =========================
           LEFT BRANDING PANEL
        ========================= */

        .brand-panel {
            background: linear-gradient(
                145deg,
                #0d3b1c,
                #17652d,
                #23883e
            );

            color: white;
            padding: 55px 45px;

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: "";
            position: absolute;

            width: 350px;
            height: 350px;

            border-radius: 50%;

            background: rgba(255,255,255,0.06);

            right: -130px;
            top: 20%;
        }

        .brand-panel::after {
            content: "";
            position: absolute;

            width: 250px;
            height: 250px;

            border-radius: 50%;

            background: rgba(255,255,255,0.05);

            left: -100px;
            bottom: 5%;
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }


        /* Logo */

        .brand-logo-wrapper {
            width: 145px;
            height: 110px;

            background: #ffffff;

            border-radius: 14px;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 12px;

            margin-bottom: 45px;
        }

        .brand-logo {
            width: 100% !important;
            height: 100% !important;

            max-width: 100% !important;
            max-height: 100% !important;

            object-fit: contain !important;
            display: block !important;
        }


        /* Branding text */

        .brand-heading {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.3;

            margin-bottom: 18px;
        }

        .brand-heading span {
            color: #b8ed9d;
        }

        .brand-description {
            color: #e0f1e4;

            font-size: 15px;
            line-height: 1.8;

            max-width: 390px;
        }


        /* Benefits */

        .brand-features {
            margin-top: 40px;

            display: flex;
            flex-direction: column;
            gap: 17px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 13px;

            color: #f0f8f1;
            font-size: 14px;
        }

        .feature-icon {
            width: 30px;
            height: 30px;

            background: rgba(255,255,255,0.15);

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 14px;
            font-weight: bold;
        }


        /* Footer */

        .brand-footer {
            position: relative;
            z-index: 1;

            color: #d0e8d5;
            font-size: 12px;
            line-height: 1.7;

            margin-top: 50px;
        }


        /* =========================
           RIGHT FORM SECTION
        ========================= */

        .form-panel {
            padding: 45px 55px;

            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .form-wrapper {
            width: 100%;
            max-width: 720px;
        }


        /* Header */

        .form-top {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 35px;
        }

        .form-top-text {
            font-size: 13px;
            color: #718076;
        }

        .login-link {
            color: #17652d;
            font-size: 13px;
            font-weight: bold;
        }

        .login-link:hover {
            text-decoration: underline;
        }


        /* Form title */

        .form-heading {
            margin-bottom: 28px;
        }

        .form-heading h1 {
            font-size: 30px;
            color: #183d25;

            margin-bottom: 10px;
        }

        .form-heading p {
            font-size: 14px;
            color: #77847b;
            line-height: 1.6;
        }


        /* Messages */

        .form-message {
            padding: 14px 16px;
            border-radius: 8px;

            margin-bottom: 22px;

            font-size: 13px;
            line-height: 1.5;
        }

        .error-message {
            background: #fff0f0;
            border: 1px solid #ffcaca;
            color: #bd3030;
        }

        .success-message {
            background: #eaf8ed;
            border: 1px solid #c9ebd0;
            color: #236d34;
        }


        /* =========================
           FORM CARD
        ========================= */

        .register-form {
            background: #ffffff;

            border: 1px solid #e5ebe6;
            border-radius: 14px;

            padding: 30px;

            box-shadow: 0 7px 25px rgba(23, 70, 36, 0.05);
        }


        /* Section */

        .form-section {
            margin-bottom: 30px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 12px;

            margin-bottom: 22px;
            padding-bottom: 13px;

            border-bottom: 1px solid #edf1ed;
        }

        .section-number {
            width: 32px;
            height: 32px;

            background: #e9f5eb;
            color: #17652d;

            border-radius: 8px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 13px;
            font-weight: bold;
        }

        .section-heading h3 {
            color: #244a2e;
            font-size: 16px;
        }

        .section-heading p {
            color: #87928a;
            font-size: 11px;

            margin-top: 4px;
        }


        /* Form grid */

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 19px 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }


        /* Labels */

        .form-group label {
            color: #3c5041;

            font-size: 12px;
            font-weight: bold;

            margin-bottom: 8px;
        }

        .required {
            color: #dc5151;
        }


        /* Inputs and Selects */

        .form-group input,
        .form-group select {
            width: 100%;
            height: 45px;

            padding: 0 13px;

            border: 1px solid #dce5de;
            border-radius: 7px;

            background: #fbfdfb;

            color: #34443a;
            font-size: 13px;

            outline: none;

            transition: 0.2s;
        }

        .form-group input::placeholder {
            color: #a5afa8;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #23883e;

            background: #ffffff;

            box-shadow: 0 0 0 3px rgba(35,136,62,0.09);
        }

        .form-group select {
            cursor: pointer;
        }

        .field-help {
            color: #929c94;
            font-size: 11px;

            margin-top: 7px;
            line-height: 1.5;
        }


        /* Security note */

        .security-note {
            background: #f1f8f2;

            border: 1px solid #dceede;
            border-radius: 8px;

            padding: 13px 15px;

            display: flex;
            gap: 10px;

            color: #54745a;

            font-size: 12px;
            line-height: 1.6;

            margin-bottom: 22px;
        }

        .security-note strong {
            color: #286339;
        }


        /* Register button */

        .register-button {
            width: 100%;
            height: 49px;

            border: none;
            border-radius: 8px;

            background: linear-gradient(
                135deg,
                #17652d,
                #23883e
            );

            color: #ffffff;

            font-size: 14px;
            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        .register-button:hover {
            background: linear-gradient(
                135deg,
                #105322,
                #17652d
            );

            box-shadow: 0 5px 13px rgba(23,101,45,0.2);

            transform: translateY(-1px);
        }


        /* Bottom text */

        .form-bottom {
            text-align: center;

            color: #849087;
            font-size: 12px;

            margin-top: 22px;
            line-height: 1.7;
        }

        .form-bottom a {
            color: #17652d;
            font-weight: bold;
        }


        /* =========================
           RESPONSIVE DESIGN
        ========================= */

        @media (max-width: 1000px) {

            .register-page {
                grid-template-columns: 34% 66%;
            }

            .brand-panel {
                padding: 40px 28px;
            }

            .form-panel {
                padding: 35px 30px;
            }

            .brand-heading {
                font-size: 27px;
            }

        }


        @media (max-width: 768px) {

            .register-page {
                display: block;
            }

            .brand-panel {
                min-height: auto;

                padding: 25px 25px 30px;
            }

            .brand-logo-wrapper {
                width: 110px;
                height: 85px;

                margin-bottom: 25px;
            }

            .brand-heading {
                font-size: 25px;
            }

            .brand-description {
                font-size: 13px;
            }

            .brand-features {
                display: none;
            }

            .brand-footer {
                margin-top: 25px;
            }

            .form-panel {
                padding: 30px 20px;
            }

        }


        @media (max-width: 500px) {

            .form-panel {
                padding: 25px 12px;
            }

            .register-form {
                padding: 20px 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .form-heading h1 {
                font-size: 25px;
            }

            .form-top {
                margin-bottom: 25px;
            }

        }

    </style>

</head>


<body>

<div class="register-page">


    <!-- =========================
         LEFT BRANDING PANEL
    ========================== -->

    <aside class="brand-panel">

        <div class="brand-content">

            <div class="brand-logo-wrapper">

                <img
                    src="../../assets/images/nsbm-logo.png"
                    alt="NSBM Green University Logo"
                    class="brand-logo"
                >

            </div>


            <h2 class="brand-heading">

                Welcome to

                <br>

                <span>NSBM NewsDesk</span>

            </h2>


            <p class="brand-description">

                Your university news and student publishing platform.

                Create your student account and become part of the
                NSBM NewsDesk community.

            </p>


            <div class="brand-features">

                <div class="feature-item">

                    <div class="feature-icon">✓</div>

                    <span>Submit your articles easily</span>

                </div>


                <div class="feature-item">

                    <div class="feature-icon">✓</div>

                    <span>Manage your article submissions</span>

                </div>


                <div class="feature-item">

                    <div class="feature-icon">✓</div>

                    <span>Stay connected with university news</span>

                </div>

            </div>

        </div>


        <div class="brand-footer">

            NSBM Green University

            <br>

            NSBM NewsDesk Student Portal

        </div>

    </aside>



    <!-- =========================
         RIGHT REGISTRATION FORM
    ========================== -->

    <main class="form-panel">

        <div class="form-wrapper">


            <!-- Top navigation -->

            <div class="form-top">

                <span class="form-top-text">
                    STUDENT REGISTRATION
                </span>

                <a href="login.php" class="login-link">
                    Already registered? Login
                </a>

            </div>



            <!-- Heading -->

            <div class="form-heading">

                <h1>Create Your Account</h1>

                <p>
                    Complete the information below to create your
                    NSBM NewsDesk student account.
                </p>

            </div>



            <!-- Error message -->

            <?php if ($errorMessage !== ''): ?>

                <div class="form-message error-message">

                    <?= escapeValue($errorMessage) ?>

                </div>

            <?php endif; ?>


            <!-- Success message -->

            <?php if ($successMessage !== ''): ?>

                <div class="form-message success-message">

                    <?= escapeValue($successMessage) ?>

                </div>

            <?php endif; ?>



            <!-- Registration Form -->

            <form
                action="../../backend/auth/register.php"
                method="POST"
                class="register-form"
                id="registerForm"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeValue($_SESSION['csrf_token']) ?>"
                >



                <!-- =========================
                     SECTION 01
                     PERSONAL DETAILS
                ========================== -->

                <section class="form-section">

                    <div class="section-heading">

                        <div class="section-number">01</div>

                        <div>

                            <h3>Personal Details</h3>

                            <p>Enter your personal information</p>

                        </div>

                    </div>


                    <div class="form-grid">


                        <div class="form-group full-width">

                            <label for="name">
                                Full Name <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Enter your full name"
                                value="<?= escapeValue($old['name'] ?? '') ?>"
                                maxlength="100"
                                autocomplete="name"
                                required
                            >

                        </div>



                        <div class="form-group">

                            <label for="email">
                                Email Address <span class="required">*</span>
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="example@email.com"
                                value="<?= escapeValue($old['email'] ?? '') ?>"
                                maxlength="255"
                                autocomplete="email"
                                required
                            >

                        </div>



                        <div class="form-group">

                            <label for="admission_no">
                                Admission Number <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="admission_no"
                                name="admission_no"
                                placeholder="Enter admission number"
                                value="<?= escapeValue($old['admission_no'] ?? '') ?>"
                                maxlength="30"
                                required
                            >

                        </div>


                    </div>

                </section>




                <!-- =========================
                     SECTION 02
                     ACADEMIC DETAILS
                ========================== -->

                <section class="form-section">

                    <div class="section-heading">

                        <div class="section-number">02</div>

                        <div>

                            <h3>Academic Details</h3>

                            <p>Select your university academic information</p>

                        </div>

                    </div>


                    <div class="form-grid">


                        <!-- Faculty -->

                        <div class="form-group full-width">

                            <label for="faculty">
                                Faculty <span class="required">*</span>
                            </label>

                            <select
                                name="faculty"
                                id="faculty"
                                required
                            >

                                <option value="">
                                    Select Your Faculty
                                </option>


                                <?php foreach (array_keys($degreePrograms) as $faculty): ?>

                                    <option
                                        value="<?= escapeValue($faculty) ?>"
                                        <?= $selectedFaculty === $faculty ? 'selected' : '' ?>
                                    >

                                        <?= escapeValue($faculty) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>



                        <!-- Degree Program -->

                        <div class="form-group full-width">

                            <label for="degree_program">
                                Degree Program <span class="required">*</span>
                            </label>

                            <select
                                name="degree_program"
                                id="degree_program"
                                required
                            >

                                <option value="">
                                    Select Your Degree Program
                                </option>

                            </select>

                            <span class="field-help">
                                Select your faculty first to see the available programmes.
                            </span>

                        </div>



                        <!-- Batch -->

                        <div class="form-group">

                            <label for="batch">
                                Batch <span class="required">*</span>
                            </label>

                            <select
                                name="batch"
                                id="batch"
                                required
                            >

                                <option value="">
                                    Select Your Batch
                                </option>


                                <?php for ($year = 23; $year <= 27; $year++): ?>

                                    <?php for ($intake = 1; $intake <= 3; $intake++): ?>

                                        <?php $batchValue = $year . '.' . $intake; ?>

                                        <option
                                            value="<?= escapeValue($batchValue) ?>"
                                            <?= ($old['batch'] ?? '') === $batchValue ? 'selected' : '' ?>
                                        >

                                            <?= escapeValue($batchValue) ?>

                                        </option>

                                    <?php endfor; ?>

                                <?php endfor; ?>

                            </select>

                        </div>



                        <!-- Academic Year -->

                        <div class="form-group">

                            <label for="academic_year">
                                Academic Year <span class="required">*</span>
                            </label>

                            <select
                                name="academic_year"
                                id="academic_year"
                                required
                            >

                                <option value="">
                                    Select Academic Year
                                </option>


                                <?php for ($year = 1; $year <= 4; $year++): ?>

                                    <?php $academicValue = 'Year ' . $year; ?>

                                    <option
                                        value="<?= escapeValue($academicValue) ?>"
                                        <?= ($old['academic_year'] ?? '') === $academicValue ? 'selected' : '' ?>
                                    >

                                        <?= escapeValue($academicValue) ?>

                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>


                    </div>

                </section>




                <!-- =========================
                     SECTION 03
                     ACCOUNT SECURITY
                ========================== -->

                <section class="form-section">

                    <div class="section-heading">

                        <div class="section-number">03</div>

                        <div>

                            <h3>Account Security</h3>

                            <p>Create a secure password for your account</p>

                        </div>

                    </div>


                    <div class="form-grid">


                        <!-- Password -->

                        <div class="form-group">

                            <label for="password">
                                Password <span class="required">*</span>
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Create a password"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >

                            <span class="field-help">
                                Minimum 8 characters.
                            </span>

                        </div>



                        <!-- Confirm Password -->

                        <div class="form-group">

                            <label for="confirm_password">
                                Confirm Password <span class="required">*</span>
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Re-enter your password"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >

                        </div>


                    </div>

                </section>



                <!-- Security information -->

                <div class="security-note">

                    <span>🔒</span>

                    <div>

                        <strong>Your information is protected.</strong>

                        <br>

                        Your account password will be securely stored.
                        Please keep your login credentials private.

                    </div>

                </div>



                <!-- Submit Button -->

                <button
                    type="submit"
                    class="register-button"
                    id="registerButton"
                >

                    Create Student Account

                </button>


            </form>



            <!-- Bottom -->

            <div class="form-bottom">

                By creating an account, you agree to use the
                NSBM NewsDesk platform responsibly.

                <br><br>

                Already have an account?

                <a href="login.php">Sign in here</a>

            </div>


        </div>

    </main>


</div>



<!-- =========================
     JAVASCRIPT
========================== -->

<script>


    // Degree Programs

    const degreePrograms = <?= json_encode(
        $degreePrograms,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_AMP |
        JSON_HEX_QUOT
    ) ?>;


    const facultySelect = document.getElementById('faculty');

    const programSelect = document.getElementById('degree_program');


    const previousProgram = <?= json_encode(
        $selectedProgram,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_AMP |
        JSON_HEX_QUOT
    ) ?>;



    // Update degree programs according to faculty

    function updatePrograms(selectedProgram = '') {

        const selectedFaculty = facultySelect.value;

        programSelect.innerHTML = '';

        const defaultOption = document.createElement('option');

        defaultOption.value = '';

        defaultOption.textContent = 'Select Your Degree Program';

        programSelect.appendChild(defaultOption);


        if (degreePrograms[selectedFaculty]) {

            degreePrograms[selectedFaculty].forEach(function(program) {

                const option = document.createElement('option');

                option.value = program;

                option.textContent = program;


                if (program === selectedProgram) {

                    option.selected = true;

                }


                programSelect.appendChild(option);

            });

        }

    }



    // When faculty changes

    facultySelect.addEventListener('change', function() {

        updatePrograms();

    });



    // Restore previous values after validation error

    updatePrograms(previousProgram);



    // Password matching validation

    document.getElementById('registerForm').addEventListener(
        'submit',
        function(event) {

            const password = document.getElementById('password').value;

            const confirmPassword = document.getElementById('confirm_password').value;


            if (password !== confirmPassword) {

                event.preventDefault();

                alert('Passwords do not match.');

                document.getElementById('confirm_password').focus();

                return;

            }


            document.getElementById('registerButton').textContent =
                'Creating Account...';

            document.getElementById('registerButton').disabled = true;

        }
    );


</script>


</body>
</html>