<?php
session_start();
require_once 'includes/config.php';

// Récupérer tous les logements disponibles avec leur photo principale
try {
    $sql = "SELECT
                a.id_annonce,
                a.titre,
                a.description,
                a.ville,
                a.pays,
                a.prix_nuit,
                a.capacite_max,
                a.type_logement,
                p.nom_fichier as photo_principale
            FROM annonces a
            LEFT JOIN photos p ON a.id_annonce = p.id_annonce AND p.photo_principale = 1
            WHERE a.disponible = 1
            ORDER BY a.date_creation DESC";

    $stmt = $pdo->query($sql);
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $annonces = [];
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

            <section>
                <h3 class="section-title">Nos logements (<?= count($annonces) ?>) :</h3>
                
                <?php if (empty($annonces)): ?>
                    <p style="text-align: center; padding: 40px; color: #666;">
                        Aucun logement disponible pour le moment.
                    </p>
                <?php else: ?>
                    <div class="cards">
                        <?php foreach ($annonces as $annonce):
                            // Déterminer le chemin de la photo
                            if (!empty($annonce['photo_principale'])) {
                                $photo = 'uploads/annonces/' . $annonce['photo_principale'];
                            } else {
                                $photo = 'images/placeholder.jpg';
                            }

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
</body>
</html>