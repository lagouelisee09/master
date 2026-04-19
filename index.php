<?php
include "includes/db.php";

// si admin connecte aller vers dashboard
if(isAdmin()){
    header("Location: admin/dashboard.php");
    exit;
}

// Récupérer les catégories
$categories = $conn->query("SELECT * FROM categories ORDER BY categorie_nom");

// Récupérer tous les produits avec infos promo
$produits = $conn->query("
    SELECT p.*, c.categorie_nom,
        CASE 
            WHEN p.prix_promo IS NOT NULL AND p.prix_promo < p.prix THEN p.prix_promo
            ELSE p.prix
        END as prix_actuel,
        CASE 
               WHEN p.prix_promo IS NOT NULL AND p.prix_promo < p.prix THEN ROUND((1 - p.prix_promo/p.prix) * 100)
            ELSE 0
        END as pourcentage_promo
    FROM products p
    LEFT JOIN categories c ON p.categorie_id = c.categorie_id
    WHERE p.disponible = 1
    ORDER BY p.product_id DESC
");

$produits_list = [];
while($p = $produits->fetch()) {
    $produits_list[] = $p;
}

// Vérifier si utilisateur est connecté
$is_logged = isset($_SESSION['user_id']);
$user_id = $_SESSION['nom'] ?? null;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMS Boutique - Votre shopping en ligne</title>
    <link rel="stylesheet" href="asset/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="apple-touch-icon" sizes="180x180" href="favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon_io/favicon-16x16.png">
    <link rel="manifest" href="favicon_io/site.webmanifest">
</head>
<body>
<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-info">
            <span><i class="fas fa-truck"></i> Livraison gratuite dès 50 000 FCFA</span>
            <span><i class="fas fa-undo-alt"></i> Retours gratuits sous 14 jours</span>
            <span><i class="fas fa-headset"></i> Support 24/7</span>
        </div>
        <div class="top-links">
            <a href="pages/about.php"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="pages/contact.php"><i class="fas fa-envelope"></i> Contact</a>
            <a href="pages/faq.php"><i class="fas fa-question-circle"></i> FAQ</a>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header">
    <div class="header-content">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Logo -->
        <a href="index.php" class="logo">
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
                <button onclick="renderProducts()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <a href="pages/favoris.php" class="action-btn">
                <i class="fas fa-heart"></i>
                <span>Favoris</span>
                <span class="badge-count" id="favCount">0</span>
            </a>
            
            <button class="action-btn" onclick="toggleCart()" style="background:none; border:none; cursor:pointer;">
                <i class="fas fa-shopping-cart"></i>
                <span>Panier</span>
                <span class="badge-count" id="cartCount">0</span>
            </button>

            <?php if($is_logged): ?>
                <div class="user-menu">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            <h4><?php echo htmlspecialchars($user_id); ?></h4>
                            <p><?php echo $user_id === 'admin' ? 'Administrateur' : 'Client'; ?></p>
                        </div>
                        <a href="pages/profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Mon profil
                        </a>
                        <a href="pages/mes-commandes.php" class="dropdown-item">
                            <i class="fas fa-shopping-bag"></i> Mes commandes
                        </a>
                        <a href="pages/favoris.php" class="dropdown-item">
                            <i class="fas fa-heart"></i> Mes favoris
                        </a>
                        <?php if($user_id === 'admin'): ?>
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
                <a href="pages/connexion.php" class="action-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Connexion</span>
                </a>
                <a href="pages/inscription.php" class="action-btn">
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
                <h1 style="color:white;">EMS Boutique</h1>
            </div>
        </div>
        <button class="close-sidebar" onclick="closeMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="mobile-menu-items">
        <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
        <a href="pages/favoris.php"><i class="fas fa-heart"></i> Favoris</a>
        <a href="#" onclick="toggleCart(); closeMobileMenu();"><i class="fas fa-shopping-cart"></i> Panier</a>
        
        <?php if($is_logged): ?>
            <a href="pages/profile.php"><i class="fas fa-user"></i> Mon profil</a>
            <a href="pages/mes-commandes.php"><i class="fas fa-shopping-bag"></i> Mes commandes</a>
            <?php if($user_role === 'admin'): ?>
                <a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php endif; ?>
            <div class="mobile-divider"></div>
            <a href="logout.php" style="color:#ff6b6b;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        <?php else: ?>
            <div class="mobile-divider"></div>
            <a href="pages/connexion.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            <a href="pages/inscription.php"><i class="fas fa-user-plus"></i> Inscription</a>
        <?php endif; ?>
        
        <div class="mobile-divider"></div>
        <a href="pages/about.php"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="pages/contact.php"><i class="fas fa-envelope"></i> Contact</a>
        <a href="pages/faq.php"><i class="fas fa-question-circle"></i> FAQ</a>
    </div>
</div>

<!-- Main Layout -->
<div class="layout">
    <!-- Sidebar Filtres -->
    <aside class="sidebar">
        <h3><i class="fas fa-filter"></i> Filtres</h3>
        
        <div class="filter-section">
            <h4><i class="fas fa-tags"></i> Catégories</h4>
            <ul class="category-filter" id="categoryFilter">
                <li><a href="#" data-cat="all" class="active">Tous les produits</a></li>
                <?php 
                $all_cats = $conn->query("SELECT * FROM categories ORDER BY categorie_nom");
                while($cat = $all_cats->fetch()): 
                ?>
                <li><a href="#" data-cat="<?php echo $cat['categorie_id']; ?>"><?php echo htmlspecialchars($cat['categorie_nom']); ?></a></li>
                <?php endwhile; ?>
            </ul>
        </div>
        
        <div class="filter-section">
            <h4><i class="fas fa-dollar-sign"></i> Prix max</h4>
            <div class="price-range">
                <input type="range" id="priceRange" min="0" max="500000" step="5000" value="500000">
                <div style="display: flex; justify-content: space-between;">
                    <span>0 FCFA</span>
                    <span id="priceValue">500 000 FCFA</span>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <h4><i class="fas fa-tag"></i> Promotions</h4>
            <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="promoOnly"> Uniquement en promotion
            </label>
        </div>
    </aside>

    <!-- Products Container -->
    <div class="products-container">
        <div class="products-header">
            <div class="products-count" id="productsCount">Chargement...</div>
            <select class="sort-select" id="sortSelect">
                <option value="default">Trier par</option>
                <option value="price-asc">Prix croissant</option>
                <option value="price-desc">Prix décroissant</option>
                <option value="name-asc">Nom A-Z</option>
                <option value="name-desc">Nom Z-A</option>
            </select>
        </div>
        
        <div class="products-grid" id="productsGrid">
            <div class="loader" style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
        </div>
    </div>
</div>
<!-- Cart Sidebar -->
<div class="overlay" id="overlay" onclick="closeCart()"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3><i class="fas fa-shopping-cart"></i> Mon Panier</h3>
        <button class="close-cart" onclick="closeCart()"><i class="fas fa-times"></i></button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="empty-cart" style="text-align:center; padding:40px; color:#999;">Votre panier est vide</div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display: none;">
        <div class="cart-total">
            <span>Total:</span>
            <span id="cartTotal">0 FCFA</span>
        </div>
        <button class="checkout-btn" onclick="checkout()"><i class="fas fa-credit-card"></i> Commander</button>
    </div>
</div>

<script>
    // Données des produits
    const productsData = <?php echo json_encode($produits_list); ?>;
    let cart = [];
    let favorites = [];
    
    // Éléments DOM
    const productsGrid = document.getElementById('productsGrid');
    const searchInput = document.getElementById('searchInput');
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const promoOnly = document.getElementById('promoOnly');
    const sortSelect = document.getElementById('sortSelect');
    const productsCount = document.getElementById('productsCount');
    const cartCountSpan = document.getElementById('cartCount');
    const favCountSpan = document.getElementById('favCount');
    const cartItemsDiv = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    const cartTotalSpan = document.getElementById('cartTotal');
    
    // Charger le panier
    function loadCart() {
        const savedCart = localStorage.getItem('ems_cart');
        if(savedCart) {
            cart = JSON.parse(savedCart);
        }
        updateCartUI();
    }
    
    function saveCart() {
        localStorage.setItem('ems_cart', JSON.stringify(cart));
        updateCartUI();
    }
    
    function updateCartUI() {
        const itemCount = cart.reduce((sum, item) => sum + item.quantite, 0);
        cartCountSpan.innerText = itemCount;
        
        if(cart.length === 0) {
            cartItemsDiv.innerHTML = '<div class="empty-cart" style="text-align:center; padding:40px; color:#999;"><i class="fas fa-shopping-cart"></i><br>Votre panier est vide</div>';
            cartFooter.style.display = 'none';
        } else {
            let itemsHtml = '';
            let total = 0;
            
            cart.forEach(item => {
                const product = productsData.find(p => p.product_id == item.id);
                if(product) {
                    const prix = product.prix_promo && product.prix_promo < product.prix ? product.prix_promo : product.prix;
                    const itemTotal = prix * item.quantite;
                    total += itemTotal;
                    
                    itemsHtml += `
                        <div class="cart-item">
                            <img src="asset/images/${product.image || 'placeholder.jpg'}" alt="${product.nom}">
                            <div class="cart-item-info">
                                <div class="cart-item-title">${product.nom}</div>
                                <div class="cart-item-price">${prix.toLocaleString()} FCFA</div>
                                <div class="cart-item-quantity">
                                    <button onclick="updateQuantity(${item.id}, -1)">-</button>
                                    <span>${item.quantite}</span>
                                    <button onclick="updateQuantity(${item.id}, 1)">+</button>
                                    <button onclick="removeFromCart(${item.id})" style="background:#dc3545; color:white;">×</button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            
            cartItemsDiv.innerHTML = itemsHtml;
            cartTotalSpan.innerText = total.toLocaleString() + ' FCFA';
            cartFooter.style.display = 'block';
        }
    }
    
    function addToCart(productId) {
        const existing = cart.find(item => item.id == productId);
        if(existing) {
            existing.quantite++;
        } else {
            cart.push({ id: productId, quantite: 1 });
        }
        saveCart();
        showToast('Produit ajouté au panier', 'success');
    }
    
    function updateQuantity(productId, change) {
        const item = cart.find(item => item.id == productId);
        if(item) {
            item.quantite += change;
            if(item.quantite <= 0) {
                cart = cart.filter(item => item.id != productId);
            }
        }
        saveCart();
    }
    
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id != productId);
        saveCart();
        showToast('Produit retiré du panier', 'info');
    }
    
    // Favoris
    function loadFavorites() {
        const savedFav = localStorage.getItem('ems_favorites');
        if(savedFav) {
            favorites = JSON.parse(savedFav);
        }
        favCountSpan.innerText = favorites.length;
    }
    
    function saveFavorites() {
        localStorage.setItem('ems_favorites', JSON.stringify(favorites));
        favCountSpan.innerText = favorites.length;
    }
    
    function toggleFavorite(productId, btn) {
        if(favorites.includes(productId)) {
            favorites = favorites.filter(id => id != productId);
            btn.classList.remove('active');
            showToast('Retiré des favoris', 'info');
        } else {
            favorites.push(productId);
            btn.classList.add('active');
            showToast('Ajouté aux favoris', 'success');
        }
        saveFavorites();
    }
    
    // Affichage des produits
    function renderProducts() {
        let filtered = [...productsData];
        
        const searchTerm = searchInput.value.toLowerCase();
        if(searchTerm) {
            filtered = filtered.filter(p => p.nom.toLowerCase().includes(searchTerm));
        }
        
        const activeCat = document.querySelector('.category-filter a.active')?.dataset.cat;
        if(activeCat && activeCat !== 'all') {
            filtered = filtered.filter(p => p.categorie_id == activeCat);
        }
        
        const maxPrice = parseInt(priceRange.value);
        filtered = filtered.filter(p => {
            const prix = p.prix_promo && p.prix_promo < p.prix ? p.prix_promo : p.prix;
            return prix <= maxPrice;
        });
        
        if(promoOnly.checked) {
            filtered = filtered.filter(p => p.prix_promo && p.prix_promo < p.prix);
        }
        
        const sortBy = sortSelect.value;
        switch(sortBy) {
            case 'price-asc':
                filtered.sort((a,b) => {
                    const prixA = a.prix_promo && a.prix_promo < a.prix ? a.prix_promo : a.prix;
                    const prixB = b.prix_promo && b.prix_promo < b.prix ? b.prix_promo : b.prix;
                    return prixA - prixB;
                });
                break;
            case 'price-desc':
                filtered.sort((a,b) => {
                    const prixA = a.prix_promo && a.prix_promo < a.prix ? a.prix_promo : a.prix;
                    const prixB = b.prix_promo && b.prix_promo < b.prix ? b.prix_promo : b.prix;
                    return prixB - prixA;
                });
                break;
            case 'name-asc':
                filtered.sort((a,b) => a.nom.localeCompare(b.nom));
                break;
            case 'name-desc':
                filtered.sort((a,b) => b.nom.localeCompare(a.nom));
                break;
        }
        
        productsCount.innerText = `${filtered.length} produit(s) trouvé(s)`;
        
        if(filtered.length === 0) {
            productsGrid.innerHTML = '<div style="text-align:center; padding:40px;">Aucun produit trouvé</div>';
            return;
        }
        
        productsGrid.innerHTML = filtered.map(p => {
            const prixActuel = p.prix_promo && p.prix_promo < p.prix ? p.prix_promo : p.prix;
            const pourcentagePromo = p.prix_promo && p.prix_promo < p.prix ? Math.round((1 - p.prix_promo/p.prix) * 100) : 0;
            const estFavori = favorites.includes(p.product_id);
            let stockClass = '';
            let stockText = '';
            
            if(p.stock == 0) {
                stockClass = 'rupture';
                stockText = 'Rupture';
            } else if(p.stock < 5) {
                stockClass = 'faible';
                stockText = `Stock: ${p.stock}`;
            } else {
                stockText = `Stock: ${p.stock}`;
            }
            
            // description courte
            const description = p.description ?
            (p.description.length > 80 ? p.description.substring(0, 80) + '...' : p.description) :'Aucune description';

            return `
                <div class="product-card">
                    <div class="product-image">
                        ${pourcentagePromo > 0 ? `<span class="promo-badge">-${pourcentagePromo}%</span>` : ''}
                        <span class="stock-badge ${stockClass}">${stockText}</span>
                        <img src="asset/images/${p.image || 'placeholder.jpg'}" alt="${p.nom}" onerror="this.src='asset/images/placeholder.jpg'">
                    </div>
                    <div class="product-info">
                        <div class="product-title">${escapeHtml(p.nom)}</div>
                        <div class="product-category"><i class="fas fa-tag"></i> ${p.categorie_nom || 'Non catégorisé'}</div>
                        <div class="product-description">${escapeHtml(description)}</div>
                        <div class="product-price">
                            <span class="current-price">${prixActuel.toLocaleString()} FCFA</span>
                            ${pourcentagePromo > 0 ? `<span class="old-price">${p.prix.toLocaleString()} FCFA</span>` : ''}
                        </div>
                        <div class="product-actions">
                            ${p.stock > 0 ? `
                                <button class="btn-cart" onclick="addToCart(${p.product_id})">
                                    <i class="fas fa-shopping-cart"></i> Panier
                                </button>
                            ` : `
                                <button class="btn-cart" disabled style="opacity:0.5; cursor:not-allowed;">
                                    <i class="fas fa-times-circle"></i> Rupture
                                </button>
                            `}
                            <button class="btn-fav ${estFavori ? 'active' : ''}" onclick="toggleFavorite(${p.product_id}, this)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    function escapeHtml(str) {
        if(!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if(m === '&') return '&amp;';
            if(m === '<') return '&lt;';
            if(m === '>') return '&gt;';
            return m;
        });
    }
    
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    function toggleCart() {
        document.getElementById('cartSidebar').classList.toggle('open');
        document.getElementById('overlay').classList.toggle('show');
    }
    
    function closeCart() {
        document.getElementById('cartSidebar').classList.remove('open');
        document.getElementById('overlay').classList.remove('show');
    }
    
    function checkout() {
        if(cart.length === 0) {
            showToast('Votre panier est vide', 'error');
            return;
        }
        
        <?php if(!$is_logged): ?>
            showToast('Veuillez vous connecter pour commander', 'error');
            setTimeout(() => window.location.href = 'pages/connexion.php', 1500);
        <?php else: ?>
            // Envoyer donnee vers checkout
            const cartData = cart.map(item => ({
                id: item.id,
                quantite: item.quantite,
                prix: productsData.find(p => p.product_id == item.id)?.prix_actuel || 0
            }));

            // creer un formulaire invisible pour envoyer les donnees
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'pages/checkout.php';

            const input =document.createElement('input');
            input.type = 'hidden';
            input.name = 'cart_data';
            input.value = JSON.stringify(cartData);

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        <?php endif; ?>
    }


    
    // Mobile menu
    function toggleMobileMenu() {
        document.getElementById('mobileSidebar').classList.toggle('open');
        document.getElementById('mobileOverlay').classList.toggle('show');
    }
    
    function closeMobileMenu() {
        document.getElementById('mobileSidebar').classList.remove('open');
        document.getElementById('mobileOverlay').classList.remove('show');
    }
    
    // Événements
    searchInput.addEventListener('input', renderProducts);
    priceRange.addEventListener('input', (e) => {
        priceValue.innerText = parseInt(e.target.value).toLocaleString() + ' FCFA';
        renderProducts();
    });
    promoOnly.addEventListener('change', renderProducts);
    sortSelect.addEventListener('change', renderProducts);
    
    document.querySelectorAll('.category-filter a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.category-filter a').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            renderProducts();
        });
    });
    
    // Initialisation
    loadCart();
    loadFavorites();
    renderProducts();
</script>
</body>
</html>

