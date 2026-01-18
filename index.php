<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);

$pag_numero = 0;
$msgErrore = 'nessun errore';


// Lettura dati dal db.
//categorie
try {
    $sql = 'SELECT * FROM categorie WHERE visibile = true ORDER BY ordine';
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $numCategorie = $stm->rowCount();

    if ($numCategorie == 0) {
        $msgErrore = 'Nessuna categoria trovata.';
    } else {
        $categorie = $stm->fetchAll(PDO::FETCH_ASSOC); 
        
        $msgErrore = 'nessun errore';
    }
} catch (PDOException $e) {
    $msgErrore = $e->getMessage();
}
// Visualizzo la pagina scelta dall'utente.
if (isset($_GET['pag']) == true && is_numeric($_GET['pag']) == true && intval($_GET['pag']) > 0&& intval($_GET['pag']) <= $numCategorie) {
    $pag_numero = intval($_GET['pag']);
}
if ($pag_numero === 0 && $numCategorie > 0) {
    $pag_numero = $categorie[0]['id_categoria'];
}

//allergeni
try {
    $sql = 'SELECT * FROM allergeni';
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $numAllergeni = $stm->rowCount();

    if ($numAllergeni == 0) {
        $msgErrore = 'Nessun allergene trovato.';
    } else {
        $allergeni = $stm->fetchAll(PDO::FETCH_ASSOC); 
        
        $msgErrore = 'nessun errore';
    }
} catch (PDOException $e) {
    $msgErrore = $e->getMessage();
}

//caratteristiche
try {
    $sql = 'SELECT * FROM caratteristiche';
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $numCaratteristiche = $stm->rowCount();

    if ($numCaratteristiche == 0) {
        $msgErrore = 'Nessuna caratteristica trovata.';
    } else {
        $caratteristiche = $stm->fetchAll(PDO::FETCH_ASSOC); 
        
        $msgErrore = 'nessun errore';
    }
} catch (PDOException $e) {
    $msgErrore = $e->getMessage();
}


