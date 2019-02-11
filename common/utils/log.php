<?php
class log extends single
{
	protected static $_instance = null;

	//使用：log::instance()->normal("testttt");
	public function normal($msg)
	{
		$strace = debug_backtrace();
		$info = $strace[0];
		$fileArr = explode('/', $info['file']);
		$file = end($fileArr);
		$roleID = $_REQUEST['role'];
		$msgID = $_REQUEST['id'];
		$time = date('H:i:s');
		$logFile = "log/logic/" . date('Y-m-d-H') . ".log";
		$log_msg = "[$time][$file][{$info['line']}][r:$roleID][m:$msgID] $msg [param:" . json_encode($info['args']) . "]\n";
		error_log($log_msg, 3, $logFile);
	}

	public function error($msg)
	{
		$this->normal($msg);
	}

	//主要用于充值和货币产出消耗统计
	public function eventMoney($roleID, $reason, $moneyType, $count, $left)
	{
		$params = array(
			'roleID' => $roleID,
			'reason' => $reason,
			'param1' => $moneyType,
			'param2' => $count,
			'param3' => $left,
		);
		$this->event($params);
	}

	//主要用于事件统计
	public function eventAction($roleID, $reason, $param1, $param2, $param3)
	{
		$params = array(
			'roleID' => $roleID,
			'reason' => $reason,
			'param1' => $param1,
			'param2' => $param2,
			'param3' => $param3,
		);
		$this->event($params);
	}

	public static function getTbByReason($reason)
	{
		$tb = "event_log";
		if ($reason > 1000)
		{
			$tb = "money_log";
		}
		return $tb;
	}

	//必传参数：roleID, reason
	//params可传参数：param1-param5
	public function event($params)
	{
		$fields = array();
		$values = array();
		foreach($params as $field => $value)
		{
			array_push($fields, $field);
			array_push($values, $value);
		}
		$fieldStr = implode(',', $fields);
		$valueStr = implode(',', $values);
		
		$reason = $params['reason'];
		$tb = self::getTbByReason($reason);
		$sql = "insert into $tb ($fieldStr) values ($valueStr)";
		dbAgent::instance()->db("log")->query($sql);
	}
}
