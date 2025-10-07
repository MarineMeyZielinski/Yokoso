<?php
require_once 'includes/config.php';

$errors = [];
$token = $_GET['token'] ?? '';
$token_valid = false;
$email = '';

if ($token) {
    try {
        $stmt = $pdo->prepare('
            SELECT email 
            FROM password_resets 
            WHERE token = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            LIMIT 1
        ');
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $token_valid = true;
            $email = $result['email'];
        } else {
            $errors[] = "Ce lien de réinitialisation est invalide ou a expiré.";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur : " . $e->getMessage();
    }
} else {
    $errors[] = "Aucun token de réinitialisation fourni.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';

    // Vérifications
    if (strlen($mot_de_passe) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if ($mot_de_passe !== $mot_de_passe_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (!$errors) {
        try {
            // Hasher le nouveau mot de passe
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe
            $stmt = $pdo->prepare('UPDATE users SET mot_de_passe = ? WHERE email = ?');
            $stmt->execute([$hash, $email]);

            // Supprimer le token utilisé
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
            $stmt->execute([$token]);

            // Redirection vers login avec message de succès
            header('Location: login.php?password_reset=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Nouveau mot de passe</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color: #fff; min-height: 100vh; }

    /* Fond + overlay */
    .page { position: relative; min-height: 100vh; display: flex; align-items: center; gap: 56px; padding: 40px; }
    .bg { position: fixed; inset: 0; background: center/cover no-repeat url('images/register-fond.png'); backdrop-filter: contrast(0.3); z-index: -2; }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: -1; }

    /* Colonne gauche: logo texte */
    .brand { flex: 1; display: flex; justify-content: flex-start; }
    .brand img { width: 520px; max-width: 40vw; height: auto; margin-left: 200px; }

    /* Carte formulaire */
    .card { position: relative; width: 520px; max-width: 92vw; padding: 28px; padding-top: 90px; border-radius: 16px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.22); backdrop-filter: blur(6px); box-shadow: 0 10px 30px rgba(0,0,0,0.25); margin-left: auto; }
    .badge { position: absolute; left: 50%; top: -60px; transform: translateX(-50%); display: grid; place-items: center; }
    .badge img { width: 115px; margin-top:30px; object-fit: contain; }
    .card h1 { text-align: left; font-size: 28px; margin-bottom: 8px; }
    .card p { font-size: 14px; color: #ddd; margin-bottom: 16px; line-height: 1.5; }

    form { display: grid; gap: 12px; }
    label { font-size: 13px; color: #eaeaea; margin-left: 8px; }
    input { height: 44px; padding: 0 16px; border-radius: 24px; border: none; outline: none; background: #fff; color: #111; width: 100%; }
    .submit { margin-top: 4px; height: 44px; border-radius: 24px; border: none; background: #000000ff; color: #fff; font-weight: 600; cursor: pointer; }
    .submit:hover { background: #3b3b3bff; }

    .link { text-align: center; margin-top: 16px; font-size: 14px; color: #eaeaea; }
    .link a { color: #fff; text-decoration: underline; }
    .link a:hover { color: #ddd; }

    .error { background: #ff4444; padding: 10px; border-radius: 8px; margin-bottom: 10px; }

    @media (max-width: 980px) { .page { flex-direction: column; align-items: center; gap: 24px; } .brand { justify-content: center; } .brand img { max-width: 70vw; } }
  </style>
</head>
<body>
  <div class="bg"></div>
  <div class="overlay"></div>
  <div class="page">
    <div class="brand">
      <img src="images/yokoso-blanc.png" alt="YOKOSO">
    </div>

    <div class="card">
      <div class="badge">
        <img src="images/logo-blanc-seul-removebg-preview.png" alt="Logo YOKOSO">
      </div>
      <h1>Nouveau mot de passe</h1>
      <p>Choisissez un nouveau mot de passe sécurisé pour votre compte.</p>

      <!-- Affichage des erreurs -->
      <?php if ($errors): ?>
        <div class="error">
          <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
        <p class="link">
          <a href="forgot-password.php">Demander un nouveau lien</a>
        </p>
      <?php endif; ?>

      <!-- Formulaire uniquement si token est valide -->
      <?php if ($token_valid && !$errors): ?>
        <form action="reset-password.php?token=<?= htmlspecialchars($token) ?>" method="post" autocomplete="off">
          <div>
            <label for="mot_de_passe">Nouveau mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Minimum 8 caractères" minlength="8" required>
          </div>
          <div>
            <label for="mot_de_passe_confirm">Confirmer le mot de passe</label>
            <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" placeholder="Retapez votre mot de passe" minlength="8" required>
          </div>
          <button type="submit" class="submit">Réinitialiser</button>
        </form>

        <p class="link">
          <a href="login.php">Retour à la connexion</a>
        </p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>