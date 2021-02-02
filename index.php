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
        case 'project-create':

            echo $twig->render('project_form.html', []);

            break;

        case 'project-insert':

            // Get Data from Form
            $form_result = $_POST;

            // Create Kunden
            $database->createProject($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/projects/";
            header($redirect);
            exit;
            
            break;
        case 'tasks':

            if (isset($_POST['project_id'])) {
                $_SESSION['project_id'] = $_POST['project_id'];
            }
            // Clean content
            $tasks = NULL; 

            // Get data from database
            $result = $database->getAllTasksByProjectID($_SESSION['project_id']);
            $sum_duration = $database->getProjectDuration($_SESSION['project_id']);

            // Create Content
            $table_tr_start = '<tr>';
            $table_tr_end = '</tr>';
            $i = 0;

            if($result){
                while ($task = $result->fetch_assoc()) {
                    $tasks[] = $task;
                }
                $result->fetch_array();
            }

            echo $twig->render('tasks.html', [
                'tasks' => $tasks,
                'project_id' => $_SESSION['project_id'],
                'sumduration' => formatTime($sum_duration),
                'title' => 'ToDo'
            ]);

            break;

        case 'task-create':
            echo $twig->render('todo_form.html', ['project_id' => $_SESSION['project_id']]);
            break;
        case 'task-insert':

            // Get Data from Form
            $form_result = $_POST;

            // Create Kunden
            $database->createTask($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/";
            header($redirect);
            exit;

            break;

        case 'task-done':

            // Get Data from Form
            $form_result = $_POST;

            if (isset($_POST['task_id'])) {
                if ($_POST['is_done'] == 0) {
                    $database->updateToDone($_POST['task_id']);
                } else {
                    $database->updateToUndone($_POST['task_id']);
                }

                // Redirect Kunden-Liste
                if (isset($_SERVER['HTTPS'])) {
                    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                } else {
                    $protocol = 'http';
                }
                $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/";
                header($redirect);
                exit;
            } else {
                echo "Task nicht gefunden!";
            }

            break;

        case 'timer':

            // get data from form
            $form_result = $_POST;

            // start timer
            $insert_id = $database->createWorktime($form_result);


            echo $twig->render('timer_form.html', [
                'task_id' => $form_result['task_id'],
                'insert_id' => $insert_id,
                'title' => 'ToDo'
            ]);

            break;

        case 'timer-stop':

            // get data from form
            $form_result = $_POST;

            // stop timer
            $database->stopWorktime($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/";
            header($redirect);

        case 'test':
            echo $twig->render('projects.html', ['title' => 'ToDo']);
            break;
        default:

            echo "Fehler!!";
    }
} else {
    //exit("Falscher Befehl!");
    // Redirect Kunden-Liste
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/projects";
    header($redirect);
}

/**
 * FUNKTIONEN: Auszulagern
 * 
 */

function formatTime($mins) {
    $str = "";
    if(abs($mins) < 60){
        $str = strval(abs($mins)) . "min";
    }else if(abs($mins) % 60 == 0){
        $str = abs($mins)/60 .":00h";
    }else{
        $hours = floor(abs($mins) / 60);
        $minutes = (abs($mins) % 60);
        if($hours > 0){
            $str .= strval($hours) . ":";
        }
        if($minutes > 0){
            $str .= sprintf('%02d',$minutes) . "h";
        }
    }
    if($mins<0){
        return "-". $str;
    }else{
        return $str;
    }
}
//Übergangsweise:
//header("Location: project-view.php"); // TODO: Log-in
//exit();
