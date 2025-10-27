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
  <div class="search-container">
    <div class="search">
      <span><i class="fa-solid fa-magnifying-glass" style="color: #000000;"></i></span>
      <input type="text" id="searchInput" placeholder="Rechercher un logement..." autocomplete="off">
      <button class="icon-btn" title="Filtres" onclick="openFiltersModal()"><i class="fa-solid fa-filter" style="color: #000000;"></i></button>
    </div>
    <!-- Dropdown des résultats -->
    <div class="search-dropdown" id="searchDropdown"></div>
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
          <?php
          // Récupérer la photo de profil si elle existe
          $photo_profil = null;
          if ($is_logged_in) {
              try {
                  require_once __DIR__ . '/config.php';
                  $stmt = $pdo->prepare('SELECT photo_profil FROM users WHERE id_user = ? LIMIT 1');
                  $stmt->execute([$_SESSION['user_id']]);
                  $result = $stmt->fetch(PDO::FETCH_ASSOC);
                  $photo_profil = $result['photo_profil'] ?? null;
              } catch (PDOException $e) {}
          }
          ?>
          <?php if (!empty($photo_profil) && file_exists($photo_profil)): ?>
            <img src="<?= htmlspecialchars($photo_profil) ?>" alt="Photo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
          <?php endif; ?>
          <div>
            <strong><?= htmlspecialchars($user_data['prenom'] . ' ' . $user_data['nom']) ?></strong>
            <span><?= htmlspecialchars($user_data['email']) ?></span>
          </div>
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
  // Menu profil
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

  // Recherche en temps réel
  let searchTimeout;
  const searchInput = document.getElementById('searchInput');
  const searchDropdown = document.getElementById('searchDropdown');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      const query = this.value.trim();

      if (query.length < 2) {
        searchDropdown.classList.remove('active');
        return;
      }

      searchTimeout = setTimeout(() => {
        fetch(`search.php?q=${encodeURIComponent(query)}`)
          .then(response => response.json())
          .then(data => {
            if (data.success && data.results.length > 0) {
              displaySearchResults(data.results);
            } else {
              searchDropdown.innerHTML = '<div class="search-no-result">Aucun résultat trouvé</div>';
              searchDropdown.classList.add('active');
            }
          })
          .catch(error => {
            console.error('Erreur de recherche:', error);
          });
      }, 300);
    });

    // Fermer le dropdown en cliquant ailleurs
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.search-container')) {
        searchDropdown.classList.remove('active');
      }
    });
  }

  function displaySearchResults(results) {
    let html = '';
    
    results.forEach(result => {
      html += `
        <a href="${result.url}" class="search-result-item">
          <img src="${result.photo}" alt="${result.titre}" class="search-result-img">
          <div class="search-result-info">
            <div class="search-result-title">${result.titre}</div>
            <div class="search-result-meta">
              <span>📍 ${result.ville}, ${result.pays}</span>
              <span>💰 ${result.prix}€/nuit</span>
              <span>👥 ${result.capacite} pers.</span>
            </div>
          </div>
        </a>
      `;
    });

    html += `
      <a href="logement.php?search=${encodeURIComponent(searchInput.value)}" class="search-view-all">
        → Voir tous les résultats
      </a>
    `;

    searchDropdown.innerHTML = html;
    searchDropdown.classList.add('active');
  }

  function openFiltersModal() {
    // TODO: Ouvrir la modale de filtres
    window.location.href = 'logement.php';
  }
</script>