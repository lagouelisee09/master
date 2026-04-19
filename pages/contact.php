<?php
include("../includes/db.php") ;
$page_title = " Contact " ;
Include("../includes/header.php") ;

$message_envoye = false ;
$error = '' ;

If ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom'] ?? '') ;
    $email = trim($_POST['email'] ?? '') ;
    $sujet = trim($_POST['sujet'] ?? '') ;
    $message = trim($_POST['message'] ?? '') ;
    
    If (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $error = " Veuillez remplir tous les champs " ;
    } elseif ( !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = " Email invalide " ;
    } else {
        // Envoyer email (à configurer)
        $message_envoye = true ;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class= "page-container ">
    <div class= "page-header ">
        <h1><i class= "fas fa-envelope "></i> Contactez-nous</h1>
        <p>Nous sommes à votre écoute 24h/24 et 7j/7</p>
    </div>

    <div class= "contact-grid ">
        <div class= "contact-info ">
            <div class= "info-card ">
                <i class= "fas fa-map-marker-alt "></i>
                <h3>Notre adresse</h3>
                <p>Abidjan, Côte d’Ivoire</p>
                <p>Cocody Angré</p>
                <p>Abobo</p>
            </div>
            <div class= "info-card">
                <i class= "fas fa-phone"></i>
                <h3>Téléphone</h3>
                <p>+225 07 05 44 89 39</p>
                <p>Lun-Ven : 8h-18h</p>
            </div>
            <div class= "info-card">
                <i class= "fas fa-envelope "></i>
                <h3>Email</h3>
                <p>contact@emsboutique.com</p>
                <p>support@emsboutique.com</p>
            </div>
            <div class= "info-card">
                <i class= "fas fa-clock"></i>
                <h3>Horaires</h3>
                <p>Lundi – Vendredi : 8h – 18h</p>
                <p>Samedi : 9h – 13h</p>
            </div>
        </div>

        <div class= "contact-form ">
            <h2><i class= "fas fa-paper-plane"></i> Envoyez-nous un message</h2>
            
            <?php if($message_envoye) : ?>
                <div class= "alert-success ">
                    <i class= »fas fa-check-circle »></i> Message envoyé ! Nous vous répondrons rapidement.
                </div>
            <?php endif ; ?>
            
            <?php if($error) : ?>
                <div class= "alert-error ">
                    <i class= »fas fa-exclamation-circle »></i> < ?php echo $error ; ?>
                </div>
            <?php endif ; ?>
            
            <form method= "POST">
                <div class= "form-group">
                    <input type= "text" name= "nom" placeholder= "Votre nom" required>
                </div>
                <div class= "form-group">
                    <input type= "email" name= "email" placeholder= "Votre email" required>
                </div>
                <div class= "form-group">
                    <input type= "text" name= "sujet" placeholder= "Sujet" required>
                </div>
                <div class= "form-group">
                    <textarea name= "message" rows= "5" placeholder= "Votre message" required></textarea>
                </div>
                <button type= "submit" class= "btn-submit">
                    <i class= "fas fa-paper-plane"></i> Envoyer le message
                </button>
            </form>
            </div>
        </div>
    </div>
    <?php include("../includes/foote.php") ; ?>
</body>
</html>