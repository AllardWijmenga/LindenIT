<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// check if logged in, in which case this entire page is useless and they are an idiot.
if (isLoggedIn()) {
    header('Location: LindenFrontPage.php');
    exit;
}

$errors = [];
$email  = '';
$notice = '';

// success message after registration
if (isset($_GET['registered'])) {
    $notice = 'Account aangemaakt.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']   ?? '';


    if ($email === '' || $email === null) {
        $errors[] = 'Je hebt geen email-adres ingevuld.';
    }
    if ($password === '' || $password === null) {
        $errors[] = 'Je hebt geen wachtwoord ingevuld.';
    }

    $statement = $pdo->prepare("SELECT * FROM `users` WHERE `email` LIKE ?");
    $statement->execute([$email]);
    $user = $statement->fetch();

    // foreach ($user as $key => $value) {
    //     echo "$key: $value<br>";
    // }

    if ($user && password_verify($password, $user['pass'])) {
        // login successful
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        header('Location: LindenFrontPage.php');
        exit;
    } else {
        $errors[] = 'Ongeldige inloggegevens.';
    }
}

$pageTitle = 'Inloggen';;
?>

<div class="container">
    <div class="form-wrapper">
        <div class="form-card">
            <h1 class="form-title">Inloggen</h1>
            <p class="form-subtitle">Inloggen</p>

            <?php if ($notice): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($notice) ?></div>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>

            <form method="POST" action="index.php" novalidate>
                <div class="form-group">
                    <label class="form-label" for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" class="form-input"
                        value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                    Inloggen
                </button>
            </form>

            <div class="form-footer">
                Nog geen account? <a href="register.php">Registreer hier</a>
            </div>
        </div>
    </div>
</div>