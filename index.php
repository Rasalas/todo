<?php
include('auth.php');
//$_SESSION['angemeldet'] = true;
//$_SESSION['uid'] = 1;
require 'libs/database.php';
require 'config.php';
require_once 'twig/vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

$app_title = 'TODO';
$app = array();
$app['version'] = '1.0.0';
$app['copyright'] = 'Copyright &copy; ' . date('Y') . ' Torben Buck';
$project_id_access = array();

// Setup Database
$database = new database($config["servername"], $config["username"], $config["password"], $config["dbname"]);

if (isset($_GET['task'])) {
    // Get Task URL
    $urlTask = $_GET['task'];
    if ($urlTask) {
        $urlCutter = explode("/", $urlTask);
        if ($urlCutter[0] == "") array_shift($urlCutter);
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

            echo $twig->render('projects.html', [
                'menu' => ['projects_top' => 1, 'projects_overview' => 1],
                'projects' => getProjects($_SESSION['uid'], $database),
                'title' => 'ToDo'
            ]);

            break;
        case 'project-create':

            echo $twig->render('project_form.html', [
                'menu' => ['projects_top' => 1, 'project_create' => 1],
                'projects' => getProjects($_SESSION['uid'], $database)
            ]);

            break;

        case 'project-insert':

            // Get Data from Form
            $form_result = $_POST;
            $form_result['uid'] = $_SESSION['uid'];

            // Create Kunden
            $_SESSION['project_id'] = $database->createProject($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];

            header($redirect);
            exit;

            break;

        case 'project-delete':

            // Get ID from TaskData
            $form_result['project_id'] = $taskData;

            // block unallowed project_id changes
            if (!hasAccess($_SESSION['uid'], $form_result['project_id'], $database)) {
                echo "Y U DO THIS?! 😡";
                exit;
            }

            $projects = getProjects($_SESSION['uid'], $database);
            $database->deleteByProjectID($form_result);

            $_SESSION['project_id'] = $projects[0]['id'];

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
            header($redirect);
            exit;

            break;
        case 'tasks':

            if (isset($taskData)) {
                $_SESSION['project_id'] = $taskData;
            }

            if (isset($_POST['project_id'])) {
                $_SESSION['project_id'] = $_POST['project_id'];
                unset($_POST['project_id']);
            }
            // Clean content
            $tasks = NULL;
            $done_tasks = NULL;
            $bills = NULL;
            $projects = array();

            // block unallowed project_id changes
            if (!hasAccess($_SESSION['uid'], $_SESSION['project_id'], $database)) {
                echo "Y U DO THIS?! ... 😡";
                exit;
            }

            // Get data from database
            $result_tasks = $database->getAllTasksByProjectID($_SESSION['project_id']);
            $result_done_tasks = $database->getAllDoneTasksByProjectID($_SESSION['project_id']);
            $sum_duration = $database->getProjectDuration($_SESSION['project_id']);
            $result_bills = $database->getBillByProjectAndUserID([ 'project_id' => $_SESSION['project_id'], 'user_id' => $_SESSION['uid']]);
            
            // tasks
            if ($result_tasks) {
                while ($task = $result_tasks->fetch_assoc()) {
                    $task['duration'] = formatTime($task['duration']);
                    $task['description'] = htmlentities(nl2br($task['description']));
                    $tasks[] = $task;
                }
                $result_tasks->fetch_array();
            }

            // done tasks
            if ($result_done_tasks) {
                while ($done_task = $result_done_tasks->fetch_assoc()) {
                    $done_task['duration'] = formatTime($done_task['duration']);
                    $done_task['timestamp_done'] = $done_task['timestamp_done'];
                    $done_tasks[] = $done_task;
                }
                $result_done_tasks->fetch_array();
            }

            // bills
            if ($result_bills) {
                while ($bill = $result_bills->fetch_assoc()) {
                    $bill['pay'] = round($bill['sum_duration'] /60*$bill['hour_pay'], 2) . "€";
                    $bill['sum_duration'] = formatTime($bill['sum_duration']);
                    $bills[] = $bill;
                }
                $result_bills->fetch_array();
            }


            $test = array();
            $test = [
                'tasks' => $tasks,
                'done_tasks' => $done_tasks,
                'bills' => $bills,
                'projects' => getProjects($_SESSION['uid'], $database),
                'menu' => ['tasks_top' => 1, 'tasks_overview' => 1, 'projects_top' => 1],
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

            $page = array();
            $page['title_header'] = $app_title . ' | Task erstellen';
            $page['title_content'] = 'Task erstellen';
            $page['button_save_title'] = 'Task speichern';
            $page['button_save_link'] = 'task-insert';

            echo $twig->render('task_form.html', [
                'page' => $page,
                'projects' => getProjects($_SESSION['uid'], $database),
                'menu' => ['projects_top' => 1, 'tasks_create' => 1],
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
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
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
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
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
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
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
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
            header($redirect);
            exit;

            break;
        case 'task-edit':

            // Get ID from TaskData
            $task_id = $taskData;

            $page = array();
            $page['title_header'] = $app_title . ' | Task ändern';
            $page['title_content'] = 'Task ändern';
            $page['button_save_title'] = 'Task speichern';
            $page['button_save_link'] = 'task-update/' . $task_id;

            /// get data from form
            $form_result['task_id'] = $task_id;

            // Get task from database
            $result = $database->getTaskByID($form_result);

            // Create Array
            $task = array();

            while ($row = $result->fetch_assoc()) {
                $task['text'] = $row['text'];
                $task['description'] = htmlentities($row['description']);
            }

            echo $twig->render('task_form.html', [
                'page' => $page,
                'projects' => getProjects($_SESSION['uid'], $database),
                'data' => $task,
                'menu' => ['projects_top' => 1, 'tasks_top' => 1],
                'task_id' => $form_result['task_id']
            ]);

            break;

        case 'task-update':

            // Get ID from TaskData
            $id = $taskData;

            // Get Data from Form
            $form_result = $_POST;

            // Create Unterkunft
            $database->updateTask($id, $form_result);

            // Redirect Unterkunft-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
            header($redirect);
            exit;

            break;
        case 'task-time':
            // Get ID from TaskData
            $task_id = $taskData;

            /// get data from form
            $form_result['task_id'] = $task_id;

            // start timer
            $_SESSION['active_wt_insert_id'] = $database->createWorktime($form_result);
            if (isset($_SESSION['active_wt_insert_id'])) {
                $_SESSION['active_wt_start_time'] = round(microtime(true) * 1000);
            }

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
            header($redirect);

            break;

        case 'timer-stop':

            // get data from Session
            $form_result['insert_id'] = $_SESSION['active_wt_insert_id'];
            unset($_SESSION['active_wt_insert_id']);
            unset($_SESSION['active_wt_start_time']);

            // stop timer
            $database->stopWorktime($form_result);

            // Redirect tasks
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
            header($redirect);

        case 'bill-create':

            $page = array();
            $page['title_header'] = $app_title . ' | Abrechnung erstellen';
            $page['title_content'] = 'Abrechnung erstellen';
            $page['button_save_title'] = 'Abrechnung speichern';
            $page['button_save_link'] = 'bill-insert/';

            $data['sum_duration'] =  $database->getDoneTasksOfProjectDuration($_SESSION['project_id']);
            $data['sum_duration_ez'] = formatTime($data['sum_duration']);
            
            echo $twig->render('bill_form.html', [
                'page' => $page,
                'menu' => ['projects_top' => 1],
                'projects' => getProjects($_SESSION['uid'], $database),
                'data' => $data,
                'project_id' => $_SESSION['project_id']
            ]);

            break;

        case 'bill-insert':

            // Get Data from Form
            $form_result = $_POST;
            $form_result['uid'] = $_SESSION['uid'];
            $form_result['project_id'] = $_SESSION['project_id'];

            // Create Kunden
            $database->createBill($form_result);

            // Redirect Kunden-Liste
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $redirect = "Location: " . $protocol . "://" . $_SERVER['SERVER_NAME'] . "/todo/tasks/" . $_SESSION['project_id'];
            header($redirect);
            exit;

            break;
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

// Laufender Timer
if (isset($_SESSION['active_wt_start_time'])) {
    echo '<script> getTimer(' . $_SESSION['active_wt_start_time']  . ') </script>';
}


/**
 * FUNKTIONEN: Auszulagern
 * 
 */
function hasAccess($uid, $project_id, $database)
{
    $projects = getProjects($uid, $database);
    //if ($id == 0) echo "ProjectID:0"; //mh...

    $allowed = false;
    foreach ($projects as $project) {
        if ($project_id == $project['id']) {
            $allowed = true;
        }
    }
    return $allowed;
}

function getProjects($uid, $database)
{
    $form_result['uid'] = $uid;
    $result = $database->getProjectsByUserID($form_result);
    if ($result) {
        while ($project = $result->fetch_assoc()) {
            $projects[] = $project;
        }
        $result->fetch_array();

        return $projects;
    } else {
        echo 'Result empty';
    }
    return false;
}

function formatTime($mins) // for task time
{
    $mins = floor($mins);
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
    } else if ($mins == 0) {
        return "";
    } else {
        return $str;
    }
}
