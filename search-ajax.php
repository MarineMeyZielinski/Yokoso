<?php
session_start();
require_once 'includes/config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$limit = 5; // Nombre de résultats dans le dropdown

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Requête trop courte']);
    exit;
}

try {
    // Recherche insensible à la casse avec LOWER()
    $searchTerm = "%" . strtolower($query) . "%";
    
    $sql = "SELECT 
                a.id_annonce,
                a.titre,
                a.ville,
                a.pays,
                a.prix_nuit,
                a.capacite_max,
                a.type_logement,
                p.nom_fichier as photo
            FROM annonces a
            LEFT JOIN photos p ON a.id_annonce = p.id_annonce AND p.photo_principale = 1
            WHERE a.disponible = 1
            AND (
                LOWER(a.titre) LIKE ? 
                OR LOWER(a.description) LIKE ?
                OR LOWER(a.ville) LIKE ?
                OR LOWER(a.pays) LIKE ?
            )
            ORDER BY 
                CASE 
                    WHEN LOWER(a.titre) LIKE ? THEN 1
                    WHEN LOWER(a.ville) LIKE ? THEN 2
                    ELSE 3
                END,
                a.date_creation DESC
            LIMIT " . (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $searchTerm, 
        $searchTerm, 
        $searchTerm, 
        $searchTerm,
        $searchTerm, // Pour le ORDER BY
        $searchTerm  // Pour le ORDER BY
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les résultats
    $formatted = array_map(function($row) {
        // Déterminer le chemin de la photo
        $photoPath = 'images/placeholder.jpg'; // Image par défaut
        if (!empty($row['photo'])) {
            // Si nom_fichier contient déjà "uploads/", on l'utilise tel quel
            if (strpos($row['photo'], 'uploads/') === 0) {
                $photoPath = $row['photo'];
            } else {
                $photoPath = 'uploads/annonces/' . $row['photo'];
            }
        }
        
        return [
            'id' => $row['id_annonce'],
            'titre' => $row['titre'],
            'ville' => $row['ville'],
            'pays' => $row['pays'],
            'prix' => number_format($row['prix_nuit'], 0, ',', ' '),
            'capacite' => $row['capacite_max'],
            'type' => ucfirst($row['type_logement']),
            'photo' => $photoPath,
            'url' => 'annonce.php?id=' . $row['id_annonce']
        ];
    }, $results);
    
    echo json_encode([
        'success' => true,
        'results' => $formatted,
        'count' => count($formatted),
        'query' => $query
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de recherche: ' . $e->getMessage()
    ]);
}
?>