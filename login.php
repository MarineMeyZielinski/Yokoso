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
  <style>
    /* Fond + overlay */
    .page-login { position: relative; min-height: 100vh; display: flex; align-items: center; gap: 56px; padding: 40px; }
    .bg-login { position: fixed; inset: 0; background: center/cover no-repeat url('images/register-fond.png'); backdrop-filter: contrast(0.3); z-index: -2; }
    .overlay-login { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: -1; }

    /* Colonne gauche: logo texte */
    .brand-login { flex: 1; display: flex; justify-content: flex-start; }
    .brand-login img { width: 520px; max-width: 40vw; height: auto; margin-left: 200px; }

    /* Carte formulaire */
    .card-login { position: relative; width: 520px; max-width: 92vw; padding: 28px; padding-top: 90px; border-radius: 16px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.22); backdrop-filter: blur(6px); box-shadow: 0 10px 30px rgba(0,0,0,0.25); margin-left: auto; }
    .badge-login { position: absolute; left: 50%; top: -60px; transform: translateX(-50%); display: grid; place-items: center; }
    .badge-login img { width: 115px; margin-top:30px; object-fit: contain; }
    .card-login h1 { text-align: left; font-size: 28px; margin-bottom: 12px; }

    form { display: grid; gap: 12px; }
    label { font-size: 13px; color: #eaeaea; margin-left: 8px; }
    input { height: 44px; padding: 0 16px; border-radius: 24px; border: none; outline: none; background: #fff; color: #111; width: 100%; }
    .submit { margin-top: 4px; height: 44px; border-radius: 24px; border: none; background: #000000ff; color: #fff; font-weight: 600; cursor: pointer; }
    .submit:hover { background: #3b3b3bff; }

    .link { text-align: center; margin-top: 16px; font-size: 14px; color: #eaeaea; }
    .link a { color: #fff; text-decoration: underline; }
    .link a:hover { color: #ddd; }

    .forgot { display: block; text-align: center; margin-top: 12px; font-size: 13px; color: #ddd; text-decoration: none; }
    .forgot:hover { color: #fff; text-decoration: underline; }

    .success { background: #44ff44; color: #000; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-weight: 600; }
    .error { background: #ff4444; padding: 10px; border-radius: 8px; margin-bottom: 10px; }

    @media (max-width: 980px) { .page { flex-direction: column; align-items: center; gap: 24px; } .brand { justify-content: center; } .brand img { max-width: 70vw; } }
  </style>
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