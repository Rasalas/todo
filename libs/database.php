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
     * GET ALL tasks
     * 
     * @return resutl|msqli_result
     */
    public function getAllTasks(){
        $sql = "SELECT * FROM
                (SELECT * FROM task) t
                LEFT JOIN ( SELECT task_id, SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS 'duration' FROM worktime GROUP BY task_id) w
                ON t.id = task_id";
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * INSERT task
     * 
     * @return resutl|msqli_result
     */
    public function createTask($form_result){
        $sql = "INSERT INTO task (
            `text`
            ) VALUES (
            '" . mysqli_real_escape_string($this->conn, $form_result['task_text'])."'
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
     * UPDATE task done
     * 
     * @return resutl|msqli_result
     */
    public function updateToDone($id){
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
    public function updateToUndone($id){
        $sql = "UPDATE task SET 
                is_done = 0, 
                timestamp_done = 0
                WHERE id = '" . mysqli_real_escape_string($this->conn, $id) . "'";

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
    public function createWorktime($form_result){
        $sql = "INSERT INTO worktime (
            `task_id`
            ) VALUES (
                '" . mysqli_real_escape_string($this->conn, $form_result['task_id'])."'
            );
        ";
        #echo $sql;
        
        if (!$this->conn->query($sql)) {
            echo 'Error MySQL: ' . $this->conn->error . '<br />';
            return false;
        } else {
            $_POST['insert_id'] = $this->conn->insert_id;
            return true;
        }
    }

    /**
     * INSERT worktime stop
     * 
     * @return resutl|msqli_result
     */
    public function stopWorktime($form_result){
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
