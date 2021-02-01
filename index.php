<?php
session_start();
require 'libs/database.php';
require 'config.php';
require_once 'twig/vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);

if (isset($_GET['task'])) {
    // Get Task URL
    $urlTask = $_GET['task'];
    if ($urlTask) {
        $urlCutter = explode("/", $urlTask);
        switch (count($urlCutter)) {
            case 1:
                $task = $urlCutter[0];
                break;
            case 2:
                $task = $urlCutter[0];
                $taskData = $urlCutter[1];
                break;
            case 3:
                $task = $urlCutter[0];
                $taskData = $urlCutter[1];
                break;
            default:
                $task = 'dashboard';
                break;
        }
    } else {
        echo 'ERROR';
    }

    // Task Switch
    switch ($task) {
        case 'dashboard':
            echo 'Das hier ist ein leeres Dashboard :)';
            break;
        case 'login':
            echo $twig->render('login.html', ['title' => 'ToDo']);
            break;
        case 'projects':

            // Clean content
            $page['content'] = '';

            // Get data from database
            $result = $database->getAllProjects();

            // All rows in array
            while ($project = $result->fetch_assoc()) {
                $projects[] = $project;
            }

            $result->fetch_array();

            // Sending content to data
            $data = array();
            $data['page'] = $page;
            
            echo $twig->render('projects.html', [
                'projects' => $projects,
                'title' => 'ToDo'
            ]);

            break;
        case 'tasks':

            if(isset($_POST['project_id'])){
                $_SESSION['project_id'] = $_POST['project_id'];
            }

            // Clean content
            $page['content'] = '';

            // Get data from database
            $result = $database->getAllTasksByProjectID($_SESSION['project_id']);

            // Create Content
            $table_tr_start = '<tr>';
            $table_tr_end = '</tr>';
            $i = 0;

            while ($project = $result->fetch_assoc()) {
                $projects[] = $project;
            }

            $result->fetch_array();

            // Sending Content to Data
            $data = array();
            $data['page'] = $page;
            
            echo $twig->render('projects.html', [
                'projects' => $projects,
                'title' => 'ToDo'
            ]);

            break;
        case 'test':
            echo $twig->render('projects.html', ['title' => 'ToDo']);
            break;
        default:

            echo 'Fehler!';
            break;
    }
} else {
    exit("Falscher Befehl!");
}

/**
 * FUNKTIONEN: Auszulagern
 * 
 */

function getOpenProjectButton($id) //unbenutzt - mit Twig in projects.html
{
    $btn = '';
    $btn .= '<form action="task-view.php" method="POST">';
    $btn .= '<input type="hidden" id="project_id" name="project_id" value="' . $id . '">';
    $btn .= '<button type="submit"><i class="material-icons">launch</i></button>';
    $btn .= '</form>';
    return $btn;
}
//Übergangsweise:
//header("Location: project-view.php"); // TODO: Log-in
//exit();
