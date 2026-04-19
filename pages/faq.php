<?php
include("../includes/db.php") ;
$page_title = " FAQ " ;
include("../includes/header.php") ;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fonctionnement</title>
    <link rel="stylesheet" href="css/faq.css">
</head>
<body>
    <div class= "page-container">
    <div class= "page-header">
        <h1><i class= "fas fa-question-circle"></i> Foire aux questions</h1>
        <p>Toutes les réponses à vos questions</p>
    </div>

    <div class= "faq-grid">
        <div class= "faq-category">
            <h2><i class= "fas fa-shopping-cart"></i> Commandes</h2>
            <div class= "faq-item">
                <div class= "faq-question">Comment passer une commande ?</div>
                <div class= "faq-answer">Ajoutez les produits au panier, puis cliquez sur « Commander » et suivez les étapes.</div>
            </div>
            <div class= "faq-item">
                <div class= "faq-question">Puis-je modifier ma commande ?</div>
                <div class= "faq-answer">Vous pouvez modifier votre commande tant qu’elle n’est pas confirmée.</div>
            </div>
            <div class= "faq-item">
                <div class= "faq-question">Comment suivre ma commande ?</div>
                <div class= "faq-answer">Connectez-vous à votre compte et allez dans « Mes commandes ».</div>
            </div>
        </div>

        <div class= "faq-category">
            <h2><i class= "fas fa-credit-card"></i> Paiement</h2>
            <div class= "faq-item">
                <div class= "faq-question">Quels modes de paiement acceptez-vous ?</div>
                <div class= "faq-answer">Nous acceptons Orange Money, Wave, MTN Mobile Money et carte bancaire.</div>
            </div>
            <div class= "faq-item">
                <div class= "faq-question">Est-ce que le paiement est sécurisé ?</div>
                <div class= "faq-answer">Oui, toutes les transactions sont sécurisées.</div>
            </div>
        </div>

        <div class= "faq-category">
            <h2><i class= "fas fa-truck"></i> Livraison</h2>
            <div class= "faq-item">
                <div class= "faq-question">Quels sont les délais de livraison ?</div>
                <div class= "faq-answer">Livraison sous 24-48h à Abidjan, 3-5 jours hors Abidjan.</div>
            </div>
            <div class= "faq-item">
                <div class= "faq-question">La livraison est-elle gratuite ?</div>
                <div class= "faq-answer">Oui, pour toute commande de 50 000 FCFA et plus.</div>
            </div>
        </div>

        <div class= "faq-category">
            <h2><i class= "fas fa-undo-alt"></i> Retours</h2>
            <div class= "faq-item">
                <div class= "faq-question">Puis-je retourner un produit ?</div>
                <div class= "faq-answer">Oui, vous disposez de 14 jours pour retourner un produit.</div>
            </div>
            <div class= "faq-item">
                <div class= "faq-question">Comment faire un retour ?</div>
                <div class= "faq-answer">Contactez notre service client pour obtenir un bon de retour.</div>
            </div>
        </div>
    </div>
</div>

<script>
Document.querySelectorAll('.faq-question').forEach(question => {
    Question.addEventListener('click', () => {
        const item = question.parentElement ;
        Item.classList.toggle('active') ;
    }) ;
}) ;
</script>

< ?php include(« ../includes/footer.php ») ; ?>
</body>
</html>