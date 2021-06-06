<?php
session_start();

if($_SESSION['type'] != 'tecnico' && $_SESSION['helpdesk'] != true){
    session_destroy();
    header('Location: index.php');
}

include_once "Utils.php";
$utils = new Utils();


if (isset($_POST['assegna_IDtecnico']) && $_POST['assegna_IDtecnico'] != "none"){
    try {
        $conn = $utils->dbConnect();
        $query = $conn->prepare("INSERT INTO InfoService.associazioni (id_tecnico, id_ticket) values(?,?);");
        $query->bindParam(1, $_POST['assegna_IDtecnico']);
        $query->bindParam(2, $_POST['assegna_IDticket']);
        $query->execute();
        $conn = null;
    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
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
    <div class="navbar-items"><a href="#"><?php echo $_SESSION['session_user']; ?></a></div>
    <div class="navbar-items"><a href="logout.php">LOGOUT</a></div></div>
<div class="mainDiv-dashboardEmployee">
    <?php

    try {
        $conn = $utils->dbConnect();
        //$query = $conn->prepare("SELECT * FROM InfoService.ticket WHERE id_cliente = ".$_SESSION['user_id'].";");
        $query = $conn->prepare("SELECT * FROM InfoService.ticket WHERE id NOT IN (SELECT id_ticket FROM InfoService.associazioni)");
        $query->execute();
        $ticketlist = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("SELECT id, nominativo FROM InfoService.tecnici WHERE nominativo NOT IN ('direttore', 'helpdesk');");
        $query->execute();
        $conn = null;
        $listaTecnici = $query->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
    }

    //echo print_r($listaTecnici);


    echo "<h1 style='margin-bottom: 30px;'>Ticket non ancora assegnati:</h1>";
    echo "<div class='tab'>";
    if(!empty($ticketlist)) {
        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>DESCRIZIONE</th>";
        //echo "<th>STATO</th>";
        echo "<th>DATA APERTURA</th>";
        //echo "<th>DATA CHIUSURA</th>";
        echo "<th>CLIENTE</th>";
        echo "<th>ASSEGNA</th>";
        echo "</tr>";

        foreach ($ticketlist as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['descrizione'] . "</td>";
            //echo ($row['stato'] == 1) ? "<td>Aperto</td>" : "<td>Chiuso</td>";
            echo "<td>" . $row['data_apertura'] . "</td>";

            try { // per trovare il nome del cliente che ha aperto il ticket
                $conn = $utils->dbConnect();
                $query = $conn->prepare(" Select nome, cognome from InfoService.clienti WHERE id = ".$row['id_cliente'].";");
                $query->execute();
                $conn = null;
                $cliente = $query->fetchAll();
            }catch (PDOException $e) { echo "Ops, connection failed: " . $e->getMessage(); }

            echo "<td>".$cliente[0]['nome']." ".$cliente[0]['cognome']."</td>";
            echo "<td><form action='helpdesk.php' method='post'> <input type='hidden' name='assegna_IDticket' value=".$row['id']."><p>Assegna a: <select name='assegna_IDtecnico'><option value='none' selected>Seleziona</option></p>";
            foreach ($listaTecnici as $tecnico) echo "<option value='".$tecnico['id']."'>".$tecnico['nominativo']."</option>";
            echo "<input style=' width: 30%; padding: 3px; margin: 5px' class='sendButton reportButton' type='submit' name='assegnaTecnico' value='Conferma'></label></form></td>";
            echo "</tr>";
        }
        echo "</table>";
    }else{
        echo "Nessun ticket da assegnare";
    }
    echo "</div>";

    ?>

    <div class="logo-dashboardCustomer">
        <div class="img-logo">
            <img src="infoservice_logo.png">
        </div>
    </div>

</div>

</body>
</html>

