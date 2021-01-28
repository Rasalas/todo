<?php
session_start();
require 'libs/database.php';
require 'config.php';

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);
$form_result = $_POST;
if(isset($_POST['task_id'])){
    if($_POST['is_done']==0){
        $database->updateToDone($_POST['task_id']);
    }else{
        $database->updateToUndone($_POST['task_id']);
    }

    header("Location: index.php");
}
?>