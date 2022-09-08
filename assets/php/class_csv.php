<?php
//ini_set("display_errors","On");
session_start();
require "db_config.php";

//読み込みCSVファイル名セット
class csv
{
    /**
     * @param string $filename
     * @param int $cnt
     * @return Object
     */
    public function ini_csv($filename = "", $cnt = 0)
    {
        return new csv_read($filename, $cnt);
    }
}

//CSVデータを読み込ます
class csv_read
{
    var $max = 10000;
    var $cnt = 0;
    var $handle = null;
    /**
     * @param string $filename
     * @param int $cnt
     */
    public function __construct($filename = "", $cnt = 0)
    {
        $this->cnt = $cnt;
        $this->handle =  fopen($filename, "r");
        $_SESSION["offset"] ? fseek($this->handle, $_SESSION["offset"]) : $this->handle;
    }
    /**
     * @param int $header_skip
     * @return Object
     */
    public function reader($header_skip = 0)
    {
        if ($this->handle !== FALSE) {
            $response = null;
            $data = fgetcsv($this->handle, null, ",");
            if (!$header_skip || $_SESSION["offset"]) {
                if ($data !== FALSE) {
                    $_SESSION["offset"] = ftell($this->handle);
                    $response["data"] = $data;
                    $response["cnt"] = $this->cnt > $this->max ? 0 : ($this->cnt + 1);
                    $flag = true;
                } else {
                    $_SESSION["offset"] = null;
                    $flag = false;
                }
            } else {
                $_SESSION["offset"] = ftell($this->handle);
                $response["cnt"] = $this->cnt > $this->max ? 0 : ($this->cnt + 1);
                $flag = false;
            }
        } else {
            $_SESSION["offset"] = null;
            $flag = false;
        }
        return new table_save($flag, $response);
    }
}

//tableにCSVデータを保存
class table_save
{
    var $flag = false;
    var $result = null;
    /**
     * @param boolean $flag
     * @param array  $response
     * @return void
     */
    public function __construct($flag, $response)
    {
        $this->flag = $flag;
        $this->result = $response;
        $this->column_name = "name,,...";
    }
    /**
     * @param string  $column_name
     * @return void
     */
    public function tbl_save($column_name = "")
    {
        if ($this->flag) {
            $column = $column_name ? $column_name : $this->column_name;
            $is_column = explode(",", $column);
            foreach ($is_column as $key => $val) {
                $is_value[$val] = $this->result["data"][$key];
            }
            try {
                $pdo = new PDO(DSN, USERNAME, PASSWORD);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = (function ($is_column) {
                    $INSERTFIRST = null;
                    $INSERTLAST = null;
                    foreach ($is_column as $key => $val) {
                        $INSERTFIRST[] = "$val";
                        $INSERTLAST[] = ":$val";
                    }
                    $INSERTSQL = "(" . implode(",", $INSERTFIRST) . ")values(" . implode(",", $INSERTLAST).")";
                    $UPDATESQL = null;
                    foreach ($is_column as $key => $val) {
                        $UPDATESQL[] = "$val = :$val";
                    }
                    return "INSERT INTO " . TABLENAME . $INSERTSQL . " ON DUPLICATE KEY UPDATE " . implode(",", $UPDATESQL) . ";";
                })($is_column);
                
                $stmt = $pdo->prepare($sql);
                foreach ($is_value as $key => &$value) {
                    $is_type = ($key==="test3" || $key==="test13")? PDO::PARAM_INT : PDO::PARAM_STR;
                    $stmt->bindValue(":$key", $value, $is_type);
                }
                $this->result["sql"] = $stmt->execute();
            } catch (\Throwable $th) {
                //throw $th;
                $this->result = null;
                print $th->getMessage();
            }
        }
        print json_encode($this->result);
    }
}

//RUN...POST DATA
if (isset($_POST["csrf_token"])  && $_POST["csrf_token"] === $_SESSION['csrf_token']) {

    $_SESSION["offset"] = (int)strip_tags($_POST["reset_flag"]) === 1 ? null : $_SESSION["offset"];
    $filename = strip_tags($_POST["filename"]);
    $cnt = (int)strip_tags($_POST["cnt"]);

    $column_name = "test1,test2,test3,test4,test5,test6,test7,test8,test9,test10,test11,test12,test13,test14,test15";
    $header_skip = 1;

    $csv = new csv();
    $csv->ini_csv($filename, $cnt)->reader($header_skip)->tbl_save($column_name);
    $csv = null;
} else {
    print "";
}
