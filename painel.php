<?php
session_start();
include("conexao.php");

// Verifica se está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["usuario_id"];

// Buscar dados do usuário no banco
$sql = "SELECT id, email, data_criacao FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel - Universus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="index.php" class="btn-topo-esquerdo">Menu</a>
<div class="painel-container">
    <h1>Painel Universus</h1>

    <div class="painel-info">
        <p><strong>ID:</strong> <?php echo $usuario["id"]; ?></p>
        <p><strong>Email:</strong> <?php echo $usuario["email"]; ?></p>
        <p><strong>Cadastrado em:</strong> <?php echo $usuario["data_criacao"]; ?></p>
    </div>

    <a href="logout.php" class="btn-logout">Sair</a>
</div>

</body>
</html>