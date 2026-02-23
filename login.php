<?php
session_start();
include("conexao.php"); // Certifique-se que o arquivo chama-se conexao.php (sem acento)

$erro = ""; // Variável para mostrar mensagens de erro no layout

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["password"]; // No seu HTML original, o nome era 'password'

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verifica a senha comparando com o hash do banco
        if (password_verify($senha, $usuario["senha_hash"])) {
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_email"] = $usuario["email"];
            header("Location: painel.php");
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UniVersus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <a href="index.php" class="btn-voltar-home">VOLTAR</a>

    <div class="login-container glass-card">
        <h1>Login</h1>
        <p class="subtitle">Bem-vindo de volta ao UniVersus</p>
        
        <?php if($erro != ""): ?>
            <p style="color: #ff4444; font-weight: bold; margin-bottom: 15px;"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form action="" method="POST"> 
            <div class="input-group">
                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required>
            </div>

            <div class="input-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" placeholder="******" required>
            </div>

            <button type="submit" class="btn-login-action">LOG IN</button>
        </form>
    </div>

</body>
</html>