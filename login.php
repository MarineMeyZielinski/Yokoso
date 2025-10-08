<?php
session_start();
require_once 'includes/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Vérifications de base
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if ($mot_de_passe === '') {
        $errors[] = "Le mot de passe est requis.";
    }

    if (!$errors) {
        try {
            // Rechercher l'utilisateur par email
            $stmt = $pdo->prepare('SELECT id_user, prenom, nom, email, mot_de_passe FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Connexion réussie - créer la session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_email'] = $user['email'];

                // Redirection vers la page d'accueil ou dashboard
                header('Location: home.php');
                exit;
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Connexion</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="bg-login"></div>
  <div class="overlay-login"></div>
  <div class="page-login">
    <div class="brand-login">
      <img src="images/yokoso-blanc.png" alt="YOKOSO">
    </div>

    <div class="card-login">
      <div class="badge-login">
        <img src="images/logo-blanc-seul-removebg-preview.png" alt="Logo YOKOSO">
      </div>
      <h1>Se connecter</h1>

      <!-- Message de succès après inscription -->
      <?php if (isset($_GET['registered'])): ?>
        <div class="success">
          <p>✓ Inscription réussie ! Connectez-vous maintenant.</p>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['password_reset'])): ?>
        <div class="success">
          <p>✓ Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.</p>
        </div>
      <?php endif; ?>

      <!-- Affichage des erreurs -->
      <?php if ($errors): ?>
        <div class="error">
          <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="post" autocomplete="on">
        <div>
          <label for="email">Adresse mail</label>
          <input type="email" id="email" name="email" placeholder="Adresse mail" inputmode="email" autocomplete="email" required>
        </div>
        <div>
          <label for="mot_de_passe">Mot de passe</label>
          <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" autocomplete="current-password" required>
        </div>
        <button type="submit" class="submit">Se connecter</button>
      </form>

      <a href="forgot-password.php" class="forgot">Mot de passe oublié ?</a>

      <p class="link">
        Pas encore de compte ? <a href="register.php">S'inscrire</a>
      </p>
    </div>
  </div>
</body>
</html>