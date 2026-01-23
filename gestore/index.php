<?php
require 'connessione.php';

$pdo = new PDO($connString, $connUser, $connPass);

$pag_numero = 0;
$pag_voci = 15;
$pag_offset = 0;
$pag_totali = 0;
$num_record = 0;
$msgErrore = [];

//dati db
try {
    $categorie = $pdo->query("SELECT * FROM categorie ORDER BY ordine")->fetchAll(PDO::FETCH_ASSOC);
    $allergeni = $pdo->query("SELECT * FROM allergeni")->fetchAll(PDO::FETCH_ASSOC);
    $caratteristiche = $pdo->query("SELECT * FROM caratteristiche")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msgErrore["lettura"] = $e->getMessage();
}

// filtri
$parametri = [];
$sqlWhere = " WHERE 1=1"; 

if (isset($_GET['nome']) && $_GET['nome'] !== '') {
    $sqlWhere .= " AND p.nome LIKE :nome";
    $parametri[':nome'] = '%' . $_GET['nome'] . '%';
}

if (isset($_GET['prezzo_min']) && $_GET['prezzo_min'] !== '') {
    $sqlWhere .= " AND p.prezzo >= :prezzo_min";
    $parametri[':prezzo_min'] = $_GET['prezzo_min'];
}

if (isset($_GET['prezzo_max']) && $_GET['prezzo_max'] !== '') {
    $sqlWhere .= " AND p.prezzo <= :prezzo_max";
    $parametri[':prezzo_max'] = $_GET['prezzo_max'];
}

if (isset($_GET['disponibile']) && $_GET['disponibile'] !== '') {
    $sqlWhere .= " AND p.disponibile = :disp";
    $parametri[':disp'] = $_GET['disponibile'];
}

if (!empty($_GET['allergeni'])) {
    $placeholders = [];
    foreach ($_GET['allergeni'] as $i => $id) {
        $key = ":allergene$i";
        $placeholders[] = $key;
        $parametri[$key] = $id;
    }
    $sqlWhere .= " AND p.id_prodotto IN (
        SELECT pa.id_prodotto FROM prodotti_allergeni pa 
        WHERE pa.id_allergene IN (" . implode(',', $placeholders) . ")
    )";
}

if (!empty($_GET['caratteristiche'])) {
    $placeholders = [];
    foreach ($_GET['caratteristiche'] as $i => $id) {
        $key = ":car$i";
        $placeholders[] = $key;
        $parametri[$key] = $id;
    }
    $sqlWhere .= " AND p.id_prodotto IN (
        SELECT pc.id_prodotto FROM prodotti_caratteristiche pc 
        WHERE pc.id_caratteristica IN (" . implode(',', $placeholders) . ")
    )";
}

if (!empty($_GET['categorie'])) {
    $placeholders = [];
    foreach ($_GET['categorie'] as $i => $id) {
        $key = ":cat$i";
        $placeholders[] = $key;
        $parametri[$key] = $id;
    }
    $sqlWhere .= " AND p.id_categoria IN (" . implode(',', $placeholders) . ")";
}
// suddivisione pagine
try{
    $sqlCount = "SELECT COUNT(*) FROM prodotti p " . $sqlWhere;

    $stm = $pdo->prepare($sqlCount);
    $stm->execute($parametri);
    $num_record = $stm->fetchColumn(); 
    
} catch (PDOException $e) {
    $msgErrore["record"] = $e->getMessage();
}
$pag_totali = intdiv($num_record, $pag_voci);
if (($num_record % $pag_voci) > 0)
    $pag_totali++;

// Calcolo offset
if (isset($_GET['pag']) && is_numeric($_GET['pag']) && intval($_GET['pag']) > 0) {
    $pag_numero = intval($_GET['pag']);
    
    if ($pag_numero > $pag_totali) $pag_numero = $pag_totali;
        
    $pag_numero -= 1; 
} else {
    $pag_numero = 0;
}

if ($pag_numero < 0) $pag_numero = 0; 
$pag_offset = $pag_numero * $pag_voci;

try {
    $sql = "SELECT
            p.id_prodotto, 
            p.nome, 
            p.descrizione, 
            p.prezzo, 
            p.immagine, 
            p.disponibile, 
            cat.nome AS categoria,
            GROUP_CONCAT(DISTINCT a.nome SEPARATOR ', ') AS allergeni,
            GROUP_CONCAT(DISTINCT c.nome SEPARATOR ', ') AS caratteristiche

        FROM prodotti p
        LEFT JOIN prodotti_allergeni pa ON p.id_prodotto = pa.id_prodotto
        LEFT JOIN allergeni a ON pa.id_allergene = a.id_allergene

        LEFT JOIN prodotti_caratteristiche pc ON p.id_prodotto = pc.id_prodotto
        LEFT JOIN caratteristiche c ON pc.id_caratteristica = c.id_caratteristica

        LEFT JOIN categorie cat ON p.id_categoria = cat.id_categoria"
        
        . $sqlWhere . 

        " GROUP BY p.id_prodotto 
        LIMIT :voci OFFSET :offset";
   
    $stm = $pdo->prepare($sql);
    
    foreach ($parametri as $k => $v) { $stm->bindValue($k, $v); }   
    $stm->bindValue(':voci', (int)$pag_voci, PDO::PARAM_INT);
    $stm->bindValue(':offset', (int)$pag_offset, PDO::PARAM_INT);

    $stm->execute();
    $prodotti = $stm->fetchAll(PDO::FETCH_ASSOC);
    $numProdotti = count($prodotti);

    if ($numProdotti == 0) {
        $msgErrore["prodotti"] = 'Nessun prodotto trovato.';
    }

} catch (PDOException $e) {
    $msgErrore["prodotti"] = $e->getMessage();
}


