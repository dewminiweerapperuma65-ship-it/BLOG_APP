<?php
/**
 * Login Page
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blog App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <h1><a href="index.php">Blog App</a></h1>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="form-container">
            <h2>Login</h2>
            <div id="message"></div>
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%">Login</button>
            </form>
            <p style="text-align: center; margin-top: 1rem;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
            <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                <p style="margin-bottom: 0.5rem; font-weight: bold;">Test Credentials:</p>
                <p style="margin: 0; font-size: 0.9rem;">Email: john@example.com</p>
                <p style="margin: 0; font-size: 0.9rem;">Password: password123</p>
            </div>
        </div>
    </main>

    <script src="assets/script.js"></script>
    <script>
        const form = document.getElementById('loginForm');
        const messageDiv = document.getElementById('message');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Get form data
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Clear previous messages
            messageDiv.innerHTML = '';

            // Send login request
            const result = await apiRequest('login_user.php', 'POST', {
                email: email,
                password: password
            });

            if (result.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                // Redirect to homepage after 1 second
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
            }
        });
    </script>
</body>
</html>
