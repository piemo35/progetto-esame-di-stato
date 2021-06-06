<?php
session_start();

if($_SESSION['type'] != 'cliente'){
    session_destroy();
    header('Location: index.php');
}

include_once "Utils.php";
$utils = new Utils();

if (isset($_POST['newTicket'])){
    $conn = $utils->dbConnect();
    $query = "INSERT INTO InfoService.ticket (descrizione, stato, data_apertura, id_cliente)VALUES (?,1,NOW(),?);";
    $query = $conn->prepare($query);
    $query->bindParam(1, $_POST['newTicket']);
    $query->bindParam(2, $_SESSION['user_id']);
    $query->execute();

    if (!$query->rowCount() > 0) {
        echo "Abbiamo avuto un problema con l'inserimento dei dati. Verrai reindirizzato alla pagina di login...";
        header('Refresh: 2; url=index.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UFT-8">
    <title>InfoService</title>
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
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
<div class="mainDiv-dashboardCustomer">

    <div class="div-openTicket">
        <h1>Richiedi l'apertura di un nuovo ticket</h1>
        <form action="dashboard-customer.php" method="post">
            <label><textarea style='margin-top: 10px; padding: 20px' rows='3' placeholder='Descrizione del problema' name='newTicket' cols='50'></textarea></label>
            <label><input class="Button" type="submit" name="apriTicket" value="Invia"></label>
        </form>
    </div>

    <div class="div-seeTicket">
        <h1>Controlla i ticket che hai aperto</h1>
        <form action="ticketCliente.php" method="post">
        <label><input class="Button" type="submit" name="ticketAperti" value="Controlla"></label>
    </div>

    <div class="logo-dashboardCustomer">
        <div class="img-logo">
        <img src="infoservice_logo.png">
        </div>
    </div>

</div>

</body>
</html>