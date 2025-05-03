<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Basic Styling - Consider linking your main CSS -->
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; }
        .login-form { padding: 2rem; background: white; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1); min-width: 300px; }
        .login-form div { margin-bottom: 1rem; }
        .login-form label { display: block; margin-bottom: 0.5rem; }
        .login-form input { width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 0.25rem; }
        .login-form button { width: 100%; padding: 0.75rem; background-color: #0d6efd; color: white; border: none; border-radius: 0.25rem; cursor: pointer; }
        .login-form button:hover { background-color: #0b5ed7; }
        .error-message { color: red; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="login-form">
        <h1>Login</h1>
        <?php
        // Correctly include partial
        echo $view->includePartial('partials/flash-messages.php', ['flashMessages' => $flashMessages ?? null]);
        ?>

        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="/login" method="POST">
            <?php
                echo App\Helpers\SecurityHelper::csrfField();
            ?>
            <div>
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>