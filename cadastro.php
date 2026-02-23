<?php
include("conexao.php"); // Certifique-se de que o arquivo está como conexao.php (sem acento)

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["password"];

    // Criptografa a senha antes de salvar
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se o e-mail já existe
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $mensagem = "Este e-mail já está cadastrado!";
        $tipo_mensagem = "erro";
    } else {
        // Insere no banco
        $sql = "INSERT INTO usuarios (email, senha_hash) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $senha_hash);

        if ($stmt->execute()) {
            $mensagem = "Cadastro realizado com sucesso! <a href='login.php' style='color: #FFEA00;'>Faça login aqui.</a>";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Erro ao cadastrar: " . $conn->error;
            $tipo_mensagem = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - UniVersus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <a href="index.php" class="btn-voltar-home">VOLTAR</a>

    <div class="login-container glass-card">
        <h1>Criar Conta</h1>
        <p class="subtitle">Junte-se ao universo do conhecimento</p>
        
        <?php if($mensagem != ""): ?>
            <p style="color: <?php echo ($tipo_mensagem == 'sucesso') ? '#00ff00' : '#ff4444'; ?>; font-weight: bold; margin-bottom: 15px;">
                <?php echo $mensagem; ?>
            </p>
        <?php endif; ?>

        <form action="" method="POST"> 
            <div class="input-group">
                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required>
            </div>

            <div class="input-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
            </div>

            <button type="submit" class="btn-login-action">CADASTRAR</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.9em;">
            Já tem uma conta? <a href="login.php" style="color: #FFEA00; text-decoration: none;">Entre aqui</a>
        </p>
    </div>

</body>
</html>