<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Als de gebruiker al is ingelogd, stuur door naar dashboard
if (isLoggedIn()) {
    header('Location: LindenFrontPage.php');
    exit;
}

$errors = [];
$name   = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';


    if (empty($name)) {
        $errors[] = "Je hebt geen naam ingevuld.";
    }
    if (strlen($name) < 2) {
        $errors[] = "Je naam is te kort, minimaal 2 tekens.";
    }
    if (strlen($name) > 90) {
        $errors[] = "Je naam is te lang. Max 90 tekens.";
    }
    if (empty($email)) {
        $errors[] = "Je hebt geen email ingevuld.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Je hebt geen geldig emailadres ingevuld.";
    }
    if (empty($password)) {
        $errors[] = "Je hebt geen wachtwoord ingevuld.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Je wachtwoord is te kort, minimaal 6 tekens.";
    }
    if ($confirm != $password) {
        $errors[] = "Je hebt niet hetzelfde wachtwoord ingevoerd.";
    }

    $statement = $pdo->prepare("SELECT * FROM `users` WHERE `email` LIKE ?");
    $statement->execute([$email]);

    if ($statement->fetch()) {
        $errors[] = "Iemand gebruikt deze email al.";
    }

    if (count($errors) > 6) {
        $errors = [];
        $errors[] = "Je hebt letterlijk niks ingevuld.";
    }


    // slag de gebruiker alleen op als er geen fouten zijn
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    if (empty($errors)) {
        try {
            $statement = $pdo->prepare(
                "INSERT INTO `users` (`id`, `name`, `pass`, `email`, `role`, `date_created`) 
        VALUES (null, ?, ?, ?, 'user', current_timestamp());"
            );
            $statement->execute([$name, $hashed, $email]);
            header("location: LindenFrontPage.php?registered");
            exit();
        } catch (Exception $e) {
            echo "Something went wrong: " . $e;
        };
    }
}



$pageTitle = 'Registreren';
?>

<div class="container">
    <div class="form-wrapper">
        <div class="form-card">
            <h1 class="form-title">Word lid</h1>
            <p class="form-subtitle">Maak een account en start direct met voorspellen.</p>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>
                <div class="form-group">
                    <label class="form-label" for="name">Volledige naam</label>
                    <input type="text" id="name" name="name" class="form-input"
                        value="<?= htmlspecialchars($name) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" class="form-input"
                        value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <p class="form-help">Minimaal 6 tekens</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Bevestig wachtwoord</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                    Account aanmaken
                </button>
            </form>

            <div class="form-footer">
                Al een account? <a href="login.php">Log hier in</a>
            </div>
        </div>
    </div>
</div>