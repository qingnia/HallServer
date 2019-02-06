<?php
include_once "common/utils/instance.php";
include_once "common/utils/log.php";
include_once "common/utils/dbAgent.php";
include_once "common/utils/diyType.php";
include_once "config/route.php";
include_once "config/db.php";

define("ROOT", "/home/web/");

log::instance()->normal("testttt");
//1.通信前值检测（开关服）
$msgID = $_REQUEST['id'];
$md5 = $_REQUEST['sign'];
$roleID = $_REQUEST['role'];
$msgStr = $_REQUEST['msg'];
echo "id:$msgID, role:$roleID, msg:$msgStr, md5:$md5";
//checkSign();

function processMsg($msgID, $roleID, $msgStr)
{
	//协议逻辑处理，组成消息体返回
	$route = route::instance();
	$config = $route->getRoute($msgID);
	$actionFile = $config['d'] . "/" . $config['m'] . ".action.php";
	include_once $actionFile;
	$action = $config['m']::instance();
	$function = $config['a'];
	$ret = $action->$function($roleID, $msgStr);

	//逻辑后置处理（error记录、消息后置修改）
	if ($ret['stat'] != diyType::SUCCESS)
	{
		log::instance()->error("msg:$msgID error, role:$roleID, ret:" . json_encode($ret));
	}
	return $ret;
}

//2.拆分协议号，逻辑前值处理（auth）
$ret = processMsg($msgID, $roleID, $msgStr);

die();
//5.die
?>
