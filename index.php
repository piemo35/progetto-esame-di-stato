<?php
session_start();

include_once "Utils.php";
$utils = new Utils();

if (isset($_POST['checker'])) {
    $username = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $match = false;

    if ($_POST['checker'] == "cliente") $table = "clienti";
    if ($_POST['checker'] == "tecnico") $table = "tecnici";

    if (empty($username) || empty($password)) {
        echo "Inserisci username e password";
    } else {

        try {
            $conn = $utils->dbConnect();
            $query = $conn->prepare("SELECT id, email, password FROM InfoService.".$table.";");
            $query->execute();
            $conn = null;

            $userlist = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
        }

        foreach ($userlist as $row){
            if ($row['email'] == $username && $row['password'] == $password){
                $match = true;
                $_SESSION['user_id'] = $row['id'];
            }
        }

        if ($match == false) {
            echo "Credenziali utente errate";
        } else {
            session_regenerate_id();
            $_SESSION['session_id'] = session_id();
            $_SESSION['session_user'] = $username;
            $_SESSION['type'] = $_POST['checker'];

            if($_SESSION['type'] == 'cliente') header('Location: dashboard-customer.php');
            if($_SESSION['type'] == 'tecnico') {
                try {
                    $conn = $utils->dbConnect();
                    $query = $conn->prepare("SELECT * FROM InfoService.tecnici WHERE id = ".$_SESSION['user_id'].";");
                    $query->execute();
                    $conn = null;

                    $user = $query->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Connection Failed: " . $e->getMessage();
                }

                if ($user[0]['nominativo'] == "direttore"){
                    $_SESSION['admin'] = true;
                    header('Location: dashboard-employee.php');
                }elseif ($user[0]['nominativo'] == "helpdesk"){
                    $_SESSION['helpdesk'] = true;
                    header('Location: helpdesk.php');
                }else{
                    header('Location: dashboard-employee.php');
                }

            }
            exit;
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
                <h1>Login</h1>
            </div>

            <label><input type="text" placeholder="email" name="email"></label>
            <label><input type="password" placeholder="password" name="password"></label>

            <div class="div-radio">
            <input type="radio" name="checker" value="cliente" checked>
            <label>Cliente</label><br>
            <input type="radio" name="checker" value="tecnico">
            <label>Personale</label><br>
            </div>

            <label><input class="sendButton" type="submit" name="submit" value="Accedi"></label>


           <div class="registratiButton"><a style="text-decoration: none; color: gray" href="registrazione.php">Registrati</a></div>

        </div>
    </form>


</div>

</body>
</html>

