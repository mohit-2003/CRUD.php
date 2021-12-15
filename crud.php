<?php

class Database
{

    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "test";
    private $result = array();
    private $conn = "";
    private $isConnected = false;

    public function __construct()
    {
        if (!$this->isConnected) {
            $this->conn = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
            $this->isConnected = true;

            if ($this->conn->connect_error) {
                array_push($this->result, $this->conn->connect_error);
                return false;
            }
        } else {
            return true;
        }
    }
    public function __destruct()
    {
        if ($this->isConnected) {
            if ($this->conn->close()) {
                $this->isConnected = false;
                return true;
            }
        } else {
            return true;
        }
    }

    public function insert($tableName, $params = array())
    {
        if ($this->isTableExists($tableName)) {
            $table_columns = implode(', ', array_keys($params));
            $table_values = implode('", "', $params);

            $sql = 'INSERT INTO ' . $tableName . ' (' . $table_columns . ') VALUES ("' . $table_values . '");';

            if ($var = $this->conn->query($sql)) {
                array_push($this->result, $this->conn->insert_id);
                return true;
            } else {
                array_push($this->result, $this->conn->error);
                return false;
            }
        } else {
            return false;
        }
    }

    public function update($tableName, $params=array(), $where = null){
        
        if ($this->isTableExists($tableName)) {

            $args = array();
            foreach ($params as $key => $value) {
                $args[] = "$key = '$value'";
            }
            $sql = 'UPDATE '.$tableName.' SET ' . implode(", ", $args) .'';
            if($where!=null){
                $sql .= " WHERE $where";
            }
          
            if($this->conn->query($sql)){
        
                array_push($this->result, $this->conn->affected_rows);
                return true;
            } else {
                
                array_push($this->result, $this->conn->error);
            }
        } else {
            return false;
        }
    }

    public function delete($tableName, $where){
        if($this->isTableExists($tableName)){
            $sql = "DELETE FROM $tableName WHERE $where";
            // if($where!=null){
            //     $sql .= " WHERE $where";  -> This is dangerous.
            // } 

            if($this->conn->query($sql)){
                array_push($this->result, $this->conn->affected_rows);
                
                return true;
            } else {
                array_push($this->result, $this->conn->error);
                return false;
            }
        } else {
            return false;
        }
    }

    public function select($tableName, $rows = "*", $join=null, $where=null, $order=null, $limit=null){
        if($this->isTableExists($tableName)){
            $sql = "SELECT $rows FROM $tableName";

            if($join!=null){
                $sql .= " JOIN $join";
            }
            if($where!=null){
                $sql .= " WHERE $where";
            }
            if($order!=null){
                $sql .= " ORDER BY $order";
            }
            if($limit!=null){
                $sql .= " LIMIT 0, $limit";
            }

            $query = $this->conn->query($sql);

            if($query){
                $this->result = $query->fetch_all(MYSQLI_ASSOC);
                return true;
            } else {
                array_push($this->result, $this->conn->error);
                return false;;
            }
        } else {
            return false;
        }
    }

    private function isTableExists($tableName)
    {
        $sql = "SHOW TABLES FROM $this->db_name LIKE '$tableName'";

        $tablesInDB = $this->conn->query($sql);
        if ($tablesInDB) {
            if ($tablesInDB->num_rows == 1) {
                return true;
            } else {
                array_push($this->result, $tableName . " does not exists");
                return false;
            }
        }
    }
    public function getResult()
    {
        $var = $this->result;
        $this->result = array();
        print_r($var);
    }
}
