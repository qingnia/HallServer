<?php

function get_db_config($name)
{
	switch($name)
	{
		case "main":
			$ret = array(
				'host' => "127.0.0.1",
				'user' => "root",
				'pwd' => "123456",
				'name' => "test",
			);
			break;
		default:
			break;
	}
	return $ret;
}
