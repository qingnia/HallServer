<?php
class gmUtil extends single
{
	protected static $_instance = null;

	const PASSWORD = "123456";

	const TYPE_INT = 1;
	const TYPE_MYSQL_STRING = 2;
	const TYPE_STRING = 3;
	const TYPE_JSON = 4;
	const TYPE_ARRAY = 5;
	const TYPE_BOOL = 6;

	const GM_ROOT = 0;
	const GM_NORMAL = 1;

	public static function checkPWD()
	{
		if (!$isset(_REQUEST('pwd')))
		{
			die("要输入密码");
		}
		$pwd = $self::getParam("pwd", TYPE_STRING);
		if ($pwd != $self::PASSWORD)
		{
			die("密码错误！");
		}
	}

	public static function getParam($index, $type)
	{
		if (!isset($_REQUEST[$index]))
		{
			return null;
		}
		$param = $_REQUEST[$index];
		switch($type)
		{
			case self::TYPE_INT:
				return intval($param);
				break;
			case self::TYPE_MYSQL_STRING:
				return addslashes($param);
				break;
			case self::TYPE_JSON:
				return json_decode($param);
				break;
			case self::TYPE_STRING:
			case self::TYPE_ARRAY:
			case self::TYPE_BOOL:
				return $param;
				break;
			default:
				die("必须指定参数类型");
				break;
		}
	}

	//将数据库的查询结果打印到网页端
	public static function printTable($result, $tableName="tb")
	{
		if (empty($result))
		{   
			echo "empty<br>";
			return;
		}
        echo "<html>";
		echo '<head>                  <meta http-equiv=Content-Type content="text/html;charset=utf-8">';
		echo "<body>";

		echo "$tableName ";
var_dump($tableName);
		echo "<table border=\"1\">";

		$fields = array();
		foreach($result as $one)
		{
			echo "<th></th>";
			foreach($one as $field => $value)
			{
				echo "<th>$field</th>";
				array_push($fields, $field);
			}
			break;
		}
		echo "</tr>";

		foreach($result as $key => $one)
		{
			echo "<td>$key</td>";
			foreach($fields as $f)
			{
				if (isset($one[$f]))
				{
					echo "<td>{$one[$f]}</td>";
				}
			}
			echo "</tr>";
		}
		echo "</table>";
		echo "<hr>";
		echo "</body>";
		echo "</html>";
	}

	//表单提交的单选框
	public static function printOption($options, $choosed=null)
	{
		$optionsStr = "";
        foreach($options as $option => $info)
        {
            $sel = ($option == $choosed) ? "selected" : "";
            $optionsStr .= "<option value='$option' $sel>{$info['show']}</option>";
        }
		return $optionsStr;
	}

	public static function checkGmPwd($level)
	{
		if (!is_release_env())
		{
			//return true;
		}
		$password = gmUtil::instance()->getParam('password', diyType::TYPE_STRING);
		$account = gmUtil::instance()->getParam('gmAccount', diyType::TYPE_STRING);
		if (empty($account) || empty($password))
		{
			return diyType::commonRet(diyType::SUCCESS, "need gm account");
		}
		//统一存储到public，这个存储多个服公用
		//如果特定渠道单独账号，可以再加一个单独用的key 
		$real_pwd = redisAgent::instance()->query("hget", "gmRootAccount", $account);
		if (!empty($real_pwd) && $password == $real_pwd)
		{
			return true;
		}

		//如果ROOT密码不对，再看普通权限，校验普通密码
		if ($level == $self::GM_NORMAL)
		{
			$normal_pwd = redisAgent::instance()->query("hget", "gmAccount", $account);
			if (!empty($normal_pwd) && $password == $normal_pwd)
			{
				return true;
			}
			diyType::commonRet(diyType::SUCCESS, "normal: password error!");
		}
		diyType::commonRet(diyType::SUCCESS, "root: password error!");
	}

	public static function IpCheck()
	{
		$gmIp = get_remote_ip();
		$whiteIpList = array("127.0.0.1", "118.89.27.78");
		if (in_array($gmIp, $commonIpList))
		{
			return true;
		}

		if (redisAgent::instance()->query("get", ip2long($sender_ip), "white_ip") == 1)
		{
			return true;
		}
		return false;
	}
}
