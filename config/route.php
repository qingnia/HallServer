<?php
class route
{
	private static $instance;

	private function __construct(){}
	private function __clone(){}
	static public function instance()
	{
var_dump("asdfads");
        if (!self::$instance instanceof self)
        {
            self::$instance = new self();
        }
        return self::$instance;
	}
	public function getRoute($msgID)
	{
var_dump($msgID);
		switch($msgID)
		{
		case 1:
			$ret = array('d'=>"ditch", 'm'=>'account', 'a'=>'login');
			break;
		default:
			break;
		}
		return $ret;
	}
}
