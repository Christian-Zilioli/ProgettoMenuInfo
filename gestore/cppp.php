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
if ($pag_numero === 0 && $numCategorie > 0) {
    $pag_numero = $categorie[0]['id_categoria'];
}

// Visualizzo la pagina scelta dall'utente.
if (isset($_GET['pag']) == true && is_numeric($_GET['pag']) == true && intval($_GET['pag']) > 0) {
    $pag_numero = intval($_GET['pag']);
    if ($pag_numero > $numCategorie)
        $pag_numero = $numCategorie;
}

//prodotti
try {
    $params = [':categoria' => $pag_numero];

    $sql = 'SELECT * FROM prodotti p
        WHERE p.id_categoria = :categoria AND p.disponibile = true';

    //filtro allergeni
    if (!empty($_GET['no_allergeni'])) {
        $placeholders = [];

        foreach ($_GET['no_allergeni'] as $i => $id) {
            $key = ":allergene$i";
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $sql .= "
        AND p.id_prodotto NOT IN (
            SELECT pa.id_prodotto
            FROM prodotti_allergeni pa
            WHERE pa.id_allergene IN (" . implode(',', $placeholders) . ")
        )";
        // echo $sql;
        // echo $_GET['no_allergeni'];
        // var_dump($_GET);
    }
    //filtro caratteristiche
    if (!empty($_GET['caratteristiche'])) {
        $placeholders = [];

        foreach ($_GET['caratteristiche'] as $i => $id) {
            $key = ":car$i";
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $sql .= "
        AND p.id_prodotto IN (
            SELECT pc.id_prodotto
            FROM prodotti_caratteristiche pc
            WHERE pc.id_caratteristica IN (" . implode(',', $placeholders) . ")
        )";
    }

    
    $stm = $pdo->prepare($sql);
    $stm->execute($params);
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

    <div>
        <form method="get" class="w3-container w3-padding w3-light-grey">

            <input type="hidden" name="pag" value="<?= $pag_numero ?>">

            <h4>Filtra per allergeni (escludi)</h4>
            <label><input type="checkbox" name="no_allergeni[]" value="1"> Glutine</label>
            <label><input type="checkbox" name="no_allergeni[]" value="2"> Lattosio</label>

            <h4>Filtra per caratteristiche</h4>
            <label><input type="checkbox" name="caratteristiche[]" value="1"> Vegano</label>
            <label><input type="checkbox" name="caratteristiche[]" value="2"> Vegetariano</label>

            <br><br>
            <button class="w3-button w3-green">Applica filtri</button>
        </form>
    </div>

    <!-- Table -->
    <?php if ($numProdotti > 0): ?>
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-card-4">
                <thead>
                    <tr class="w3-teal">
                        <th><i class="fa fa-hashtag"></i> ID</th>
                        <th><i class="fa fa-align-left"></i> Nome</th>
                        <th><i class="fa fa-map-marker"></i> Descrizione</th>
                        <th><i class="fa fa-euro"></i> Prezzo</th>
                        <th><i class="fa fa-img"></i> Immagine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti as $p): ?>
                        <tr>
                            <td><?= $p["id_prodotto"] ?></td>
                            <td><?= htmlspecialchars($p["nome"]) ?></td>
                            <td><?= htmlspecialchars($p["descrizione"]) ?></td>
                            <td>€ <?= number_format($p["prezzo"], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($p["immagine"]) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>

    </div>

        <?php endif ?>

    </div>

    <!-- Footer -->
    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top footer">
        <p class="w3-center">© 2025 Gestione Opere d'Arte</p>
    </footer>

</body>

</html>