<?php

include_once(ROOT_PATH . "/Common/statsd_helper.php");
include_once(ROOT_PATH . "/Common/msg_queue_helper.php");

define ("MYSQL_CONNECT_TIMEOUT_SEC", 1); // should be >=1 

class dbAgent
{
    // 操作状态
    const MODEL_SELECT      =   1;      //  查询模型数据
    const MODEL_UPDATE    =   2;      //  更新模型数据
    const MODEL_BOTH      =   3;      //  包含上面两种方式

    static private $instance;
    private $db_connect_list;
    private $db_name;
    private function __construct()
    {
        $this->db_connect_list = array();
    }
    private function __clone()
    {
    }
    static public function instance()
    {
        if (!self::$instance instanceof self)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function db($name)
    {
        $this->db_name = $name;
        return $this;
    }

    public function get_db($name)
    {
        //建立连接
        if (!isset($this->db_connect_list[$name]))
        {
            $db_config = get_db_config($name);
            $mysqli = mysqli_init();
            if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, MYSQL_CONNECT_TIMEOUT_SEC)) {
                $this->error_log('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
            } 
            $db_port = isset($db_config['db_port']) ? $db_config['db_port'] : 3306;
            if (!$mysqli->real_connect($db_config['db_host'], $db_config['db_user'], $db_config['db_pwd'], $db_config['db_name'], $db_port)) {
                $this->error_log('Connect Error (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());

                if ($this->params && $this->params['elastic_flag']) {
                }
                else {
                    $this->return_error(0, CONNECT_DB_ERROR, "connect $name failed");
                }
                $this->db_connect_list[$name] = false;
            }  
            else {
                $this->db_connect_list[$name] = $mysqli;
            }
        }
        return $this->db_connect_list[$name];
    }

    public function query($sql)
    {
        $model = 0;
        if (0 == strncasecmp($sql, "select", 6))
        {
            $model = self::MODEL_SELECT;
        }
        else
        {
            $model = self::MODEL_UPDATE;
        }
        $db = $this->get_db($this->db_name);
        $result = mysqli_query($db, $sql);
        if ($result === false)
        {
            $msg = $sql . "----------------------------------mysql error info:" . mysqli_error($db);
            $this->error_log($msg);
            report_req_fail_count(SQL_ERROR);
            $at_tail = false;
            mq_enter("error_msg", array(
                'msg'=>$msg,
                'logtime'=>time(),
                'logtime_str'=>t2s(time()),
            ), $at_tail);
            return false;
        }
        if ($model == self::MODEL_UPDATE)
        {
            return $db->affected_rows;
        }
        elseif ($model == self::MODEL_SELECT)
        {
            $fields = $result->fetch_fields();
            $one = array();
            $ret = array();
            for ($x = 0; $x < $result->num_rows; $x++)
            {
                $row = $result->fetch_row();
                foreach($fields as $key=>$field)
                {
                    $one[$field->name] = $row[$key];
                }
                array_push($ret, $one);
            }
            return $ret;
        }
    }
 
    public function add($info, $ignore=false)
    {
        $value_array = array();
        $field_array = array();
        foreach($info as $field => $value)
        {
            array_push($field_array, $field);
            if (is_string($value))
            {
                array_push($value_array, "'" . $value . "'");
            }
            else
            {
                array_push($value_array, $value);
            }
        }
        $fields = implode(",", $field_array);
        $values = implode(",", $value_array);

        if (!$ignore) 
        {
        $sql = "insert into {$this->table_name}({$fields}) values({$values})";
        }
        else
        {
        $sql = "insert ignore into {$this->table_name}({$fields}) values({$values})";
        }
        return $this->query($sql);
    }

    public function return_error($msg_id, $error_code = -1,$errmsg="")
	{
        $ret = array(
            'errorCode' => $error_code,
            'errorMsg' => $errmsg,
        );
        die(json_encode($ret));
	}

    public function getLastInsID()
    {
        $db = $this->get_db($this->db_name);
        return $db->insert_id;
    }
};

