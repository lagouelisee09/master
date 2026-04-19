<?php
$host='localhost';
$dbname='ems';
$username='root';
$password='';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

//démarrer la session si ce n'est pas fait
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

//Fonctions glogales
function isLogged(){
    return isset($_SESSION['user_id']);
}

function isAdmin(){
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function formatPrice($price){
    return  number_format($price, 0, ',', ' ') . 'FCFA';
}
?>
