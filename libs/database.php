<?php

/**
 * Database Class
 */

class database
{

    // Setup Database
    private $servername = "";
    private $username = "";
    private $password = "";
    private $dbname = "";

    private $conn = NULL;

    /**
     * Construtor
     *
     * @param $servername
     * @param $username
     * @param $password
     * @param $dbname
     */
    function __construct($servername, $username, $password, $dbname)
    {

        // Setup login
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;

        // Setup databse
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        // Set database charset utf8
        if (!$this->conn->set_charset("utf8")) {
            die("Error loading character set utf8: " . $this->conn->connect_error);
        }
    }

    /**
     * Destructor
     */
    function __destruct()
    {
        $this->conn->close();
    }

    /**
     * GET user login information
     * 
     * @return resutl|msqli_result
     */
    public function checkUserLogin($email)
    {
        $sql = "SELECT id, admin, email, password, uuid FROM user WHERE email = '" . mysqli_real_escape_string($this->conn, $email) . "'";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * GET user login information
     * 
     * @return resutl|msqli_result
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT email FROM user WHERE email = '" . mysqli_real_escape_string($this->conn, $email) . "'";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * INSERT task
     * 
     * @return resutl|msqli_result
     */
    public function createUser($firstname, $lastname, $email, $password)
    {
        $sql = "INSERT INTO user (
            `firstname`,
            `lastname`,
            `email`,
            `password`
            ) VALUES (
            '" . mysqli_real_escape_string($this->conn, $firstname) . "',
            '" . mysqli_real_escape_string($this->conn, $lastname) . "',
            '" . mysqli_real_escape_string($this->conn, $email) . "',
            '" . mysqli_real_escape_string($this->conn, $password) . "'
            );
        ";
        #echo $sql;
        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * GET ALL tasks
     * 
     * @return resutl|msqli_result
     */
    public function getAllTasks()
    {
        $sql = "SELECT * FROM
                (SELECT * FROM task) t
                LEFT JOIN ( SELECT task_id, SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS 'duration' FROM worktime GROUP BY task_id) w
                ON t.id = task_id";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * GET SUM(duration) by project_id
     * 
     * @return resutl|msqli_result
     */
    public function getProjectDuration($id)
    {
        $sql = "SELECT project_id, SUM(duration) AS 'sum_duration' FROM
            (SELECT id, project_id FROM task WHERE `project_id`= '" . mysqli_real_escape_string($this->conn, $id) . "') t
            LEFT JOIN ( SELECT task_id, SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS 'duration' FROM worktime GROUP BY task_id) w
            ON t.id = task_id";

        $result = $this->conn->query($sql)->fetch_object()->sum_duration;
        return $result;
    }

    /**
     * GET ALL tasks by project_id
     * 
     * @return resutl|msqli_result
     */
    public function getAllTasksByProjectID($id)
    {
        $sql = "SELECT * FROM
                (SELECT id, project_id, `text`, is_done FROM task WHERE (timestamp_done >=(NOW()-INTERVAL 1 DAY) OR timestamp_done IS NULL) AND `project_id`= '" . mysqli_real_escape_string($this->conn, $id) . "') t
                LEFT JOIN ( SELECT task_id, SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS 'duration', MIN(start_time) AS 'started', MAX(end_time) AS 'ended' FROM worktime GROUP BY task_id) w
                ON t.id = task_id";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * GET task by task_id
     * 
     * @return resutl|msqli_result
     */
    public function getTaskByID($form_result)
    {
        $task_id = mysqli_real_escape_string($this->conn, $form_result['task_id']);
        $sql = "SELECT * FROM task WHERE id = " . $task_id . ";";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * GET ALL Projects
     * 
     * @return resutl|msqli_result
     */
    public function getAllProjects()
    {
        $sql = "SELECT * FROM project";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * GET Projects by user_id
     * 
     * @return resutl|msqli_result
     */
    public function getProjectsByUserID($form_result)
    {
        $user_id = mysqli_real_escape_string($this->conn, $form_result['uid']);
        $sql = "SELECT p.id, is_admin, `name` FROM workgroup, project p WHERE p.id = project_id AND user_id = " . $user_id . ";";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * INSERT project
     * 
     * @return resutl|msqli_result
     */
    public function createProject($form_result)
    {
        $project_text = mysqli_real_escape_string($this->conn, $form_result['project_text']);
        $uid = mysqli_real_escape_string($this->conn, $form_result['uid']);
        $sql = "INSERT INTO project (
            `name`
            ) VALUES (
            '" . $project_text . "'
            );
        ";
        $sql .= " INSERT INTO workgroup (
            `user_id`, 
            `project_id`, 
            `is_admin`
            ) VALUES (
                " . $uid . ", 
                LAST_INSERT_ID(), 
                1
            );";

        //echo $sql;

        if (!$this->conn->multi_query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * INSERT task
     * 
     * @return resutl|msqli_result
     */
    public function createTask($form_result)
    {
        $sql = "INSERT INTO task (
            `text`,
            `project_id`,
            `user_id`
            ) VALUES (
            '" . mysqli_real_escape_string($this->conn, $form_result['task_text']) . "',
            '" . mysqli_real_escape_string($this->conn, $form_result['project_id']) . "',
            '" . mysqli_real_escape_string($this->conn, $form_result['uid']) . "'
            );
        ";

        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * Update task
     *
     * @return boolean|mysqli_result
     */
    public function updateTask($id, $form_result)
    {
        $sql = "UPDATE `task` SET
                `text` =  '" . mysqli_real_escape_string($this->conn, $form_result['task_text']) . "',
                `description` =  '" . mysqli_real_escape_string($this->conn, $form_result['task_description']) . "'
                WHERE 
                id = '" . mysqli_real_escape_string($this->conn, $id) . "'
        ;";
        #echo $sql;

        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * UPDATE task done
     * 
     * @return resutl|msqli_result
     */
    public function updateToDone($id)
    {
        $sql = "UPDATE task SET 
                is_done = 1, 
                timestamp_done = CURRENT_TIMESTAMP()
                WHERE id = '" . mysqli_real_escape_string($this->conn, $id) . "'";

        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * UPDATE task done
     * 
     * @return resutl|msqli_result
     */
    public function updateToUndone($id)
    {
        $sql = "UPDATE task SET 
                is_done = 0, 
                timestamp_done = NULL
                WHERE id = '" . mysqli_real_escape_string($this->conn, $id) . "'";

        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * Delete Task by ID
     *
     * @return boolean|mysqli_result
     */
    public function deleteByTaskID($form_result)
    {
        $task_id = mysqli_real_escape_string($this->conn, $form_result['task_id']); 
        $sql = "DELETE FROM task WHERE id = '" . $task_id . "';";   // won't work with recorded worktime 
        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
     * INSERT worktime start
     * 
     * @return resutl|msqli_result
     */
    public function createWorktime($form_result)
    {
        $sql = "INSERT INTO worktime (
            `task_id`
            ) VALUES (
                '" . mysqli_real_escape_string($this->conn, $form_result['task_id']) . "'
            );
        ";
        #echo $sql;

        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            //$_POST['insert_id'] = $this->conn->insert_id;
            return  $this->conn->insert_id;
        }
    }

    /**
     * INSERT worktime stop
     * 
     * @return resutl|msqli_result
     */
    public function stopWorktime($form_result)
    {
        $sql = "UPDATE worktime SET
                end_time = CURRENT_TIMESTAMP()
                WHERE id = '" . mysqli_real_escape_string($this->conn, $form_result['insert_id']) . "'";

        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            return true;
        }
    }
} // Klasse Ende
