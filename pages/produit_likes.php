<?php
include("../includes/db.php");

$id = $_GET['id'];

$conn->prepare("UPDATE produits SET likes = likes + 1 WHERE id=?")
     ->execute([$id]);
header("Location: ../pages/produits.php");
exit();
