<?php
class log extends instance
{
	protected static $_instance = null;

	public function normal($msg)
	{
		$roleID = $_REQUEST['role'];
		$msg = $_REQUEST['id'];
		$time = date('Y-m-d H:i:s');
		$logFile = "log/logic/" . date('Y-m-d-H') . ".log";
		$log_msg = "[r:$roleID][m:$msg] $msg \n";
		error_log($log_msg, 3, $logFile);
	}
}
