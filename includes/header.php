<?php
// Vérifier si l'utilisateur est connecté
$is_logged = isset($_SESSION['user_id']);
$user_nom = $_SESSION['nom'] ?? '';
$user_role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Header Principal */
        .modern-header {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Top Bar */
        .top-bar {
            background: rgba(255,255,255,0.05);
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .top-bar .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .top-info {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .top-info span {
            color: rgba(255,255,255,0.7);
        }

        .top-info i {
            margin-right: 8px;
            color: #ffcc00;
        }

        .top-links {
            display: flex;
            gap: 20px;
        }

        .top-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }

        .top-links a:hover {
            color: #ffcc00;
        }

        /* Main Header */
        .main-header {
            padding: 15px 30px;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #ffcc00 0%, #ff8c00 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .logo-icon i {
            font-size: 24px;
            color: #1a1a2e;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #ffcc00 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo-text p {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.6);
        }

        /* Search Bar */
        .search-wrapper {
            flex: 1;
            max-width: 450px;
            position: relative;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 5px 5px 5px 20px;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .search-bar:focus-within {
            background: rgba(255,255,255,0.15);
            border-color: #ffcc00;
            box-shadow: 0 0 15px rgba(255,204,0,0.2);
        }

        .search-bar input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 12px 0;
            color: white;
            font-size: 14px;
            outline: none;
        }

        .search-bar input::placeholder {
            color: rgba(255,255,255,0.5);
        }

        .search-bar button {
            background: linear-gradient(135deg, #ffcc00 0%, #ff8c00 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-bar button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255,204,0,0.3);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 15px;
            margin-top: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .search-results.show {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .action-btn {
            position: relative;
            color: white;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .action-btn i {
            font-size: 1.3rem;
        }

        .action-btn span {
            font-size: 0.7rem;
            opacity: 0.8;
        }

        .action-btn:hover {
            color: #ffcc00;
            transform: translateY(-3px);
        }

        .badge-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #ffcc00;
            color: #1a1a2e;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
            min-width: 18px;
            text-align: center;
        }

        /* User Menu */
        .user-menu {
            position: relative;
            cursor: pointer;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #ffcc00, #ff8c00);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .user-avatar i {
            font-size: 1.2rem;
            color: #1a1a2e;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255,204,0,0.5);
        }

        .dropdown-menu {
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border-radius: 15px;
            width: 250px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            display: none;
            animation: fadeInUp 0.3s ease;
            z-index: 100;
        }

        .user-menu:hover .dropdown-menu {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-header {
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px 15px 0 0;
            color: white;
        }

        .dropdown-header h4 {
            margin-bottom: 5px;
        }

        .dropdown-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item i {
            width: 20px;
            color: #667eea;
        }

        .logout-item {
            color: #dc3545;
        }

        .logout-item i {
            color: #dc3545;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Mobile Sidebar */
        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 280px;
            height: 100%;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 100%);
            z-index: 2000;
            transition: left 0.3s ease;
            padding: 20px;
            overflow-y: auto;
        }

        .mobile-sidebar.open {
            left: 0;
        }

        .mobile-sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .close-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .mobile-sidebar .action-btn {
            padding: 12px 0;
            flex-direction: row;
            gap: 12px;
        }

        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1999;
            display: none;
        }

        .mobile-overlay.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .search-wrapper {
                order: 3;
                max-width: 100%;
                width: 100%;
            }
            .main-header {
                gap: 15px;
            }
        }

        @media (max-width: 768px) {
            .top-info {
                display: none;
            }
            .header-actions {
                gap: 12px;
            }
            .action-btn span {
                display: none;
            }
            .action-btn i {
                font-size: 1.2rem;
            }
            .mobile-menu-btn {
                display: block;
            }
            .user-menu {
                display: none;
            }
            .main-header {
                padding: 12px 20px;
            }
        }

        @media (max-width: 480px) {
            .logo-text h1 {
                font-size: 1.2rem;
            }
            .logo-icon {
                width: 35px;
                height: 35px;
            }
            .logo-icon i {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<!-- Header Principal -->
<header class="modern-header">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-info">
                <span><i class="fas fa-truck"></i> Livraison gratuite dès 50 000 FCFA</span>
                <span><i class="fas fa-undo-alt"></i> Retours gratuits sous 14 jours</span>
                <span><i class="fas fa-headset"></i> Support 24/7</span>
            </div>
            <div class="top-links">
                <a href="about.php"><i class="fas fa-info-circle"></i> À propos</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a>
            </div>
        </div>
    </div>

    <!-- Main Header Content -->
    <div class="main-header">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Logo -->
        <a href="../index.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="logo-text">
                <h1>EMS Boutique</h1>
                <p>Votre shopping premium</p>
            </div>
        </a>

        <!-- Search Bar -->
        <div class="search-wrapper">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher un produit...">
                <button onclick="performSearch()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <a href="favoris.php" class="action-btn">
                <i class="fas fa-heart"></i>
                <span>Favoris</span>
                <span class="badge-count" id="favCount">0</span>
            </a>
            
            <a href="#" class="action-btn" onclick="toggleCart(event)">
                <i class="fas fa-shopping-cart"></i>
                <span>Panier</span>
                <span class="badge-count" id="cartCount">0</span>
            </a>

            <?php if($is_logged): ?>
                <div class="user-menu">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            <h4><?php echo htmlspecialchars($user_nom); ?></h4>
                            <p><?php echo $user_role === 'admin' ? 'Administrateur' : 'Client'; ?></p>
                        </div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Mon profil
                        </a>
                        <a href="mes-commandes.php" class="dropdown-item">
                            <i class="fas fa-shopping-bag"></i> Mes commandes
                        </a>
                        <a href="mes-favoris.php" class="dropdown-item">
                            <i class="fas fa-heart"></i> Mes favoris
                        </a>
                        <?php if($user_role === 'admin'): ?>
                            <div class="dropdown-divider"></div>
                            <a href="admin/dashboard.php" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="action-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Connexion</span>
                </a>
                <a href="inscription.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Inscription</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Mobile Sidebar -->
<div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>
<div class="mobile-sidebar" id="mobileSidebar">
    <div class="mobile-sidebar-header">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="logo-text">
                <h1>EMS Boutique</h1>
            </div>
        </div>
        <button class="close-sidebar" onclick="closeMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <?php if($is_logged): ?>
        <div style="padding: 15px; background: rgba(255,255,255,0.1); border-radius: 15px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 50px; height: 50px; background: #ffcc00; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user" style="font-size: 1.5rem; color: #1a1a2e;"></i>
                </div>
                <div>
                    <h4 style="color: white;"><?php echo htmlspecialchars($user_nom); ?></h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 12px;"><?php echo $user_role === 'admin' ? 'Administrateur' : 'Client'; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <a href="../index.php" class="action-btn">
        <i class="fas fa-home"></i> Accueil
    </a>
    <a href="favoris.php" class="action-btn">
        <i class="fas fa-heart"></i> Favoris
    </a>
    <a href="#" class="action-btn" onclick="toggleCart(event)">
        <i class="fas fa-shopping-cart"></i> Panier
    </a>
    <a href="profile.php" class="action-btn">
        <i class="fas fa-user"></i> Mon profil
    </a>
    <a href="mes-commandes.php" class="action-btn">
        <i class="fas fa-shopping-bag"></i> Mes commandes
    </a>
    <?php if($user_role === 'admin'): ?>
        <a href="admin/dashboard.php" class="action-btn">
            <i class="fas fa-tachometer-alt"></i> Dashboard Admin
        </a>
    <?php endif; ?>
    
    <div class="dropdown-divider" style="margin: 15px 0; background: rgba(255,255,255,0.1);"></div>
    
    <a href="about.php" class="action-btn">
        <i class="fas fa-info-circle"></i> À propos
    </a>
    <a href="contact.php" class="action-btn">
        <i class="fas fa-envelope"></i> Contact
    </a>
    
    <?php if($is_logged): ?>
        <div class="dropdown-divider" style="margin: 15px 0; background: rgba(255,255,255,0.1);"></div>
        <a href="logout.php" class="action-btn" style="color: #ff6b6b;">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    <?php else: ?>
        <div class="dropdown-divider" style="margin: 15px 0; background: rgba(255,255,255,0.1);"></div>
        <a href="login.php" class="action-btn">
            <i class="fas fa-sign-in-alt"></i> Connexion
        </a>
        <a href="inscription.php" class="action-btn">
            <i class="fas fa-user-plus"></i> Inscription
        </a>
    <?php endif; ?>
</div>

<script>
    // Fonctions pour le header
    
    // Mobile menu
    function toggleMobileMenu() {
        document.getElementById('mobileSidebar').classList.toggle('open');
        document.getElementById('mobileOverlay').classList.toggle('show');
    }
    
    function closeMobileMenu() {
        document.getElementById('mobileSidebar').classList.remove('open');
        document.getElementById('mobileOverlay').classList.remove('show');
    }
    
    // Mettre à jour le compteur du panier
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('ems_cart') || '[]');
        const count = cart.reduce((sum, item) => sum + item.quantite, 0);
        const cartCounts = document.querySelectorAll('#cartCount');
        cartCounts.forEach(el => el.innerText = count);
    }
    
    // Mettre à jour le compteur des favoris
    function updateFavCount() {
        const fav = JSON.parse(localStorage.getItem('ems_favorites') || '[]');
        const favCounts = document.querySelectorAll('#favCount');
        favCounts.forEach(el => el.innerText = fav.length);
    }
    
    // Recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    if(searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });
    }
    
    function performSearch() {
        const query = searchInput.value.trim();
        if(query.length < 2) {
            searchResults.classList.remove('show');
            return;
        }
        
        fetch(`search_ajax.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if(data.length > 0) {
                    searchResults.innerHTML = data.map(product => `
                        <a href="product.php?id=${product.product_id}" style="display: flex; align-items: center; gap: 15px; padding: 12px; text-decoration: none; color: #333; border-bottom: 1px solid #eee;">
                            <img src="asset/images/${product.image || 'placeholder.jpg'}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 10px;">
                            <div style="flex:1;">
                                <div style="font-weight: 600;">${product.nom}</div>
                                <div style="color: #28a745; font-weight: bold;">${product.prix_actuel.toLocaleString()} FCFA</div>
                            </div>
                        </a>
                    `).join('');
                    searchResults.classList.add('show');
                } else {
                    searchResults.innerHTML = '<div style="padding: 15px; text-align: center; color: #999;">Aucun produit trouvé</div>';
                    searchResults.classList.add('show');
                }
            });
    }
    
    // Fermer les résultats de recherche en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if(!searchInput?.contains(e.target) && !searchResults?.contains(e.target)) {
            searchResults?.classList.remove('show');
        }
    });
    
    // Fonction panier à implémenter
    function toggleCart(event) {
        if(event) event.preventDefault();
        if(typeof window.toggleCart === 'function') {
            window.toggleCart();
        } else {
            console.log('Fonction panier à définir');
        }
    }
    
    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
        updateFavCount();
        
        // Écouter les changements de panier
        window.addEventListener('storage', function(e) {
            if(e.key === 'ems_cart') updateCartCount();
            if(e.key === 'ems_favorites') updateFavCount();
        });
    });
</script>

</body>
</html>