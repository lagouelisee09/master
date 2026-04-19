<?php
include("../includes/db.php");


// Vérifier si l'utilisateur est connecté
if (!isLogged()) {
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer le panier
$cart = [];
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cart = json_decode($_POST['cart_data'], true);
    foreach($cart as $item) {
        $total += $item['prix'] * $item['quantite'];
    }
    $_SESSION['checkout_cart'] = $cart;
    $_SESSION['checkout_total'] = $total;
} elseif (isset($_SESSION['checkout_cart'])) {
    $cart = $_SESSION['checkout_cart'];
    $total = $_SESSION['checkout_total'];
}

if (empty($cart)) {
    header("Location: index.php");
    exit;
}

// Récupérer les infos utilisateur
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$payment_error = '';
$commande_id = null;

// Fonction pour enregistrer la commande
function enregistrerCommande($conn, $user_id, $total, $methode) {
    $stmt = $conn->prepare("INSERT INTO commandes (user_id, total, statut, statut_paiement, methode_paiement, date_commande) VALUES (?, ?, 'en_attente', 'en_attente', ?, NOW())");
    $stmt->execute([$user_id, $total, $methode]);
    $commande_id = $conn->lastInsertId();
    
    $cart = $_SESSION['checkout_cart'] ?? [];
    foreach($cart as $item) {
        $stmt = $conn->prepare("INSERT INTO commande_details (commande_id, product_id, quantite, prix_unitaire, sous_total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$commande_id, $item['id'], $item['quantite'], $item['prix'], $item['prix'] * $item['quantite']]);
    }
    
    return $commande_id;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method === 'wave') {
        $commande_id = enregistrerCommande($conn, $user_id, $total, 'wave');
        $_SESSION['last_commande_id'] = $commande_id;
        
        $wave_link = "https://pay.wave.com/m/M_ci_ZmzseAzg71tZ/c/ci/?amount=$total&currency=XOF&reference=CMD$commande_id";
        echo "<script>
            localStorage.removeItem('ems_cart');
            sessionStorage.setItem('commande_id', '$commande_id');
            window.location.href = '$wave_link';
        </script>";
        exit;
        
    } elseif ($payment_method === 'orange_money') {
        $commande_id = enregistrerCommande($conn, $user_id, $total, 'orange_money');
        unset($_SESSION['checkout_cart']);
        unset($_SESSION['checkout_total']);
        echo "<script>
            localStorage.removeItem('ems_cart');
            alert('Commande #$commande_id enregistrée !');
            window.location.href = 'mes-commandes.php';
        </script>";
        exit;
        
    } elseif ($payment_method === 'mtn_money') {
        $commande_id = enregistrerCommande($conn, $user_id, $total, 'mtn_money');
        unset($_SESSION['checkout_cart']);
        unset($_SESSION['checkout_total']);
        echo "<script>
            localStorage.removeItem('ems_cart');
            alert('Commande #$commande_id enregistrée !');
            window.location.href = 'mes-commandes.php';
        </script>";
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - EMS Boutique</title>
    <link rel="stylesheet" href="css/mes-commande.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .checkout-container { max-width: 1200px; margin: 0 auto; }
        .checkout-header { text-align: center; margin-bottom: 40px; }
        .checkout-header h1 { color: white; font-size: 2rem; }
        .checkout-header h1 i { color: #ffcc00; margin-right: 15px; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; }
        
        .order-summary {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .summary-header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            padding: 20px;
        }
        .summary-products { padding: 20px; max-height: 400px; overflow-y: auto; }
        .product-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .product-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .product-details { flex: 1; }
        .product-name { font-weight: 600; }
        .summary-total {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .grand-total {
            font-size: 1.3rem;
            font-weight: bold;
            border-top: 2px solid #ddd;
            padding-top: 15px;
            margin-top: 10px;
        }
        .grand-total .amount { color: #28a745; font-size: 1.5rem; }
        
        .payment-section {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .payment-header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            padding: 20px;
        }
        .payment-body { padding: 25px; }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .payment-method.selected {
            border-color: #28a745;
            background: #d4edda;
        }
        .method-icon {
            width: 50px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .orange-money .method-icon { background: #ff6600; color: white; }
        .mtn-money .method-icon { background: #ffcc00; color: #1a1a2e; }
        .wave .method-icon { background: #4a00e0; color: white; }
        .method-info { flex: 1; }
        .method-name { font-weight: 600; }
        
        .payment-form { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .payment-form.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* QR Code et infos paiement */
        .payment-info-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 15px;
        }
        .qr-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .qr-code {
            display: inline-block;
            padding: 15px;
            background: white;
            border-radius: 15px;
            margin-bottom: 10px;
        }
        .phone-number {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a2e;
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            display: inline-block;
            margin: 10px 0;
        }
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            margin-left: 10px;
        }
        .amount-info {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin: 15px 0;
        }
        .instruction-list {
            text-align: left;
            margin: 15px 0;
            padding-left: 20px;
        }
        .instruction-list li {
            margin: 8px 0;
            color: #666;
        }
        
        .wave-button, .btn-confirm {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }
        .wave-button {
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .checkout-grid {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) ;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .product-item {
            font-weight: bold;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            border-radius: 10px;
            padding: 15px;
        }

        .checkout-container a:active{
            cursor: pointer;
            color: black;
        }

        .checkout-container a:hover{
            cursor: pointer;
            color: #ffcc00;
        }

        .checkout-container a {
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 10px;
            text-decoration: none;
            border: transparent;
            border-radius:20px;
            box-shadow: 5px 5px 10px rgba(0,0,0,0.2);
        }
        @media (max-width: 768px) { .checkout-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div>
            <a href="../index.php" style=" box-shadow: 5px 5px 10px rgba(0,0,0,0.2)"><i class="fa-solid fa-arrow-left fa-xl" style="color: rgb(255, 212, 59);"></i> Retour a la boutique</a>
        </div>
        <div class="checkout-header">
            <h1><i class="fas fa-credit-card"></i> Paiement sécurisé</h1>
            <p>Choisissez votre méthode de paiement</p>
        </div>

        <div class="checkout-grid">
            <!-- Résumé commande -->
            <div class="order-summary">
                <div class="summary-header">
                    <h2><i class="fas fa-shopping-cart"></i> Résumé de la commande</h2>
                </div>
                <div class="summary-products">
                    <?php foreach($cart as $item): 
                        $product = $conn->query("SELECT * FROM products WHERE product_id = " . $item['id'])->fetch();
                    ?>
                    <div class="product-item">
                        <img src="../asset/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>">
                        <div class="product-details">
                            <div class="product-name"><?php echo htmlspecialchars($product['nom']); ?></div>
                            <div class="product-price"><?php echo number_format($item['prix'], 0, ',', ' '); ?> FCFA</div>
                            <div class="product-quantity">Quantité: <?php echo $item['quantite']; ?></div>
                        </div>
                        <div class="product-total"><?php echo number_format($item['prix'] * $item['quantite'], 0, ',', ' '); ?> FCFA</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-total">
                    <div class="grand-total">
                        <span>TOTAL À PAYER</span>
                        <span class="amount"><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
                    </div>
                </div>
            </div>

            <!-- Paiement -->
            <div class="payment-section">
                <div class="payment-header">
                    <h2><i class="fas fa-lock"></i> Moyen de paiement</h2>
                </div>
                <div class="payment-body">
                    <?php if($payment_error): ?>
                        <div class="alert-error"><?php echo $payment_error; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="paymentForm">
                        <div class="payment-methods">
                            <!-- Orange Money -->
                            <div class="payment-method orange-money" data-method="orange">
                                <div class="method-icon"><i class="fas fa-mobile-alt"></i></div>
                                <div class="method-info">
                                    <div class="method-name">Orange Money</div>
                                    <div class="method-desc">Paiement mobile Orange Money</div>
                                </div>
                                <input type="radio" name="payment_method" value="orange_money" class="method-radio" required>
                            </div>

                            <!-- MTN Money -->
                            <div class="payment-method mtn-money" data-method="mtn">
                                <div class="method-icon"><i class="fas fa-mobile-alt"></i></div>
                                <div class="method-info">
                                    <div class="method-name">MTN Mobile Money</div>
                                    <div class="method-desc">Paiement mobile MTN Money</div>
                                </div>
                                <input type="radio" name="payment_method" value="mtn_money" class="method-radio" required>
                            </div>

                            <!-- Wave -->
                            <div class="payment-method wave" data-method="wave">
                                <div class="method-icon"><i class="fas fa-waveform"></i></div>
                                <div class="method-info">
                                    <div class="method-name">Wave</div>
                                    <div class="method-desc">Paiement Wave sécurisé</div>
                                </div>
                                <input type="radio" name="payment_method" value="wave" class="method-radio" required>
                            </div>
                        </div>

                        <!-- Formulaire Orange Money -->
                        <div id="orange-form" class="payment-form">
                            <div class="payment-info-card">
                                <div class="qr-container">
                                    <div class="qr-code" id="orange-qr"></div>
                                    <p>Scannez le QR code ou envoyez l'argent</p>
                                </div>
                                <div style="text-align: center;">
                                    <div class="phone-number">
                                        <i class="fas fa-phone"></i> 07 05 44 89 39
                                        <button type="button" class="copy-btn" onclick="copyNumber('07 05 44 89 39')">Copier</button>
                                    </div>
                                    <div class="amount-info">Montant: <?php echo number_format($total, 0, ',', ' '); ?> FCFA</div>
                                    <ul class="instruction-list">
                                        <li>📱 Ouvrez l'application Orange Money</li>
                                        <li>💸 Choisissez "Envoyer de l'argent"</li>
                                        <li>📞 Entrez le numéro ci-dessus</li>
                                        <li>💰 Saisissez le montant exact</li>
                                        <li>✅ Confirmez avec votre code secret</li>
                                    </ul>
                                </div>
                            </div>
                            <button type="submit" class="btn-confirm">
                                <i class="fas fa-check-circle"></i> J'ai payé, confirmer la commande
                            </button>
                        </div>

                        <!-- Formulaire MTN Money -->
                        <div id="mtn-form" class="payment-form">
                            <div class="payment-info-card">
                                <div class="qr-container">
                                    <div class="qr-code" id="mtn-qr"></div>
                                    <p>Scannez le QR code ou envoyez l'argent</p>
                                </div>
                                <div style="text-align: center;">
                                    <div class="phone-number">
                                        <i class="fas fa-phone"></i> 05 96 02 95 62
                                        <button type="button" class="copy-btn" onclick="copyNumber('05 96 02 95 62')">Copier</button>
                                    </div>
                                    <div class="amount-info">Montant: <?php echo number_format($total, 0, ',', ' '); ?> FCFA</div>
                                    <ul class="instruction-list">
                                        <li>📱 Ouvrez l'application MTN Money</li>
                                        <li>💸 Choisissez "Transfert d'argent"</li>
                                        <li>📞 Entrez le numéro ci-dessus</li>
                                        <li>💰 Saisissez le montant exact</li>
                                        <li>✅ Confirmez avec votre code secret</li>
                                    </ul>
                                </div>
                            </div>
                            <button type="submit" class="btn-confirm">
                                <i class="fas fa-check-circle"></i> J'ai payé, confirmer la commande
                            </button>
                        </div>

                        <!-- Formulaire Wave -->
                        <div id="wave-form" class="payment-form">
                            <div class="payment-info-card" style="background: linear-gradient(135deg, #4a00e0, #8e2de2); color: white;">
                                <div style="text-align: center;">
                                    <i class="fas fa-waveform" style="font-size: 60px; margin-bottom: 15px;"></i>
                                    <h3>Paiement Wave sécurisé</h3>
                                    <div class="amount-info" style="color: #ffcc00;"><?php echo number_format($total, 0, ',', ' '); ?> FCFA</div>
                                    <p>Vous allez être redirigé vers Wave pour effectuer le paiement</p>
                                    <button type="submit" class="wave-button" style="background: white; color: #4a00e0;">
                                        <i class="fas fa-external-link-alt"></i> Payer avec Wave
                                    </button>
                                    <a href="<?php echo $wave_link; ?>" target="_blank"></a>
                                    <p style="font-size: 12px; margin-top: 15px;">
                                        <i class="fas fa-lock"></i> Paiement 100% sécurisé
                                    </p>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="cart_data" value='<?php echo json_encode($cart); ?>'>
                    </form>
                </div>
            </div>
        </div>
    </div>

     <script>
        // Générer les QR codes
        const totalAmount = <?php echo $total; ?>;
        
        // Orange Money QR
        new QRCode(document.getElementById("orange-qr"), {
            text: `OM:${totalAmount}?merchant=0705448939&ref=CMD${Date.now()}`,
            width: 180,
            height: 180
        });
        
        // MTN Money QR
        new QRCode(document.getElementById("mtn-qr"), {
            text: `MTN:${totalAmount}?merchant=0596029562&ref=CMD${Date.now()}`,
            width: 180,
            height: 180
        });
        
        // Sélection des méthodes de paiement
        const methods = document.querySelectorAll('.payment-method');
        const orangeForm = document.getElementById('orange-form');
        const mtnForm = document.getElementById('mtn-form');
        const waveForm = document.getElementById('wave-form');
        const radioInputs = document.querySelectorAll('.method-radio');

        function switchMethod(method) {
            orangeForm.classList.remove('active');
            mtnForm.classList.remove('active');
            waveForm.classList.remove('active');
            if (method === 'orange') orangeForm.classList.add('active');
            else if (method === 'mtn') mtnForm.classList.add('active');
            else if (method === 'wave') waveForm.classList.add('active');
        }

        methods.forEach((method, index) => {
            method.addEventListener('click', () => {
                methods.forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');
                radioInputs[index].checked = true;
                switchMethod(method.dataset.method);
            });
        });

        // Sélection par défaut
        methods[0].classList.add('selected');
        radioInputs[0].checked = true;
        switchMethod('orange');

        // Copier un numéro
        function copyNumber(number) {
            navigator.clipboard.writeText(number).then(() => {
                alert('Numéro copié : ' + number);
            });
        }
    </script>
</body>
</html>
