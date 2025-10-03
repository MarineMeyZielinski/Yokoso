<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Accueil</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/scss/main.css">
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
        <a href="home.php">Accueil</a>
        <a href= 'logement.php'>Nos logements</a>
        <a href="#">Mes reservations</a>
        <a href="#">Devenir hôte</a>
        <a href="#">Publier une annonce</a>
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
        <button class="connexion">S'inscrire</button>
        <button class="connexion">Connexion</button>
        <button class="icon-btn" title="Profil"><i class="fa-solid fa-user" style="color: #000000;"></i></button>
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

  <script>
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
      function update(fromDot){
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

