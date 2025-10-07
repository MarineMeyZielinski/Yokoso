<?php
require_once 'includes/config.php';

$errors = [];
$success = false;
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Vérification de base
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (!$errors) {
        try {
            // Vérifier si l'email existe
            $stmt = $pdo->prepare('SELECT id_user, prenom FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                
                // Supprimer les anciens tokens pour cet email
                $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
                $stmt->execute([$email]);

                // Insérer le nouveau token
                $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())');
                $stmt->execute([$email, $token]);

                // Créer le lien de réinitialisation
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;

                // Quand on enverra des vrais email

//                 $subject = "YOKOSO - Réinitialisation de votre mot de passe";
//                 $message = "Bonjour " . htmlspecialchars($user['prenom']) . ",\n\n";
//                 $message .= "Vous avez demandé à réinitialiser votre mot de passe.\n\n";
//                 $message .= "Cliquez sur ce lien pour créer un nouveau mot de passe :\n";
//                 $message .= $reset_link . "\n\n";
//                 $message .= "Ce lien est valable pendant 1 heure.\n\n";
//                 $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
//                 $message .= "Cordialement,\nL'équipe YOKOSO";

//                 $headers = "From: noreply@yokoso.com\r\n";
//                 $headers .= "Reply-To: noreply@yokoso.com\r\n";
//                 $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

//                 if (mail($email, $subject, $message, $headers)) {
//                     $success = true;
//                 } else {
//                     $errors[] = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
//                 }
//             } else {
                
//                 $success = true;
//             }
//         } catch (PDOException $e) {
//             $errors[] = "Erreur : " . $e->getMessage();
//         }
//     }
// }
//

                $success = true;
            } else {
                // Pour des raisons de sécurité, on affiche le même message même si l'email n'existe pas
                $errors[] = "Aucun compte associé à cet email.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Mot de passe oublié</title>
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

    .success { background: #44ff44; color: #000; padding: 14px; border-radius: 8px; margin-bottom: 16px; font-weight: 500; line-height: 1.6; }
    .success strong { display: block; margin-top: 10px; margin-bottom: 6px; }
    .reset-link { background: #fff; color: #000; padding: 12px; border-radius: 8px; word-break: break-all; font-size: 13px; margin-top: 8px; border: 2px dashed #000; }
    .copy-btn { margin-top: 10px; padding: 8px 16px; background: #000; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 13px; }
    .copy-btn:hover { background: #333; }
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
      <h1>Mot de passe oublié ?</h1>
      <p>Entrez votre adresse email et nous générerons un lien pour réinitialiser votre mot de passe.</p>

      <!-- Message de succès avec le lien -->
      <?php if ($success): ?>
        <div class="success">
          <strong>Copiez ce lien (valable 1 heure) :</strong>
          <div class="reset-link" id="resetLink"><?= htmlspecialchars($reset_link) ?></div>
          <button class="copy-btn" onclick="copyLink()">📋 Copier le lien</button>
        </div>
        <p class="link">
          <a href="login.php">Retour à la connexion</a>
        </p>
      <?php else: ?>

        <!-- Affichage des erreurs -->
        <?php if ($errors): ?>
          <div class="error">
            <?php foreach ($errors as $error): ?>
              <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form action="forgot-password.php" method="post" autocomplete="on">
          <div>
            <label for="email">Adresse mail</label>
            <input type="email" id="email" name="email" placeholder="Adresse mail" inputmode="email" autocomplete="email" required>
          </div>
          <button type="submit" class="submit">Envoyer le lien</button>
        </form>

        <p class="link">
          <a href="login.php">Retour à la connexion</a>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function copyLink() {
      const linkText = document.getElementById('resetLink').textContent;
      navigator.clipboard.writeText(linkText).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.textContent = '✓ Copié !';
        btn.style.background = '#679c67ff';
        setTimeout(() => {
          btn.textContent = 'Copier le lien';
          btn.style.background = '#000';
        }, 2000);
      });
    }
  </script>
</body>
</html>