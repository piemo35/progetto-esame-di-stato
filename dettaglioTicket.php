<?php
session_start();
if(!isset($_POST['id_ticket']) && !isset($_POST['newReport']) && !isset($_POST['convalidaReport']) && !isset($_SESSION['id_ticket'])){
    echo "C'è stato un errore, verrai reindirizzato alla dashboard..";
    header('Refresh: 0; url=dashboard-employee.php');
}else if(isset($_POST['id_ticket'])){
    $_SESSION['id_ticket'] = $_POST['id_ticket'];
}

include_once "Utils.php";
$utils = new Utils();

if (isset($_POST['newReport'])){

    $conn = $utils->dbConnect();
    $query = "INSERT INTO InfoService.interventi (data, report, tempo_impiegato, convalida, id_ticket, id_tecnico) VALUES (NOW(),?,?,0,?,?);";
    $query = $conn->prepare($query);
    $query->bindParam(1, $_POST['newReport']);
    $query->bindParam(2, $_POST['minuti']);
    $query->bindParam(3, $_SESSION['id_ticket']);
    $query->bindParam(4, $_SESSION['user_id']);
    $query->execute();

    if (!$query->rowCount() > 0) {
        echo "Abbiamo avuto un problema con l'inserimento dei dati. Verrai reindirizzato alla dashboard...";
        header('Refresh: 2; url=dashboard-employee.php');
    }
}

if (isset($_POST['convalidaReport'])){
    $chiudiTicket = null;
    try {
        $conn = $utils->dbConnect();
        $query = "UPDATE InfoService.interventi SET convalida = 1, commento = ? WHERE id = ".$_POST['id_report'].";";
        $query = $conn->prepare($query);
        $query->bindParam(1, $_POST['commentoReport']);
        $query->execute();
    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
        echo "Abbiamo avuto un problema con la convalida del report. Verrai reindirizzato alla dashboard...";
    }

    try {
        $conn = $utils->dbConnect();
        $query = $conn->prepare("SELECT convalida FROM InfoService.interventi WHERE id_ticket = ".$_SESSION['id_ticket'].";");
        $query->execute();
        $conn = null;

        $convalidati = $query->fetchAll(PDO::FETCH_ASSOC);
        $chiudiTicket = true;
    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
    }

    foreach ($convalidati as $row){
        if ($row['convalida'] == 0) $chiudiTicket = false;
    }

    if($chiudiTicket != null && $chiudiTicket == true){
        try {
            $conn = $utils->dbConnect();
            $query = "UPDATE InfoService.ticket SET stato = 0, data_chiusura = NOW() WHERE id = " . $_SESSION['id_ticket'] . ";";
            $query = $conn->prepare($query);
            $query->execute();
        } catch (PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            echo "Abbiamo avuto un problema con la convalida del report. Verrai reindirizzato alla dashboard...";
        }
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UFT-8">
    <title>InfoService</title>
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <script type="text/javascript" src="functions.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" integrity="sha256-PHcOkPmOshsMBC+vtJdVr5Mwb7r0LkSVJPlPrp/IMpU=" crossorigin="anonymous" />
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700|Montserrat:300" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
</head>
<body>

<div class="navbar">
    <div class="navbar-items"><a href="dashboard-customer.php"><?php echo $_SESSION['session_user']; ?></a></div>
    <div class="navbar-items"><a href="logout.php">LOGOUT</a></div></div>

<div class="main-ticket-info" style="margin-top: 20px">

    <?php

    try {
        $conn = $utils->dbConnect();
        $query = $conn->prepare("SELECT * FROM InfoService.ticket WHERE id = ".$_SESSION['id_ticket'].";");
        //$query = $conn->prepare("SELECT * FROM InfoService.ticket WHERE id = 1;");
        $query->execute();
        $conn = null;

        $ticketinfo = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
    }

    try {
        $conn = $utils->dbConnect();
        $query = $conn->prepare(" Select nome, cognome from InfoService.clienti WHERE id = ".$ticketinfo[0]['id_cliente'].";");
        $query->execute();
        $conn = null;
        $cliente = $query->fetchAll();
    }catch (PDOException $e) { echo "Connection Failed: " . $e->getMessage(); }

    echo "<div class='tab'>";
    echo "<table>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>DESCRIZIONE</th>";
    echo "<th>STATO</th>";
    echo "<th>DATA APERTURA</th>";
    echo "<th>DATA CHIUSURA</th>";
    echo "<th>CLIENTE</th>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>" . $ticketinfo[0]['id'] . "</td>";
    echo "<td>" . $ticketinfo[0]['descrizione'] . "</td>";
    echo ($ticketinfo[0]['stato'] == 1) ? "<td>Aperto</td>" : "<td>Chiuso</td>";
    echo "<td>" . $ticketinfo[0]['data_apertura'] . "</td>";
    echo "<td>" . $ticketinfo[0]['data_chiusura'] . "</td>";
    echo "<td>".$cliente[0]['nome']." ".$cliente[0]['cognome']."</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";

    $chiuso = !($ticketinfo[0]['stato'] == 1);

    try {
        $conn = $utils->dbConnect();
        $query = $conn->prepare("SELECT * FROM InfoService.interventi WHERE id_ticket = ".$_SESSION['id_ticket'].";");
        $query->execute();
        $conn = null;

        $ticketreport = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
    }

    foreach ($ticketreport as $report){
        $convalidato = ($report['convalida'] == 0) ? "NO" : "SI";
        echo "<div class='ticket-info'>";
        echo "<p style='font-size: 30px'>ID report: ".$report['id']."</p>";
        echo "<p style='font-size: 30px'>Data: ".$report['data']."</p>";
        echo "<p style='font-size: 30px'>Report: ".$report['report']."</p>";
        echo "<p style='font-size: 30px'>Tempo impiegato: ".$report['tempo_impiegato']."</p>";
        echo "<p style='font-size: 30px'>Convalida: ".$convalidato."</p>";
        echo "<p style='font-size: 30px'>Commento: ".$report['commento']."</p>";
        if($_SESSION['type'] == "cliente" && $report['convalida'] == 0) {
            echo "<form action='dettaglioTicket.php' method='post'>";
            echo "<label><textarea style='margin-top: 30px; padding: 20px' rows='3' placeholder='Esprimi un commento (facoltativo)' name='commentoReport' cols='50'></textarea></label>";
            echo "<br><br><label><input type='hidden' name='id_report' value=".$report['id']."><input class='sendButton' style='width: 30%' type='submit' name='convalidaReport' value='Convalida report'></label>";
            echo "</form>";
        }
        echo "</div>";
    }

    if($_SESSION['type'] == "tecnico" && !isset($_SESSION['admin']) && $chiuso == false) {
        echo "<div class='ticket-info'>";
        echo "<h1>Aggiungi un nuovo report:</h1>";
        echo "<form action='dettaglioTicket.php' method='post'>";
        echo "<label><textarea style='margin-top: 30px; padding: 20px' rows='10' placeholder='Report' name='newReport' cols='50'></textarea></label>";
        echo "<br><br><label>Tempo impiegato in minuti (min. 15 minuti)</label>";
        echo "<br><br><label><input type='number' name='minuti'></label>";
        echo "<br><br><label><input class='sendButton' style='width: 60%' type='submit' name='sendReport' value='Invia'></label>";
        echo "</form>";
        echo "</div>";
    }

    ?>

    </div>


    <div class="logo-dashboardCustomer">
        <div class="img-logo">
            <img src="infoservice_logo.png">
        </div>
    </div>


</body>
</html>
