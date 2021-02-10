<?php
include('auth.php');
//$_SESSION['angemeldet'] = true;
//$_SESSION['uid'] = 1;
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
        if($urlCutter[0] == "") array_shift($urlCutter);
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
            if (!isset($_GET['go'])) {
                $data = array();
                $data['title'] = 'ToDo';
                if (isset($_SESSION['error'])) {
                    $data['error'] = $_SESSION['error'];
                    $_SESSION['error'] = ''; //TODO: unset?
                }
                echo $twig->render('login.html', $data);
            } else {
                if (isset($_SERVER['HTTPS'])) {
                    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                } else {
                    $protocol = 'http';
                }

                // if already logged in, going back in history ### doesn't seem to do anything - cached?
                if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet']) {
                    $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/projects/";
                    header($redirect);
                }

                // Get data from form
                $form_result = $_POST;

                $email = $form_result['email'];
                $password = $form_result['password'];

                $result = $database->checkUserLogin($email);
                $user = $result->fetch_assoc();

                if ($user !== false && password_verify($password, $user['password'])) {
                    $_SESSION['uid'] = $user['id'];
                    $_SESSION['angemeldet'] = true;

                    $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/projects";
                    header($redirect);
                } else { // error - back to login
                    $_SESSION['error'] = 'E-Mail oder Passwort falsch';

                    $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/login";
                    header($redirect);
                }
            }
            break;
        case 'register':
            if (!isset($_GET['go'])) {
                $data = array();
                $data['title'] = 'ToDo';
                if (isset($_SESSION['error'])) {
                    $data['error'] = $_SESSION['error'];
                    $_SESSION['error'] = '';
                }
                echo $twig->render('register.html', ['title' => 'ToDo']);
            } else {
                // for redirect
                if (isset($_SERVER['HTTPS'])) {
                    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                } else {
                    $protocol = 'http';
                }

                // get data from form
                $form_result = $_POST;
                $error = false;
                $firstname = $form_result['firstname'];
                $lastname = $form_result['lastname'];
                $email = $form_result['email'];
                $password = $form_result['password'];
                $password_repeat = $form_result['password_repeat'];

                /* if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error'] .= 'Bitte eine gültige E-Mail-Adresse eingeben<br>';
                    $error = true;
                }
                if (strlen($passwort) == 0) {
                    $_SESSION['error'] .= 'Bitte ein Passwort eingeben<br>';
                    $error = true;
                }
                if ($passwort != $passwort_repeat) {
                    $_SESSION['error'] .= 'Die Passwörter müssen übereinstimmen<br>';
                    $error = true;
                } */

                /* //Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde
                if (!$error) {
                    $result = $database->getUserByEmail($email);
                    $user = $result->fetch_assoc();

                    if ($user !== false) {
                        $_SESSION['error'] .= 'Du hast bereits einen Account.<br>';
                        $error = true;
                    }
                } */

                if (!$error) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $result = $database->createUser($firstname, $lastname, $email, $password_hash);

                    $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/login";
                    header($redirect);
                } else { // error - back to register
                    $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/register";
                    header($redirect);
                }
            }
            break;

        case 'logout':
            session_destroy();

            $hostname = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['PHP_SELF']);

            //back to login
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/login";
            header($redirect);
            break;
        case 'projects':
            echo $_SESSION['uid'] . '<br>';

            // Get Data from Form
            $form_result['uid'] = $_SESSION['uid'];

            // Clean content
            $page['content'] = '';

            // Get data from database
            $result = $database->getProjectsByUserID($form_result);

            // All rows in array
            while ($project = $result->fetch_assoc()) {
                $projects[] = $project;
            }

            $result->fetch_array();

            // Sending content to data
            $data = array();
            $data['page'] = $page;

            echo $twig->render('projects.html', [
                'menu' => ['projects_top' => 1, 'projects_overview' => 1],
                'projects' => $projects,
                'title' => 'ToDo'
            ]);

            break;
        case 'project-create':

            echo $twig->render('project_form.html', [
                'menu' => ['projects_top' => 1, 'project_create' => 1]
            ]);

            break;

        case 'project-insert':

            // Get Data from Form
            $form_result = $_POST;
            $form_result['uid'] = $_SESSION['uid'];

            // Create Kunden
            $database->createProject($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/projects";
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

            if ($result) {
                while ($task = $result->fetch_assoc()) {
                    $tasks[] = $task;
                }
                $result->fetch_array();
            }
            $test = array();
            $test = [
                'session' => $_SESSION,
                'tasks' => $tasks,
                'menu' => ['tasks_top' => 1, 'tasks_overview' => 1],
                'project_id' => $_SESSION['project_id'],
                'sumduration' => formatTime($sum_duration),
                'title' => 'ToDo'
            ];
            if (isset($_SESSION['active_wt_insert_id'])) {
                $test['active_wt_insert_id'] = $_SESSION['active_wt_insert_id'];
            }
            echo $twig->render('tasks.html', $test);

            break;

        case 'task-create':

            echo $twig->render('task_form.html', [
                'menu' => ['tasks_create' => 1],
                'project_id' => $_SESSION['project_id']
            ]);
            break;
        case 'task-insert':

            // Get Data from Form
            $form_result = $_POST;
            $form_result['uid'] = $_SESSION['uid'];

            // Create Kunden
            $database->createTask($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks";
            header($redirect);
            exit;

            break;

        case 'task-done':

            // Get ID from TaskData
            $task_id = $taskData;

            $database->updateToDone($task_id);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks";
            header($redirect);
            exit;

        case 'task-undone':

            // Get ID from TaskData
            $task_id = $taskData;

            $database->updateToUndone($task_id);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks";
            header($redirect);
            exit;

            break;

        case 'task-delete':

            // Get ID from TaskData
            $form_result['task_id'] = $taskData;

            $database->deleteByTaskID($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks";
            header($redirect);
            exit;

            break;
        case 'task-edit':

            /* // Get ID from TaskData
            $task_id = $taskData;
                    
            /// get data from form
            $form_result['task_id'] = $task_id;

            // Get task from database
            $result = $database->getTaskByID($form_result);

            // Create Array
            $task = array();

            while ($row = $result->fetch_assoc()) {
                $task['text'] = $row['text'];
                //$task['description'] = htmlentities($row['description']);
            }  */

            /* echo $twig->render('task_form.html', [
                'task_text' => $task,
                'menu' => ['tasks_top' => 1,],
                'task_id' => $form_result['task_id']
            ]); */
            echo $twig->render('task_form.html');

            break;
        case 'task-time':
            // Get ID from TaskData
            $task_id = $taskData;

            /// get data from form
            $form_result['task_id'] = $task_id;

            // start timer
            $_SESSION['active_wt_insert_id'] = $database->createWorktime($form_result);
            if(isset($_SESSION['active_wt_insert_id'])){
                $_SESSION['active_wt_start_time'] = round(microtime(true) * 1000);
            }

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks";
            header($redirect);

            break;

        case 'timer-stop':

            // get data from Session
            $form_result['insert_id'] = $_SESSION['active_wt_insert_id'];
            unset($_SESSION['active_wt_insert_id']);
            unset($_SESSION['active_wt_start_time']);

            // stop timer
            $database->stopWorktime($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks";
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

if(isset($_SESSION['active_wt_start_time'])){
    echo '<script> getTimer('. $_SESSION['active_wt_start_time']  .') </script>';
}


/**
 * FUNKTIONEN: Auszulagern
 * 
 */

function formatTime($mins) // for task time
{
    $str = "";
    if (abs($mins) < 60) {
        $str = strval(abs($mins)) . "min";
    } else if (abs($mins) % 60 == 0) {
        $str = abs($mins) / 60 . ":00h";
    } else {
        $hours = floor(abs($mins) / 60);
        $minutes = (abs($mins) % 60);
        if ($hours > 0) {
            $str .= strval($hours) . ":";
        }
        if ($minutes > 0) {
            $str .= sprintf('%02d', $minutes) . "h";
        }
    }
    if ($mins < 0) {
        return "-" . $str;
    } else {
        return $str;
    }
}