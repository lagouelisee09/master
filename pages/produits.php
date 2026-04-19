<?php
include("../includes/db.php");
include("../includes/header.php");

$produits = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <!-- default theme -->
        <link rel="stylesheet" href="https://ka-f.webawesome.com/webawesome@3.5.0/styles/themes/default.css">
        <!-- optional native styles/CSS reset -->
        <link rel="stylesheet" href="https://ka-f.webawesome.com/webawesome@3.5.0/styles/native.css">
        <link rel="stylesheet" href="../asset/css/style.css">
    </head>
    <body>
        <div class="produits">
        <?php while($p = $produits->fetch()) { ?>
            <div class="produit">
                <img src="../asset/images/<?php echo $p['image']; ?>" width="100%">
                <h3><?php echo $p['nom']; ?></h3>
                <p><?php echo $p['prix']; ?> FCFA</p>
                <button onclick="ajouterPanier(<?php echo $p['id']; ?>)">
                    Ajouter au panier
                </button>
            </div>
        <?php } ?>
        </div>

        <?php include("../includes/footer.php"); ?>
            <!-- Web Awesome autoloader -->
        <script type="module" src="https://ka-f.webawesome.com/webawesome@3.5.0/webawesome.loader.js"></script>
    </body>
</html>