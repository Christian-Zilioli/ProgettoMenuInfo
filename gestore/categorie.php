<?php
//lettura dati db
require 'connessione.php';

$pdo = new PDO($connString, $connUser, $connPass);

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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>