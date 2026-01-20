<?php
//lettura dati db
require 'connessione.php';

$pdo = new PDO($connString, $connUser, $connPass);

$pag_numero = 0;
$pag_voci = 15; 
$pag_offset = 0;
$pag_totali = 0;
$num_record = 0;

$msgErrore = [];

// dati base
try {
    $categorie = $pdo->query("SELECT * FROM categorie ORDER BY ordine")->fetchAll(PDO::FETCH_ASSOC);
    $allergeni = $pdo->query("SELECT * FROM allergeni")->fetchAll(PDO::FETCH_ASSOC);
    $caratteristiche = $pdo->query("SELECT * FROM caratteristiche")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msgErrore["lettura"] = $e->getMessage();
}

//suddivisione pagine
try{
    $sql = "SELECT COUNT(*) FROM prodotti";

    $stm = $pdo->prepare($sql);
    $stm->execute();
    $ris = $stm->fetchAll(PDO::FETCH_NUM);
    $num_record = $ris[0][0];
    
} catch (PDOException $e) {
    $msgErrore["record"] = $e->getMessage();
}

$pag_totali = intdiv($num_record, $pag_voci);
if (($num_record / $pag_voci) - intdiv($num_record, $pag_voci) > 0)
    $pag_totali++;

// Visualizzo la pagina scelta dall'utente.
if (isset($_GET['pag']) == true && is_numeric($_GET['pag']) == true && intval($_GET['pag']) > 0) {
    $pag_numero = intval($_GET['pag']);
    if ($pag_numero > $pag_totali)
        $pag_numero = $pag_totali;
    $pag_numero -= 1;
    $pag_offset = $pag_numero * $pag_voci;
}

//prodotti
try {
    $parametri = [];
 
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

        LEFT JOIN categorie cat ON p.id_categoria = cat.id_categoria
        
        WHERE 1=1";

    // filtro nome
    if (isset($_GET['nome']) && $_GET['nome'] !== '') {
        $sql .= " AND p.nome LIKE :nome";
        $parametri[':nome'] = '%' . $_GET['nome'] . '%';
    }

    // filtro prezzo minimo
    if (isset($_GET['prezzo_min']) && $_GET['prezzo_min'] !== '') {
        $sql .= " AND p.prezzo >= :prezzo_min";
        $parametri[':prezzo_min'] = $_GET['prezzo_min'];
    }

    // filtro prezzo massimo
    if (isset($_GET['prezzo_max']) && $_GET['prezzo_max'] !== '') {
        $sql .= " AND p.prezzo <= :prezzo_max";
        $parametri[':prezzo_max'] = $_GET['prezzo_max'];
    }

    // filtro disponibilità
    if (isset($_GET['disponibile']) && $_GET['disponibile'] !== '') {
        $sql .= " AND p.disponibile = :disp";
        $parametri[':disp'] = $_GET['disponibile'];
    }
 
    // filtro allergeni
    if (!empty($_GET['allergeni'])) {
        $placeholders = [];
        foreach ($_GET['allergeni'] as $i => $id) {
            $key = ":allergene$i";
            $placeholders[] = $key;
            $parametri[$key] = $id;
        }
        $sql .= " AND p.id_prodotto IN (
            SELECT pa.id_prodotto FROM prodotti_allergeni pa 
            WHERE pa.id_allergene IN (" . implode(',', $placeholders) . ")
        )";
    }
    
    //filtro caratteristiche
    if (!empty($_GET['caratteristiche'])) {
        $placeholders = [];
        foreach ($_GET['caratteristiche'] as $i => $id) {
            $key = ":car$i";
            $placeholders[] = $key;
            $parametri[$key] = $id;
        }
        $sql .= " AND p.id_prodotto IN (
            SELECT pc.id_prodotto FROM prodotti_caratteristiche pc 
            WHERE pc.id_caratteristica IN (" . implode(',', $placeholders) . ")
        )";
    }

    //filtro categorie
    if (!empty($_GET['categorie'])) {
        $placeholders = [];
        foreach ($_GET['categorie'] as $i => $id) {
            $key = ":cat$i";
            $placeholders[] = $key;
            $parametri[$key] = $id;
        }
        $sql .= " AND p.id_categoria IN (" . implode(',', $placeholders) . ")";
    }
    $sql .= " GROUP BY p.id_prodotto 
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
</head>

