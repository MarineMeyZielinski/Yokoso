<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
$user_data = null;

if ($is_logged_in) {
    // Récupérer les données de l'utilisateur si pas déjà en session
    if (!isset($_SESSION['user_prenom']) || !isset($_SESSION['user_nom']) || !isset($_SESSION['user_email'])) {
        require_once __DIR__ . '/config.php';
        try {
            $stmt = $pdo->prepare('SELECT prenom, nom, email FROM users WHERE id_user = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data) {
                $_SESSION['user_prenom'] = $user_data['prenom'];
                $_SESSION['user_nom'] = $user_data['nom'];
                $_SESSION['user_email'] = $user_data['email'];
            }
        } catch (PDOException $e) {
            // En cas d'erreur, on ne fait rien
        }
    }
    
    // Utiliser les données de session
    $user_data = [
        'prenom' => $_SESSION['user_prenom'] ?? '',
        'nom' => $_SESSION['user_nom'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ];
}
?>

<!-- Topbar -->
<div class="topbar">
  <div class="search">
    <span><i class="fa-solid fa-magnifying-glass" style="color: #000000;"></i></span>
    <input type="text" placeholder="Rechercher">
    <button class="icon-btn" title="Filtres"><i class="fa-solid fa-filter" style="color: #000000;"></i></button>
  </div>
  <button class="icon-btn" title="Notifications"><i class="fa-solid fa-bell" style="color: #000000;"></i></button>
  <button class="icon-btn" title="Messages"><i class="fa-solid fa-envelope" style="color: #000000;"></i></button>
  <button class="icon-btn" title="Favoris"><i class="fa-solid fa-heart" style="color: #000000;"></i></button>

  <?php if ($is_logged_in): ?>
    <div class="user-greeting">
      Bonjour, <?= htmlspecialchars($user_data['prenom']) ?>
    </div>
    <div class="profile-menu-wrapper">
      <button class="icon-btn" title="Mon profil" onclick="toggleProfileMenu(event)">
        <i class="fa-solid fa-user" style="color: #000000;"></i>
      </button>
      
      <!-- Menu déroulant -->
      <div class="profile-dropdown" id="profileDropdown">
        <div class="profile-dropdown-header">
          <strong><?= htmlspecialchars($user_data['prenom'] . ' ' . $user_data['nom']) ?></strong>
          <span><?= htmlspecialchars($user_data['email']) ?></span>
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
  <?php else: ?>
    <a href="register.php" class="connexion">S'inscrire</a>
    <a href="login.php" class="connexion">Connexion</a>
  <?php endif; ?>
</div>

<!-- Script pour le menu déroulant -->
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