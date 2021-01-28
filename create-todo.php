<?php
session_start();
require 'libs/database.php';
require 'config.php';

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);
$form_result = $_POST;
if(isset($_GET['i'])){
    $database->createTask($form_result);
    header("Location: index.php");
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="icon" href="wave.png" type="image/png">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no user-scalable=no">
    <title>ToDo</title>
</head>

<body>
    <nav class="navbar fixed-top navbar-expand-lg justify-content-center">
        <a class="navbar-brand" href="#">
            <img src="" height="76px"> <!--logo-->
        </a>
    </nav>
    <nav class="navbar justify-content-center title">
        <h1>ToDo</h1>
    </nav>
    <div class="container px-5">
        <form action="?i=1" method="POST">
            <div class="form-group">
                <label for="task_text">Aufgabe</label>
                <input type="text" class="form-control" id="task_text" name="task_text" placeholder="Fische füttern ...">
            </div>
            <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
    </div>
    <footer class="footer"></footer>

    <script></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>

</html>