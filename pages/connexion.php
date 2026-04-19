<?php
include("../includes/db.php");

if (isLogged()) {
    if (isAdmin()) {
    
        header("Location: ../index.php");
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($email)) {
        $errors['email'] = "L'email est requis";
    }
    if (empty($password)) {
        $errors['password'] = "Le mot de passe est requis";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nom'] = $user['nom'] ?? ($user['prenom'] ?? 'Utilisateur');
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            
            $redirect = ($user['role'] === 'admin') ? '../admin/dashboard.php' : '../index.php';
            
            if ($is_ajax) {
                echo json_encode(['success' => true, 'redirect' => $redirect, 'role' => $user['role']]);
                exit;
            } else {
                header("Location: $redirect");
                exit;
            }
        } else {
            $errors['general'] = "Email ou mot de passe incorrect";
        }
    }
    
    if ($is_ajax) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    } else {
        $error = $errors['general'] ?? "Email ou mot de passe incorrect";
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EMS Boutique</title>
    <link rel="stylesheet" href="css/connect.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-store"></i> Shop EMS</h1>
                <p>Connectez-vous à votre compte</p>
            </div>
            <div class="card-body">
                <div id="alertContainer"></div>

                <form id="loginForm">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="email" placeholder="exemple@email.com">
                        <div class="error-message" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mot de passe</label>
                        <input type="password" name="password" id="password" placeholder="Votre mot de passe">
                        <div class="error-message" id="passwordError"></div>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember" style="margin-bottom: 0;">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Se connecter</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <div class="register-link">
                    Pas encore inscrit ? <a href="inscription.php"><i class="fas fa-user-plus"></i> Créer un compte</a>
                </div>
                
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i> Espace sécurisé
                </div>
            </div>
        </div>
    </div>

   

    <script>
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.spinner');
    const alertContainer = document.getElementById('alertContainer');

    emailInput.addEventListener('input', validateEmail);
    passwordInput.addEventListener('input', validatePassword);

    function validateEmail() {
        const value = emailInput.value.trim();
        const errorDiv = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value.length === 0) {
            showError(emailInput, errorDiv, 'L\'email est requis');
            return false;
        } else if (!emailRegex.test(value)) {
            showError(emailInput, errorDiv, 'Email invalide');
            return false;
        } else {
            showSuccess(emailInput, errorDiv);
            return true;
        }
    }

    function validatePassword() {
        const value = passwordInput.value;
        const errorDiv = document.getElementById('passwordError');
        
        if (value.length === 0) {
            showError(passwordInput, errorDiv, 'Le mot de passe est requis');
            return false;
        } else {
            showSuccess(passwordInput, errorDiv);
            return true;
        }
    }

    function showError(input, errorDiv, message) {
        input.classList.add('error');
        input.classList.remove('valid');
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
    }

    function showSuccess(input, errorDiv) {
        input.classList.remove('error');
        input.classList.add('valid');
        errorDiv.classList.remove('show');
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i> ${message}`;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function validateForm() {
        return validateEmail() && validatePassword();
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validateForm()) {
            showToast('Veuillez remplir tous les champs', 'error');
            return;
        }
        
        submitBtn.disabled = true;
        btnText.style.opacity = '0';
        btnSpinner.style.display = 'block';
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('connexion.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (document.getElementById('remember').checked) {
                    localStorage.setItem('rememberedEmail', emailInput.value.trim());
                } else {
                    localStorage.removeItem('rememberedEmail');
                }
                
                showToast(`Bienvenue ! Redirection...`, 'success');
                
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                if (data.errors) {
                    if (data.errors.general) {
                        showAlert('error', data.errors.general);
                        showToast(data.errors.general, 'error');
                    }
                    for (const [field, message] of Object.entries(data.errors)) {
                        if (field !== 'general') {
                            const input = document.getElementById(field);
                            const errorDiv = document.getElementById(`${field}Error`);
                            if (input && errorDiv) {
                                showError(input, errorDiv, message);
                            }
                        }
                    }
                } else {
                    showAlert('error', 'Email ou mot de passe incorrect');
                    showToast('Email ou mot de passe incorrect', 'error');
                }
                
                submitBtn.disabled = false;
                btnText.style.opacity = '1';
                btnSpinner.style.display = 'none';
            }
        } catch (error) {
            console.error('Erreur:', error);
            showAlert('error', 'Erreur de connexion au serveur');
            showToast('Erreur de connexion au serveur', 'error');
            
            submitBtn.disabled = false;
            btnText.style.opacity = '1';
            btnSpinner.style.display = 'none';
        }
    });

    const rememberedEmail = localStorage.getItem('rememberedEmail');
    if (rememberedEmail) {
        emailInput.value = rememberedEmail;
        document.getElementById('remember').checked = true;
        validateEmail();
    }
</script>
     
</body>
</html>