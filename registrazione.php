<?php
include_once "Utils.php";

$utils = new Utils();

if (isset($_POST['registra'])) {
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $username = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $duplicato = false;


    if (empty($username) || empty($password)) {
        $msg = 'Compila tutti i campi %s';
    }elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Formato email non valido";
    }elseif (mb_strlen($password) < 6 || mb_strlen($password) > 25) {
        $msg = 'Lunghezza minima password 6 caratteri.
                Lunghezza massima 20 caratteri';
    }else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);


        try {
            $conn = $utils->dbConnect();
            $query = $conn->prepare("SELECT email FROM InfoService.clienti;");
            $query->execute();
            $conn = null;

            $userlist = $query->fetchall(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
        }

        foreach ($userlist as $user){
            if ($user['email'] == $username) $duplicato = true;
        }
}

        if (!$duplicato) {
            $conn = $utils->dbConnect();
            $query = "INSERT INTO InfoService.clienti (nome, cognome, email, password) VALUES (?,?,?,?);";

            $query = $conn->prepare($query);
            $query->bindParam(1, $nome);
            $query->bindParam(2, $cognome);
            $query->bindParam(3, $username);
            $query->bindParam(4, $password);
            $query->execute();

            if ($query->rowCount() > 0) {
                echo "Registrazione eseguita con successo. Verrai reindirizzato alla pagina di login...";
                header('Refresh: 3; url=index.php');
            } else {
                echo "Abbiamo avuto un problema con l'inserimento dei dati.";
            }
        }else{
            echo "Spiacenti, questa mail è già stata registrata";
        }


    //printf($msg, '<a href="registrazione.php">torna indietro</a>');
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



<div class="login-main">

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div id="login-form" class="login-form">

            <div class="title">
                <h4>Registrazione</h4>
            </div>


            <label><input type="text" placeholder="nome" name="nome"></label>
            <label><input type="text" placeholder="cognome" name="cognome"></label>
            <label><input type="text" placeholder="email" name="email"></label>
            <label><input type="password" placeholder="password" name="password"></label>


            <label><input class="sendButton" type="submit" name="registra" value="Registrati"></label>

        </div>
    </form>


</div>

</body>
</html>
