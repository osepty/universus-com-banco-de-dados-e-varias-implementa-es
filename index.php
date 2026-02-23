<?php
session_start();
include("conexao.php");
$instituicoes = [];
$sql = "SELECT * FROM instituicao";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    $instituicoes[] = $row;
}

if (isset($_POST["enviar_comentario"]) && isset($_SESSION["usuario_id"])) {

    $comentario = $_POST["comentario"];
    $faculdade = $_POST["faculdade"];
    $usuario_id = $_SESSION["usuario_id"];

    $sql = "INSERT INTO comentarios (usuario_id, faculdade, comentario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $usuario_id, $faculdade, $comentario);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// --- 1. NOSSO "BANCO DE DADOS" EM PHP (NÃO MUDOU NADA AQUI) ---

$f1 = null;
$f2 = null;

if(isset($_POST['faculdade1']) && $_POST['faculdade1'] != ""){
    $id1 = $_POST['faculdade1'];

    $stmt = $conn->prepare("SELECT * FROM instituicao WHERE id = ?");
    $stmt->bind_param("i", $id1);
    $stmt->execute();
    $f1 = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if(isset($_POST['faculdade2']) && $_POST['faculdade2'] != ""){
    $id2 = $_POST['faculdade2'];

    $stmt = $conn->prepare("SELECT * FROM instituicao WHERE id = ?");
    $stmt->bind_param("i", $id2);
    $stmt->execute();
    $f2 = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparador Galáctico</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <?php if (isset($_SESSION["usuario_email"])): ?>
    <div class="usuario-logado">
        👋 Olá, <?php echo $_SESSION["usuario_email"]; ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION["usuario_id"])): ?>
    <a href="logout.php" class="btn-inferior-esquerdo">Logoff</a>
<?php endif; ?>
    <button class="btn-menu-topo" onclick="toggleMainMenu()">MENU</button>

    <form action="index.php" method="POST" class="container-principal">
        <div class="coluna glass-card">
            <div class="caixa-selecao">
                <select name="faculdade1" onchange="this.form.submit()">
    <option value="">Selecione a faculdade</option>
    <?php foreach($instituicoes as $inst): ?>
        <option value="<?php echo $inst['id']; ?>"
            <?php if(isset($_POST['faculdade1']) && $_POST['faculdade1'] == $inst['id']) echo 'selected'; ?>>
            <?php echo $inst['nome']; ?>
        </option>
    <?php endforeach; ?>
</select>
            </div>
            <div class="espaco-imagem">
<?php if($f1 && !empty($f1['logo'])): ?>
    <img src="<?php echo $f1['logo']; ?>" class="logo-facul">
<?php else: ?>
    <div class="placeholder">LOGO INSTITUIÇÃO</div>
<?php endif; ?>
</div>
            <div class="detalhes">
                <p>⭐ Nota ENADE: <strong><?php echo $f1 ? $f1['nota_enade'] : "-"; ?></strong></p>
<p>🏛 Nota MEC: <strong><?php echo $f1 ? $f1['nota_mec'] : "-"; ?></strong></p>
<p>💰 Mensalidade: 
<strong>
<?php echo $f1 ? "R$ " . number_format($f1['mensalidade_media'], 2, ',', '.') : "-"; ?>
</strong>
</p>
            </div>
            <button type="button" class="btn-comentarios" onclick="toggleSidebar('sidebar-esq')">Comentários</button>
        </div>

        <div class="coluna-centro">
            <h1 class="vs-texto">VS</h1>
        </div>

        <div class="coluna glass-card">
            <div class="caixa-selecao">
                <select name="faculdade2" onchange="this.form.submit()">
    <option value="">Selecione a faculdade</option>
    <?php foreach($instituicoes as $inst): ?>
        <option value="<?php echo $inst['id']; ?>"
            <?php if(isset($_POST['faculdade2']) && $_POST['faculdade2'] == $inst['id']) echo 'selected'; ?>>
            <?php echo $inst['nome']; ?>
        </option>
    <?php endforeach; ?>
</select>
            </div>
            <div class="espaco-imagem">
<?php if($f2 && !empty($f2['logo'])): ?>
    <img src="<?php echo $f2['logo']; ?>" class="logo-facul">
<?php else: ?>
    <div class="placeholder">LOGO INSTITUIÇÃO</div>
<?php endif; ?>
</div>
            <div class="detalhes">
                <p>⭐ Nota ENADE: <strong><?php echo $f2 ? $f2['nota_enade'] : "-"; ?></strong></p>
<p>🏛 Nota MEC: <strong><?php echo $f2 ? $f2['nota_mec'] : "-"; ?></strong></p>
<p>💰 Mensalidade: 
<strong>
<?php echo $f2 ? "R$ " . number_format($f2['mensalidade_media'], 2, ',', '.') : "-"; ?>
</strong>
</p>
            </div>
            <button type="button" class="btn-comentarios" onclick="toggleSidebar('sidebar-dir')">Comentários</button>
        </div>
    </form>


    <div id="main-menu-overlay" class="menu-overlay">
        <button class="btn-fechar-menu-topo" onclick="toggleMainMenu()">X</button>
        
        <div class="menu-botoes-container">
            <button class="glass-card menu-btn-grande" onclick="toggleMainMenu()">Comparar</button>
            
            <a href="login.php" class="glass-card menu-btn-grande">Login</a>
            
            
           <a href="cadastro.php" class="glass-card menu-btn-grande">Cadastro</a>
        </div>
        <div class="menu-footer">U</div>
        <a href="universus.php" class="glass-card menu-btn-grande">Universus</a>
        </div>
        <div class="menu-footer">U</div>
    </div>


    <div id="sidebar-esq" class="sidebar sidebar-esquerda glass-card">
    <button class="btn-fechar" onclick="toggleSidebar('sidebar-esq')">X</button>
    <h2>Comentários - <?php echo $f1['nome']; ?></h2>

    <div class="area-comentarios">
    <?php
    if(isset($_POST['faculdade1']) && $_POST['faculdade1'] != ""){
        $sql = "SELECT comentarios.comentario, comentarios.data_comentario, usuarios.email
                FROM comentarios
                JOIN usuarios ON comentarios.usuario_id = usuarios.id
                WHERE comentarios.faculdade = ?
                ORDER BY comentarios.data_comentario DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $escolha1);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while($row = $resultado->fetch_assoc()):
    ?>
        <p>
            <strong><?php echo htmlspecialchars($row["email"]); ?></strong><br>
            <?php echo htmlspecialchars($row["comentario"]); ?><br>
            <small><?php echo $row["data_comentario"]; ?></small>
        </p>
    <?php endwhile; $stmt->close(); } ?>
    </div>

    <?php if(isset($_SESSION["usuario_id"]) && $escolha1 != "padrao"): ?>
    <div class="add-comentario">
        <form method="POST" style="display:flex; gap:10px; width:100%;">
            <input type="hidden" name="faculdade" value="<?php echo $escolha1; ?>">
            <input type="text" name="comentario" placeholder="Escreva..." required>
            <button type="submit" name="enviar_comentario">Enviar</button>
        </form>
    </div>
    <?php else: ?>
        <p style="color:#aaa;">Faça login e selecione uma faculdade.</p>
    <?php endif; ?>
