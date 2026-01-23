<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);

$pag_numero = 0;
$pag_voci = 15; 
$pag_offset = 0;
$pag_totali = 0;
$num_record = 0;
$parametri = [];
$msgErrore = [];

$sqlWhere = " WHERE 1=1";
if (isset($_GET['nome']) && $_GET['nome'] !== '') {
    $sqlWhere .= " AND nome LIKE :nome";
    $parametri[':nome'] = '%' . $_GET['nome'] . '%';
}

try {
    $sqlCount = "SELECT COUNT(*) FROM caratteristiche" . $sqlWhere;
    $stm = $pdo->prepare($sqlCount);
    $stm->execute($parametri);
    $num_record = $stm->fetchColumn(); 
} catch (PDOException $e) {
    $msgErrore["record"] = $e->getMessage();
}

$pag_totali = ceil($num_record / $pag_voci);
if (isset($_GET['pag']) && is_numeric($_GET['pag']) && intval($_GET['pag']) > 0) {
    $pag_numero = intval($_GET['pag']);
    if ($pag_numero > $pag_totali) $pag_numero = max(1, $pag_totali);
    $pag_offset = ($pag_numero - 1) * $pag_voci;
    $pag_numero -= 1;
}

try {
    $sql = "SELECT * FROM caratteristiche" . $sqlWhere . " LIMIT :voci OFFSET :offset";
    $stm = $pdo->prepare($sql);
    foreach ($parametri as $k => $v) { $stm->bindValue($k, $v); }   
    $stm->bindValue(':voci', (int)$pag_voci, PDO::PARAM_INT);
    $stm->bindValue(':offset', (int)$pag_offset, PDO::PARAM_INT);
    $stm->execute();
    $numCaratteristiche = $stm->rowCount();
    if ($numCaratteristiche == 0) {
        $msgErrore["caratteristiche"] = 'Nessuna caratteristica trovata.';
    } else {
        $caratteristiche = $stm->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $msgErrore["caratteristiche"] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Caratteristiche | Pizzeria da Paggi</title>
    <link rel="icon" type="image/x-icon" href="data:image/png;base64,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">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body { background-color: #f7f3ee; color: #333; }
        .bg-pizza { background-color: #b22222 !important; color: white; }
        .bg-basil { background-color: #2e7d32 !important; color: white; }
        .bg-cream { background-color: #fffaf2; }
        .text-pizza { color: #b22222; }
        .w3-button.bg-basil:hover { background-color: #1b5e20 !important; }
    </style>
</head>

<body>
    <div class="w3-container bg-pizza w3-padding-16">
        <h2 class="w3-xlarge w3-margin-0">
            <i class="fa fa-leaf"></i> Gestione Caratteristiche Pizzeria da Paggi
        </h2>
    </div>

    <?php $pagina = basename($_SERVER['PHP_SELF']); ?>
    <div class="w3-bar w3-white w3-card w3-margin-bottom">
        <a href="index.php" class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'index.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-pizza-slice"></i> Prodotti
        </a>
        <a href="categorie.php" class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'categorie.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-list"></i> Categorie
        </a>
        <a href="allergeni.php" class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'allergeni.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-exclamation-triangle"></i> Allergeni
        </a>
        <a href="caratteristiche.php" class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'caratteristiche.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-leaf"></i> Caratteristiche
        </a>
        <div class="w3-right">
            <a href="gestione_caratteristiche.php" class="w3-bar-item w3-button bg-basil w3-padding-16">
                <i class="fa fa-plus"></i> Inserisci Caratteristica
            </a>
        </div>
    </div>

    <?php if (!empty($msgErrore)): ?>
        <div class="w3-container">
            <div class="w3-panel w3-red w3-display-container w3-round">
                <span onclick="this.parentElement.style.display='none'" class="w3-button w3-red w3-large w3-display-topright">&times;</span>
                <h3>Errore!</h3>
                <?php foreach($msgErrore as $query => $err ): ?>
                    <p><?= $query .' - '. $err; ?></p>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>

    <div class="w3-content" style="max-width:1200px">
        
        <div class="w3-container w3-padding-16 bg-cream w3-card w3-round-large w3-margin">
            <form method="GET">
                <h4 class="text-pizza w3-border-bottom w3-padding-16"><i class="fa fa-search"></i> Filtra Caratteristiche</h4>
                <div class="w3-row-padding">
                    <div class="w3-half">
                        <label>Nome Caratteristica</label>
                        <input class="w3-input w3-border w3-round" type="text" name="nome" placeholder="Es: Vegano, Bio..." value="<?= $_GET['nome'] ?? '' ?>">
                    </div>
                </div>
                <button class="w3-button bg-basil w3-round w3-margin-top" type="submit">
                    <i class="fa fa-filter"></i> Applica Filtro
                </button>
            </form>
        </div>

        <div class="w3-container">
            <div class="w3-panel w3-white w3-card w3-border-left w3-border-red">
                <p>
                    <?php if ($num_record > 0): ?>
                        <i class="fa fa-info-circle text-pizza"></i> Nell'archivio sono presenti <strong><?= $num_record ?></strong> caratteristiche.
                    <?php else: ?>
                        <i class="fa fa-exclamation-triangle w3-text-red"></i> Non ci sono caratteristiche memorizzate.
                    <?php endif ?>
                </p>
            </div>
        </div>

        <?php if ($pag_totali > 1): ?>
            <div class="w3-center w3-padding">
                <div class="w3-bar w3-border w3-white w3-round">
                    <?php if ($pag_numero > 0): ?>
                        <a href="?pag=<?= $pag_numero ?><?php foreach($_GET as $k=>$v){if($k!='pag') echo '&'.urlencode($k).'='.urlencode($v);} ?>" class="w3-button">←</a>
                    <?php endif ?>

                    <?php for ($i = 1; $i <= min($pag_totali, 10); $i++): ?>
                        <a href="?pag=<?= $i ?><?php foreach($_GET as $k=>$v){if($k!='pag') echo '&'.urlencode($k).'='.urlencode($v);} ?>" class="w3-button <?php if ($i == $pag_numero + 1) echo 'bg-pizza'; ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor ?>

                    <?php if ($pag_numero < $pag_totali - 1): ?>
                        <a href="?pag=<?= $pag_numero + 2 ?><?php foreach($_GET as $k=>$v){if($k!='pag') echo '&'.urlencode($k).'='.urlencode($v);} ?>" class="w3-button">→</a>
                    <?php endif ?>
                </div>
            </div>
        <?php endif ?>

        <?php if ($numCaratteristiche > 0): ?>
            <div class="w3-container w3-margin-bottom">
                <div class="w3-responsive w3-card-4">
                    <table class="w3-table-all w3-hoverable">
                        <thead>
                            <tr class="bg-pizza">
                                <th>ID</th>
                                <th>Nome</th>
                                <th colspan="2" class="w3-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($caratteristiche as $c): ?>
                                <tr>
                                    <td><?= $c["id_caratteristica"] ?></td>
                                    <td><strong><?= htmlspecialchars($c["nome"]) ?></strong></td>
                                    <td class="w3-center">
                                        <a href="gestione_caratteristiche.php?id=<?= $c["id_caratteristica"] ?>" title="Modifica">
                                            <i class="fa fa-edit w3-text-blue"></i>
                                        </a>
                                    </td>
                                    <td class="w3-center">
                                        <a href="elimina.php?id=<?= $c["id_caratteristica"] ?>&tabella=caratteristiche" title="Elimina" onclick="return confirm('Vuoi eliminare questa caratteristica?')">
                                            <i class="fa fa-trash w3-text-red"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>

    </div>

    <footer class="w3-container bg-pizza w3-padding-32 w3-margin-top">
        <p class="w3-center w3-margin-0">© 2026 Gestore menu Pizzeria da Paggi</p>
    </footer>

</body>
</html>