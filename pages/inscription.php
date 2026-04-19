<?php
include("../includes/db.php");

if (isLogged()) {
    if (isAdmin()) {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Détecter si c'est une requête AJAX
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($nom)) {
        $errors['nom'] = "Le nom est requis";
    }
    if (empty($email)) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email invalide";
    }
    if (empty($password)) {
        $errors['password'] = "Le mot de passe est requis";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Minimum 6 caractères";
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Les mots de passe ne correspondent pas";
    }
    
    if (empty($errors)) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $errors['email'] = "Cet email est déjà utilisé";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nom, email, telephone, adresse, password, role) VALUES (?, ?, ?, ?, ?, 'user')");
            
            if ($stmt->execute([$nom, $email, $telephone, $adresse, $hashed_password])) {
                if ($is_ajax) {
                    echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Redirection...']);
                    exit;
                } else {
                    $success = "Inscription réussie !";
                    header("refresh:2;url=connexion.php");
                }
            } else {
                $errors['general'] = "Erreur lors de l'inscription";
            }
        }
    }
    
    if ($is_ajax) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ShopManager</title>
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-store"></i> Shop EMS Manager</h1>
                <p>Créez votre compte client</p>
            </div>
            <div class="card-body">
                <div id="alertContainer"></div>

                <form id="registerForm">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nom complet *</label>
                        <input type="text" name="nom" id="nom" placeholder="Votre nom et prénom">
                        <div class="error-message" id="nomError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email *</label>
                        <input type="email" name="email" id="email" placeholder="exemple@email.com">
                        <div class="error-message" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Téléphone</label>
                        <input type="tel" name="telephone" id="telephone" placeholder="+225 XX XX XX XX">
                        <div class="error-message" id="telephoneError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Adresse</label>
                        <textarea name="adresse" id="adresse" rows="2" placeholder="Votre adresse complète"></textarea>
                        <div class="error-message" id="adresseError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mot de passe *</label>
                        <input type="password" name="password" id="password" placeholder="Minimum 6 caractères">
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-strength-text" id="passwordStrengthText"></div>
                        <div class="error-message" id="passwordError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirmer le mot de passe *</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Retapez votre mot de passe">
                        <div class="error-message" id="confirmPasswordError"></div>
                    </div>

                    <button type="submit" class="btn-register" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-user-plus"></i> S'inscrire</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <div class="login-link">
                    Déjà inscrit ? <a href="connexion.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
                </div>
            </div>
        </div>
    </div>

                 
    <script>
    // DOM Elements
    const form = document.getElementById('registerForm');
    const nomInput = document.getElementById('nom');
    const emailInput = document.getElementById('email');
    const telephoneInput = document.getElementById('telephone');
    const adresseInput = document.getElementById('adresse');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.spinner');
    const alertContainer = document.getElementById('alertContainer');

    // Validation en temps réel
    nomInput.addEventListener('input', validateNom);
    emailInput.addEventListener('input', validateEmail);
    telephoneInput.addEventListener('input', validateTelephone);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);

    function validateNom() {
        const value = nomInput.value.trim();
        const errorDiv = document.getElementById('nomError');
        if (value.length === 0) {
            showError(nomInput, errorDiv, 'Le nom est requis');
            return false;
        } else if (value.length < 2) {
            showError(nomInput, errorDiv, 'Minimum 2 caractères');
            return false;
        } else {
            showSuccess(nomInput, errorDiv);
            return true;
        }
    }

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

    function validateTelephone() {
        const value = telephoneInput.value.trim();
        const errorDiv = document.getElementById('telephoneError');
        
        if (value.length > 0 && value.length < 8) {
            showError(telephoneInput, errorDiv, 'Numéro invalide');
            return false;
        } else {
            showSuccess(telephoneInput, errorDiv);
            return true;
        }
    }

    function validatePassword() {
        const value = passwordInput.value;
        const errorDiv = document.getElementById('passwordError');
        const strengthBar = document.getElementById('passwordStrengthBar');
        
        if (value.length === 0) {
            showError(passwordInput, errorDiv, 'Le mot de passe est requis');
            strengthBar.style.width = '0%';
            strengthBar.style.background = '#e9ecef';
            return false;
        } else if (value.length < 6) {
            showError(passwordInput, errorDiv, 'Minimum 6 caractères');
            strengthBar.style.width = '33%';
            strengthBar.style.background = '#dc3545';
            return false;
        } else if (value.length >= 6 && value.length < 10) {
            showSuccess(passwordInput, errorDiv);
            strengthBar.style.width = '66%';
            strengthBar.style.background = '#ffc107';
            return true;
        } else {
            showSuccess(passwordInput, errorDiv);
            strengthBar.style.width = '100%';
            strengthBar.style.background = '#28a745';
            return true;
        }
    }

    function validateConfirmPassword() {
        const password = passwordInput.value;
        const value = confirmPasswordInput.value;
        const errorDiv = document.getElementById('confirmPasswordError');
        
        if (value.length === 0) {
            showError(confirmPasswordInput, errorDiv, 'Confirmez votre mot de passe');
            return false;
        } else if (value !== password) {
            showError(confirmPasswordInput, errorDiv, 'Les mots de passe ne correspondent pas');
            return false;
        } else {
            showSuccess(confirmPasswordInput, errorDiv);
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
        return validateNom() && validateEmail() && validateTelephone() && validatePassword() && validateConfirmPassword();
    }

    // Soumission du formulaire
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validateForm()) {
            showToast('Veuillez corriger les erreurs', 'error');
            return;
        }
        
        submitBtn.disabled = true;
        btnText.style.opacity = '0';
        btnSpinner.style.display = 'block';
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('inscription.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = 'connexion.php';
                }, 2000);
            } else {
                if (data.errors) {
                    for (const [field, message] of Object.entries(data.errors)) {
                        if (field === 'general') {
                            showAlert('error', message);
                            showToast(message, 'error');
                        } else {
                            const input = document.getElementById(field);
                            const errorDiv = document.getElementById(`${field}Error`);
                            if (input && errorDiv) {
                                showError(input, errorDiv, message);
                            }
                        }
                    }
                }
                submitBtn.disabled = false;
                btnText.style.opacity = '1';
                btnSpinner.style.display = 'none';
            }
        } catch (error) {
            console.error('Erreur:', error);
            showAlert('error', 'Erreur de connexion au serveur');
            showToast('Erreur de connexion', 'error');
            submitBtn.disabled = false;
            btnText.style.opacity = '1';
            btnSpinner.style.display = 'none';
        }
    });
</script>
</body>
</html>