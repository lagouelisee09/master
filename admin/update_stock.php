<?php
include("../includes/db.php");

if(isset($_POST['product_id']) && isset($_POST['stock'])) {
    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
    $stmt->execute([$_POST['stock'], $_POST['product_id']]);
    echo "ok";
}
?>