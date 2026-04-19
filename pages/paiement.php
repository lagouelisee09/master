<?php

include("../includes/db.php");
include("../includes/header.php");

$panier = $_SESSION['panier'] ?? [];
$montant_total = 0;

// Calculer le montant total
foreach($panier as $id) {
    $req = $conn->prepare("SELECT * FROM products WHERE id=?");
    $req->execute([$id]);
    $p = $req->fetch();
    $montant_total += $p['prix'];
}

$error = '';
$success = '';

if(isset($_POST['payer'])) {
    $methode_paiement = $_POST['methode_paiement'] ?? '';
    
    if(!empty($panier) && !empty($methode_paiement)) {
        // Créer la commande
        $user_id = $_SESSION['user'] ?? 1;
        $stmt = $conn->prepare("INSERT INTO commandes (user_id, date_commande, statut_paiement, methode_paiement) VALUES (?, NOW(), 'en_attente', ?)");
        $stmt->execute([$user_id, $methode_paiement]);
        $commande_id = $conn->lastInsertId();

        // Ajouter les détails
        foreach($panier as $id) {
            $req = $conn->prepare("SELECT * FROM produits WHERE id=?");
            $req->execute([$id]);
            $p = $req->fetch();
            $stmt = $conn->prepare("INSERT INTO details_commande (commande_id, produit_id, quantite, prix) VALUES (?, ?, 1, ?)");
            $stmt->execute([$commande_id, $id, $p['prix']]);
        }

        $success = "Commande créée avec succès ✅ Montant : " . $montant_total . " FCFA via " . $methode_paiement;
        unset($_SESSION['panier']);
    } else {
        $error = "Veuillez sélectionner une méthode de paiement et avoir des produits dans le panier.";
    }
}
?>




<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Paiement - Shop</title>
        <!-- default theme -->
        <link rel="stylesheet" href="https://ka-f.webawesome.com/webawesome@3.5.0/styles/themes/default.css">
        <!-- optional native styles/CSS reset -->
        <link rel="stylesheet" href="https://ka-f.webawesome.com/webawesome@3.5.0/styles/native.css">
        <link rel="stylesheet" href="../asset/css/style.css">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .container { max-width: 500px; margin: 0 auto; }
            .resume { background: #f0f0f0; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .methodes { display: flex; flex-direction: column; gap: 10px; }
            .methode { 
                padding: 15px; 
                border: 2px solid #ccc; 
                border-radius: 8px; 
                cursor: pointer;
                transition: 0.3s;
            }
            .methode:hover { border-color: #007bff; background: #f9f9f9; }
            .methode input[type="radio"] { margin-right: 10px; }
            .methode label { cursor: pointer; display: flex; align-items: center; }
            .logo { width: 40px; margin-right: 10px; }
            button { 
                padding: 12px; 
                width: 100%; 
                background: #007bff; 
                color: white; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer;
                font-size: 16px;
            }
            button:hover { background: #0056b3; }
            .error { color: red; margin-bottom: 10px; }
            .success { color: green; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>💳 Paiement</h1>

            <?php if($error) { echo "<p class='error'>$error</p>"; } ?>
            <?php if($success) { echo "<p class='success'>$success</p>"; } ?>

            <div class="resume">
                <h3>Résumé du panier</h3>
                <?php 
                $panier = $_SESSION['panier'] ?? [];
                foreach($panier as $id) {
                    $req = $conn->prepare("SELECT * FROM produits WHERE id=?");
                    $req->execute([$id]);
                    $p = $req->fetch();
                    echo "<p>" . $p['nom'] . " - " . $p['prix'] . " FCFA</p>";
                }
                ?>
                <hr>
                <h2>Total : <?php echo $montant_total; ?> FCFA</h2>
            </div>

            <h3>Choisir une méthode de paiement :</h3>
            <form method="POST">
                <div class="methodes">
                    <div class="methode">
                        <label>
                            <input type="radio" name="methode_paiement" value="Orange Money" required>
                            <span style="font-weight:bold;">🟠 Orange Money</span>
                        </label>
                    </div>
                    
                    <div class="methode">
                        <label>
                            <input type="radio" name="methode_paiement" value="Wave" required>
                            <span style="font-weight:bold;">💜 Wave</span>
                        </label>
                    </div>
                    
                    <div class="methode">
                        <label>
                            <input type="radio" name="methode_paiement" value="MTN Mobile Money" required>
                            <span style="font-weight:bold;">📱 MTN Mobile Money</span>
                        </label>
                    </div>
                </div>

                <br>
                <button type="submit" name="payer" class="button">Procéder au paiement</button>
            </form>

            <p style="text-align:center; margin-top:20px; font-size:12px;">
                Paiement sécurisé | Confidentialité garantie
            </p>
        </div>

        <footer></footer>
        <!-- Web Awesome autoloader -->
        <script type="module" src="https://ka-f.webawesome.com/webawesome@3.5.0/webawesome.loader.js"></script>
    </body>
</html>

