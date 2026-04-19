<?php
session_start();
include("../includes/db.php");

$panier = $_SESSION['panier'] ?? [];

foreach($panier as $id) {

    $req = $conn->prepare("SELECT * FROM products WHERE id=?");
    $req->execute([$id]);
    $p = $req->fetch();

    echo "<p>".$p['nom']." - ".$p['prix']." FCFA</p>";
}
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
        <link rel="stylesheet" href="asset/css/style.css">
    </head>
    <body>

    <?php
session_start();

$id = $_POST['id'];

$_SESSION['panier'][] = $id;

echo count($_SESSION['panier']);
?>

        <?php include("../includes/footer.php"); ?>
            <!-- Web Awesome autoloader -->
        <script type="module" src="https://ka-f.webawesome.com/webawesome@3.5.0/webawesome.loader.js"></script>
    </body>
</html>