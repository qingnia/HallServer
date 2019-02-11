<?php
class route extends single
{
	static protected $_instance = null;

	public function getRoute($msgID)
	{
		//10000以下是gm权限
		switch($msgID)
		{
		case 1:
			$ret = array('d'=>"common", 'm'=>'gm', 'a'=>'test');
			break;
		case 2:
			$ret = array('d'=>"common", 'm'=>'gm', 'a'=>'dynamicStore');
			break;
		case 3:
			$ret = array('d'=>"common", 'm'=>'gm', 'a'=>'diyStats');
			break;
		case 1001:
			$ret = array('d'=>"common", 'm'=>'timeMgr', 'a'=>'test');
			break;
		case 10001:
			$ret = array('d'=>"logic", 'm'=>'account', 'a'=>'login');
			break;
		default:
			break;
		}
		return $ret;
	}
}
