<?php
session_start();
require_once 'includes/config.php';

header('Content-Type: application/json');

// Mapping temporaire des photos
$photos_map = [
    1 => 'images/kioshi.jpg',
    2 => 'images/appartement-lucas.jpg', 
    3 => 'images/appartement-saitama.jpg',
    4 => 'images/france-1.webp',
    5 => 'images/france-2.jpg',
    6 => 'images/france-3.jpg',
    7 => 'images/apparttradionnel.jpg',
    8 => 'images/kyoto-appart.jpg',
    9 => 'images/kioshi.jpg',
    10 => 'images/appartement-lucas.jpg',
    11 => 'images/appartement-saitama.jpg',
    12 => 'images/kyoto-appart.jpg'
];

$query = trim($_GET['q'] ?? '');
$limit = 5;

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Requête trop courte']);
    exit;
}

try {
    $searchTerm = "%" . strtolower($query) . "%";
    
    $sql = "SELECT 
                a.id_annonce,
                a.titre,
                a.ville,
                a.pays,
                a.prix_nuit,
                a.capacite_max,
                a.type_logement
            FROM annonces a
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
        $searchTerm,
        $searchTerm
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted = array_map(function($row) use ($photos_map) {
        return [
            'id' => $row['id_annonce'],
            'titre' => $row['titre'],
            'ville' => $row['ville'],
            'pays' => $row['pays'],
            'prix' => number_format($row['prix_nuit'], 0, ',', ' '),
            'capacite' => $row['capacite_max'],
            'type' => ucfirst($row['type_logement']),
            'photo' => $photos_map[$row['id_annonce']] ?? 'images/placeholder.jpg',
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