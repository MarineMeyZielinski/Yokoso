<?php
session_start();
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Mes réservations</title>
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
          <a href="edit-profile.php" class="profile-tab">Compléter le profil</a>
          <a href="my-bookings.php" class="profile-tab active">Mes réservations</a>
          <a href="my-listings.php" class="profile-tab">Mes annonces</a>
        </div>

        <!-- État vide -->
        <div class="empty-state">
          <i class="fa-solid fa-calendar-xmark"></i>
          <h2>Aucune réservation</h2>
          <p>Vous n'avez pas encore effectué de réservation.</p>
        </div>
      </div>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
  </div>

</body>
</html>