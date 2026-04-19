<?php
Include("../includes/db.php") ;
$page_title = "Mes commandes" ;
Include("../includes/header.php") ;

// Vérifier si l’utilisateur est connecté
If ( !isLogged()) {
    header("Location : connexion.php") ;
    Exit ;
}

$user_id = $_SESSION['user_id'] ;

// Récupérer les commandes du client
$commandes = $conn->prepare(" 
    SELECT c.*, 
        COUNT(cd.product_id) as nb_articles
    FROM commandes c
    LEFT JOIN commande_details cd ON c.commande_id = cd.commande_id
    WHERE c.user_id = ?
    GROUP BY c.commande_id
    ORDER BY c.date_commande DESC
" ) ;
$commandes->execute([$user_id]) ;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes</title>
    <link rel="stylesheet" href="css/mes-commande.css">
    <link rel="stylesheet" href="asset/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class= "page-container ">
    <div class= "page-header ">
        <h1 style="color: black;"><i class= "fas fa-shopping-bag"></i> Mes commandes</h1>
        <p style="color: darkgray;">Retrouvez l’historique de toutes vos commandes</p>
    </div>

    <?php if($commandes->rowCount() == 0) : ?>
        <div class= "empty-state">
            <i class= "fas fa-shopping-cart "></i>
            <h2>Vous n’avez pas encore de commandes</h2>
            <p>Découvrez nos produits et passez votre première commande !</p>
            <a href= "../index.php" class= "btn-shop">Découvrir les produits</a>
        </div>
    <?php else : ?>
        <div class= "commands-list">
        <?php while($cmd = $commandes->fetch()) : 
            $status_class = '' ;
            $status_text = '' ;
            Switch($cmd['statut']) {
                Case 'en_attente' : $status_class = 'status-pending' ; $status_text = 'En attente' ; break ;
                Case 'confirmee' : $status_class = 'status-confirmed' ; $status_text = 'Confirmée' ; break ;
                Case 'expediee' : $status_class = 'status-shipped' ; $status_text = 'Expédiée' ; break ;
                Case 'livree' : $status_class = 'status-delivered' ; $status_text = 'Livrée' ; break ;
                Case 'annulee' : $status_class = 'status-cancelled' ; $status_text = 'Annulée' ; break ;
                Default : $status_class = 'status-pending' ; $status_text = $cmd['statut'] ;
            }
            
            $paiement_class = $cmd['statut_paiement'] == 'paye' ? 'paid' : 'unpaid' ;
            $paiement_text = $cmd['statut_paiement'] == 'paye' ? 'Payé' : 'Non payé' ;
        ?>
        <div class= "command-card">
            <div class= "command-header">
                <div class= "command-number">
                    <i class= "fas fa-receipt"></i>
                    <span>Commande #<?php echo $cmd['commande_id'] ; ?></span>
                </div>
                <div class= "command-date">
                    <i class= "fas fa-calendar"></i>
                    <?php echo date('d/m/Y H :i', strtotime($cmd['date_commande'])) ; ?>
                </div>
            </div>
            
            <div class= "command-body">
                <div class= "command-info">
                    <div class= "info-item">
                        <span class= "label">Total :</span>
                        <span class= "value"><?php echo number_format($cmd['total'], 0, ',', ' ') ; ?> FCFA</span>
                    </div>
                    <div class= "info-item ">
                        <span class= "label">Articles :</span>
                        <span class= "value><?php echo $cmd['nb_articles'] ; ?>"</span>
                    </div>
                    <div class= "info-item">
                        <span class= "label">Statut :</span>
                        <span class= "value status-badge <?php echo $status_class ; ?>"><?php echo $status_text ; ?></span>
                    </div>
                    <div class= "info-item">
                        <span class= "label">Paiement :</span>
                        <span class= "value payment-badge <?php echo $paiement_class ; ?> "><?php echo $paiement_text ; ?></span>
                    </div>
                </div>
                
                <div class= "command-actions">
                    <button onclick= "voirDetails(<?php echo $cmd['commande_id'] ; ?>) " class= "btn-details">
                        <i class= "fas fa-eye" style="align-self: center;"></i> Voir détails
                    </button>
                    <?php if($cmd['statut'] == 'en_attente') : ?>
                    <form method= "POST" action= "annuler_commande.php" style= "display :inline ; " onsubmit= "return confirm('Annuler cette commande ?') ">
                        <input type= "hidden" name= "commande_id" value= "<?php echo $cmd['commande_id'] ; ?> ">
                        <button type= "submit" class= "btn-cancel">
                            <i class= "fas fa-times-circle"></i> Annuler
                        </button>
                    </form>
                    <?php endif ; ?>
                </div>
            </div>
        </div>
        <?php endwhile ; ?>
    </div>
    <?php endif ; ?>

    <!--Modal détails commande -->
<div id= "detailsModal" class= "modal">
    <div class= "modal-content">
        <div class= "modal-header">
            <h3><i class= "fas fa-receipt"></i> Détails de la commande</h3>
            <button class= "close-modal" onclick= "closeModal()">&times;</button>
        </div>
        <div class= "modal-body" id= "modalBody">
            <div style= "text-align :center ; padding :20px ; ">
                <i class= "fas fa-spinner fa-spin"></i> Chargement…
            </div>
        </div>
    </div>
</div>
</div>

<script>
function voirDetails(commandeId) {
    const modal = document.getElementById('detailsModal') ;
    const modalBody = document.getElementById('modalBody') ;
    
    modal.style.display = 'flex' ;
    modalBody.innerHTML = '<div style= "text-align :center ; padding :20px ; "><i class= "fas fa-spinner fa-spin"></i> Chargement…</div>' ;
    
    fetch('../admin/get_recu.php ?id=' + commandeId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const commande = data.commande ;
                const client = data.client ;
                const produits = data.produits ;
                
                let produitsHtml = '' ;
                for(let i = 0 ; i <produits.length ; i++) {
                    const p = produits[i] ;
                    produitsHtml += `
                        <div style= "display : flex ; align-items : center ; gap : 15px ; padding : 10px 0 ; border-bottom : 1px solid #eee ; ">
                            <img src= "../asset/images/${p.image || 'placeholder.jpg'} " style= "width : 50px ; height : 50px ; object-fit : cover ; border-radius : 8px ; ">
                            <div style= "flex :1 ; "><strong>${p.nom}</strong><br><small>Quantité : ${p.quantite}</small></div>
                            <div>${parseInt(p.prix_unitaire).toLocaleString()} FCFA</div>
                        </div>
                    ` ;
                }
                
                modalBody.innerHTML = `
                    <div><strong>N° commande :</strong> #${commande.commande_id}</div>
                    <div><strong>Date :</strong> ${new Date(commande.date_commande).toLocaleString('fr-FR')}</div>
                    <div><strong>Statut :</strong> ${commande.statut === 'livree' ? 'Livrée' : commande.statut === 'annulee' ? 'Annulée' : commande.statut}</div>
                    <div><strong>Paiement :</strong> ${commande.statut_paiement === 'paye' ? 'Payé' : commande.statut_paiement === 'non_paye' ? 'Annulé' : 'En attente'}</div>
                    <hr>
                    <h4>Produits :</h4>
                    ${produitsHtml}
                    <hr>
                    <h3 style= "text-align :right ; ">Total : ${parseInt(commande.total).toLocaleString()} FCFA</h3>
                ` ;
            } else {
                modalBody.innerHTML = '<div style= "text-align :center ; color :red ; ">Erreur lors du chargement</div>' ;
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<div style= "text-align :center ; color :red ; ">Erreur de connexion</div>' ;
        }) ;
}

