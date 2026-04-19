function ajouterPanier(id) {
    alert("Produit ajouté au panier !");
}

function ajouterPanier(id) {
    fetch("/pages/ajouter_panier.php?id=" + id)
    .then(() => {
        alert("Produit ajouté 🛒");
    });
}

function likeProduit(id) {
    fetch("/pages/like.php?id=" + id)
    .then(() => alert("Like ajouté ❤️"));
}