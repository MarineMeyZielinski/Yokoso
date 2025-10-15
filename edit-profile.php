<?php
session_start();
require_once 'includes/config.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validations
    if (empty($prenom) || empty($nom)) {
        echo json_encode(['success' => false, 'message' => 'Le prénom et le nom sont requis']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide']);
        exit;
    }

    try {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = ? AND id_user != ? LIMIT 1');
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }

        // Mettre à jour les informations
        $stmt = $pdo->prepare('
            UPDATE users 
            SET prenom = ?, nom = ?, email = ?, telephone = ? 
            WHERE id_user = ?
        ');
        $stmt->execute([$prenom, $nom, $email, $telephone, $user_id]);

        // Mettre à jour la session
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_email'] = $email;

        echo json_encode(['success' => true, 'message' => 'Informations mises à jour avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>