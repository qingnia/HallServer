<?php

function getRedisCfg($name)
{
	switch($name)
	{
		case "config":
		case "main":
			$ret = array(
				'host' => "127.0.0.1",
				'pwd' => "123456",
			);
			break;
		default:
			die("配置缺失：redis:$name, host empty");
			break;
	}
	$ret['port'] = isset($ret['port'])? $ret['port'] : 6379;
	$ret['pwd'] = isset($ret['pwd'])? $ret['pwd'] : "123456";
	return $ret;
}
