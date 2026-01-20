<?php
//lettura dati db
require 'connessione.php';

$pdo = new PDO($connString, $connUser, $connPass);


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