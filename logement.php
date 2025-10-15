<?php
session_start();
require_once 'includes/config.php';

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
$user_data = null;

if ($is_logged_in) {
    // Récupérer les données complètes de l'utilisateur
    try {
        $stmt = $pdo->prepare('SELECT prenom, nom, email, telephone FROM users WHERE id_user = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // En cas d'erreur, on déconnecte l'utilisateur
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOKOSO - Nos logements</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
   <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
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
        <a href= 'logement.php'>Nos logements</a>
        <a href="#">Publier une annonce</a>
        <a href='edit-profile.php'>Compléter le profil</a>
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


      <section>
        <h3 class="section-title">Nos logements :</h3>
        <div class="cards">
          <article class="card">
            <img src="images/kioshi.jpg" alt="Appartement de Kioshi" class="thumb">
            <div class="name">Appartement de Kioshi :</div>
            <p class="desc">Studio moderne et lumineux à deux pas du célèbre carrefour, avec lit confortable, cuisine équipée et Wi‑Fi rapide. Profitez du calme d'une rue discrète tout en étant au cœur de l'énergie tokyoïte.</p>
          </article>
          <article class="card">
            <img src="images/appartement-lucas.jpg" alt="Appartement de Luca" class="thumb">
            <div class="name">Appartement de Luca :</div>
            <p class="desc">Charmant studio au cœur de Paris, alliant confort moderne et authenticité. À deux pas des cafés typiques et des monuments emblématiques, idéal pour découvrir la Ville Lumière.</p>
          </article>
          <article class="card">
            <img src="images/appartement-saitama.jpg" alt="Appartement de Saitama" class="thumb">
            <div class="name">Appartement de Saitama :</div>
            <p class="desc">Maison typique au toit rouge et tatamis, entourée de verdure et proche de la mer turquoise. Une immersion authentique dans la culture d'Okinawa, entre calme et nature.</p>
          </article>
        </div>
      </section>


      <section>
        <div class="cards">
          <article class="card">
            <img src="images/france-1.webp" alt="Appartement de Kioshi" class="thumb">
            <div class="name">Appartement de Jean :</div>
            <p class="desc">Studio moderne et lumineux à deux pas du célèbre carrefour, avec lit confortable, cuisine équipée et Wi‑Fi rapide. Profitez du calme d'une rue discrète tout en étant au cœur de l'énergie tokyoïte.</p>
          </article>
          <article class="card">
            <img src="images/france-2.jpg" alt="Appartement de Luca" class="thumb">
            <div class="name">Appartement de Loïc:</div>
            <p class="desc">Charmant studio au cœur de Paris, alliant confort moderne et authenticité. À deux pas des cafés typiques et des monuments emblématiques, idéal pour découvrir la Ville Lumière.</p>
          </article>
          <article class="card">
            <img src="images/france-3.jpg" alt="Appartement de Saitama" class="thumb">
            <div class="name">Appartement de Yoru :</div>
            <p class="desc">Maison typique au toit rouge et tatamis, entourée de verdure et proche de la mer turquoise. Une immersion authentique dans la culture d'Okinawa, entre calme et nature.</p>
          </article>
        </div>
      </section>





      <section>
        <div class="cards">
          <article class="card">
            <img src="images/apparttradionnel.jpg" alt="Appartement de Kioshi" class="thumb">
            <div class="name">Appartement de Takeda :</div>
            <p class="desc">Studio moderne et lumineux à deux pas du célèbre carrefour, avec lit confortable, cuisine équipée et Wi‑Fi rapide. Profitez du calme d'une rue discrète tout en étant au cœur de l'énergie tokyoïte.</p>
          </article>
          <article class="card">
            <img src="images/kioshi.jpg" alt="Appartement de Luca" class="thumb">
            <div class="name">Appartement de Leia:</div>
            <p class="desc">Charmant studio au cœur de Paris, alliant confort moderne et authenticité. À deux pas des cafés typiques et des monuments emblématiques, idéal pour découvrir la Ville Lumière.</p>
          </article>
          <article class="card">
            <img src="images/kyoto-appart.jpg" alt="Appartement de Saitama" class="thumb">
            <div class="name">Appartement de Suzuya :</div>
            <p class="desc">Maison typique au toit rouge et tatamis, entourée de verdure et proche de la mer turquoise. Une immersion authentique dans la culture d'Okinawa, entre calme et nature.</p>
          </article>
        </div>
      </section>





      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
    <script>
    // Menu déroulant profil
    function toggleProfileMenu(event) {
      event.stopPropagation();
      const dropdown = document.getElementById('profileDropdown');
      dropdown.classList.toggle('active');
    }

    // Fermer le menu en cliquant ailleurs
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('profileDropdown');
      const wrapper = document.querySelector('.profile-menu-wrapper');
      
      if (dropdown && !wrapper.contains(event.target)) {
        dropdown.classList.remove('active');
      }
    });

    // Empêcher la fermeture quand on clique dans le menu
    document.getElementById('profileDropdown')?.addEventListener('click', function(event) {
      event.stopPropagation();
    });
    </script>
</body>
</html>