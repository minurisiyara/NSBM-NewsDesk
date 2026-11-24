// Student Registration Form Validation

const registerForm = document.getElementById("registerForm");

if (registerForm) {

    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const registerButton = document.getElementById("registerButton");

    // Password Matching Function
    function checkPassword() {

        if (confirmPassword.value === "") {
            confirmPassword.setCustomValidity("");
        }

        else if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords do not match.");
        }

        else {
            confirmPassword.setCustomValidity("");
        }

    }

    // Check password when typing
    password.addEventListener("input", checkPassword);

    confirmPassword.addEventListener("input", checkPassword);

    // Form Submission
    registerForm.addEventListener("submit", function (event) {

        checkPassword();

        // Check form validation
        if (!registerForm.checkValidity()) {

            event.preventDefault();
            registerForm.reportValidity();

            return;
        }

        // Disable button
        registerButton.disabled = true;

        // Change button text
        registerButton.textContent = "Creating Account...";

    });

}
// Student Login Form Validation

const loginForm = document.getElementById("loginForm");

if (loginForm) {

    const loginButton = document.getElementById("loginButton");

    // Login Form Submission
    loginForm.addEventListener("submit", function (event) {

        if (!loginForm.checkValidity()) {

            event.preventDefault();
            loginForm.reportValidity();

            return;
        }

        // Loading State
        loginButton.disabled = true;
        loginButton.textContent = "Logging In...";

    });

}