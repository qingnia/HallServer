<?php
class route extends single
{
	private static $instance = null;

	public function getRoute($msgID)
	{
		switch($msgID)
		{
		case 1:
			$ret = array('d'=>"common/ditch", 'm'=>'account', 'a'=>'login');
			break;
		default:
			break;
		}
		return $ret;
	}
}
