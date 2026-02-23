<?php
// REMOVA a linha do session_start daqui!

$host = "localhost";
$usuario = "root";
$senha = "Semprejc123!"; 
$banco = "universus";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>