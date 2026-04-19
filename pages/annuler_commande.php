<?php
include "../includes/db.php" ;
Session_start() ;

If ( !isLogged()) {
    header("Location : connexion.php") ;
    Exit ;
}

If ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commande_id = $_POST['commande_id'] ;
    $user_id = $_SESSION['user_id'] ;
    
    // Vérifier que la commande appartient bien à l’utilisateur
    $stmt = $conn->prepare("SELECT * FROM commandes WHERE commande_id = ? AND user_id = ? AND statut = 'en_attente' ") ;
    $stmt->execute([$commande_id, $user_id]) ;
    
    If ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("UPDATE commandes SET statut = 'annulee', statut_paiement = 'annulee' WHERE commande_id = ? ") ;
        $stmt->execute([$commande_id]) ;
        $_SESSION['success'] = "Commande annulée avec succès " ;
    } else {
        $_SESSION['error'] = "Impossible d’annuler cette commande" ;
    }
}

header("Location : mes-commandes.php ") ;
Exit ;