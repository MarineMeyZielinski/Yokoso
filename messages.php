<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['user_id'];

// Toutes les conversations de l'utilisateur avec le dernier message et l'interlocuteur
$stmt = $pdo->prepare('
    SELECT
        c.id_conversation,
        c.id_annonce,
        c.date_dernier_message,
        a.titre as annonce_titre,
        -- Interlocuteur
        CASE WHEN c.id_user1 = ? THEN c.id_user2 ELSE c.id_user1 END as id_interlocuteur,
        u.prenom as interlocuteur_prenom,
        u.nom    as interlocuteur_nom,
        u.photo_profil as interlocuteur_photo,
        -- Dernier message
        (SELECT contenu FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.date_envoi DESC LIMIT 1) as dernier_message,
        -- Non lus
        (SELECT COUNT(*) FROM messages m WHERE m.id_conversation = c.id_conversation AND m.id_expediteur != ? AND m.lu = 0) as non_lus
    FROM conversations c
    JOIN users u ON u.id_user = CASE WHEN c.id_user1 = ? THEN c.id_user2 ELSE c.id_user1 END
    LEFT JOIN annonces a ON a.id_annonce = c.id_annonce
    WHERE c.id_user1 = ? OR c.id_user2 = ?
    ORDER BY c.date_dernier_message DESC
');
$stmt->execute([$id_user, $id_user, $id_user, $id_user, $id_user]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_non_lus = array_sum(array_column($conversations, 'non_lus'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YOKOSO - Messages</title>
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

      <div class="messagerie-page">
        <div class="messagerie-header">
          <h2><i class="fa-solid fa-envelope"></i> Messages</h2>
          <?php if ($total_non_lus > 0): ?>
            <span class="msg-count-badge"><?= $total_non_lus ?> non lu<?= $total_non_lus > 1 ? 's' : '' ?></span>
          <?php endif; ?>
        </div>

        <?php if (empty($conversations)): ?>
          <div class="empty-state-msg">
            <i class="fa-regular fa-envelope"></i>
            <h3>Aucun message</h3>
            <p>Contactez un hôte depuis la page d'une annonce pour démarrer une conversation.</p>
            <a href="logement.php" class="btn-explore-msg">Explorer les logements</a>
          </div>
        <?php else: ?>
          <div class="conv-list">
            <?php foreach ($conversations as $conv): ?>
              <a href="conversation.php?id=<?= $conv['id_conversation'] ?>"
                 class="conv-item <?= $conv['non_lus'] > 0 ? 'has-unread' : '' ?>">

                <div class="conv-avatar">
                  <?php if (!empty($conv['interlocuteur_photo']) && file_exists($conv['interlocuteur_photo'])): ?>
                    <img src="<?= htmlspecialchars($conv['interlocuteur_photo']) ?>" alt="">
                  <?php else: ?>
                    <div class="conv-avatar-placeholder">
                      <?= strtoupper(mb_substr($conv['interlocuteur_prenom'], 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="conv-body">
                  <div class="conv-top">
                    <span class="conv-name">
                      <?= htmlspecialchars($conv['interlocuteur_prenom'] . ' ' . $conv['interlocuteur_nom']) ?>
                    </span>
                    <span class="conv-time">
                      <?= (new DateTime($conv['date_dernier_message']))->format('d/m · H:i') ?>
                    </span>
                  </div>
                  <?php if (!empty($conv['annonce_titre'])): ?>
                    <div class="conv-annonce">
                      <i class="fa-solid fa-house"></i>
                      <?= htmlspecialchars($conv['annonce_titre']) ?>
                    </div>
                  <?php endif; ?>
                  <div class="conv-preview">
                    <?= htmlspecialchars(mb_substr($conv['dernier_message'] ?? '…', 0, 80)) ?>
                  </div>
                </div>

                <?php if ($conv['non_lus'] > 0): ?>
                  <span class="conv-badge"><?= $conv['non_lus'] ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés.</div>
    </main>
  </div>
</body>
</html>
