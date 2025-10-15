<?php
session_start();
require_once 'includes/config.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;
$user_id = $_SESSION['user_id'];

// Récupérer les données actuelles de l'utilisateur
try {
    $stmt = $pdo->prepare('SELECT prenom, nom, email, telephone, date_inscription FROM users WHERE id_user = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    $errors[] = "Erreur lors de la récupération des données.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');

    // Validations
    if (empty($prenom) || empty($nom)) {
        $errors[] = "Le prénom et le nom sont requis.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (!$errors) {
        try {
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = ? AND id_user != ? LIMIT 1');
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé par un autre compte.";
            } else {
                // Mettre à jour les informations
                $stmt = $pdo->prepare('
                    UPDATE users 
                    SET prenom = ?, nom = ?, email = ?, telephone = ?, date_naissance = ?
                    WHERE id_user = ?
                ');
                $stmt->execute([$prenom, $nom, $email, $telephone, $date_naissance ?: null, $user_id]);

                // Mettre à jour la session
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_email'] = $email;

                // Mettre à jour les données affichées
                $user['prenom'] = $prenom;
                $user['nom'] = $nom;
                $user['email'] = $email;
                $user['telephone'] = $telephone;
                $user['date_naissance'] = $date_naissance;

                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Calculer l'année d'inscription
$annee_inscription = date('Y', strtotime($user['date_inscription']));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Mon Profil</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
  <div class="page">
    <aside class="sidebar">
      <div class="logo">
        <a href="home.php"><img src="images/logo-blanc-seul.png" class="logo-blanc"></a>
        <img src="images/yokoso-blanc.png" alt="Yokoso" class="logo-yokoso">
      </div>
      <nav class="menu">
        <a href='home.php'>Accueil</a>
        <a href='logement.php'>Nos logements</a>
        <a href="#">Publier une annonce</a>
        <a href="edit-profile.php">Mon profil</a>
        <a href='my-bookings.php'>Mes réservations</a>
        <a href='my-listings.php'>Mes annonces</a>
      </nav>
    </aside>

    <main class="content">
      <?php include 'includes/header.php'; ?>

      <div class="profile-container">
        <!-- Onglets -->
        <div class="profile-tabs">
          <a href="edit-profile.php" class="profile-tab active">Compléter le profil</a>
          <a href="my-bookings.php" class="profile-tab">Mes réservations</a>
          <a href="my-listings.php" class="profile-tab">Mes annonces</a>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
          <div class="message success">
            ✓ Profil mis à jour avec succès !
          </div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="message error">
            <?php foreach ($errors as $error): ?>
              <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Header profil -->
        <div class="profile-header">
          <div class="profile-avatar">
            <i class="fa-solid fa-user" style="font-size: 48px; color: #999;"></i>
          </div>
          <div class="profile-info">
            <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <p>Inscrit depuis <?= htmlspecialchars($annee_inscription) ?></p>
          </div>
        </div>

        <!-- Formulaire -->
        <form method="post" action="edit-profile.php">
          <div class="profile-form">
            <div class="form-field">
              <label>Prénom:</label>
              <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            </div>

            <div class="form-field">
              <label>Nom:</label>
              <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>

            <div class="form-field">
              <label>Email:</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-field">
              <label>Numéro de téléphone:</label>
              <input type="tel" name="telephone" placeholder="Optionnel" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
            </div>

            <div class="form-field password">
              <label>Mot de passe:</label>
              <input type="password" value="••••••••••" readonly>
            </div>

            <div class="form-field">
              <label>Date de naissance:</label>
              <input type="date" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
            </div>
          </div>

          <button type="submit" class="save-btn">Sauvegarder</button>
        </form>
      </div>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
  </div>
  
</body>
</html>