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
  <title>YOKOSO - Accueil</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/scss/main.css">
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
        <a href="#">Compléter le profil</a>
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
          <button class="icon-btn" title="Mon profil" onclick="openProfileModal()">
            <i class="fa-solid fa-user" style="color: #000000;"></i>
          </button>
        <?php else: ?>
          <a href="register.php" class="connexion">S'inscrire</a>
          <a href="login.php" class="connexion">Connexion</a>
        <?php endif; ?>
      </div>

      <section class="hero" aria-label="Carousel Pays Disponibles">
        <p>YOKOSO est disponible dans ces pays :</p>
        <div class="carousel" data-index="0">
          <div class="slides">
            <div class="slide" data-city="TOKYO" data-country="Japon">
              <img src="images/tokyo-japon.jpg" alt="Tokyo, Japon">
            </div>
            <div class="slide" data-city="PARIS" data-country="France">
              <img src="images/paris-france.jpg" alt="Paris, France">
            </div>
          </div>
          <button class="prev-btn carousel-btn" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
          <button class="next-btn carousel-btn" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
          <div class="dots" role="tablist">
            <button class="dot active" aria-label="Aller à Tokyo" data-to="0"></button>
            <button class="dot" aria-label="Aller à Paris" data-to="1"></button>
          </div>
          <div class="overlay"></div>
          <div class="centered">
            <div class="title" id="hero-city">TOKYO</div>
            <div class="subtitle" id="hero-country">Japon</div>
          </div>
        </div>
      </section>

      <section>
        <h3 class="section-title">Nos logements les mieux notés :</h3>
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

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
  </div>

  <?php if ($is_logged_in): ?>
  <!-- Modale de profil -->
  <div class="modal-overlay" id="profileModal" onclick="closeModalOnOverlay(event)">
    <div class="modal">
      <div class="modal-header">
        <h2>Mon profil</h2>
        <button class="modal-close" onclick="closeProfileModal()">×</button>
      </div>
      <div class="modal-body">
        <div class="tabs">
          <button class="tab active" onclick="switchTab('info')">Informations</button>
          <button class="tab" onclick="switchTab('password')">Mot de passe</button>
        </div>

        <!-- Onglet Informations -->
        <div class="tab-content active" id="info-tab">
          <div id="info-messages"></div>
          <form id="infoForm">
            <div class="form-group">
              <label for="prenom">Prénom</label>
              <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user_data['prenom']) ?>" required>
            </div>
            <div class="form-group">
              <label for="nom">Nom</label>
              <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user_data['nom']) ?>" required>
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
            </div>
            <div class="form-group">
              <label for="telephone">Téléphone</label>
              <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($user_data['telephone'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
          </form>
        </div>

        <!-- Onglet Mot de passe -->
        <div class="tab-content" id="password-tab">
          <div id="password-messages"></div>
          <form id="passwordForm">
            <div class="form-group">
              <label for="current_password">Mot de passe actuel</label>
              <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
              <label for="new_password">Nouveau mot de passe</label>
              <input type="password" id="new_password" name="new_password" minlength="8" required>
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirmer le mot de passe</label>
              <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
          </form>
        </div>

        <!-- Bouton de déconnexion -->
        <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script>
    // Carousel
    (function(){
      const root = document.querySelector('.carousel');
      if(!root) return;
      const slides = root.querySelector('.slides');
      const slideEls = Array.from(root.querySelectorAll('.slide'));
      const prev = root.querySelector('.prev-btn');
      const next = root.querySelector('.next-btn');
      const dots = Array.from(root.querySelectorAll('.dot'));
      const cityEl = document.getElementById('hero-city');
      const countryEl = document.getElementById('hero-country');

      let index = 0;
      function update(){
        slides.style.transform = `translateX(${-index * 100}%)`;
        dots.forEach((d,i)=>d.classList.toggle('active', i===index));
        const s = slideEls[index];
        if(cityEl && countryEl && s){
          cityEl.textContent = s.getAttribute('data-city') || '';
          countryEl.textContent = s.getAttribute('data-country') || '';
        }
      }
      function go(to){ index = (to + slideEls.length) % slideEls.length; update(); }
      prev.addEventListener('click', ()=>go(index-1));
      next.addEventListener('click', ()=>go(index+1));
      dots.forEach((d)=> d.addEventListener('click', ()=>{ go(parseInt(d.getAttribute('data-to')||'0',10)); }));
      let timer = setInterval(()=>go(index+1), 5000);
      root.addEventListener('mouseenter', ()=>clearInterval(timer));
      root.addEventListener('mouseleave', ()=>{ timer = setInterval(()=>go(index+1), 5000); });
      update();
    })();

    // Modale de profil
    function openProfileModal() {
      document.getElementById('profileModal').classList.add('active');
    }

    function closeProfileModal() {
      document.getElementById('profileModal').classList.remove('active');
    }

    function closeModalOnOverlay(event) {
      if (event.target.id === 'profileModal') {
        closeProfileModal();
      }
    }

    function switchTab(tab) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      
      if (tab === 'info') {
        document.querySelector('.tab:nth-child(1)').classList.add('active');
        document.getElementById('info-tab').classList.add('active');
      } else {
        document.querySelector('.tab:nth-child(2)').classList.add('active');
        document.getElementById('password-tab').classList.add('active');
      }
    }

    // Formulaire des informations
    document.getElementById('infoForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      
      try {
        const response = await fetch('update-profile.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        const msgDiv = document.getElementById('info-messages');
        if (result.success) {
          msgDiv.innerHTML = '<div class="success-msg">' + result.message + '</div>';
          // Mettre à jour le prénom affiché
          setTimeout(() => location.reload(), 1500);
        } else {
          msgDiv.innerHTML = '<div class="error-msg">' + result.message + '</div>';
        }
      } catch (error) {
        document.getElementById('info-messages').innerHTML = '<div class="error-msg">Erreur de connexion</div>';
      }
    });

    // Formulaire du mot de passe
    document.getElementById('passwordForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      
      try {
        const response = await fetch('update-password.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        const msgDiv = document.getElementById('password-messages');
        if (result.success) {
          msgDiv.innerHTML = '<div class="success-msg">' + result.message + '</div>';
          e.target.reset();
        } else {
          msgDiv.innerHTML = '<div class="error-msg">' + result.message + '</div>';
        }
      } catch (error) {
        document.getElementById('password-messages').innerHTML = '<div class="error-msg">Erreur de connexion</div>';
      }
    });
  </script>
</body>
</html>