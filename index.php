<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Introduction - YOKOSO</title>
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="bodyyokoso">
  <div class="image-container-introsite">
    <img src="images/yokoso.png" alt="Yokoso" class="Yokoso">
    <a href="home.php" id="logo-link">
      <img src="images/logo.png" alt="Logo" class="logo-intro-site">
    </a>
  </div>

  <!-- Son -->
  <audio id="click-sound" src="sound/Yokoso.mp3" preload="auto"></audio>

  <script>
    const logo = document.querySelector(".logo-intro-site");
    const link = document.getElementById("logo-link");
    const sound = document.getElementById("click-sound");

    link.addEventListener("click", function (e) {
      e.preventDefault();
      logo.classList.add("clicked");e
      sound.play();

      setTimeout(() => {
        window.location.href = link.href;
      }, 1200);
    });
  </script>
</body>
</html>