<body>

    <!-- Banner -->
    <div class="w3-container w3-light-blue w3-xlarge">
        <p>
            <i class="fa fa-paint-brush"></i>
            Gestione menu Pizzeria da Paggi
        </p>
    </div>

    <!-- Navbar -->
    <div class="w3-bar w3-light-grey">
        <a href="." class="w3-bar-item w3-button w3-green">
            <i class="fa fa-home"></i>
        </a>
        <a href="prodotto_form.php" class="w3-bar-item w3-button">
            <i class="fa fa-plus"></i> Inserisci
        </a>
    </div>

    <!-- Banner errore -->
    <?php if (!empty($msgErrore)): ?>
        <div class="w3-panel w3-red w3-display-container">
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

    <?php endif; ?>

    <!-- Form filtri -->
    <div class="w3-container w3-padding-32">
        <form method="GET" class="w3-margin-bottom">

            <div class="w3-row-padding">

                <div class="w3-third">
                    <input
                        class="w3-input w3-border w3-round"
                        type="text"
                        name="nome"
                        placeholder="Filtra per nome prodotto"
                        value="<?= $_GET['nome'] ?? '' ?>">
                </div>

                <div class="w3-third">
                    <input
                        class="w3-input w3-border w3-round"
                        type="number"
                        step="0.01"
                        name="prezzo_min"
                        placeholder="Prezzo minimo"
                        value="<?= $_GET['prezzo_min'] ?? '' ?>">
                </div>

                <div class="w3-third">
                    <input
                        class="w3-input w3-border w3-round"
                        type="number"
                        step="0.01"
                        name="prezzo_max"
                        placeholder="Prezzo massimo"
                        value="<?= $_GET['prezzo_max'] ?? '' ?>">
                </div>

            </div>

            <div class="w3-row-padding w3-margin-top">
                <h4>Filtra per allergeni</h4>
                <?php foreach ($allergeni as $a): ?>
                    <label>
                        <input
                            type="checkbox"
                            name="allergeni[]"
                            value="<?= $a['id_allergene']; ?>"
                            <?= (!empty($_GET['allergeni']) && in_array($a['id_allergene'], $_GET['allergeni'])) ? 'checked' : ''; ?>>
                        <?= $a['nome']; ?>
                    </label>
                <?php endforeach; ?>

                <h4>Filtra per caratteristiche</h4>
                <?php foreach ($caratteristiche as $c): ?>
                    <label>
                        <input
                            type="checkbox"
                            name="caratteristiche[]"
                            value="<?= $c['id_caratteristica']; ?>"
                            <?= (!empty($_GET['caratteristiche']) && in_array($c['id_caratteristica'], $_GET['caratteristiche'])) ? 'checked' : ''; ?>>
                        <?= $c['nome']; ?>
                    </label>
                <?php endforeach; ?>

                <h4>Filtra per categoria</h4>
                <?php foreach ($categorie as $c): ?>
                    <label>
                        <input
                            type="checkbox"
                            name="categorie[]"
                            value="<?= $c['id_categoria']; ?>"
                            <?= (!empty($_GET['categorie']) && in_array($c['id_categoria'], $_GET['categorie'])) ? 'checked' : ''; ?>>
                        <?= $c['nome']; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <select class="w3-select w3-border" name="disponibile">
                        <option value="">Disponibilità</option>
                        <option value="1" <?= ($_GET['disponibile'] ?? '') === '1' ? 'selected' : ''; ?>>
                            Disponibili
                        </option>
                        <option value="0" <?= ($_GET['disponibile'] ?? '') === '0' ? 'selected' : ''; ?>>
                            Non disponibili
                        </option>
                    </select>
                </div>
            </div>

            <button class="w3-button w3-teal w3-round w3-margin-top" type="submit">
                <i class="fa fa-filter"></i> Applica Filtri
            </button>

        </form>
    </div>

    <!-- Navigazione pagine gestore -->
    <?php $pagina = basename($_SERVER['PHP_SELF']); ?>

    <div class="w3-bar w3-light-grey w3-card w3-margin-bottom">

        <a href="index.php"
        class="w3-bar-item w3-button <?= $pagina == 'index.php' ? 'w3-green' : '' ?>">
            <i class="fa fa-pizza-slice"></i> Prodotti
        </a>

        <a href="categorie.php"
        class="w3-bar-item w3-button <?= $pagina == 'categorie.php' ? 'w3-green' : '' ?>">
            <i class="fa fa-list"></i> Categorie
        </a>

        <a href="allergeni.php"
        class="w3-bar-item w3-button <?= $pagina == 'allergeni.php' ? 'w3-green' : '' ?>">
            <i class="fa fa-exclamation-triangle"></i> Allergeni
        </a>

        <a href="caratteristiche.php"
        class="w3-bar-item w3-button <?= $pagina == 'caratteristiche.php' ? 'w3-green' : '' ?>">
            <i class="fa fa-leaf"></i> Caratteristiche
        </a>

    </div>

    <!-- Tabella prodotti -->
    <?php if ($numProdotti > 0): ?>
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-card-4">
                <thead>
                    <tr class="w3-teal">
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Immagine</th>
                        <th>Prezzo</th>
                        <th>Caratteristiche</th>
                        <th>Allergeni</th>
                        <th>Categoria</th>
                        <th>Disponibile</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti as $p): ?>
                        <tr>
                            <td><?= $p['id_prodotto']; ?></td>
                            <td><?= htmlspecialchars($p['nome']); ?></td>
                            <td><?= htmlspecialchars($p['descrizione']); ?></td>
                            <td><?= htmlspecialchars($p['immagine']); ?></td>
                            <td>€ <?= number_format($p['prezzo'], 2, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($p['caratteristiche']); ?></td>
                            <td><?= htmlspecialchars($p['allergeni']); ?></td>
                            <td><?= htmlspecialchars($p['categoria']); ?></td>
                            <td>
                                <?php if ($p['disponibile']): ?>
                                    <span class="w3-text-green" style="text-decoration: underline;">
                                        DISPONIBILE
                                    </span>
                                <?php else: ?>
                                    <span class="w3-text-red" style="text-decoration: underline;">
                                        NON DISPONIBILE
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="confermaElimina.php?id=<?= $p['id_prodotto']; ?>">
                                    <i class="fa fa-trash w3-text-red"></i>
                                </a>
                            </td>
                            <td>
                                <a href="prodotto_form.php?id=<?= $p['id_prodotto']; ?>">
                                    <i class="fa fa-edit w3-text-blue"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    

    <!-- Footer -->
    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top">
        <p class="w3-center">© 2026 Gestore menu Pizzeria da Paggi</p>
    </footer>

</body>
</html>
