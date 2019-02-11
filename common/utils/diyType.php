<?php
include_once ROOT . "msg/commonRet.php";
include_once ROOT . "msg/pinfo.php";
include_once ROOT . "msg/loginRet.php";

class diyType extends single
{
	static protected $_instance = null;

	const SUCCESS	=	0;
	const FAILURE	=	1;
	const DB_FAILURE	=	2;

	public static function commonRet($retStat, $msg="")
	{
		$ret = new commonRet();
		$ret->setStat($retStat);
		$ret->setMsg($msg);
		return $ret;
	}
}
