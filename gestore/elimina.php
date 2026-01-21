<?php
require 'connessione.php';

$id = $_GET['id'] ?? null;
$tabella = $_GET['tabella'] ?? null;
$conferma = $_GET['conferma'] ?? null;

$successo = false;
$errore = "";
$datiElemento = null;

$id_tabelle = [
    'prodotti' => ['id' => 'id_prodotto'],
    'categorie' => ['id' => 'id_categoria'],
    'allergeni' => ['id' => 'id_allergene'],
    'caratteristiche' => ['id' => 'id_caratteristica']
];

if (!$id || !is_numeric($id) || !array_key_exists($tabella, $id_tabelle)) {
    $errore = "Parametri non validi o tabella non autorizzata.";
} else {
    $pdo = new PDO($connString, $connUser, $connPass);
    $id_tabella = $id_tabelle[$tabella]['id'];

    try {
        if ($conferma === 'si') {
            // eliminazione
            $sql = "DELETE FROM $tabella WHERE $id_tabella = :id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':id', $id, PDO::PARAM_INT);
            $stm->execute();
            $successo = true;
        } else {
            // riepilogo
            $sql = "SELECT * FROM $tabella WHERE $id_tabella = :id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':id', $id, PDO::PARAM_INT);
            $stm->execute();
            $datiElemento = $stm->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $errore = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimina Elemento</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="w3-light-grey">

    <header class="w3-container w3-teal w3-padding-16">
        <h1><i class="fa fa-trash"></i> Gestione Eliminazione</h1>
    </header>

    <div class="w3-container w3-padding-64">
        <div class="w3-content" style="max-width:600px">
            
            <?php if($successo): ?>
                <div class="w3-panel w3-green w3-round-large w3-card-4">
                    <h3><i class="fa fa-check-circle"></i> Successo!</h3>
                    <p>L'elemento è stato eliminato definitivamente dalla tabella <strong><?= htmlspecialchars($tabella) ?></strong>.</p>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="<?= ($tabella === 'prodotti') ? 'index.php' : $tabella . '.php' ?>" class="w3-button w3-teal w3-round">
                        <i class="fa fa-arrow-left"></i> Torna alla Lista
                    </a>
                </div>
                
            <?php elseif($errore != "" || !$datiElemento): ?>
                <div class="w3-panel w3-red w3-round-large w3-card-4">
                    <h3><i class="fa fa-times-circle"></i> Errore!</h3>
                    <p><?= $errore ?: "Elemento non trovato." ?></p>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="javascript:history.back()" class="w3-button w3-teal w3-round">Torna Indietro</a>
                </div>
                
            <?php else: ?>
                <div class="w3-card-4 w3-white w3-round-large w3-margin-top">
                    <header class="w3-container w3-red">
                        <h3>Conferma Eliminazione</h3>
                    </header>
                    <div class="w3-container w3-padding">
                        <p>Sei sicuro di voler eliminare questo elemento?</p>
                        <hr>
                        <p><strong><i class="fa fa-table"></i> Tabella:</strong> <?= ucfirst($tabella) ?></p>
                        <p><strong><i class="fa fa-tag"></i> Nome:</strong> <?= htmlspecialchars($datiElemento['nome']) ?></p>
                        <p><strong><i class="fa fa-info-circle"></i> ID:</strong> <?= htmlspecialchars($id) ?></p>
                    </div>
                </div>

                <div class="w3-margin-top w3-center">
                    <a href="elimina.php?tabella=<?= $tabella ?>&id=<?= $id ?>&conferma=si" class="w3-button w3-red w3-round w3-margin-right">
                        <i class="fa fa-trash"></i> Sì, Elimina Definitivamente
                    </a>
                    <a href="javascript:history.back()" class="w3-button w3-teal w3-round">
                        <i class="fa fa-times"></i> Annulla
                    </a>
                </div>
            <?php endif ?>

        </div>
    </div>

    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top">
        <p class="w3-center">© 2026 Sistema Gestionale</p>
    </footer>
</body>
</html>