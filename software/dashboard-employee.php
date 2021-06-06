<?php
session_start();

if($_SESSION['type'] != 'tecnico' && $_SESSION['admin'] != true){
    session_destroy();
    header('Location: index.php');
}

include_once "Utils.php";
$utils = new Utils();

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
    <div class="navbar-items"><a href="logout.php">LOGOUT</a></div>
</div>
<div class="mainDiv-dashboardEmployee">
    <?php

    if(isset($_POST['calcoloMedia'])){

        $numeroMese = $_POST['mese'] ?? '';
        $nomeMese = null;
        switch ($numeroMese){
            case 1:
                $nomeMese = "Gennaio";
                break;
            case 2:
                $nomeMese = "Febbraio";
                break;
            case 3:
                $nomeMese = "Marzo";
                break;
            case 4:
                $nomeMese = "Aprile";
                break;
            case 5:
                $nomeMese = "Maggio";
                break;
            case 6:
                $nomeMese = "Giugno";
                break;
            case 7:
                $nomeMese = "Luglio";
                break;
            case 8:
                $nomeMese = "Agosto";
                break;
            case 9:
                $nomeMese = "Settembre";
                break;
            case 10:
                $nomeMese = "Ottobre";
                break;
            case 11:
                $nomeMese = "Novembre";
                break;
            case 12:
                $nomeMese = "Dicembre";
                break;
            default:
                $nomeMese = null;
        }

        if($nomeMese != null) {
            try {
                $conn = $utils->dbConnect();
                $query = $conn->prepare("create table if not exists tmp as (Select DATEDIFF(data_chiusura, data_apertura) as media from InfoService.ticket where stato=0 and data_chiusura>='2021-" . $_POST['mese'] . "-01 00:00:00' and data_chiusura<='2021-" . $_POST['mese'] . "-31 23:59:59');");
                $query->execute();
                $conn = null;

                $conn = $utils->dbConnect();
                $query = $conn->prepare(" Select ROUND(avg( media ),1) from InfoService.tmp;");
                $query->execute();
                $conn = null;

                $media = $query->fetchAll();

                $conn = $utils->dbConnect();
                $query = $conn->prepare("DROP TABLE InfoService.tmp");
                $query->execute();
                $conn = null;


                if($media[0][0] != null) echo "<div style='margin: 20px 0 100px 0; display: flex; flex-basis: 100%; justify-content: center'><p>Tempo medio di chiusura ticket nel mese di " . $nomeMese . " = " . $media[0][0] . " Giorni</p></div>";
                //else echo "<div style='margin-top: 50px; display: flex; flex-basis: 100%; justify-content: center'><p>Nessun ticket è stato aperto nel mese di ".$nomeMese.".</p></div>";
                else echo "<div style='margin: 20px 0 100px 0; display: flex; flex-basis: 100%; justify-content: center'><p>Nessun ticket è stato chiuso nel mese di ".$nomeMese.".</p></div>";

            }catch (PDOException $e) {
                //echo "Connection Failed: " . $e->getMessage();
                echo "<div style='margin: 20px 0 100px 0; display: flex; flex-basis: 100%; justify-content: center'><p>Nessun ticket è stato chiuso nel mese di ".$nomeMese.".</p></div>";
            }
        }else{
            echo "<p>Inserisci un mese valido!</p>";
        }
    }




    try {
        $conn = $utils->dbConnect();
        if(!isset($_SESSION['admin'])){
            $query = $conn->prepare("SELECT * FROM InfoService.ticket WHERE id IN (SELECT id_ticket FROM InfoService.associazioni WHERE id_tecnico = ".$_SESSION['user_id'].");");
        }else{
            $query = $conn->prepare("SELECT * FROM InfoService.ticket;");
        }
        $query->execute();
        $conn = null;

        $ticketlist = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Connection Failed: " . $e->getMessage();
    }

    echo (!isset($_SESSION['admin'])) ? "<br><h1 style='margin-bottom: 30px;'>Ticket che ti sono stati assegnati:</h1>" : "<h1 style='margin-bottom: 30px;'>Ticket totali aperti sul portale:</h1>";
    echo "<div class='tab'>";
    if(!empty($ticketlist)) {
        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>DESCRIZIONE</th>";
        echo "<th>STATO</th>";
        echo "<th>DATA APERTURA</th>";
        echo "<th>DATA CHIUSURA</th>";
        echo "<th>CLIENTE</th>";
        echo "<th>VAI AI REPORT</th>";
        echo "</tr>";

        foreach ($ticketlist as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['descrizione'] . "</td>";
            echo ($row['stato'] == 1) ? "<td>Aperto</td>" : "<td>Chiuso</td>";
            echo "<td>" . $row['data_apertura'] . "</td>";
            echo "<td>" . $row['data_chiusura'] . "</td>";

            try {
                $conn = $utils->dbConnect();
                $query = $conn->prepare(" Select nome, cognome from InfoService.clienti WHERE id = ".$row['id_cliente'].";");
                $query->execute();
                $conn = null;
                $cliente = $query->fetchAll();
            }catch (PDOException $e) { echo "Connection Failed: " . $e->getMessage(); }

            echo "<td>".$cliente[0]['nome']." ".$cliente[0]['cognome']."</td>";
            echo "<td> <form action='dettaglioTicket.php' method='post' <label><input type='hidden' name='id_ticket' value=".$row['id']."><input class='sendButton reportButton' type='submit' name='dettaglioTicket' value='Dettaglio ticket'></label> </form></td>";
            echo "</tr>";
        }
        echo "</table>";
        }else{
        echo "Ancora nessun ticket";
    }

    echo "</div>";



    echo "<div class='div-calcolo-media'>";

    if(isset($_SESSION['admin'])){
        echo "<form action='dashboard-employee.php' method='post' <br><br><p>Visualizza il tempo medio di chiusura (in giorni) dei ticket nel mese di: " .
            "<select name='mese'><option value='none' selected>Seleziona mese</option></p>".
            "<option value='01'>Gennaio</option>".
            "<option value='02'>Febbraio</option>".
            "<option value='03'>Marzo</option>".
            "<option value='04'>Aprile</option>".
            "<option value='05'>Maggio</option>".
            "<option value='06'>Giugno</option>".
            "<option value='07'>Luglio</option>".
            "<option value='08'>Agosto</option>".
            "<option value='09'>Settembre</option>".
            "<option value='10'>Ottobre</option>".
            "<option value='11'>Novembre</option>".
            "<option value='12'>Dicembre</option>".
            /*"<input type='number' name='mese' max='12' min='1'></label>".*/
            "<input style='margin-top: 30px; width: 20%; float: right' class='sendButton reportButton' type='submit' name='calcoloMedia' value='Calcola'></label> ".
            "</form>";
    }

    //////

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