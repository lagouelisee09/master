<?php
include("../includes/db.php");
$page_title = "À propos";
include("../includes/header.php");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A propost</title>
    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h1><i class="fas fa-info-circle"></i> À propos de EMS Boutique</h1>
            <p>Découvrez qui nous sommes et notre engagement</p>
        </div>

        <div class="about-content">
            <div class="about-section">
                <div class="about-text">
                    <h2>Notre Histoire</h2>
                    <p>Fondée en 2024, EMS Boutique est née d'une passion pour la mode et le shopping de qualité. Notre mission est de proposer des produits tendance, de qualité et accessibles à tous.</p>
                    <p>Aujourd'hui, nous sommes fiers de servir des milliers de clients satisfaits à travers tout le pays.</p>
                </div>
                <div class="about-icon">
                    <i class="fas fa-store"></i>
                </div>
            </div>

            <div class="about-section">
                <div class="about-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="about-text">
                    <h2>Notre Mission</h2>
                    <p>Offrir une expérience de shopping en ligne simple, sécurisée et agréable, avec des produits de qualité à des prix compétitifs.</p>
                </div>
            </div>
            <div class="about-section">
                <div class="about-text">
                    <h2>Nos Valeurs</h2>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Qualité irréprochable</li>
                        <li><i class="fas fa-check-circle"></i> Service client réactif</li>
                        <li><i class="fas fa-check-circle"></i> Livraison rapide</li>
                        <li><i class="fas fa-check-circle"></i> Paiement sécurisé</li>
                    </ul>
                </div>
                <div class="about-icon">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <h3>5000+</h3>
                <p>Clients satisfaits</p>
            </div>
    <div class="stat-box">
    <i class="fas fa-box"></i>
    <h3>200+</h3>
    <p>Produits disponibles</p>
    </div>
    <div class="stat-box">
    <i class="fas fa-truck"></i>
    <h3>48h</h3>
    <p>Livraison express</p>
    </div>
    <div class="stat-box">
    <i class="fas fa-headset"></i>
    <h3>24/7</h3>
    <p>Support client</p>
    </div>
    </div>
    </div>
        <?php include("../includes/foote.php"); ?>
</body>
</html>