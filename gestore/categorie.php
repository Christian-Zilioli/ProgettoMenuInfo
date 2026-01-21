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

//filtri
$sqlWhere = " WHERE 1=1";
if (isset($_GET['nome']) && $_GET['nome'] !== '') {
    $sqlWhere .= " AND nome LIKE :nome";
    $parametri[':nome'] = '%' . $_GET['nome'] . '%';
}
if (isset($_GET['visibile']) && $_GET['visibile'] !== '') {
    $sqlWhere .= " AND visibile = :vis";
    $parametri[':vis'] = $_GET['visibile'];
}

//divisione pagine
try {
    $sqlCount = "SELECT COUNT(*) FROM categorie" . $sqlWhere;
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
    $sql = "SELECT * FROM categorie" . $sqlWhere . " ORDER BY ordine LIMIT :voci OFFSET :offset";
    $stm = $pdo->prepare($sql);
    foreach ($parametri as $k => $v) { $stm->bindValue($k, $v); }   
    $stm->bindValue(':voci', (int)$pag_voci, PDO::PARAM_INT);
    $stm->bindValue(':offset', (int)$pag_offset, PDO::PARAM_INT);
    $stm->execute();
    $numCategorie = $stm->rowCount();
    if ($numCategorie == 0) {
        $msgErrore["categorie"] = 'Nessuna categoria trovata.';
    } else {
        $categorie = $stm->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $msgErrore["categorie"] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione menu Pizzeria da Paggi</title>
    <link rel="icon" type="image/x-icon"
        href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAFvklEQVR4nOWaSWwbZRiGBwQIhISEOHAArogTEgfEuVLAhwoOVCWO421sz19CBajlgERT0tJCm2ahabOqKdBaAdqUViWVEIuSFqFGytIkzR5n8aSpdyde4iZObb9oZjyLM6lUQLHH4pU+xaMvI/2P/+/957Vlivq/qqoHj3FFFbNMF6IWQ30gVlYTSBi/j+ylilHGMyszpZUelB7IVqUH5U0hN90ZfYMqJtlqMjC3xqGv8sowXFV5YWhf7rVfjT9PFYPoaoCvkymUN67wO6IEKjvqS5nORdqoKjxKFQVItVCWpg0Y6oK5u8MB1QZWyztiDkqrokWIkSugG+PC6+OApfUeyo74coE4/zSH5syd8VcpzYLgAOj1Izm7Q9dy/olB/7naP+Xt4evlztAzlFZEi4t2OQWY7HVD54A8bqfuo/z0smrc9Mf8GyZnpJ7SgugWxQ5ccEmvkSzFmOvLXP+0rMNwIqACMjQEw6bO2LsFBSELgKMboBtyTb8atfEw4vUHX6f5v9YawNyagH6Tf/SVHhhawxNll2KvFAaEBfhyAfYrAF0jLPzDUzF097VLIHNuH85cXYU927fWp2FsifIAOUCHvRnjt8uXLT14sjAgrFDMKGA/n7s7XHk8Hr5GJgM45lyXe5x/GsJq/1T7k6aOyMGCgZBsOW4CtiYZpLpjDaPTfgmouy+MT5pTUt/csoayY361f04FfeZL8R15A6HHY2DcmVwgN+D4HbDVCYt11IAfr3nWy8PcWfLip+4oKuozMlDbKvSHNh3XBz0wtIWH91xMvLDtIMY+D0wDPtimEuodmgLslwD6hLDYj06ncfVGBHfvCrszPetD8+UEbNm+9WQaxuaIKu6UHvWmTedWnLsv4oltBRHLPBSEYzapAmKGAPtZedwq2zfQOxKSxq1/NIhD3yTl47px67izq242mhcQsSy3l8EspNT++QugTwuLtVUD9T+uYcIl+Oeux4Nfe5exrzGliDtr0H/lx3uHF7Hzsz9RYu1CXkH46vdu7Z85wH6NizECEKnLwPlLDOwdwT/sope/JrVZ/9RmUGLv4iEKA5It06Aftql7av9MAvYL8rjtb0rht95ladzGZ/xST4QoKIjkn+EQHHMbav/0A7Y2GeiL75IYHA/yMLQWQYRx82z57OGP6+ty3LGfAFquJDQM0ieD2CZX1UCzgONnOe7QxQDCj9utAOwz6+pxGwNsTjXIm/s1CGIeDMjH9XAYjvn7aqA+wNYKlFR04a2aLujOahCEH6+pBJ8KxJ51NAJmIa3yj65dgNAmiDv7ri+kYR2L8geBFHc4/2T7hIUEoUkQ060AHAp/cEczd0RLx/WtYHGASP4Y4fwhxxn7zBrMg/6c/9VpGYQej/MxRni+eEFPxECycYaLNfREPL8g1tsr/9rsXLDkDC72TAP+LT8O6LYVxI0+ab5nN2AeCv0jEO6eB92/+eOAbjtBKOARwmI348aiNN/Ta/y7+rAe2Rz3uXCpvJ/kBSQroxdPMywOERZrwrGa4eO7NP8PBBH6Jt4fnB+ycV9xP8kniKiKJbxEWJyXxmU+BcsW/nlQ37wp7itPMl0+QUSRBewgboxI4zab5J8JW5md77uSMA3JfctwCIzCP6RQIJyqgEf3sDAzLPy58++TFmcdU8QRN5eGFXGl38P3SaFBRFWweJZhcZywSIpxJOf50+/b5I+04I+sf4hWQEQ5lvAyYXFNOS7mkbD8/FDFlfv8E59oDUQUw6KEYTEuLdi1zkNIhufi/Jw6zuu0BsKJDOBxhsXHhEVE8s/kKn8MS3F+LJrz7YtOiyCi9i7hOcKigbiRkuJ8TlzxFQeIqPcX8Brjxg1lnOeOYE2a/WFEWLxNWMxL4zZ9rzhBOO1bxFOExaeERUzzZn8YOe7gRcKig7iRUYK8c7nXTRWjmEW8zrC4ufOHnsSuP8aK80c7yrhT1dNT3D+j+i/6G/A00WmFPz+jAAAAAElFTkSuQmCC">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <!-- Banner -->
    <div class="w3-container w3-light-blue w3-xlarge">
        <p><i class="fa fa-paint-brush"></i>Gestione menu Pizzeria da Paggi</p>
    </div>

    <div class="w3-bar w3-light-grey">
        <a href="gestione_categorie.php" class="w3-bar-item w3-button"><i class="fa fa-plus"></i> Inserisci</a>
    </div>

    <!-- Banner per l'errore -->
    <?php if (!empty($msgErrore)): ?>
        <div class="w3-panel w3-red w3-display-container">
            <span onclick="this.parentElement.style.display='none'" 
                  class="w3-button w3-red w3-large w3-display-topright">&times;</span>
            <h3>Errore!</h3>
            
            <?php foreach($msgErrore as $query => $err ): ?>
                <p><?php echo $query .' - '. $err; ?></p>
            <?php endforeach ?>
        </div>
    <?php endif ?>

        <!-- Form filtri -->
    <div class="w3-container w3-padding-32">
        <form method="GET" class="w3-margin-bottom">

            <div class="w3-row-padding">

                <div class="w3-third">
                    <input
                        class="w3-input w3-border w3-round"
                        type="text"
                        name="nome"
                        placeholder="Filtra per nome"
                        value="<?= $_GET['nome'] ?? '' ?>">
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <select class="w3-select w3-border" name="visibile">
                        <option value="">Visibilità</option>
                        <option value="1" <?= ($_GET['visibile'] ?? '') === '1' ? 'selected' : ''; ?>>
                            Visibili
                        </option>
                        <option value="0" <?= ($_GET['visibile'] ?? '') === '0' ? 'selected' : ''; ?>>
                            Nascosti
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

    <!-- Info Card -->
    <div class="w3-panel w3-blue w3-card-4">
        <p>
            <?php if ($num_record > 0): ?>
                <i class="fa fa-info-circle"></i> Nell'archivio sono presenti <strong><?= $num_record ?></strong> categorie.
            <?php else: ?>
                <i class="fa fa-exclamation-triangle"></i> Non ci sono categorie memorizzate in archivio
            <?php endif ?>
        </p>
    </div>

    <!-- Visualizzazione bottoni scorrimento pagine -->
    <?php if ($pag_totali > 1): ?>
        <div class="w3-bar w3-small w3-center">
            <!-- Bottone Precedente -->
            <?php if ($pag_numero > 0): ?>
                <a href="?pag=<?= $pag_numero ?><?php foreach($_GET as $k=>$v){if($k!='pag') echo '&'.urlencode($k).'='.urlencode($v);} ?>" class="w3-button">← Prec</a>
            <?php else: ?>
                <div class="w3-button w3-disabled">← Prec</div>
            <?php endif ?>

            <!-- Numeri pagine (max 10) -->
            <?php for ($i = 1; $i <= min($pag_totali, 10); $i++): ?>
                <a href="?pag=<?= $i ?><?php foreach($_GET as $k=>$v){if($k!='pag') echo '&'.urlencode($k).'='.urlencode($v);} ?>" class="w3-button <?php if ($i == $pag_numero + 1) echo 'w3-red'; ?>">
                    <?= $i ?>
                </a>
            <?php endfor ?>

            <!-- Bottone Successivo -->
            <?php if ($pag_numero < $pag_totali - 1): ?>
                <a href="?pag=<?= $pag_numero + 2 ?><?php foreach($_GET as $k=>$v){if($k!='pag') echo '&'.urlencode($k).'='.urlencode($v);} ?>" class="w3-button">Succ →</a>
            <?php else: ?>
                <div class="w3-button w3-disabled">Succ →</div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <!-- Table -->
    <?php if ($numCategorie > 0): ?>
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-card-4">
                <thead>
                    <tr class="w3-teal">
                        <th><i class="fa fa-hashtag"></i> ID</th>
                        <th><i class="fa fa-align-left"></i> Nome</th>
                        <th><i class="fa fa-sort"></i> Ordine</th>
                        <th>Disponibile</th>
                        <th><i class="fa fa-trash"></i></th>
                        <th><i class="fa fa-edit"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorie as $cat): ?>
                        <tr>
                            <td><?= $cat["id_categoria"] ?></td>
                            <td><?= htmlspecialchars($cat["nome"]) ?></td>
                            <td><?= $cat["ordine"] ?></td>
                            <td>
                                <?php if ($cat['visibile']): ?>
                                    <span class="w3-text-green" style="text-decoration: underline;">
                                        VISIBILE
                                    </span>
                                <?php else: ?>
                                    <span class="w3-text-red" style="text-decoration: underline;">
                                        NASCOSTO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><a href="elimina.php?id=<?= $cat["id_categoria"] ?>&tabella=categorie"><i class="fa fa-trash w3-text-red"></i></a></td>
                            <td><a href="gestione_categorie.php?id=<?= $cat["id_categoria"] ?>"><i class="fa fa-edit w3-text-blue"></i></a></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>

    </div>

    

    </div>

    <!-- Footer -->
    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top footer">
        <p class="w3-center">© 2026 Gestore menu Pizzeria da Paggi</p>
    </footer>

</body>

</html>