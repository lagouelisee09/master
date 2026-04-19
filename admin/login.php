<?php
include("../includes/db.php");

// Rediriger si déjà connecté en tant qu'admin
if (isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nom'] = $user['nom'] ?? ($user['prenom'] ?? 'Administrateur');
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - EMS Boutique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .admin-container {
            width: 100%;
            max-width: 450px;
        }

        .admin-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-header {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            padding: 35px;
            text-align: center;
            color: white;
        }

        .admin-header .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ffcc00, #ff8c00);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .admin-header .icon i {
            font-size: 40px;
            color: #1a1a2e;
        }

        .admin-header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .admin-header p {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .admin-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .form-group label i {
            margin-right: 8px;
            color: #667eea;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.4);
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .security-badge {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }

        .security-badge i {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-card">
            <div class="admin-header">
                <div class="icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h1>Espace Administrateur</h1>
                <p>Connectez-vous à votre panel d'administration</p>
            </div>
            <div class="admin-body">
                <?php if($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email administrateur</label>
                        <input type="email" name="email" required autofocus placeholder="admin@ems.com">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mot de passe</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>

                <div class="back-link">
                    <a href="../index.php"><i class="fas fa-arrow-left"></i> Retour à la boutique</a>
                </div>

                <div class="security-badge">
                    <i class="fas fa-shield-alt"></i> Connexion sécurisée
                </div>
            </div>
        </div>
    </div>
</body>
</html>