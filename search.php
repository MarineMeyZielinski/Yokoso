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
    // Recherche dans titre, description, ville, pays
    $searchTerm = "%$query%";
    
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
                a.titre LIKE ? 
                OR a.description LIKE ?
                OR a.ville LIKE ?
                OR a.pays LIKE ?
            )
            ORDER BY 
                CASE 
                    WHEN a.titre LIKE ? THEN 1
                    WHEN a.ville LIKE ? THEN 2
                    ELSE 3
                END,
                a.date_creation DESC
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $searchTerm, 
        $searchTerm, 
        $searchTerm, 
        $searchTerm,
        $searchTerm, // Pour le ORDER BY
        $searchTerm, // Pour le ORDER BY
        $limit
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les résultats
    $formatted = array_map(function($row) {
        return [
            'id' => $row['id_annonce'],
            'titre' => $row['titre'],
            'ville' => $row['ville'],
            'pays' => $row['pays'],
            'prix' => number_format($row['prix_nuit'], 0, ',', ' '),
            'capacite' => $row['capacite_max'],
            'type' => ucfirst($row['type_logement']),
            'photo' => $row['photo'] ? 'uploads/annonces/' . $row['nom_fichier'] : 'images/placeholder.jpg',
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
        'message' => 'Erreur de recherche'
    ]);
}
?>