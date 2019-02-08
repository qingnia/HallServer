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
		case 101:
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
