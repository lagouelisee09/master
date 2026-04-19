<?php
include("../includes/db.php");
header('Content-Type: application/json');

$commande_id = $_GET['id'] ?? 0;

if($commande_id) {
    // Récupérer la commande
    $stmt = $conn->prepare("SELECT * FROM commandes WHERE commande_id = ?");
    $stmt->execute([$commande_id]);
    $commande = $stmt->fetch();
    
    if($commande) {
        // Récupérer le client
        $stmt = $conn->prepare("SELECT nom, email, telephone, adresse FROM users WHERE user_id = ?");
        $stmt->execute([$commande['user_id']]);
        $client = $stmt->fetch();
        
        // Récupérer les produits de la commande
        $stmt = $conn->prepare("
            SELECT cd.*, p.nom, p.image
            FROM commande_details cd
            LEFT JOIN products p ON cd.product_id = p.product_id 
            WHERE cd.commande_id = ?
        ");
        $stmt->execute([$commande_id]);
        $produits = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'commande' => $commande,
            'client' => $client,
            'produits' => $produits
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Commande non trouvée']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID commande manquant']);
}
?>