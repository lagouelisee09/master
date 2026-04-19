<?php
include("../includes/db.php");

$id = $_GET['id'];

$req = $conn->prepare("SELECT * FROM produits WHERE id=?");
$req->execute([$id]);
$produit = $req->fetch();

if(isset($_POST['update'])){

    $nom = $_POST['nom'];
    $prix = $_POST['prix'];

    $stmt = $conn->prepare("UPDATE produits SET nom=?, prix=? WHERE id=?");
    $stmt->execute([$nom, $prix, $id]);

    echo "✅ Produit modifié";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    


<form method="POST">
    <input name="nom" value="<?php echo $produit['nom']; ?>">
    <input name="prix" value="<?php echo $produit['prix']; ?>">
    <button name="update">Modifier</button>
</form>

</body>
</html>