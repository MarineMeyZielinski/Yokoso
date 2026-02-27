<?php
session_start();
require_once 'includes/config.php';
$user_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Mentions légales</title>
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
        <a href="home.php">Accueil</a>
        <a href="logement.php">Nos logements</a>
        <?php if ($user_id): ?>
          <a href="publier-annonce.php">Publier une annonce</a>
          <a href="edit-profile.php">Mon profil</a>
          <a href="my-bookings.php">Mes réservations</a>
          <a href="my-listings.php">Mes annonces</a>
        <?php endif; ?>
      </nav>
    </aside>

    <main class="content">
      <?php include 'includes/header.php'; ?>

      <div class="legal-page">
        <h1>Mentions légales</h1>

        <section class="legal-section">
          <h2>1. Éditeur du site</h2>
          <p><strong>YOKOSO Corp.</strong><br>
          Plateforme de location de logements entre particuliers.<br>
          Projet étudiant — à titre illustratif uniquement.<br>
          Email : contact@yokoso.fr</p>
        </section>

        <section class="legal-section">
          <h2>2. Hébergement</h2>
          <p>Ce site est hébergé localement à des fins de développement via WampServer.<br>
          En production, l'hébergement serait assuré par un prestataire tiers.</p>
        </section>

        <section class="legal-section">
          <h2>3. Propriété intellectuelle</h2>
          <p>L'ensemble du contenu présent sur le site YOKOSO (textes, images, logos, icônes) est protégé par le droit de la propriété intellectuelle. Toute reproduction, distribution ou utilisation sans autorisation préalable est strictement interdite.</p>
          <p>Les photographies de logements appartiennent à leurs propriétaires respectifs.</p>
        </section>

        <section class="legal-section">
          <h2>4. Responsabilité</h2>
          <p>YOKOSO agit en tant qu'intermédiaire entre hôtes et voyageurs. La plateforme ne peut être tenue responsable des informations publiées par les hôtes, ni des litiges survenant entre utilisateurs.</p>
          <p>YOKOSO se réserve le droit de supprimer tout contenu contraire aux présentes mentions légales ou à la réglementation en vigueur.</p>
        </section>

        <section class="legal-section">
          <h2>5. Cookies</h2>
          <p>Ce site utilise des cookies de session nécessaires au fonctionnement de l'authentification. Aucun cookie publicitaire ou de traçage tiers n'est utilisé.</p>
        </section>

        <section class="legal-section">
          <h2>6. Droit applicable</h2>
          <p>Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.</p>
        </section>

        <p class="legal-update">Dernière mise à jour : février 2025</p>
      </div>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés.<br><span class="footer-links"><a href="mentions-legales.php">Mentions légales</a> | <a href="politique-confidentialite.php">Politique de confidentialité</a></span></div>
    </main>
  </div>
</body>
</html>
