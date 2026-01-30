<?php
$protocollo = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$ip_locale = gethostbyname(gethostname()); 
$percorso_progetto = "/progettoMenuInfo/cliente/index.php";

$url_finale = $protocollo . $ip_locale . $percorso_progetto;

$dimensioni = "250x250";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=$dimensioni&data=" . urlencode($url_finale);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>QR Menù Cliente</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        body {
            background-color: #f7f3ee;
        }
        .bg-pizza {
            background-color: #b22222;
            color: white;
        }
        .bg-basil {
            background-color: #2e7d32;
            color: white;
        }
        .card {
            background-color: #fffaf2;
        }
        .text-pizza {
            color: #b22222;
        }
    </style>
</head>

<body class="w3-center">

    <div class="w3-container bg-pizza w3-padding-32">
        <h2>
            <i class="fa fa-pizza-slice"></i>
            Pizzeria Da Paggi
        </h2>
        <p>QR Code Menù Digitale</p>
    </div>

    <div class="w3-container w3-padding-64">

        <div class="w3-card-4 card w3-padding-large w3-round-large"
             style="max-width:420px; margin:auto;">

            <h3 class="text-pizza">
                <i class="fa fa-qrcode"></i> Scansiona il QR
            </h3>

            <hr>

            <img src="<?= $qr_api_url ?>"
                 alt="QR Code Menù"
                 class="w3-image w3-margin-top"
                 style="max-width:250px;">

            <div class="w3-container w3-margin-top">
                <p class="w3-small">Link diretto al menù:</p>
                
                <div class="w3-light-grey w3-padding w3-small w3-round">
                    <a href="<?= $url_finale ?>"><?= $url_finale ?></a>
                </div>
            </div>

            <div class="w3-margin-top">
                <a href="index.php"
                   class="w3-button bg-basil w3-round w3-block w3-padding">
                    <i class="fa fa-arrow-left"></i> Torna alla Dashboard
                </a>
            </div>

        </div>

    </div>

    <footer class="w3-container w3-padding-16 bg-pizza">
        <p class="w3-small">
            © 2026 Pizzeria Da Paggi – Menù Digitale
        </p>
    </footer>

</body>
</html>
