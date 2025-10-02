<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-image: url('assets/maison-traditionelle-japonaise.jpg');
      background-size: auto;
      background-position: center;
      background-repeat: no-repeat;
      backdrop-filter: contrast(0.3);
      height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center; 
    }

    .image-container {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .logo {
      width: 150px;
      height: 150px;
      cursor: pointer;
      transition: transform 0.6s ease, opacity 0.6s ease;
    }

    .logo.clicked {
      transform: scale(2);
      opacity: 0;
    }
  </style>
</head>
<body>
  <div class="image-container">
    <img src="assets/yokoso.png" alt="Yokoso" class="Yokoso">
    <a href="home.php" id="logo-link">
      <img src="assets/logo.png" alt="Logo" class="logo">
    </a>
  </div>

  <!-- Son -->
  <audio id="click-sound" src="assets/Yokoso-2.mp3" preload="auto"></audio>

  <script>
    const logo = document.querySelector(".logo");
    const link = document.getElementById("logo-link");
    const sound = document.getElementById("click-sound");

    link.addEventListener("click", function (e) {
      e.preventDefault();
      logo.classList.add("clicked");e
      sound.play();

      setTimeout(() => {
        window.location.href = link.href;
      }, 1000);
    });
  </script>
</body>
</html>
