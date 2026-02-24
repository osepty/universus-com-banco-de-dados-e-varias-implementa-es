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

// Busca Faculdade 1 com a média calculada dinamicamente
if(isset($_POST['faculdade1']) && $_POST['faculdade1'] != ""){
    $id1 = $_POST['faculdade1'];

    $sql1 = "SELECT i.*, 
                    COUNT(a.nota) AS total_votos, 
                    COALESCE(AVG(a.nota), 0) AS media_notas 
             FROM instituicao i 
             LEFT JOIN avaliacoes a ON i.id = a.faculdade_id 
             WHERE i.id = ? 
             GROUP BY i.id";
             
    $stmt = $conn->prepare($sql1);
    $stmt->bind_param("i", $id1);
    $stmt->execute();
    $f1 = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Busca Faculdade 2 com a média calculada dinamicamente
if(isset($_POST['faculdade2']) && $_POST['faculdade2'] != ""){
    $id2 = $_POST['faculdade2'];

    $sql2 = "SELECT i.*, 
                    COUNT(a.nota) AS total_votos, 
                    COALESCE(AVG(a.nota), 0) AS media_notas 
             FROM instituicao i 
             LEFT JOIN avaliacoes a ON i.id = a.faculdade_id 
             WHERE i.id = ? 
             GROUP BY i.id";
             
    $stmt = $conn->prepare($sql2);
    $stmt->bind_param("i", $id2);
    $stmt->execute();
    $f2 = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Lógica para salvar a avaliação - AJUSTADA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['votar']) && isset($_SESSION['usuario_id'])) {
    
    // Verificamos se a nota e o ID da faculdade foram realmente enviados
    if (isset($_POST['nota']) && isset($_POST['faculdade_id'])) {
        $usuario_id = $_SESSION['usuario_id'];
        $faculdade_id = $_POST['faculdade_id'];
        $nota = $_POST['nota'];

        $sql = "INSERT INTO avaliacoes (usuario_id, faculdade_id, nota) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE nota = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $usuario_id, $faculdade_id, $nota, $nota);
        
        if($stmt->execute()){
            $stmt->close();
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    }
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
<?php if (isset($_SESSION['usuario_id']) && $f1): ?>
    <div class="container-estrelas">
    <p style="font-size: 0.8em; color: #ccc; margin-bottom: 5px;">Avalie esta instituição</p>
    
    <div class="rating">
        <input type="radio" name="nota" value="5" id="f1-5" onchange="this.form.submit()"><label for="f1-5"></label>
        <input type="radio" name="nota" value="4" id="f1-4" onchange="this.form.submit()"><label for="f1-4"></label>
        <input type="radio" name="nota" value="3" id="f1-3" onchange="this.form.submit()"><label for="f1-3"></label>
        <input type="radio" name="nota" value="2" id="f1-2" onchange="this.form.submit()"><label for="f1-2"></label>
        <input type="radio" name="nota" value="1" id="f1-1" onchange="this.form.submit()"><label for="f1-1"></label>
    </div>

    <input type="hidden" name="faculdade_id" value="<?php echo $f1['id']; ?>">
    <input type="hidden" name="votar" value="1">
</div>
<?php endif; ?>
            <div class="detalhes">
    <?php if ($f1 && $f1['total_votos'] > 0): ?>
        <?php 
            $media = round($f1['media_notas'], 1); // Usando a nova coluna do SQL
            $votos = $f1['total_votos'];
            $votos_txt = ($votos >= 1000) ? round($votos/1000, 1)."K" : $votos;
        ?>
        <div class="rating-display" style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
            <div class="stars-fixed" style="color: #ff6600; font-size: 1.3em; letter-spacing: -2px;">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= round($media)) ? '★' : '<span style="color: #444;">★</span>';
                }
                ?>
            </div>
            <span style="font-size: 14px; color: #fff;">
                Classificação média: <strong><?php echo $media; ?></strong> <span style="color: #aaa;">(<?php echo $votos_txt; ?>)</span>
            </span>
        </div>
    <?php else: ?>
        <p style="font-size: 13px; color: #aaa; margin-bottom: 15px;">Nenhuma avaliação ainda.</p>
    <?php endif; ?>

    <p>⭐ Nota ENADE: <strong><?php echo $f1 ? $f1['nota_enade'] : "-"; ?></strong></p>
    <p>🏛 Nota MEC: <strong><?php echo $f1 ? $f1['nota_mec'] : "-"; ?></strong></p>
    <p>💰 Mensalidade: <strong><?php echo $f1 ? "R$ " . number_format($f1['mensalidade_media'], 2, ',', '.') : "-"; ?></strong></p>
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
<?php if (isset($_SESSION['usuario_id']) && $f2): ?>
    <div class="container-estrelas">
        <p style="font-size: 0.8em; color: #ccc; margin-bottom: 5px;">Avalie esta instituição</p>
        <form method="POST" class="rating-form">
            <input type="hidden" name="faculdade_id" value="<?php echo $f2['id']; ?>">
            <div class="rating">
                <input type="radio" name="nota" value="5" id="f2-5" onchange="this.form.submit()"><label for="f2-5"></label>
                <input type="radio" name="nota" value="4" id="f2-4" onchange="this.form.submit()"><label for="f2-4"></label>
                <input type="radio" name="nota" value="3" id="f2-3" onchange="this.form.submit()"><label for="f2-3"></label>
                <input type="radio" name="nota" value="2" id="f2-2" onchange="this.form.submit()"><label for="f2-2"></label>
                <input type="radio" name="nota" value="1" id="f2-1" onchange="this.form.submit()"><label for="f2-1"></label>
            </div>
            <input type="hidden" name="votar" value="1">
        </form>
    </div>
