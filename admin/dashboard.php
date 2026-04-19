<?php
include "../includes/db.php";


//Vérification admin
if (!isAdmin()){
    header("Location: login.php");
}


// forcer paiement
if(isset($_POST['forcer_paiement'])){
    $commande_id = $_POST['commande_id'];
    $stmt = $conn->prepare("UPDATE commandes SET  statut_paiement = 'paye' WHERE commande_id = ?");
    $stmt->execute([$commande_id]);
    header("Location: dashboard.php");
    exit;
}

// Statistiques
$users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch();
$produits = $conn->query("SELECT COUNT(*) as total FROM products")->fetch();
$produits_dispo = $conn->query("SELECT COUNT(*) as total FROM products WHERE disponible = 1 AND stock > 0")->fetch();
$produits_rupture = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock = 0")->fetch();
$categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch();

// Revenus
$revenu_jour = $conn->query("SELECT SUM(total) as total FROM commandes WHERE statut_paiement='paye' AND DATE(date_commande) = CURDATE()")->fetch();
$revenu_mois = $conn->query("SELECT SUM(total) as total FROM commandes WHERE statut_paiement='paye' AND MONTH(date_commande) = MONTH(CURDATE())")->fetch();
$revenu_total = $conn->query("SELECT SUM(total) as total FROM commandes WHERE statut_paiement='paye'")->fetch();

// Commandes en attente
$commandes_attente = $conn->query("SELECT COUNT(*) as total FROM commandes WHERE statut='en_attente'")->fetch();

// Ajout produit
if (isset($_POST['ajouter_produit'])) {
    $nom = $_POST['nom'];
    $stock = $_POST['stock'];
    $prix = $_POST['prix'];
    $prix_promo = !empty($_POST['prix_promo']) ? $_POST['prix_promo'] : null;
    $categorie_id = $_POST['categorie_id'];
    $description = $_POST['description'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed) && $_FILES['image']['size'] <= 2*1024*1024){
            $image = uniqid('prod_', true) . '.' . $ext;
            $destination = "../asset/images/{$image}";
            move_uploaded_file($_FILES['image']['tmp_name'], $destination);
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (nom, prix, prix_promo, stock, image, categorie_id, description, disponible) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prix, $prix_promo, $stock, $image, $categorie_id, $description, $disponible]);
    echo "<script>alert('Produit ajouté avec succès !'); window.location.href='dashboard.php';</script>";
}

