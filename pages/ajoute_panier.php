<?php
session_start();

$id = $_GET['id'];

if(!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$_SESSION['panier'][] = $id;
header("Location: ../pages/produits.php");
exit();