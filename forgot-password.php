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
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
  <div class="bg-forget-pwd">
    <div class="overlay-forget-pwd">
      <div class="page-forget-pwd">
        <div class="brand-forget-pwd">
          <img src="images/yokoso-blanc.png" alt="YOKOSO">
        </div>

        <div class="card-forget-pwd">
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
              <button class="copy-btn" onclick="copyLink()">Copier le lien</button>
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