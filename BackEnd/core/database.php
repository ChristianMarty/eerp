<?php
//*************************************************************************************************
// FileName : database.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/../config.php";

class database
{
    private object $pdo;

    public function __construct( )
    {
        global $databaseServerAddress;
        global $databasePort;
        global $databaseName;
        global $databaseUser;
        global $databasePassword;

        $options = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        $dsn = "mysql:host=$databaseServerAddress:$databasePort;dbname=$databaseName;charset=UTF8";
        $this->pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
    }

    public function beginTransaction():bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commitTransaction():bool
    {
        return $this->pdo->commit();
    }

    public function rollBackTransaction():bool
    {
        return $this->pdo->rollBack();
    }

    public function lastInsertId():int|false
    {
        $lastInsertId = $this->pdo()->lastInsertId();
        if($lastInsertId === false) return false;
        else return intval($lastInsertId);
    }

    public function pdo():PDO
    {
        return $this->pdo;
    }

    public function getErrorMessage():null|string
    {
        $info = $this->pdo->errorInfo();

        if($info[0] == 0) return null;
        else return $info[0]."-".$info[1]."-".$info[2];
    }

    public function getEnumOptions(string $table, string$column):array|null
    {
        $table = $this->pdo->quote($table);
        $column = $this->pdo->quote($column);

        $table = substr($table,1, -1); // TODO: is this save?

        $query =  <<< QUERY
            SHOW COLUMNS FROM `$table` LIKE $column;
        QUERY;

        $data = $this->pdo->query($query);
        $result = $data->fetch();

        if(!$result) return null;
        return explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result->Type));
    }

    public function escape(string|int|float|null $input):string
    {
        if($input === null) return "NULL";
        else return $this->pdo->quote(strval($input));
    }

    function query(string $baseQuery, array|null $queryParameters = null, string|null $postFix = null ):array
    {
        $query = $baseQuery;

        if(!empty($queryParameters))
        {
            $query .= " WHERE ";
            $query .= $queryParameters[0];
            unset($queryParameters[0]);

            foreach ($queryParameters as $param)
            {
                $query .= " AND ".$param;
            }
        }

        $query .= " ".$postFix??"";

        //echo $query;
        //exit;

        try {
            $data = $this->pdo->query($query);
            $result = $data->fetchAll();
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }

        return $result;
    }

    function execute(string $query)
    {
        try {
            $this->pdo->exec($query);
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    public function insert(string $tableName, array $data, bool $ignore = false): int
    {
        $keys ="";
        $values ="";
        foreach ($data as $key => $value)
        {
            $keys .= "`".$key."`, ";
            if(is_array($value))
            {
                $values .= $value['raw'].", ";
            }
            else if(is_bool($value)){
                if($value) $values .= "b'1',";
                else $values .=  "b'0',";
            }
            else
            {
                if($value === null) $values .= "NULL, ";
                else $values .= $this->pdo->quote(strval($value)).", ";
            }
        }

        $keys = rtrim($keys, ", ");
        $values = rtrim($values, ", ");

        if($ignore)$ignoreString = "IGNORE";
        else $ignoreString = "";

        $query = <<< QUERY
            INSERT $ignoreString INTO $tableName ($keys) VALUES ($values);
        QUERY;
        
        try {
            $this->pdo->exec($query);
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        return intval($this->pdo->lastInsertId());
    }

    public function update(string $tableName, array $data, string|null $condition = null): void
    {
        if($condition === null){
            trigger_error("DB Update -> will not run update without condition", E_USER_ERROR);
            return;
        }

        $pairs ="";

        foreach ($data as $key => $value)
        {
            $val = "";

            if(is_array($value))
            {
                $val .= $value['raw'];
            }
            else if(is_bool($value)){
                if($value) $val .= "b'1'";
                else $val .=  "b'0'";
            }
            else
            {
                if($value === null) $val = "NULL";
                else $val =  $this->pdo->quote(strval($value));
            }

            $pairs .= $key." = ".$val.", ";
        }

        $pairs = rtrim($pairs, ", ");

        if($condition == NULL) $condition = "";
        else $condition = " WHERE ".$condition;

        $query = "UPDATE $tableName SET $pairs $condition;";

        try {
            $this->pdo->exec($query);
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    static public function toBool(int &$value):void
    {
        if($value != 0) $value = true;
        else $value = false;
    }

    static public function dbToBit($value): string
    {
        if($value) return "b'1'";
        else return "b'0'";
    }
}

