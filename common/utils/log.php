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
}
