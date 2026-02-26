<?php
session_start();
require_once 'includes/config.php';

// Récupérer l'ID de l'annonce
$id_annonce = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_annonce === 0) {
    header('Location: logement.php');
    exit;
}

// Récupérer les détails de l'annonce
try {
    $sql = "SELECT
                a.*,
                u.prenom as proprietaire_prenom,
                u.nom as proprietaire_nom,
                u.photo_profil as proprietaire_photo
            FROM annonces a
            LEFT JOIN users u ON a.id_proprietaire = u.id_user
            WHERE a.id_annonce = ? AND a.disponible = 1
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_annonce]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$annonce) {
        header('Location: logement.php');
        exit;
    }

    // Récupérer toutes les photos de l'annonce depuis la BDD
    $sql_photos = "SELECT nom_fichier, photo_principale, ordre_affichage
                   FROM photos
                   WHERE id_annonce = ?
                   ORDER BY photo_principale DESC, ordre_affichage ASC";

    $stmt_photos = $pdo->prepare($sql_photos);
    $stmt_photos->execute([$id_annonce]);
    $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Location: logement.php');
    exit;
}

// Équipements disponibles
$equipements = [];
if ($annonce['wifi']) $equipements[] = ['icon' => 'wifi', 'label' => 'WiFi'];
if ($annonce['parking']) $equipements[] = ['icon' => 'square-parking', 'label' => 'Parking'];
if ($annonce['climatisation']) $equipements[] = ['icon' => 'snowflake', 'label' => 'Climatisation'];
if ($annonce['lave_linge']) $equipements[] = ['icon' => 'shirt', 'label' => 'Lave-linge'];
if ($annonce['television']) $equipements[] = ['icon' => 'tv', 'label' => 'Télévision'];
if ($annonce['cuisine_equipee']) $equipements[] = ['icon' => 'utensils', 'label' => 'Cuisine équipée'];
if ($annonce['seche_cheveux']) $equipements[] = ['icon' => 'wind', 'label' => 'Sèche-cheveux'];
if ($annonce['animaux_accepte']) $equipements[] = ['icon' => 'paw', 'label' => 'Animaux acceptés'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($annonce['titre']) ?> - YOKOSO</title>
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

            <div class="annonce-detail">
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="home.php">Accueil</a>
                    <span>›</span>
                    <a href="logement.php">Logements</a>
                    <span>›</span>
                    <span><?= htmlspecialchars($annonce['ville']) ?></span>
                </div>

                <!-- Titre et localisation -->
                <div class="annonce-header">
                    <div>
                        <h1><?= htmlspecialchars($annonce['titre']) ?></h1>
                        <div class="annonce-location">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= htmlspecialchars($annonce['adresse']) ?>, <?= htmlspecialchars($annonce['ville']) ?>, <?= htmlspecialchars($annonce['pays']) ?>
                        </div>
                    </div>
                    <div class="annonce-price">
                        <span class="price-amount"><?= number_format($annonce['prix_nuit'], 0, ',', ' ') ?>€</span>
                        <span class="price-label">/nuit</span>
                    </div>
                </div>

                <!-- Galerie photos -->
                <div class="annonce-gallery">
                    <?php if (!empty($photos)): ?>
                        <div class="gallery-main">
                            <img id="mainImage"
                                 src="uploads/annonces/<?= htmlspecialchars($photos[0]['nom_fichier']) ?>"
                                 alt="Photo principale">
                        </div>

                        <?php if (count($photos) > 1): ?>
                            <div class="gallery-thumbnails">
                                <?php foreach ($photos as $index => $photo): ?>
                                    <img src="uploads/annonces/<?= htmlspecialchars($photo['nom_fichier']) ?>"
                                         alt="Photo <?= $index + 1 ?>"
                                         class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                         onclick="changeMainImage('uploads/annonces/<?= htmlspecialchars($photo['nom_fichier']) ?>', this)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="gallery-main">
                            <img src="images/placeholder.jpg" alt="Pas de photo disponible">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="annonce-content">
                    <!-- Informations principales -->
                    <div class="annonce-main">
                        <div class="annonce-section">
                            <h2>À propos de ce logement</h2>
                            <div class="annonce-specs">
                                <span><i class="fa-solid fa-bed"></i> <?= $annonce['nb_chambres'] ?> chambre(s)</span>
                                <span><i class="fa-solid fa-person"></i> <?= $annonce['capacite_max'] ?> voyageur(s)</span>
                                <span><i class="fa-solid fa-bath"></i> <?= $annonce['nb_sdb'] ?> salle(s) de bain</span>
                                <span><i class="fa-solid fa-home"></i> <?= ucfirst($annonce['type_logement']) ?></span>
                            </div>
                        </div>

                        <div class="annonce-section">
                            <h2>Description</h2>
                            <p class="annonce-description"><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
                        </div>

                        <?php if (!empty($equipements)): ?>
                            <div class="annonce-section">
                                <h2>Équipements et services</h2>
                                <div class="annonce-equipements">
                                    <?php foreach ($equipements as $equip): ?>
                                        <div class="equipement-item">
                                            <i class="fa-solid fa-fw fa-<?= $equip['icon'] ?>"></i>
                                            <span><?= $equip['label'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar réservation -->
                    <aside class="annonce-sidebar">
                        <div class="booking-card">
                            <div class="booking-price">
                                <span class="amount"><?= number_format($annonce['prix_nuit'], 0, ',', ' ') ?>€</span>
                                <span class="label">/nuit</span>
                            </div>

                            <?php if (isset($_GET['booking_error']) && !empty($_SESSION['reservation_errors'])): ?>
                                <div class="booking-errors">
                                    <?php foreach ($_SESSION['reservation_errors'] as $err): ?>
                                        <p><?= htmlspecialchars($err) ?></p>
                                    <?php endforeach; ?>
                                </div>
                                <?php unset($_SESSION['reservation_errors']); ?>
                            <?php endif; ?>

                            <form action="reservation.php" method="post" class="booking-form">
                                <input type="hidden" name="id_annonce" value="<?= $annonce['id_annonce'] ?>">
                                
                                <div class="form-group">
                                    <label>Arrivée</label>
                                    <input type="date" name="date_debut" required min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Départ</label>
                                    <input type="date" name="date_fin" required min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Voyageurs</label>
                                    <select name="nb_voyageurs" required>
                                        <?php for ($i = 1; $i <= $annonce['capacite_max']; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> voyageur<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn-reserve">Réserver</button>
                            </form>

                            <!-- Propriétaire -->
                            <div class="owner-info">
                                <h3>Hôte</h3>
                                <div class="owner-card">
                                    <?php if (!empty($annonce['proprietaire_photo'])): ?>
                                        <img src="<?= htmlspecialchars($annonce['proprietaire_photo']) ?>" alt="Hôte">
                                    <?php else: ?>
                                        <div class="owner-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="owner-name">
                                        <?= htmlspecialchars($annonce['proprietaire_prenom']) ?> <?= htmlspecialchars($annonce['proprietaire_nom']) ?>
                                    </div>
                                </div>
                                <a href="contact.php?user=<?= $annonce['id_proprietaire'] ?>&annonce=<?= $annonce['id_annonce'] ?>" class="btn-contact">
                                    <i class="fa-solid fa-envelope"></i> Contacter l'hôte
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
        </main>
    </div>

    <script>
        function changeMainImage(src, thumbnail) {
            // Changer l'image principale
            document.getElementById('mainImage').src = src;

            // Retirer la classe active de toutes les miniatures
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });

            // Ajouter la classe active à la miniature cliquée
            thumbnail.classList.add('active');
        }
    </script>
</body>
</html>