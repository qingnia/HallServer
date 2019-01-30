<?php
//error_log("ttttttttt\n", 3, "/tmp/aaa.log");
//var_dump(123213);
echo phpinfo();
#include_once("config/route.php");

//1.通信前值检测（开关服）
$msgID = $_POST['id'];
$md5 = $_POST['sign'];
$roleID = $_POST['role'];
$msgStr = $_POST['msg'];
//checkSign();

//2.拆分协议号，逻辑前值处理（auth）
$ret = processMsg($msgID, $roleID, $msgStr);
//3.协议逻辑处理，组成消息体返回
if ($ret['stat'] != errorCede::SUCCESS)
{
	logError("msg:$msgID error, role:$roleID, ret:" . json_encode($ret));
}
//4.逻辑后置处理（error记录、消息后置修改）
//5.die
?>
