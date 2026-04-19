<?php
include("../includes/db.php");
header('Content-Type: application/json');

if(isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}
?>