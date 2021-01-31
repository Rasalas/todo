<?php
session_start();
require 'libs/database.php';
require 'config.php';
if(isset($_POST['project_id'])){
    $_SESSION['project_id'] = $_POST['project_id'];
}

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Clean Content
$page['content'] = '';

// Get Data from Database
$result = $database->getAllTasksByProjectID($_SESSION['project_id']);

// Create Content
$table_tr_start = '<tr>';
$table_tr_end = '</tr>';
$i = 0;

while ($row = $result->fetch_assoc()) {
    $i++;
    // Create Row
    $table_tr_content = '';
    $table_tr_content .= '<td>' . $row["id"] . '</td>';
    $table_tr_content .= '<td>' . getIsDoneButton($row["id"], $row["is_done"]) . '</td>';
    $table_tr_content .= '<td>' . $row["text"] . '</td>';
    $table_tr_content .= '<td>' . $row["timestamp_created"] . '</td>';
    $table_tr_content .= '<td>' . $row["timestamp_done"] . '</td>';
    $table_tr_content .= '<td>' . $row["duration"] . '</td>';
    $table_tr_content .= '<td>' . getStartWorktimeButton($row["id"]) . '</td>';

    $page['content'] .=  $table_tr_start . $table_tr_content  . $table_tr_end;
}

function getIsDoneButton($id, $state)
{
    $btn = '';
    if ($state == 1) {
        $btn .= '<form action="update.php" method="POST">';
        $btn .= '<input type="hidden" id="task_id" name="task_id" value="' . $id . '">';
        $btn .= '<input type="hidden" id="is_done" name="is_done" value="' . $state . '">';
        $btn .= '<button type="submit"><i class="material-icons">check_box</i></button>';
        $btn .= '</form>';
    } else {
        $btn .= '<form action="update.php" method="POST">';
        $btn .= '<input type="hidden" id="task_id" name="task_id" value="' . $id . '">';
        $btn .= '<input type="hidden" id="is_done" name="is_done" value="' . $state . '">';
        $btn .= '<button type="submit"><i class="material-icons">check_box_outline_blank</i></button>';
        $btn .= '</form>';
    }
    return $btn;
}

function getStartWorktimeButton($id)
{
    $btn = '';
    $btn .= '<form action="timer-stop.php" method="POST">';
    $btn .= '<input type="hidden" id="task_id" name="task_id" value="' . $id . '">';
    $btn .= '<button type="submit"><i class="material-icons">play_arrow</i></button>';
    $btn .= '</form>';
    return $btn;
}

?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
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
                <th>Erledigt</th>
                <th>Text</th>
                <th>Created @</th>
                <th>Done @</th>
                <th>Duration</th>
                <th></th>
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
    <div class="container px-5 mt-5" style="display:flex; justify-content:center;">
        <form action="create-todo.php" method="POST">
            <input type="hidden" id="project_id" name="project_id" value="<?=$_POST['project_id']?>">
            <input type="submit" id="create-task" type="button" value="Neue Aufgabe">
        </form>
    </div>
</footer>

</html>