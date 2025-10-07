<?php
// Inclure le fichier de configuration pour la connexion à la base de données
require_once 'includes/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $prenom   = trim($_POST['prenom'] ?? '');
    $nom      = trim($_POST['nom'] ?? '');
    $email    = trim($_POST['email'] ?? ''); 
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $telephone    = trim($_POST['telephone'] ?? '');

    // Vérifications de base
    if ($prenom === '' || $nom === '') {
        $errors[] = "Le prénom et le nom sont requis.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (strlen($mot_de_passe) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if (!$errors) {  // seulement s'il n'y a PAS d'erreurs
        try {
            // Vérifier que l'adresse e-mail est unique
            $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]); 

            if ($stmt->fetch()) { 
                $errors[] = "Cet e-mail est déjà utilisé.";
            } else {
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

                // Insertion de l'utilisateur
                $stmt = $pdo->prepare(
                    'INSERT INTO users (prenom, nom, email, mot_de_passe, telephone, date_inscription) 
                     VALUES (?, ?, ?, ?, ?, NOW())'
                );
                $stmt->execute([$prenom, $nom, $email, $hash, $telephone]); 

                // Redirection après succès
                header('Location: login.php?registered=1'); 
                exit;
            }
        } catch (PDOException $e) { 
            $errors[] = "Erreur SQL : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Inscription</title>
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
    .badge img { width: 115px; margin-top:30px;  object-fit: contain; }
    .card h1 { text-align: left; font-size: 28px; margin-bottom: 12px; }

    form { display: grid; gap: 12px; }
    label { font-size: 13px; color: #eaeaea; margin-left: 8px; }
    input { height: 44px; padding: 0 16px; border-radius: 24px; border: none; outline: none; background: #fff; color: #111; width: 100%; }
    .submit { margin-top: 4px; height: 44px; border-radius: 24px; border: none; background: #000000ff; color: #fff; font-weight: 600; cursor: pointer; }
    .submit:hover { background: #3b3b3bff; }

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
      <h1>S'inscrire</h1>

      <!-- Affichage des erreurs -->
      <?php if ($errors): ?>
        <div style="background:#ff4444; padding:10px; border-radius:8px; margin-bottom:10px;">
          <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="register.php" method="post" autocomplete="on">
        <div>
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" placeholder="Nom" required>
        </div>
        <div>
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" placeholder="Prénom" required>
        </div>
        <div>
          <label for="email">Adresse mail</label>
          <input type="email" id="email" name="email" placeholder="Adresse mail" inputmode="email" autocomplete="email" required>
        </div>
        <div>
          <label for="mot_de_passe">Mot de passe</label>
          <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" minlength="8" required>
        </div>
        <div>
          <label for="telephone">Numéro de téléphone (optionnel)</label>
          <input type="tel" id="telephone" name="telephone" placeholder="Numéro de téléphone" inputmode="tel">
        </div>
        <button type="submit" class="submit">Terminé</button>
      </form>
    </div>
  </div>
</body>
</html>