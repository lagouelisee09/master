<?php
Include("../includes/db.php") ;
$page_title = "Mon profil" ;
Include("../includes/header.php") ;

// Vérifier si l’utilisateur est connecté
If ( !isLogged()) {
    header("Location : connexion.php") ;
    Exit ;
}

$user_id = $_SESSION['user_id'] ;
$success = '' ;
$error = '' ;

// Récupérer les infos utilisateur
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? ") ;
$stmt->execute([$user_id]) ;
$user = $stmt->fetch() ;

// Mettre à jour le profil
If ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '') ;
    $telephone = trim($_POST['telephone'] ?? '') ;
    $adresse = trim($_POST['adresse'] ?? '') ;
    $email = trim($_POST['email'] ?? '') ;
    
    If (empty($nom) || empty($email)) {
        $error = "Le nom et l’email sont requis" ;
    } elseif ( !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide" ;
    } else {
        $stmt = $conn->prepare("UPDATE users SET nom = ?, telephone = ?, adresse = ?, email = ? WHERE user_id = ? ") ;
        If ($stmt->execute([$nom, $telephone, $adresse, $email, $user_id])) {
            $_SESSION['nom'] = $nom ;
            $_SESSION['email'] = $email ;
            $success = "Profil mis à jour avec succès !" ;
            // Recharger les données
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? ") ;
            $stmt->execute([$user_id]) ;
            $user = $stmt->fetch() ;
        } else {
            $error = "Erreur lors de la mise à jour " ;
        }
    }
}

// Changer mot de passe
If (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'] ?? '' ;
    $new_password = $_POST['new_password'] ?? '' ;
    $confirm_password = $_POST['confirm_password'] ?? '' ;
    
    If (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs " ;
    } elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas " ;
    } elseif (strlen($new_password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères " ;
    } else {
        // Vérifier l’ancien mot de passe
        If (password_verify($old_password, $user['password'])) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT) ;
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ? ") ;
            If ($stmt->execute([$new_hashed, $user_id])) {
                $success = "Mot de passe modifié avec succès ! " ;
            } else {
                $error = " Erreur lors du changement de mot de passe " ;
            }
        } else {
            $error = "Ancien mot de passe incorrect " ;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <div class= "page-container">
    <div class= "page-header">
        <h1><i class= "fas fa-user-circle "></i> Mon profil</h1>
        <p>Gérez vos informations personnelles</p>
    </div>

    <div class= "profile-grid">
        <div class= "profile-sidebar">
            <div class= "profile-avatar">
                <i class= "fas fa-user-circle"></i>
                <h3><?php echo htmlspecialchars($user['nom']) ; ?></h3>
                <p><?php echo htmlspecialchars($user['email']) ; ?></p>
            </div>
            <div class= "profile-menu">
                <a href= "mes-commandes.php" class= "menu-item">
                    <i class= "fas fa-shopping-bag"></i> Mes commandes
                </a>
                <a href= "mes-favoris.php" class= "menu-item">
                    <i class= "fas fa-heart "></i> Mes favoris
                </a>
                <a href= "../logout.php" class= "menu-item logout">
                    <i class= "fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        <div class= "profile-content">
            <?php if($success) : ?>
                <div class= "alert-success"><?php echo $success ; ?></div>
            <?php endif ; ?>
            <?php if($error) : ?>
                <div class= "alert-error"><?php echo $error ; ?></div>
            <?php endif ; ?>

            <div class= "profile-section">
                <h2><i class= "fas fa-user-edit"></i> Informations personnelles</h2>
                <form method= "POST">
                    <div class= "form-group">
                        <label>Nom complet</label>
                        <input type= "text" name= "nom" value= "<?php echo htmlspecialchars($user['nom']) ; ?> " required>
                    </div>
                    <div class= "form-group">
                        <label>Email</label>
                        <input type= "email" name= "email " value= "<?php echo htmlspecialchars($user['email']) ; ?> " required>
                    </div>
                    <div class= "form-group">
                        <label>Téléphone</label>
                        <input type= "tel" name= "telephone" value= "<?php echo htmlspecialchars($user['telephone'] ?? '') ; ?> ">
                    </div>
                    <div class= "form-group">
                        <label>Adresse</label>
                        <textarea name= "adresse" rows= "3"><?php echo htmlspecialchars($user['adresse'] ?? '') ; ?></textarea>
                    </div>
                    <button type= "submit" class= "btn-update">
                        <i class= "fas fa-save"></i> Mettre à jour
                    </button>
                </form>
            </div>

            <div class= "profile-section">
                <h2><i class= "fas fa-lock"></i> Changer de mot de passe</h2>
                <form method= "POST">
                    <div class= "form-group">
                        <label>Ancien mot de passe</label>
                        <input type= "password" name= "old_password" required>
                    </div>
                    <div class= "form-group">
                        <label>Nouveau mot de passe</label>
                        <input type= "password" name= "new_password" required>
                    </div>
                    <div class= "form-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type= "password" name= "confirm_password" required>
                    </div>
                    <button type= "submit" name= "change_password" class= "btn-password">
                        <i class= "fas fa-key"></i> Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

< ?php include(« ../includes/footer.php ») ; ?>
</body>
</html>