// ========== FERMETURE ==========
function closeReçu() {
    const modal = document.getElementById('detailsModal') ;
    if (modal) modal.style.display = 'none';
}

// ========== ÉVÉNEMENTS ==========
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeReçu();
});

window.onclick = function(e) {
    const modal = document.getElementById('detailsModal') ;
    if (e.target === modal) closeReçu();
}
</script>

<!--<footer class="footer" style="background: linear-gradient(135deg, #1a1a2e, #16213e) ; color: #fff; padding: 10px 10px; text-align: center; margin-top: 40px; position:static">
    <div class="page-footer">
        <div class="footer-content" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div class="footer-section" style=" background: transparent; padding: 10px; border-radius: 8px; box-shadow: 0 4px 12px rgba(248, 246, 246, 0.1); border: 1px solid rgba(255,255,255,0.3);">
                <h3>Contactez-nous</h3>
                <p><i class="fas fa-phone"></i> +237 123 456 789</p>
                <p><i class="fas fa-envelope"></i> support@ems_service.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Côte d'Ivoire, Cameroun</p>
            </div>
            <div class="footer-section-social" style="background: transparent; padding: 10px; border-radius: 8px; box-shadow: 0 4px 12px rgba(248, 246, 246, 0.1); border: 1px solid rgba(255,255,255,0.3);">
                <h3>Suivez-nous</h3>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <hr style=" width: 100%;">
            <div class="footer-bottom" style="margin-top: 10px;">
                <p>&copy; 2026 EMS_Service - Tous droits réservés</p>
            </div>
    </div>

</footer>-->


</body>
</html>