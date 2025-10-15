<?php
session_start();
require_once 'includes/config.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les données de l'utilisateur
try {
    $stmt = $pdo->prepare('SELECT prenom, nom, email FROM users WHERE id_user = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// TODO: Récupérer les annonces de l'utilisateur depuis la base de données
// $stmt = $pdo->prepare('SELECT * FROM annonces WHERE id_user = ? ORDER BY date_creation DESC');
// $stmt->execute([$user_id]);
// $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
$annonces = []; // Pour l'instant vide
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Mes annonces</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
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
        <a href='logement.php'>Nos logements</a>
        <a href="#">Publier une annonce</a>
        <a href="edit-profile.php">Mon profil</a>
        <a href="#">Mes réservations</a>
        <a href="#">Mes annonces</a>
      </nav>
    </aside>

    <main class="content">
      <div class="topbar">
        <div class="search">
          <span><i class="fa-solid fa-magnifying-glass" style="color: #000000;"></i></span>
          <input type="text" placeholder="Rechercher">
          <button class="icon-btn" title="Filtres"><i class="fa-solid fa-filter" style="color: #000000;"></i></button>
        </div>
        <button class="icon-btn" title="Notifications"><i class="fa-solid fa-bell" style="color: #000000;"></i></button>
        <button class="icon-btn" title="Messages"><i class="fa-solid fa-envelope" style="color: #000000;"></i></button>
        <button class="icon-btn" title="Favoris"><i class="fa-solid fa-heart" style="color: #000000;"></i></button>

        <div class="user-greeting">
          Bonjour, <?= htmlspecialchars($user['prenom']) ?>
        </div>
        <div class="profile-menu-wrapper">
          <button class="icon-btn" title="Mon profil" onclick="toggleProfileMenu(event)">
            <i class="fa-solid fa-user" style="color: #000000;"></i>
          </button>
          
          <div class="profile-dropdown" id="profileDropdown">
            <div class="profile-dropdown-header">
              <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
              <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="profile-dropdown-menu">
              <a href="edit-profile.php" class="profile-dropdown-item">
                <i class="fa-solid fa-user-pen"></i>
                <span>Mon profil</span>
              </a>
              <a href="my-listings.php" class="profile-dropdown-item">
                <i class="fa-solid fa-house"></i>
                <span>Mes annonces</span>
              </a>
              <a href="my-bookings.php" class="profile-dropdown-item">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Mes réservations</span>
              </a>
              <div class="profile-dropdown-divider"></div>
              <a href="logout.php" class="profile-dropdown-item logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Se déconnecter</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="profile-container">
        <!-- Onglets -->
        <div class="profile-tabs">
          <a href="edit-profile.php" class="profile-tab">Compléter le profil</a>
          <a href="my-bookings.php" class="profile-tab">Mes réservations</a>
          <a href="my-listings.php" class="profile-tab active">Mes annonces</a>
        </div>

        <?php if (empty($annonces)): ?>
          <!-- État vide -->
          <div class="empty-state">
            <i class="fa-solid fa-house-circle-xmark"></i>
            <h2>Aucune annonce</h2>
            <p>Vous n'avez pas encore publié d'annonce.</p>
          </div>
        <?php else: ?>
          <!-- Liste des annonces (exemple pour quand il y aura des données) -->
          <?php foreach ($annonces as $annonce): ?>
            <div class="listing-item">
              <img src="<?= htmlspecialchars($annonce['image']) ?>" alt="<?= htmlspecialchars($annonce['titre']) ?>" class="listing-image">
              <div class="listing-content">
                <div class="listing-title"><?= htmlspecialchars($annonce['titre']) ?></div>
                <div class="listing-date">Publiée le <?= htmlspecialchars($annonce['date_publication']) ?></div>
                <div class="listing-description"><?= htmlspecialchars($annonce['description']) ?></div>
              </div>
              <div class="listing-actions">
                <button class="action-btn" title="Modifier">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="action-btn delete" title="Supprimer">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
  </div>

  <script>
    function toggleProfileMenu(event) {
      event.stopPropagation();
      const dropdown = document.getElementById('profileDropdown');
      dropdown.classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('profileDropdown');
      const wrapper = document.querySelector('.profile-menu-wrapper');
      
      if (dropdown && wrapper && !wrapper.contains(event.target)) {
        dropdown.classList.remove('active');
      }
    });

    document.getElementById('profileDropdown')?.addEventListener('click', function(event) {
      event.stopPropagation();
    });
  </script>
</body>
</html>