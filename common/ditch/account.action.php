<?php
//require_once ROOT . "msg/loginRet.php";
class account extends single
{
	protected static $_instance = null;

	public function getRole($openID)
	{
		$nowTs = time();
		$defaultDitch = 0;

		//尝试创建新用户,如果账户已存在，就更新时间
		$sql = "insert into account_tb (openID, ditch, create_ts, name) values ($openID, $defaultDitch, $nowTs, 'guest') on duplicate key update update_ts = $nowTs";
		$ret = dbAgent::instance()->db("main")->query($sql);
		if ($ret === false)
		{
			log::instance()->error("create new account false open:$openID");
			return 0;
		}

		$sql = "select * from account_tb where roleID = $openID and ditch = $defaultDitch";
		$ret = dbAgent::instance()->db("main")->query($sql)[0];
		$roleID = $ret['roleID'];
		log::instance()->normal("login success, role:$roleID");
		return $ret['roleID'];
	}

	public function login($openID, $msg)
	{
		return $this->getRole($openID);
	}
}
