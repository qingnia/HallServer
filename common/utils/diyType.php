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
	const REDIS_FAILURE	=	3;
	const HTTP_FAILURE	=	3;

	public static function commonRet($retStat, $msg="")
	{
		$ret = new commonRet();
		$ret->setStat($retStat);
		$ret->setMsg($msg);

		//错误上报
		log::instance()->error("error:$retStat, msg:$msg");
		die($ret->serializeToString());
	}
}
