<?php
session_start();
require_once 'includes/config.php';

$id_annonce = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_annonce === 0) {
    header('Location: logement.php');
    exit;
}

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

$is_logged_in = isset($_SESSION['user_id']);
$is_owner     = $is_logged_in && $_SESSION['user_id'] == $annonce['id_proprietaire'];

// --- Avis ---
$avis_list = $pdo->prepare('
    SELECT av.note, av.commentaire, av.date_avis,
           u.prenom, u.nom, u.photo_profil
    FROM avis av
    JOIN users u ON av.id_auteur = u.id_user
    WHERE av.id_annonce = ?
    ORDER BY av.date_avis DESC
');
$avis_list->execute([$id_annonce]);
$avis_list = $avis_list->fetchAll(PDO::FETCH_ASSOC);

$nb_avis    = count($avis_list);
$note_moy   = $nb_avis > 0 ? array_sum(array_column($avis_list, 'note')) / $nb_avis : null;

// L'utilisateur peut-il laisser un avis ?
$can_review = false;
if ($is_logged_in && !$is_owner) {
    $stmt = $pdo->prepare('
        SELECT id_reservation FROM reservations
        WHERE id_annonce = ? AND id_voyageur = ? AND date_fin < NOW() AND statut != "annulee"
        LIMIT 1
    ');
    $stmt->execute([$id_annonce, $_SESSION['user_id']]);
    $has_stayed = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT id_avis FROM avis WHERE id_annonce = ? AND id_auteur = ? LIMIT 1');
    $stmt->execute([$id_annonce, $_SESSION['user_id']]);
    $already_reviewed = $stmt->fetch();

    $can_review = $has_stayed && !$already_reviewed;
}
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
                    <div class="annonce-header-right">
                        <?php if ($note_moy !== null): ?>
                            <div class="annonce-rating">
                                <i class="fa-solid fa-star"></i>
                                <span class="rating-value"><?= number_format($note_moy, 1) ?></span>
                                <span class="rating-count">(<?= $nb_avis ?> avis)</span>
                            </div>
                        <?php endif; ?>
                        <div class="annonce-price">
                            <span class="price-amount"><?= number_format($annonce['prix_nuit'], 0, ',', ' ') ?>€</span>
                            <span class="price-label">/nuit</span>
                        </div>
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
                                         loading="lazy"
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

                            <?php if (!$is_logged_in): ?>
                                <!-- Visiteur non connecté -->
                                <div class="booking-guest-prompt">
                                    <p>Connectez-vous pour réserver ce logement.</p>
                                    <a href="login.php?redirect=<?= urlencode('annonce.php?id=' . $annonce['id_annonce']) ?>" class="btn-guest-reserve">
                                        Se connecter pour réserver
                                    </a>
                                </div>

                            <?php elseif ($is_owner): ?>
                                <!-- Propriétaire de l'annonce -->
                                <div class="booking-owner-notice">
                                    <i class="fa-solid fa-house-user"></i>
                                    <p>Vous êtes l'hôte de ce logement.</p>
                                </div>

                            <?php else: ?>
                                <!-- Utilisateur connecté, pas le propriétaire -->
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
                                        <input type="date" name="date_debut" id="dateDebut" required min="<?= date('Y-m-d') ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Départ</label>
                                        <input type="date" name="date_fin" id="dateFin" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
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
                            <?php endif; ?>

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

            <!-- Section avis -->
            <div class="avis-section" id="avis">
                <div class="avis-header">
                    <h2>
                        <?php if ($note_moy !== null): ?>
                            <i class="fa-solid fa-star"></i>
                            <?= number_format($note_moy, 1) ?> · <?= $nb_avis ?> avis
                        <?php else: ?>
                            Avis
                        <?php endif; ?>
                    </h2>
                </div>

                <?php if (isset($_GET['avis_ok'])): ?>
                    <div class="avis-flash avis-flash--success">Votre avis a été publié, merci !</div>
                <?php elseif (isset($_GET['avis_error'])): ?>
                    <div class="avis-flash avis-flash--error">
                        <?php
                        $codes = ['1' => 'Note invalide.', '2' => 'Vous devez avoir séjourné dans ce logement pour laisser un avis.', '3' => 'Vous avez déjà laissé un avis pour ce logement.'];
                        echo $codes[$_GET['avis_error']] ?? 'Une erreur est survenue.';
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($can_review): ?>
                    <form action="submit-avis.php" method="post" class="avis-form">
                        <input type="hidden" name="id_annonce" value="<?= $id_annonce ?>">
                        <div class="star-picker">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="note" id="star<?= $i ?>" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>"><i class="fa-solid fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                        <textarea name="commentaire" placeholder="Partagez votre expérience (optionnel)" rows="3"></textarea>
                        <button type="submit" class="btn-submit-avis">Publier mon avis</button>
                    </form>
                <?php elseif ($is_logged_in && !$is_owner && !$can_review && $already_reviewed ?? false): ?>
                    <p class="avis-already">Vous avez déjà laissé un avis pour ce logement.</p>
                <?php endif; ?>

                <?php if (empty($avis_list)): ?>
                    <p class="avis-empty">Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
                <?php else: ?>
                    <div class="avis-grid">
                        <?php foreach ($avis_list as $avis): ?>
                            <div class="avis-card">
                                <div class="avis-card-header">
                                    <div class="avis-avatar">
                                        <?php if (!empty($avis['photo_profil']) && file_exists($avis['photo_profil'])): ?>
                                            <img src="<?= htmlspecialchars($avis['photo_profil']) ?>" alt="<?= htmlspecialchars($avis['prenom']) ?>">
                                        <?php else: ?>
                                            <i class="fa-solid fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="avis-author"><?= htmlspecialchars($avis['prenom'] . ' ' . mb_substr($avis['nom'], 0, 1) . '.') ?></div>
                                        <div class="avis-date"><?= (new DateTime($avis['date_avis']))->format('F Y') ?></div>
                                    </div>
                                    <div class="avis-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-<?= $i <= $avis['note'] ? 'solid' : 'regular' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if (!empty($avis['commentaire'])): ?>
                                    <p class="avis-commentaire"><?= nl2br(htmlspecialchars($avis['commentaire'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer">© 2025 YOKOSO Corp. Tous droits réservés. | Mentions légales | Politique de confidentialité</div>
        </main>
    </div>

    <script>
        // Date départ liée dynamiquement à l'arrivée
        const dateDebut = document.getElementById('dateDebut');
        const dateFin   = document.getElementById('dateFin');
        if (dateDebut && dateFin) {
            dateDebut.addEventListener('change', function() {
                const d = new Date(this.value);
                d.setDate(d.getDate() + 1);
                const minFin = d.toISOString().split('T')[0];
                dateFin.min = minFin;
                if (dateFin.value && dateFin.value <= this.value) {
                    dateFin.value = minFin;
                }
            });
        }

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