<?php endif; ?>
            <div class="detalhes">
    <?php if ($f2 && $f2['total_votos'] > 0): ?>
        <?php 
            $media = round($f2['media_notas'], 1); 
            $votos = $f2['total_votos'];
            $votos_txt = ($votos >= 1000) ? round($votos/1000, 1)."K" : $votos;
        ?>
        <div class="rating-display" style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
            <div class="stars-fixed" style="color: #ff6600; font-size: 1.3em; letter-spacing: -2px;">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= round($media)) ? '★' : '<span style="color: #444;">★</span>';
                }
                ?>
            </div>
            <span style="font-size: 14px; color: #fff;">
                Classificação média: <strong><?php echo $media; ?></strong> <span style="color: #aaa;">(<?php echo $votos_txt; ?>)</span>
            </span>
        </div>
    <?php else: ?>
        <p style="font-size: 13px; color: #aaa; margin-bottom: 15px;">Nenhuma avaliação ainda.</p>
    <?php endif; ?>

    <p>⭐ Nota ENADE: <strong><?php echo $f2 ? $f2['nota_enade'] : "-"; ?></strong></p>
    <p>🏛 Nota MEC: <strong><?php echo $f2 ? $f2['nota_mec'] : "-"; ?></strong></p>
    <p>💰 Mensalidade: <strong><?php echo $f2 ? "R$ " . number_format($f2['mensalidade_media'], 2, ',', '.') : "-"; ?></strong></p>
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
            
            <a href="universus.php" class="glass-card menu-btn-grande">Universus</a>
        </div>
        
        <div class="menu-footer">U</div>
    </div>


    <div id="sidebar-esq" class="sidebar sidebar-esquerda glass-card">
    <button class="btn-fechar" onclick="toggleSidebar('sidebar-esq')">X</button>
    <h2>Comentários - <?php echo $f1 ? $f1['nome'] : "Selecione uma faculdade"; ?></h2>

    <div class="area-comentarios">
    <?php
    if(isset($_POST['faculdade1']) && $_POST['faculdade1'] != ""){
        $sql = "SELECT comentarios.comentario, comentarios.data_comentario, usuarios.email
                FROM comentarios
                JOIN usuarios ON comentarios.usuario_id = usuarios.id
                WHERE comentarios.faculdade = ?
                ORDER BY comentarios.data_comentario DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $f1['id']);
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

    <?php if(isset($_SESSION["usuario_id"]) && $f1): ?>
    <div class="add-comentario">
        <form method="POST" style="display:flex; gap:10px; width:100%;">
            <input type="hidden" name="faculdade" value="<?php echo $f1['id']; ?>">
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
    
    <h2>Comentários - <?php echo $f2 ? htmlspecialchars($f2['nome']) : "Selecione uma faculdade"; ?></h2>

    <div class="area-comentarios">
    <?php
    if(isset($f2) && $f2 != null){ // Usamos a variável $f2 que já foi carregada no topo
        $sql = "SELECT comentarios.comentario, comentarios.data_comentario, usuarios.email
                FROM comentarios
                JOIN usuarios ON comentarios.usuario_id = usuarios.id
                WHERE comentarios.faculdade = ?
                ORDER BY comentarios.data_comentario DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $f2['id']); // "i" de integer, pois o ID costuma ser número
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

    <?php if(isset($_SESSION["usuario_id"]) && $f2): ?>
    <div class="add-comentario">
        <form method="POST" style="display:flex; gap:10px; width:100%;">
            <input type="hidden" name="faculdade" value="<?php echo $f2['id']; ?>">
            <input type="text" name="comentario" placeholder="Escreva..." required>
            <button type="submit" name="enviar_comentario">Enviar</button>
        </form>
    </div>
    <?php else: ?>
        <p style="color:#aaa;">Faça login e selecione a faculdade da direita.</p>
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
