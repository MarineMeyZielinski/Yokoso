<?php
session_start();
require_once 'includes/config.php';

// Mapping temporaire des photos
$photos_map = [
    1 => 'images/kioshi.jpg',
    2 => 'images/appartement-lucas.jpg', 
    3 => 'images/appartement-saitama.jpg',
    4 => 'images/france-1.webp',
    5 => 'images/france-2.jpg',
    6 => 'images/france-3.jpg',
    7 => 'images/apparttradionnel.jpg',
    8 => 'images/kyoto-appart.jpg',
    9 => 'images/kioshi.jpg',
    10 => 'images/appartement-lucas.jpg',
    11 => 'images/appartement-saitama.jpg',
    12 => 'images/kyoto-appart.jpg'
];

// Récupérer les 3 logements les plus récents (ou mieux notés)
try {
    $sql = "SELECT 
                id_annonce,
                titre,
                description,
                ville,
                pays,
                prix_nuit,
                capacite_max,
                type_logement
            FROM annonces
            WHERE disponible = 1
            ORDER BY date_creation DESC
            LIMIT 3";
    
    $stmt = $pdo->query($sql);
    $annonces_featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $annonces_featured = [];
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
        <a href="publier-annonce.php">Publier une annonce</a>
        <a href="edit-profile.php">Mon profil</a>
        <a href='my-bookings.php'>Mes réservations</a>
        <a href='my-listings.php'>Mes annonces</a>
      </nav>
    </aside>

    <main class="content">
      <?php include 'includes/header.php'; ?>
      
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
        <div class="section-header">
          <h3 class="section-title">Nos logements les plus récents :</h3>
          <a href="logement.php" class="view-all">Voir tous les logements →</a>
        </div>
        
        <?php if (empty($annonces_featured)): ?>
          <p style="text-align: center; padding: 40px; color: #666;">
            Aucun logement disponible pour le moment.
          </p>
        <?php else: ?>
          <div class="cards">
            <?php foreach ($annonces_featured as $annonce): 
              $photo = $photos_map[$annonce['id_annonce']] ?? 'images/placeholder.jpg';
              
              // Tronquer la description
              $description = strlen($annonce['description']) > 150 
                  ? substr($annonce['description'], 0, 150) . '...' 
                  : $annonce['description'];
            ?>
              <article class="card" onclick="window.location.href='annonce.php?id=<?= $annonce['id_annonce'] ?>'">
                <img src="<?= htmlspecialchars($photo) ?>" 
                     alt="<?= htmlspecialchars($annonce['titre']) ?>" 
                     class="thumb">
                
                <div class="card-header">
                  <div class="name"><?= htmlspecialchars($annonce['titre']) ?></div>
                  <div class="card-location">
                    <i class="fa-solid fa-location-dot"></i>
                    <?= htmlspecialchars($annonce['ville']) ?>, <?= htmlspecialchars($annonce['pays']) ?>
                  </div>
                </div>
                
                <p class="desc"><?= htmlspecialchars($description) ?></p>
                
                <div class="card-footer">
                  <span class="price"><?= number_format($annonce['prix_nuit'], 0, ',', ' ') ?>€<small>/nuit</small></span>
                  <span class="capacity">
                    <i class="fa-solid fa-user"></i> <?= $annonce['capacite_max'] ?> pers.
                  </span>
                  <span class="type"><?= ucfirst($annonce['type_logement']) ?></span>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
  </div>

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
  </script>
</body>
</html>