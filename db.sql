-- Ajouter la colonne disponible à la table produits
ALTER TABLE produits ADD COLUMN disponible TINYINT(1) DEFAULT 1;

-- Tables pour les commandes
CREATE TABLE IF NOT EXISTS commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut_paiement ENUM('en_attente', 'paye', 'annule') DEFAULT 'en_attente',
    methode_paiement VARCHAR(50) DEFAULT 'Mobile Money',
    recu_paiement VARCHAR(255) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Ajouter colonne méthode de paiement à une table existante
ALTER TABLE commandes ADD COLUMN methode_paiement VARCHAR(50) DEFAULT 'Mobile Money';

CREATE TABLE IF NOT EXISTS details_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT,
    produit_id INT,
    quantite INT DEFAULT 1,
    prix DECIMAL(10,2),
    FOREIGN KEY (commande_id) REFERENCES commandes(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);            