</div>

    <div id="sidebar-dir" class="sidebar sidebar-direita glass-card">
    <button class="btn-fechar" onclick="toggleSidebar('sidebar-dir')">X</button>
    <h2>Comentários - <?php echo $f2['nome']; ?></h2>

    <div class="area-comentarios">
    <?php
    if(isset($_POST['faculdade2']) && $_POST['faculdade2'] != ""){
        $sql = "SELECT comentarios.comentario, comentarios.data_comentario, usuarios.email
                FROM comentarios
                JOIN usuarios ON comentarios.usuario_id = usuarios.id
                WHERE comentarios.faculdade = ?
                ORDER BY comentarios.data_comentario DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $escolha2);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while($row = $resultado->fetch_assoc()):
    ?>
        <p>
            <strong><?php echo htmlspecialchars($row["email"]); ?></strong><br>
            <?php echo htmlspecialchars($row["comentario"]); ?><br>
            <small><?php echo $row["data_comentario"]; ?></small>
        </p>
    <?php endwhile; $stmt->close(); } ?>
    </div>

    <?php if(isset($_SESSION["usuario_id"]) && $escolha2 != "padrao"): ?>
    <div class="add-comentario">
        <form method="POST" style="display:flex; gap:10px; width:100%;">
            <input type="hidden" name="faculdade" value="<?php echo $escolha2; ?>">
            <input type="text" name="comentario" placeholder="Escreva..." required>
            <button type="submit" name="enviar_comentario">Enviar</button>
        </form>
    </div>
    <?php else: ?>
        <p style="color:#aaa;">Faça login e selecione uma faculdade.</p>
    <?php endif; ?>
</div>

    <script>
        // Função para as sidebars laterais (mantida)
        function toggleSidebar(id) {
            const sidebar = document.getElementById(id);
            const estaAtiva = sidebar.classList.contains('ativa');
            document.querySelectorAll('.sidebar').forEach(s => s.classList.remove('ativa'));
            if (!estaAtiva) sidebar.classList.add('ativa');
        }

        // ALTERAÇÃO 3: NOVA função para o Menu Principal de Tela Cheia
        function toggleMainMenu() {
            const menuOverlay = document.getElementById('main-menu-overlay');
            menuOverlay.classList.toggle('ativa');

            // Opcional: Se abrir o menu principal, fecha as sidebars laterais para não bugar
            if (menuOverlay.classList.contains('ativa')) {
                document.querySelectorAll('.sidebar').forEach(s => s.classList.remove('ativa'));
            }
        }
    </script>
</body>
</html>