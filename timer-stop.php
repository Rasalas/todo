<?php
session_start();
require 'libs/database.php';
require 'config.php';

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);
$form_result = $_POST;
if(isset($_POST['task_id']) && !isset($_GET['stop'])){
    $database->createWorktime($form_result);
}
if(isset($_GET['stop'])){
    $database->stopWorktime($form_result);
    header("Location: task-view.php");
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
        <h1>ToDo - Timer stoppen</h1>
    </nav>
    <div class="container px-5">
        <h4 id="timer">0d 0h 0m 0s</h4>
        <form action="?stop=1" method="POST">
            <input type="hidden" id="task_id" name="task_id" value="<?=$_POST['task_id']?>">
            <input type="hidden" id="insert_id" name="insert_id" value="<?=$_POST['insert_id']?>">
            <input type="hidden" id="task_id" name="task_id" value="<?=$_POST['task_id']?>">
            <button type="submit" class="btn btn-primary">Timer stoppen</button>
        </form>
    </div>
    <footer class="footer"></footer>

    <script>

        var startTime = new Date().getTime();

        var x = setInterval(function(){
            var now = new Date().getTime();

            var duration = now - startTime;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(duration / (1000 * 60 * 60 * 24));
            var hours = Math.floor((duration % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((duration % (1000 * 60)) / 1000);

            document.getElementById('timer').innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
        },1000);

    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>

</html>