// Modification produit
if (isset($_POST['modifier_produit'])) {
    $id = $_POST['product_id'];
    $nom = $_POST['nom'];
    $stock = $_POST['stock'];
    $prix = $_POST['prix'];
    $prix_promo = !empty($_POST['prix_promo']) ? $_POST['prix_promo'] : null;
    $categorie_id = $_POST['categorie_id'];
    $description = $_POST['description'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE products SET nom=?, prix=?, prix_promo=?, stock=?, categorie_id=?, description=?, disponible=? WHERE product_id=?");
    $stmt->execute([$nom, $prix, $prix_promo, $stock, $categorie_id, $description, $disponible, $id]);
    echo "<script>alert('Produit modifié !'); window.location.href='dashbaord.php';</script>";
}

// Ajout catégorie
if (isset($_POST['ajouter_categorie'])) {
    $nom_cat = $_POST['categorie_nom'];
    $desc_cat = $_POST['description_categorie'];
    $stmt = $conn->prepare("INSERT INTO categories (categorie_nom, description) VALUES (?, ?)");
    $stmt->execute([$nom_cat, $desc_cat]);
    echo "<script>alert('Catégorie ajoutée !'); window.location.href='dashboard.php';</script>";
}

// Supprimer catégorie
if (isset($_GET['supprimer_cat'])) {
    $cat_id = $_GET['cat_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE categorie_id = ?");
    $stmt->execute([$cat_id]);
    header("Location: dashboard.php");
    exit;
}

// Supprimer produit
if(isset($_GET['delete'])){
    $id = $_GET['id'];
    $img = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $img->execute([$id]);
    $img = $img->fetch();
    if($img && file_exists("../asset/images/".$img['image'])){
        unlink("../asset/images/".$img['image']);
    }
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

// Changer disponibilité
if (isset($_GET['toggle_dispo'])) {
    $id = $_GET['product_id'];
    $stmt = $conn->prepare("UPDATE products SET disponible = 1 - disponible WHERE product_id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

// Ajout code promo
if (isset($_POST['ajouter_promo'])) {
    $code = strtoupper($_POST['code']);
    $reduction_type = $_POST['reduction_type'];
    $reduction_valeur = $_POST['reduction_valeur'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $usage_max = $_POST['usage_max'] ?? 1;
    $montant_min = $_POST['montant_min'] ?? 0;

    $stmt = $conn->prepare("INSERT INTO promos (code, reduction_type, reduction_valeur, date_debut, date_fin, usage_max, montant_min) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$code, $reduction_type, $reduction_valeur, $date_debut, $date_fin, $usage_max, $montant_min]);
    echo "<script>alert('Code promo ajouté !'); window.location.href='dashboard.php';</script>";
}

// Supprimer promo
if (isset($_GET['delete_promo'])) {
    $id = $_GET['promo_id'];
    $stmt = $conn->prepare("DELETE FROM promos WHERE promo_id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

// Changer statut commande
if (isset($_POST['update_commande_statut'])) {
    $commande_id = $_POST['commande_id'];
    $statut = $_POST['statut'];

    // mettre a jour le statut de la commande
    $stmt = $conn->prepare("UPDATE commandes SET statut = ? WHERE commande_id = ?");
    $stmt->execute([$statut, $commande_id]);

    //Gestion automatique du statut paiement
    if($statut === 'livree'){
        //si livree, passage a paye
        $stmt = $conn->prepare("UPDATE commandes SET statut_paiement = 'paye' WHERE commande_id = ?");
        $stmt->execute([$commande_id]);
    }elseif($statut === 'confirmee'){
        $stmt = $conn->prepare("UPDATE commandes SET statut_paiement = 'paye' WHERE commande_id = ?");
        $stmt->execute([$commande_id]);
    }
    elseif($statut === 'annulee'){
        // si annulee, passage non paye
        $stmt = $conn->prepare("UPDATE commandes SET statut_paiement = 'non_paye' WHERE commande_id = ?");
        $stmt->execute([$commande_id]);
    }elseif($statut === 'en_attente'){
        $stmt = $conn->prepare("UPDATE commandes SET statut_paiement = 'en_attente' WHERE commande_id = ?");
        $stmt->execute([$commande_id]);
    }
    header("Location: dashboard.php");
    exit;
}

//Annulation commande speciale
if(isset($_POST['annuler_commande'])){
    $commande_id = $_POST['commande_id'];
    $statut = $_POST['statut'];

    $stmt = $conn->prepare("UPDATE commandes SET statut = ?, statut_paiement = 'non_paye' WHERE commande_id = ?");
    $stmt->execute([$statut, $commande_id]);

    header("Location: dashboard.php");
    exit;
}

// Confirmer paiement
if (isset($_POST['confirmer_paiement'])) {
    $commande_id = $_POST['commande_id'];
    $recu = '';
    if (isset($_FILES['recu']) && $_FILES['recu']['error'] == 0) {
        $recu = uniqid() . '_' . basename($_FILES['recu']['name']);
        if(!is_dir("../asset/images/recus")) {
            mkdir("../asset/images/recus", 0777, true);
        }
        move_uploaded_file($_FILES['recu']['tmp_name'], "../asset/images/recus/{$recu}");
    }
    $stmt = $conn->prepare("UPDATE commandes SET statut = 'paye', recu_paiement = ? WHERE commande_id = ?");
    $stmt->execute([$recu, $commande_id]);
    header("Location: dashboard.php");
    exit;
}

// Récupérer les données
$all_produits = $conn->query("SELECT p.*, c.categorie_nom as categorie_nom FROM products p LEFT JOIN categories c ON p.categorie_id = c.categorie_id ORDER BY p.product_id DESC");
$commandes = $conn->query("SELECT c.*, u.nom as user_nom FROM commandes c LEFT JOIN users u ON c.user_id = u.user_id ORDER BY c.date_commande DESC");
$promos = $conn->query("SELECT * FROM promos ORDER BY promo_id DESC");

// Ventes par mois
$ventes_mois = $conn->query("SELECT MONTH(date_commande) as mois, SUM(total) as total FROM commandes WHERE statut='paye' AND YEAR(date_commande) = YEAR(CURDATE()) GROUP BY MONTH(date_commande)");
$ventes_data = array_fill(0, 12, 0);
while($v = $ventes_mois->fetch()) {
    $ventes_data[$v['mois']-1] = $v['total'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | EMS - Shop</title>
    <link  rel="stylesheet" href="admin.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    </head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-crown"></i> Dashboard Admin</h1>
        <div class="user-info">
            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['nom'] ?? 'admin'); ?></span>
            <a href="login.php" class="logout-btn" onclick="return confirm('Être-vous sûr de vouloir vous déconnecter ?')"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Cartes Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users" style="color: #667eea;"></i></div>
            <div class="value"><?php echo $users['total']; ?></div>
            <div class="label">Utilisateurs</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-box" style="color: #28a745;"></i></div>
            <div class="value"><?php echo $produits['total']; ?></div>
            <div class="label">Produits</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-check-circle" style="color: #17a2b8;"></i></div>
            <div class="value"><?php echo $produits_dispo['total']; ?></div>
            <div class="label">En stock</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i></div>
            <div class="value"><?php echo $produits_rupture['total']; ?></div>
            <div class="label">Rupture</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-tags" style="color: #6f42c1;"></i></div>
            <div class="value"><?php echo $categories['total']; ?></div>
            <div class="label">Catégories</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-clock" style="color: #fd7e14;"></i></div>
            <div class="value"><?php echo $commandes_attente['total']; ?></div>
            <div class="label">Commandes en attente</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-day" style="color: #20c997;"></i></div>
            <div class="value"><?php echo number_format($revenu_jour['total'] ?? 0, 0, ',', ' '); ?> FCFA</div>
            <div class="label">Revenus aujourd'hui</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-alt" style="color: #6f42c1;"></i></div>
            <div class="value"><?php echo number_format($revenu_mois['total'] ?? 0, 0, ',', ' '); ?> FCFA</div>
            <div class="label">Revenus du mois</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-chart-line" style="color: #e83e8c;"></i></div>
            <div class="value"><?php echo number_format($revenu_total['total'] ?? 0, 0, ',', ' '); ?> FCFA</div>
            <div class="label">Revenus total</div>
        </div>
    </div>

    <!-- Graphique Ventes -->
    <div class="section">
        <h2><i class="fas fa-chart-line"></i> Ventes mensuelles</h2>
        <canvas id="monthlySalesChart" height="100"></canvas>
    </div>

    <!-- SECTION AJOUT PRODUIT & CATEGORIE -->
    <div class="two-columns">
        <div class="section">
            <h2><i class="fas fa-plus-circle"></i> Ajouter une catégorie</h2>
            <form method="POST">
                <input type="text" name="categorie_nom" placeholder="Nom de la catégorie" required style="width:100%; margin-bottom:12px;">
                <input type="text" name="description_categorie" placeholder="Description (optionnel)" style="width:100%; margin-bottom:12px;">
                <button type="submit" name="ajouter_categorie"><i class="fas fa-save"></i> Ajouter la catégorie</button>
            </form>
        </div>

        <div class="section">
            <h2><i class="fas fa-plus-circle"></i> Ajouter un produit</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="nom" placeholder="Nom du produit" required style="width:100%; margin-bottom:12px;">
                <input type="number" name="stock" placeholder="Stock" required style="width:100%; margin-bottom:12px;">
                <input type="number" name="prix" step="100" placeholder="Prix normal (FCFA)" required style="width:100%; margin-bottom:12px;">
                <input type="number" name="prix_promo" step="100" placeholder="Prix promotionnel (optionnel)" style="width:100%; margin-bottom:12px;">
                <select name="categorie_id" required style="width:100%; margin-bottom:12px;">
                    <option value="">Sélectionner une catégorie</option>
                    <?php 
                    $cats_list = $conn->query("SELECT * FROM categories ORDER BY categorie_nom");
                    while($c = $cats_list->fetch()) { ?>
                        <option value="<?php echo $c['categorie_id']; ?>"><?php echo htmlspecialchars($c['categorie_nom']); ?></option>
                    <?php } ?>
                </select>
                <input type="file" name="image" accept="image/*" style="width:100%; margin-bottom:12px;">
                <textarea name="description" placeholder="Description du produit" rows="3" style="width:100%; margin-bottom:12px;"></textarea>
                <label style="display:block; margin-bottom:12px;"><input type="checkbox" name="disponible" checked> Disponible</label>
                <button type="submit" name="ajouter_produit"><i class="fas fa-save"></i> Ajouter le produit</button>
            </form>
        </div>
    </div>
    
    <!-- SECTION CODES PROMO -->
    <div class="section">
        <h2><i class="fas fa-percent"></i> Codes promotionnels</h2>
        <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 12px; margin-bottom: 30px;">
            <input type="text" name="code" placeholder="Code promo (ex: PROMO20)" required>
            <select name="reduction_type" required>
                <option value="pourcentage">Pourcentage (%)</option>
                <option value="fixe">Montant fixe (FCFA)</option>
            </select>
            <input type="number" name="reduction_valeur" placeholder="Valeur" required>
            <input type="date" name="date_debut" required>
            <input type="date" name="date_fin" required>
            <input type="number" name="usage_max" placeholder="Utilisations max" value="1">
            <input type="number" name="montant_min" placeholder="Montant mini" value="0">
            <button type="submit" name="ajouter_promo"><i class="fas fa-tag"></i> Créer le code</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Réduction</th>
                        <th>Valable du</th>
                        <th>au</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $promos_list = $conn->query("SELECT * FROM promos ORDER BY promo_id DESC");
                    while($promo = $promos_list->fetch()) { 
                        $active = strtotime($promo['date_debut']) <= time() && strtotime($promo['date_fin']) >= time();
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($promo['code']); ?></strong></td>
                        <td><?php echo $promo['reduction_type'] == 'pourcentage' ? $promo['reduction_valeur'] . '%' : number_format($promo['reduction_valeur'],0,',',' ') . ' FCFA'; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($promo['date_debut'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($promo['date_fin'])); ?></td>
                        <td><span class="badge badge-<?php echo $active ? 'success' : 'danger'; ?>"><?php echo $active ? 'Actif' : 'Expiré'; ?></span></td>
                        <td><a href="?delete_promo=1&promo_id=<?php echo $promo['promo_id']; ?>" onclick="return confirm('Supprimer ce code promo ?')" style="color:#dc3545;"><i class="fas fa-trash"></i> Supprimer</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION LISTE PRODUITS -->
    <div class="section">
        <h2><i class="fas fa-boxes"></i> Gestion des produits</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Catégorie</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $all_products = $conn->query("SELECT p.*, c.categorie_nom FROM products p LEFT JOIN categories c ON p.categorie_id = c.categorie_id ORDER BY p.product_id DESC");
                    while($p = $all_products->fetch()) { 
                        $stock_class = $p['stock'] == 0 ? 'stock-rupture' : ($p['stock'] < 5 ? 'stock-faible' : '');
                    ?>
                    <tr>
                        <td><?php echo $p['product_id']; ?></td>
                        <td><?php if($p['image']) { ?><img src="../asset/images/<?php echo $p['image']; ?>" class="product-img"><?php } else { echo "<span style='color:#999;'>Aucune</span>"; } ?></td>
                        <td><?php echo htmlspecialchars($p['nom']); ?></td>
                        <td>
                            <?php if($p['prix_promo'] && $p['prix_promo'] > 0) { ?>
                                <span class="old-price"><?php echo number_format($p['prix'],0,',',' '); ?> FCFA</span><br>
                                <span class="promo-price"><?php echo number_format($p['prix_promo'],0,',',' '); ?> FCFA</span>
                            <?php } else { ?>
                                <?php echo number_format($p['prix'],0,',',' '); ?> FCFA
                            <?php } ?>
                        </td>
                        <td class="<?php echo $stock_class; ?>">
                            <?php echo $p['stock']; ?>
                            <button onclick="updateStock(<?php echo $p['product_id']; ?>, <?php echo $p['stock']; ?>)" style="padding:4px 10px; margin-left:8px; font-size:12px;">✏️</button>
                        </td>
                        <td><?php echo htmlspecialchars($p['categorie_nom']); ?></td>
                        <td><span class="badge badge-<?php echo $p['disponible'] ? 'success' : 'danger'; ?>"><?php echo $p['disponible'] ? 'Disponible' : 'Indisponible'; ?></span></td>
                        <td class="action-links">
                            <a href="#" onclick="editProduct(<?php echo $p['product_id']; ?>)"><i class="fas fa-edit"></i> Modifier</a>
                            <a href="?toggle_dispo=1&product_id=<?php echo $p['product_id']; ?>"><i class="fas fa-power-off"></i> Changer</a>
                            <a href="?delete=1&id=<?php echo $p['product_id']; ?>" onclick="return confirm('Supprimer ce produit ?')"><i class="fas fa-trash"></i> Supprimer</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION LISTE CATEGORIES -->
    <div class="section">
        <h2><i class="fas fa-list"></i> Liste des catégories</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Description</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $all_cats = $conn->query("SELECT * FROM categories ORDER BY categorie_nom");
                    while($cat = $all_cats->fetch()) { ?>
                    <tr>
                        <td><?php echo $cat['categorie_id']; ?></td>
                        <td><?php echo htmlspecialchars($cat['categorie_nom']); ?></td>
                        <td><?php echo htmlspecialchars($cat['description'] ?? '-'); ?></td>
                        <td><a href="?supprimer_cat=1&cat_id=<?php echo $cat['categorie_id']; ?>" onclick="return confirm('Supprimer cette catégorie ?')" style="color:#dc3545;"><i class="fas fa-trash"></i> Supprimer</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
   <!-- SECTION COMMANDES -->
<div class="section">
    <h2><i class="fas fa-shopping-cart"></i> Commandes</h2>
    
    <?php
    // Récupérer les commandes
    $all_orders = $conn->query("
        SELECT c.*, u.nom as user_nom 
        FROM commandes c 
        LEFT JOIN users u ON c.user_id = u.user_id 
        ORDER BY c.date_commande DESC
    ");
    
    if($all_orders->rowCount() == 0): 
    ?>
        <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 15px;">
            <i class="fas fa-shopping-cart" style="font-size: 50px; color: #ccc; margin-bottom: 15px;"></i>
            <h3>Aucune commande pour le moment</h3>
            <p>Les commandes des clients apparaîtront ici.</p>
        </div>
    <?php else: ?>
    
    <div class="table-container">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">ID</th>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">Client</th>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">Date</th>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">Total</th>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">Paiement</th>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">Statut</th>
                    <th style="padding: 12px; text-align: left; background: #f0f0f0;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($cmd = $all_orders->fetch()): 
                    $paiement_class = ($cmd['statut_paiement'] == 'paye') ? 'badge-success' : (($cmd['statut_paiement'] == 'non_paye') ? 'badge-danger' : 'badge-warning');
                    $paiement_text = ($cmd['statut_paiement'] == 'paye') ? 'Payé' : (($cmd['statut_paiement'] == 'non_paye') ? 'Annulée' : (($cmd['statut'] == 'expediee') ? 'Expédiée' : 'En attente'));
                    
                    $statut_class = '';
                    switch($cmd['statut']) {
                        case 'en_attente': $statut_class = 'badge-warning'; break;
                        case 'confirmee': $statut_class = 'badge-info'; break;
                        case 'expediee': $statut_class = 'badge-info'; break;
                        case 'livree': $statut_class = 'badge-success'; break;
                        case 'annulee': $statut_class = 'badge-danger'; break;
                        default: $statut_class = 'badge-secondary';
                    }
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">#<?php echo $cmd['commande_id']; ?></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($cmd['user_nom'] ?? 'Client inconnu'); ?></td>
                    <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($cmd['date_commande'])); ?></td>
                    <td style="padding: 12px;"><?php echo number_format($cmd['total'], 0, ',', ' '); ?> FCFA</td>
                    <td style="padding: 12px;">
                        <span class="badge <?php echo $paiement_class; ?>"><?php echo $paiement_text; ?></span>
                    </td>
                    <td style="padding: 12px;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="commande_id" value="<?php echo $cmd['commande_id']; ?>">
                            <select name="statut" onchange="this.form.submit()" style="padding: 5px 10px; border-radius: 5px;">
                                <option value="en_attente" <?php echo $cmd['statut'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="confirmee" <?php echo $cmd['statut'] == 'confirmee' ? 'selected' : ''; ?>>Confirmée</option>
                                <option value="expediee" <?php echo $cmd['statut'] == 'expediee' ? 'selected' : ''; ?>>Expédiée</option>
                                <option value="livree" <?php echo $cmd['statut'] == 'livree' ? 'selected' : ''; ?>>Livrée</option>
                                <option value="annulee" <?php echo $cmd['statut'] == 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                            <input type="hidden" name="update_commande_statut" value="1">
                        </form>
                    </td>
                    <td style="padding: 12px;">
                        <button onclick="voirReçu(<?php echo $cmd['commande_id']; ?>)" style="padding: 5px 10px; background: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 5px;">
                            <i class="fas fa-receipt"></i> Reçu
                        </button>
                        <?php if($cmd['statut_paiement'] != 'paye'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="commande_id" value="<?php echo $cmd['commande_id']; ?>">
                            <button type="submit" name="forcer_paiement" style="padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                <i class="fas fa-check"></i> Forcer paiement
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL MODIFICATION PRODUIT -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier le produit</h3>
        </div>
        <form method="POST" id="editProductForm">
            <div class="modal-body">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="text" name="nom" id="edit_nom" placeholder="Nom du produit" required>
                <input type="number" name="stock" id="edit_stock" placeholder="Stock" required>
                <input type="number" name="prix" id="edit_prix" placeholder="Prix normal (FCFA)" required>
                <input type="number" name="prix_promo" id="edit_prix_promo" placeholder="Prix promotionnel">
                <select name="categorie_id" id="edit_categorie_id" required>
                    <?php 
                    $cats_modal = $conn->query("SELECT * FROM categories");
                    while($c = $cats_modal->fetch()) { ?>
                        <option value="<?php echo $c['categorie_id']; ?>"><?php echo htmlspecialchars($c['categorie_nom']); ?></option>
                    <?php } ?>
                </select>
                <textarea name="description" id="edit_description" placeholder="Description" rows="3"></textarea>
                <label><input type="checkbox" name="disponible" id="edit_disponible"> Disponible</label>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn-danger">Annuler</button>
                <button type="submit" name="modifier_produit">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
 

    // Graphique ventes mensuelles
    const ctx = document.getElementById('monthlySalesChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Ventes (FCFA)',
                    data: <?php echo json_encode($ventes_data); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102,126,234,0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#764ba2',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 12, weight: 'bold' } }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#ffcc00',
                        bodyColor: '#fff'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e9ecef' },
                        ticks: { callback: function(value) { return value.toLocaleString() + ' FCFA'; } }
                    }
                }
            }
        });
    }

    // Modifier produit
    function editProduct(id) {
        fetch('get_product.php?id=' + id)
            .then(response => response.json())
            .then(product => {
                document.getElementById('edit_product_id').value = product.product_id;
                document.getElementById('edit_nom').value = product.nom;
                document.getElementById('edit_stock').value = product.stock;
                document.getElementById('edit_prix').value = product.prix;
                document.getElementById('edit_prix_promo').value = product.prix_promo || '';
                document.getElementById('edit_categorie_id').value = product.categorie_id;
                document.getElementById('edit_description').value = product.description || '';
                document.getElementById('edit_disponible').checked = product.disponible == 1;
                document.getElementById('editProductModal').style.display = 'flex';
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Mettre à jour stock
    function updateStock(id, currentStock) {
        let newStock = prompt('Nouveau stock:', currentStock);
        if(newStock !== null && !isNaN(newStock) && newStock >= 0) {
            fetch('update_stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${id}&stock=${newStock}`
            }).then(() => location.reload());
        }
    }

    // Fermer modal
    function closeModal() {
        document.getElementById('editProductModal').style.display = 'none';
    }

    // Fermer modal en cliquant à l'extérieur
    window.onclick = function(event) {
        let modal = document.getElementById('editProductModal');
        if(event.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Ajoutez ce script dans votre dashboard pour déconnexion automatique après inactivité

// Délai d'inactivité en millisecondes (30 minutes)
const INACTIVITY_TIMEOUT = 30 * 60 * 1000;

let inactivityTimer;

// Réinitialiser le timer
function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(() => {
        autoLogout();
    }, INACTIVITY_TIMEOUT);
}

// Déconnexion automatique
function autoLogout() {
    // Afficher une notification
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ffc107;
        color: #1a1a2e;
        padding: 15px 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    `;
    toast.innerHTML = `<i class="fas fa-clock"></i> Session expirée - Redirection vers la connexion...`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 2000);
}

// Écouter les événements d'activité
document.addEventListener('mousemove', resetInactivityTimer);
document.addEventListener('keypress', resetInactivityTimer);
document.addEventListener('click', resetInactivityTimer);
document.addEventListener('scroll', resetInactivityTimer);

// Démarrer le timer
resetInactivityTimer();
</script>

<!--scrip pour le recu-->
<script>
// ========== VARIABLES ==========
let currentCommandeData = null;

// ========== VOIR REÇU ==========
function voirReçu(commandeId) {
    const modal = document.getElementById('reçuModal');
    const reçuBody = document.getElementById('reçuBody');
    
    if (!modal || !reçuBody) return;
    
    modal.style.display = 'flex';
    reçuBody.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    
    fetch('get_recu.php?id=' + commandeId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentCommandeData = data;
                afficherReçu(data);
            } else {
                reçuBody.innerHTML = '<div style="text-align:center; padding:40px; color:red;">Erreur: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            reçuBody.innerHTML = '<div style="text-align:center; padding:40px; color:red;">Erreur de connexion</div>';
        });
}

// ========== AFFICHER REÇU ==========
function afficherReçu(data) {
    const reçuBody = document.getElementById('reçuBody');
    if (!reçuBody) return;
    
    const commande = data.commande || {};
    const client = data.client || {};
    const produits = data.produits || [];
    
    let produitsHtml = '';
    for (let i = 0; i < produits.length; i++) {
        const p = produits[i];
        const imagePath = '../asset/images/' + (p.image || 'placeholder.jpg');
        const quantite = parseInt(p.quantite) || 0;
        const prix = parseInt(p.prix_unitaire) || 0;
        
        produitsHtml += '<div style="display: flex; align-items: center; gap: 15px; padding: 10px 0; border-bottom: 1px solid #eee;">';
        produitsHtml += '<img src="' + imagePath + '" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px;" onerror="this.src=\'../asset/images/placeholder.jpg\'">';
        produitsHtml += '<div style="flex:1;"><strong>' + (p.nom || 'Produit') + '</strong><br><small>Quantité: ' + quantite + '</small></div>';
        produitsHtml += '<div style="font-weight:bold; color:#28a745;">' + prix.toLocaleString() + ' FCFA</div>';
        produitsHtml += '</div>';
    }
    
    let dateFormatee = 'Non définie';
    if (commande.date_commande) {
        const date = new Date(commande.date_commande);
        dateFormatee = date.toLocaleDateString('fr-FR') + ' à ' + date.toLocaleTimeString('fr-FR');
    }
    
    reçuBody.innerHTML = `
        <div style="text-align:center; padding:20px; background:linear-gradient(135deg,#1a1a2e,#16213e); color:white;">
            <i class="fas fa-crown" style="font-size:40px; color:#ffcc00;"></i>
            <h2>EMS Boutique</h2>
            <p>Reçu de commande N° ${commande.commande_id}</p>
        </div>
        <div style="padding:20px;">
            <div style="margin-bottom:20px;">
                <h3>Détails de la commande</h3>
                <p>Date: ${dateFormatee}</p>
                <p>Statut: ${commande.statut === 'livree' ? 'Livrée' : commande.statut === 'annulee' ? 'Annulée' : commande.statut}</p>
                <p>Paiement: ${commande.statut_paiement === 'paye' ? 'Payé' : commande.statut_paiement === 'non_paye' ? 'Annulé' : 'En attente'}</p>
            </div>
            <div style="margin-bottom:20px;">
                <h3>Informations client</h3>
                <p>Nom: ${client.nom || 'Non renseigné'}</p>
                <p>Email: ${client.email || 'Non renseigné'}</p>
                <p>Téléphone: ${client.telephone || 'Non renseigné'}</p>
                <p>Adresse: ${client.adresse || 'Non renseignée'}</p>
            </div>
            <div style="margin-bottom:20px;">
                <h3>Produits commandés</h3>
                ${produitsHtml}
            </div>
            <div style="background:#f8f9fa; padding:15px; border-radius:10px; text-align:center;">
                <h3>Total: ${parseInt(commande.total || 0).toLocaleString()} FCFA</h3>
            </div>
        </div>
        <div style="padding:15px; text-align:center; background:#f8f9fa; font-size:12px; color:#999;">
            <p>Merci de votre confiance !</p>
            <p>EMS Boutique - Votre shopping premium</p>
        </div>
    `;
}

// ========== IMPRIMER ==========
function imprimerReçu() {
    if (!currentCommandeData) {
        alert('Aucun reçu à imprimer');
        return;
    }
    
    const win = window.open('', '_blank');
    win.document.write('<html><head><title>Reçu</title>');
    win.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">');
    win.document.write('<style>body{font-family:sans-serif;padding:40px;} .reçu{max-width:800px;margin:0 auto;}</style>');
    win.document.write('</head><body>');
    win.document.write(document.getElementById('reçuBody').innerHTML);
    win.document.write('<script>window.print();<\/script>');
    win.document.write('</body></html>');
    win.document.close();
}

// ========== FERMETURE ==========
function closeReçu() {
    const modal = document.getElementById('reçuModal');
    if (modal) modal.style.display = 'none';
}

// ========== ÉVÉNEMENTS ==========
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeReçu();
});

window.onclick = function(e) {
    const modal = document.getElementById('reçuModal');
    if (e.target === modal) closeReçu();
}
</script>

<!-- Modale Reçu -->
<div id="reçuModal" class="reçu-modal">
    <div class="reçu-content">
        <div class="reçu-header">
            <h3><i class="fas fa-receipt"></i> Reçu de commande</h3>
            <button class="close-reçu" onclick="closeReçu()">&times;</button>
        </div>
        <div class="reçu-body" id="reçuBody">
            <!-- Contenu dynamique -->
        </div>
        <div class="reçu-footer">
            <button class="btn-print" onclick="imprimerReçu()">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button class="btn-close" onclick="closeReçu()">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>
    </div>
</div>

</body>
</html>