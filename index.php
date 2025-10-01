<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=Y, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <div class="image-container">
  <img src="assets/yokoso.png" alt="Yokoso" class="Yokoso">
  <a href="home.php" ><img src="assets/logo.png" alt="Logo" class="logo" width="150px" height="150px"> </a>
</div>

  <style>

    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

    h1 {
      color: black;
      text-align: center;
      font-size: 100px;
      font-family: 'Arial', sans-serif;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
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

</body>
</html>