//prodotti
try {
    $parametri = [':categoria' => $pag_numero];

    $sql = "
        SELECT 
            p.id_prodotto,
            p.nome,
            p.descrizione,
            p.prezzo,
            p.immagine,

            GROUP_CONCAT(DISTINCT a.nome SEPARATOR ', ') AS allergeni,
            GROUP_CONCAT(DISTINCT c.nome SEPARATOR ', ') AS caratteristiche

        FROM prodotti p

        LEFT JOIN prodotti_allergeni pa 
            ON p.id_prodotto = pa.id_prodotto
        LEFT JOIN allergeni a 
            ON pa.id_allergene = a.id_allergene

        LEFT JOIN prodotti_caratteristiche pc 
            ON p.id_prodotto = pc.id_prodotto
        LEFT JOIN caratteristiche c 
            ON pc.id_caratteristica = c.id_caratteristica

        WHERE p.id_categoria = :categoria
        AND p.disponibile = TRUE
        ";

    //filtro allergeni
    if (!empty($_GET['no_allergeni'])) {
        $placeholders = [];

        foreach ($_GET['no_allergeni'] as $i => $id) {
            $key = ":allergene$i";
            $placeholders[] = $key;
            $parametri[$key] = $id;
        }

        $sql .= "
        AND p.id_prodotto NOT IN (
            SELECT pa.id_prodotto
            FROM prodotti_allergeni pa
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

        $sql .= "
        AND p.id_prodotto IN (
            SELECT pc.id_prodotto
            FROM prodotti_caratteristiche pc
            WHERE pc.id_caratteristica IN (" . implode(',', $placeholders) . ")
        )";
    }
    $sql .= " GROUP BY p.id_prodotto";
    
    $stm = $pdo->prepare($sql);
    $stm->execute($parametri);
    $numProdotti = $stm->rowCount();

    if ($numProdotti == 0) {
        $msgErrore = 'Nessun prodotto trovato.';
    } else {
        $prodotti = $stm->fetchAll(PDO::FETCH_ASSOC); 
        $msgErrore = 'nessun errore';
    }
} catch (PDOException $e) {
    $msgErrore = $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Opere d'Arte</title>
    <link rel="icon" type="image/x-icon"
        href="data:image/png;base64,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">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <!-- Banner -->
    <div class="w3-container w3-light-blue w3-xlarge">
        <p><i class="fa fa-paint-brush"></i>MENU PIZZERIA DA PAGGI</p>
    </div>

    <div class="w3-bar w3-light-grey">
        <a href="." class="w3-bar-item w3-button w3-green"><i class="fa fa-home"></i></a>
        <a href="inserisci.php" class="w3-bar-item w3-button"><i class="fa fa-plus"></i> Inserisci</a>
    </div>

    <!-- Banner per l'errore -->
    <?php if ($msgErrore != 'nessun errore'): ?>
        <div class="w3-panel w3-red w3-display-container">
            <span onclick="this.parentElement.style.display='none'" 
                  class="w3-button w3-red w3-large w3-display-topright">&times;</span>
            <h3>Errore!</h3>
            <p><?= $msgErrore ?></p>
        </div>
    <?php else: ?>

    <!-- Visualizzazione bottoni scorrimento pagine -->
    <?php if ($numCategorie > 1): ?>
        <div class="w3-bar w3-small w3-center">
            <!-- Bottone Precedente -->
            <?php if ($pag_numero > 1): ?>
                <a href="?pag=<?= $pag_numero - 1?>" class="w3-button">← Prec</a>
            <?php else: ?>
                <div class="w3-button w3-disabled">← Prec</div>
            <?php endif ?>

            <!-- Numeri pagine (max 10) -->
            <?php for ($i = 0; $i <= min($numCategorie -1, 10); $i++): ?>
                <a href="?pag=<?= $i+1 ?>" class="w3-button <?php if ($i == $pag_numero-1) echo 'w3-red'; ?>">
                    <?= $categorie[$i]["nome"]?>
                </a>
            <?php endfor ?>

            <!-- Bottone Successivo -->
            <?php if ($pag_numero < $numCategorie): ?>
                <a href="?pag=<?= $pag_numero + 1 ?>" class="w3-button">Succ →</a>
            <?php else: ?>
                <div class="w3-button w3-disabled">Succ →</div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <!-- filtri -->
    <div>
        <form method="get" class="w3-container w3-padding w3-light-grey">

            <input type="hidden" name="pag" value="<?= $pag_numero ?>">

            <h4>Filtra per allergeni (escludi)</h4>
            <?php foreach($allergeni as $k => $a): ?>
                <label><input type="checkbox" name="no_allergeni[]" value="<?php echo $a["id_allergene"]; ?>"
                <?php if (!empty($_GET['no_allergeni']) && in_array($a["id_allergene"], $_GET['no_allergeni'])) echo 'checked'; ?>> 
                <?php echo $a["nome"]; ?> </label>
            <?php endforeach ?>

            <h4>Filtra per caratteristiche</h4>
            <?php foreach($caratteristiche as $k => $c): ?>
                <label><input type="checkbox" name="caratteristiche[]" value="<?php echo $c["id_caratteristica"]; ?>" 
                <?php if (!empty($_GET['caratteristiche']) && in_array($c["id_caratteristica"], $_GET['caratteristiche'])) echo 'checked'; ?>> 
                <?php echo $c["nome"]; ?> </label>
            <?php endforeach ?>

                
            

            <br><br>
            <button class="w3-button w3-green">Applica filtri</button>
            <a href="?pag=<?= $pag_numero ?>" class="w3-button w3-grey"> Rimuovi filtri </a>
        </form>
    </div>


    <?php if ($numProdotti > 0): ?>
<div class="mobile-cards w3-padding">

    <?php foreach ($prodotti as $p): ?>
        <div class="w3-card w3-white w3-padding w3-margin-bottom">

            <h4 class="w3-text-teal">
                <?= htmlspecialchars($p['nome']) ?>
            </h4>

            <p><?= htmlspecialchars($p['descrizione']) ?></p>

            <p class="w3-large">
                <strong>€ <?= number_format($p['prezzo'], 2, ',', '.') ?></strong>
            </p>

            <?php if (!empty($p['allergeni'])): ?>
                <p>
                    <span class="w3-tag w3-red">Allergeni</span><br>
                    <?= htmlspecialchars($p['allergeni']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($p['caratteristiche'])): ?>
                <p>
                    <span class="w3-tag w3-green">Caratteristiche</span><br>
                    <?= htmlspecialchars($p['caratteristiche']) ?>
                </p>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

</div>
<?php endif; ?>

    </div>

        <?php endif ?>

    </div>

    <!-- Footer -->
    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top footer">
        <p class="w3-center">© 2026 Menu - Pizzeria da Paggi</p>
    </footer>

</body>

</html>