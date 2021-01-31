<?php
session_start();
require 'libs/database.php';
require 'config.php';

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Clean Content
$page['content'] = '';

// Get Data from Database
$result = $database->getAllProjects();

// Create Content
$table_tr_start = '<tr>';
$table_tr_end = '</tr>';
$i = 0;

while ($row = $result->fetch_assoc()) {
    $i++;
    // Create Row
    $table_tr_content = '';
    $table_tr_content .= '<td>' . $row["id"] . '</td>';
    $table_tr_content .= '<td>' . getOpenProjectButton($row["id"]) . '</td>';
    $table_tr_content .= '<td>' . $row["name"] . '</td>';
    

    $page['content'] .=  $table_tr_start . $table_tr_content  . $table_tr_end;
}

function getOpenProjectButton($id)
{
    $btn = '';
    $btn .= '<form action="task-view.php" method="POST">';
    $btn .= '<input type="hidden" id="project_id" name="project_id" value="' . $id . '">';
    $btn .= '<button type="submit"><i class="material-icons">launch</i></button>';
    $btn .= '</form>';
    return $btn;
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <style>td:nth-child(3) { width: 100%; }</style>
    <link rel="icon" href="wave.png" type="image/png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no user-scalable=no">
    <title>ToDo</title>
</head>

<body>
    <nav class="navbar fixed-top navbar-expand-lg justify-content-center">
        <a class="navbar-brand" href="#">
            <img src="" height="76px">
            <!--logo-->
        </a>
    </nav>
    <nav class="navbar justify-content-center title">
        <h1>ToDo</h1>
    </nav>
    <div class="container px-5">
        <table style="width:100%">
            <tr>
                <th>ID</th>
                <th></th>
                <th>Name</th>
            </tr>
            <?= $page['content'] ?>
        </table>
    </div>


    <script></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
<footer class="footer">
</footer>

</html>