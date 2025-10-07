<?php
// Inclure le fichier de configuration pour la connexion à la base de données
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Inscription</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/scss/main.css">

</head>
<body class="register-body">
  <div class="bg-register"></div>
  <div class="overlay-register"></div>
  <div class="page-register">
    <div class="brand-register">
      <img src="images/yokoso-blanc.png" alt="YOKOSO">
    </div>

    <div class="card-register">
      <div class="badge-register">
        <img src="images/logo-blanc-seul-removebg-preview.png" alt="Logo YOKOSO">
      </div>
      <h1>S'inscrire</h1>
      <form action="register.php" method="post" autocomplete="on">
        <div>
          <label for="last_name" class="labelregister">Nom</label>
          <input type="text" id="last_name" name="last_name" placeholder="Nom" required class="registerinput">
        </div>
        <div>
          <label for="first_name" class="labelregister">Prénom</label>
          <input type="text" id="first_name" name="first_name" placeholder="Prénom" required class="registerinput">
        </div>
        <div>
          <label for="email"class="labelregister">Adresse mail</label>
          <input type="email" id="email" name="email" placeholder="Adresse mail" inputmode="email" autocomplete="email" required class="registerinput">
        </div>
        <div>
          <label for="password" class="labelregister">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Mot de passe" minlength="6" required class="registerinput">
        </div>
        <div>
          <label for="phone" class="labelregister">Numéro de téléphone (optionnel)</label>
          <input type="tel" id="phone" name="phone" placeholder="Numéro de téléphone" inputmode="tel" class="registerinput">
        </div>
        <button type="submit" class="submit">Terminé</button>
      </form>
    </div>
  </div>
</body>
</html>