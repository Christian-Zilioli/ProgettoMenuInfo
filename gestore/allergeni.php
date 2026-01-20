<?php
//lettura dati db
require 'connessione.php';

$pdo = new PDO($connString, $connUser, $connPass);

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