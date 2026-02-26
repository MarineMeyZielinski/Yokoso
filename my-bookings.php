<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_voyageur = $_SESSION['user_id'];

// Annuler une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancel_id = (int)$_POST['cancel_id'];
    $stmt = $pdo->prepare('
        UPDATE reservations SET statut = "annulee"
        WHERE id_reservation = ? AND id_voyageur = ? AND date_debut > NOW()
    ');
    $stmt->execute([$cancel_id, $id_voyageur]);
    header('Location: my-bookings.php?cancelled=1');
    exit;
}

// Récupérer les réservations de l'utilisateur
$stmt = $pdo->prepare('
    SELECT r.*,
           a.titre, a.ville, a.pays, a.prix_nuit,
           p.nom_fichier as photo
    FROM reservations r
    JOIN annonces a ON r.id_annonce = a.id_annonce
    LEFT JOIN photos p ON p.id_annonce = a.id_annonce AND p.photo_principale = 1
    WHERE r.id_voyageur = ?
    ORDER BY r.date_reservation DESC
');
$stmt->execute([$id_voyageur]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Mes réservations</title>
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

      <div class="profile-container">
        <!-- Onglets -->
        <div class="profile-tabs">
          <a href="edit-profile.php" class="profile-tab">Compléter le profil</a>
          <a href="my-bookings.php" class="profile-tab active">Mes réservations</a>
          <a href="my-listings.php" class="profile-tab">Mes annonces</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
          <div class="booking-success">
            <i class="fa-solid fa-circle-check"></i>
            Réservation confirmée ! Bon séjour.
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['cancelled'])): ?>
          <div class="booking-cancelled-msg">
            <i class="fa-solid fa-circle-xmark"></i>
            Réservation annulée.
          </div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
          <div class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <h2>Aucune réservation</h2>
            <p>Vous n'avez pas encore effectué de réservation.</p>
            <a href="logement.php" class="btn-add-listing">Explorer les logements</a>
          </div>

        <?php else: ?>
          <div class="bookings-list">
            <?php foreach ($reservations as $r):
              $d1    = new DateTime($r['date_debut']);
              $d2    = new DateTime($r['date_fin']);
              $nuits = $d1->diff($d2)->days;
              $past  = $d2 < new DateTime('today');
              $can_cancel = ($r['statut'] === 'confirmee') && !$past;
            ?>
              <div class="booking-item <?= $r['statut'] === 'annulee' ? 'is-cancelled' : ($past ? 'is-past' : '') ?>">
                <?php if (!empty($r['photo'])): ?>
                  <img src="uploads/annonces/<?= htmlspecialchars($r['photo']) ?>" alt="<?= htmlspecialchars($r['titre']) ?>" class="booking-image">
                <?php else: ?>
                  <div class="booking-image booking-image--placeholder"><i class="fa-solid fa-image"></i></div>
                <?php endif; ?>

                <div class="booking-content">
                  <a href="annonce.php?id=<?= $r['id_annonce'] ?>" class="booking-title">
                    <?= htmlspecialchars($r['titre']) ?>
                  </a>
                  <p class="booking-location">
                    <i class="fa-solid fa-location-dot"></i>
                    <?= htmlspecialchars($r['ville']) ?>, <?= htmlspecialchars($r['pays']) ?>
                  </p>
                  <p class="booking-dates">
                    <i class="fa-regular fa-calendar"></i>
                    <?= $d1->format('d/m/Y') ?> → <?= $d2->format('d/m/Y') ?>
                    <span class="booking-nights"><?= $nuits ?> nuit<?= $nuits > 1 ? 's' : '' ?></span>
                  </p>
                  <p class="booking-travelers">
                    <i class="fa-solid fa-user-group"></i>
                    <?= $r['nb_voyageurs'] ?> voyageur<?= $r['nb_voyageurs'] > 1 ? 's' : '' ?>
                  </p>
                </div>

                <div class="booking-aside">
                  <span class="booking-status booking-status--<?= $r['statut'] === 'annulee' ? 'cancelled' : ($past ? 'past' : 'confirmed') ?>">
                    <?php if ($r['statut'] === 'annulee'): ?>
                      Annulée
                    <?php elseif ($past): ?>
                      Terminée
                    <?php else: ?>
                      Confirmée
                    <?php endif; ?>
                  </span>
                  <p class="booking-price"><?= number_format($r['prix_total'], 0, ',', ' ') ?> €</p>
                  <?php if ($can_cancel): ?>
                    <form method="post" onsubmit="return confirm('Annuler cette réservation ?')">
                      <input type="hidden" name="cancel_id" value="<?= $r['id_reservation'] ?>">
                      <button type="submit" class="btn-cancel-booking">Annuler</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
    </main>
  </div>
</body>
</html>