?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione menu Pizzeria da Paggi</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            background-color: #f7f3ee;
            color: #333;
        }
        .bg-pizza {
            background-color: #b22222 !important;
            color: white;
        }
        .bg-basil {
            background-color: #2e7d32 !important;
            color: white;
        }
        .bg-cream {
            background-color: #fffaf2;
        }
        .text-pizza {
            color: #b22222;
        }
        .w3-button.bg-basil:hover {
            background-color: #1b5e20 !important;
        }
    </style>
</head>

<body>

    <div class="w3-container bg-pizza w3-padding-16">
        <h2 class="w3-xlarge w3-margin-0">
            <i class="fa fa-pizza-slice"></i>
            Gestione menu Pizzeria da Paggi
        </h2>
    </div>

    <!-- navigazione pagine gestore -->
    <?php $pagina = basename($_SERVER['PHP_SELF']); ?>
    <div class="w3-bar w3-white w3-card w3-margin-bottom">
        <a href="index.php"
        class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'index.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-home"></i> Prodotti
        </a>

        <a href="categorie.php"
        class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'categorie.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-list"></i> Categorie
        </a>

        <a href="allergeni.php"
        class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'allergeni.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-exclamation-triangle"></i> Allergeni
        </a>

        <a href="caratteristiche.php"
        class="w3-bar-item w3-button w3-padding-16 <?= $pagina == 'caratteristiche.php' ? 'w3-border-bottom w3-border-red text-pizza' : '' ?>">
            <i class="fa fa-leaf"></i> Caratteristiche
        </a>

        <div class="w3-right    bg-cream w3-card">
            <a href="gestione_prodotto.php" class="w3-bar-item w3-button w3-padding-16">
                <i class="fa fa-plus"></i> Inserisci
            </a>
            <a href="qr.php" class="w3-bar-item w3-button bg-basil w3-padding-16">
                <i class="fa fa-qrcode"></i> QR Tavoli
            </a>
        </div>
    </div>

    <!-- banner errore -->
    <?php if (!empty($msgErrore)): ?>
        <div class="w3-container">
            <div class="w3-panel w3-red w3-display-container w3-round">
                <span
                    onclick="this.parentElement.style.display='none'"
                    class="w3-button w3-red w3-large w3-display-topright">
                    &times;
                </span>

                <h3>Errore!</h3>

                <?php foreach ($msgErrore as $query => $err): ?>
                    <p><?= $query . ' - ' . $err; ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- filtri -->
    <div class="w3-content" style="max-width:1200px">

        <div class="w3-container w3-padding-16 bg-cream w3-card w3-round-large w3-margin">
            <form method="GET">
                <h4 class="text-pizza w3-border-bottom w3-padding-16"><i class="fa fa-search"></i> Filtra Ricerca</h4>
                <div class="w3-row-padding">
                    <div class="w3-third">
                        <label>Nome</label>
                        <input
                            class="w3-input w3-border w3-round"
                            type="text"
                            name="nome"
                            placeholder="Prodotto..."
                            value="<?= $_GET['nome'] ?? '' ?>">
                    </div>

                    <div class="w3-third">
                        <label>Prezzo Min</label>
                        <input
                            class="w3-input w3-border w3-round"
                            type="number"
                            step="0.01"
                            name="prezzo_min"
                            value="<?= $_GET['prezzo_min'] ?? '' ?>">
                    </div>

                    <div class="w3-third">
                        <label>Prezzo Max</label>
                        <input
                            class="w3-input w3-border w3-round"
                            type="number"
                            step="0.01"
                            name="prezzo_max"
                            value="<?= $_GET['prezzo_max'] ?? '' ?>">
                    </div>
                </div>

                <div class="w3-row-padding w3-margin-top">
                    <div class="w3-third">
                        <h5>Allergeni</h5>
                        <div class="w3-white w3-padding w3-border w3-round" style="max-height:100px; overflow-y:auto">
                            <?php foreach ($allergeni as $a): ?>
                                <label class="w3-show-block">
                                    <input
                                        type="checkbox"
                                        name="allergeni[]"
                                        value="<?= $a['id_allergene']; ?>"
                                        <?= (!empty($_GET['allergeni']) && in_array($a['id_allergene'], $_GET['allergeni'])) ? 'checked' : ''; ?>>
                                    <?= $a['nome']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="w3-third">
                        <h5>Caratteristiche</h5>
                        <div class="w3-white w3-padding w3-border w3-round" style="max-height:100px; overflow-y:auto">
                            <?php foreach ($caratteristiche as $c): ?>
                                <label class="w3-show-block">
                                    <input
                                        type="checkbox"
                                        name="caratteristiche[]"
                                        value="<?= $c['id_caratteristica']; ?>"
                                        <?= (!empty($_GET['caratteristiche']) && in_array($c['id_caratteristica'], $_GET['caratteristiche'])) ? 'checked' : ''; ?>>
                                    <?= $c['nome']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="w3-third">
                        <h5>Categorie</h5>
                        <div class="w3-white w3-padding w3-border w3-round" style="max-height:100px; overflow-y:auto">
                            <?php foreach ($categorie as $c): ?>
                                <label class="w3-show-block">
                                    <input
                                        type="checkbox"
                                        name="categorie[]"
                                        value="<?= $c['id_categoria']; ?>"
                                        <?= (!empty($_GET['categorie']) && in_array($c['id_categoria'], $_GET['categorie'])) ? 'checked' : ''; ?>>
                                    <?= $c['nome']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="w3-row-padding w3-margin-top">
                    <div class="w3-third">
                        <select class="w3-select w3-border w3-round" name="disponibile">
                            <option value="">Tutte le disponibilità</option>
                            <option value="1" <?= ($_GET['disponibile'] ?? '') === '1' ? 'selected' : ''; ?>>Disponibili</option>
                            <option value="0" <?= ($_GET['disponibile'] ?? '') === '0' ? 'selected' : ''; ?>>Non disponibili</option>
                        </select>
                    </div>
                    <div class="w3-twothird w3-right-align">
                         <button class="w3-button bg-basil w3-round" type="submit">
                            <i class="fa fa-filter"></i> Applica Filtri
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="w3-container">
            <div class="w3-panel w3-white w3-card w3-border-left w3-border-red">
                <p>
                    <?php if ($num_record > 0): ?>
                        <i class="fa fa-info-circle text-pizza"></i> Nell'archivio sono presenti <strong><?= $num_record ?></strong> prodotti.
                    <?php else: ?>
                        <i class="fa fa-exclamation-triangle w3-text-red"></i> Non ci sono prodotti corrispondenti
                    <?php endif ?>
                </p>
            </div>
        </div>

        <!-- scorrimento pagine -->
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

        <?php if ($numProdotti > 0): ?>
            <div class="w3-container w3-margin-bottom">
                <div class="w3-responsive w3-card-4">
                    <table class="w3-table-all w3-hoverable">
                        <thead>
                            <tr class="bg-pizza">
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Descrizione</th>
                                <!-- <th>Immagine</th> -->
                                <th>Prezzo</th>
                                <th>Caratteristiche</th>
                                <th>Allergeni</th>
                                <th>Categoria</th>
                                <th>Disponibile</th>
                                <th colspan="2" class="w3-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prodotti as $p): ?>
                                <tr>
                                    <td><?= $p['id_prodotto']; ?></td>
                                    <td><strong><?= htmlspecialchars($p['nome']); ?></strong></td>
                                    <td><small><?= htmlspecialchars($p['descrizione']); ?></small></td>
                                    <!-- <td><small><?= htmlspecialchars($p['immagine']); ?></small></td> -->
                                    <td class="text-pizza"><strong>€ <?= number_format($p['prezzo'], 2, ',', '.'); ?></strong></td>
                                    <td><small><?= htmlspecialchars($p['caratteristiche']); ?></small></td>
                                    <td><small><?= htmlspecialchars($p['allergeni']); ?></small></td>
                                    <td><span class="w3-tag w3-light-grey w3-border w3-round"><?= htmlspecialchars($p['categoria']); ?></span></td>
                                    <td>
                                        <?php if ($p['disponibile']): ?>
                                            <span class="w3-text-green w3-bold">SI</span>
                                        <?php else: ?>
                                            <span class="w3-text-red w3-bold">NO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="w3-center">
                                        <a href="gestione_prodotto.php?id=<?= $p['id_prodotto']; ?>" title="Modifica">
                                            <i class="fa fa-edit w3-text-blue"></i>
                                        </a>
                                    </td>
                                    <td class="w3-center">
                                        <a href="elimina.php?id=<?= $p['id_prodotto']; ?>&tabella=prodotti">
                                            <i class="fa fa-trash w3-text-red"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <footer class="w3-container bg-pizza w3-padding-32 w3-margin-top">
        <p class="w3-center w3-margin-0">© 2026 Gestore menu Pizzeria da Paggi</p>
        <p class="w3-center w3-small w3-opacity w3-margin-0">Qualità e tradizione sulla tua tavola</p>
    </footer>

</body>
</html>
