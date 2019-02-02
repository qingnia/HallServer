<?php
include_once "common/utils/instance.php";
include_once "common/utils/log.php";
include_once "config/route.php";

$log = log::getInstance();
$log->normal("ttttttttt\n");
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
	$actionFile = "Action/" . $config['d'] . "/" . $config['m'] . "Module.class.php";
var_dump($actionFile);
	require_once $actionFile;
	$ret = $action = new $config['m']($msgID, $roleID, $msgStr);

	//逻辑后置处理（error记录、消息后置修改）
	if ($ret['stat'] != errorCede::SUCCESS)
	{
		logError("msg:$msgID error, role:$roleID, ret:" . json_encode($ret));
	}
	return $ret;
}

//2.拆分协议号，逻辑前值处理（auth）
$ret = processMsg($msgID, $roleID, $msgStr);

die();
//5.die
?>
