<?php
class redisAgent extends single
{
	const CONNECT_TIMEOUT_SEC = 1;
    static protected $_instance = null;
    private $connectList;

	private function getKeyCfg($key)
	{
		switch($key)
		{
			case "store1":
				return array('db'=>'main', 'diyExpire'=>"dayEnd,300");
				break;
			case "store2":
				return array('db'=>'main', 'diyExpire'=>"weekEnd,300");
				break;
			default:
				break;
		}
	}

    private function getDb($name)
    {
        //建立连接
        if (!isset($this->connectList[$name]))
        {
            $cfg = getRedisCfg($name);
			$redis = new Redis();
			//1秒超时
			if (! $redis->connect($cfg['host'], $cfg['port'], self::CONNECT_TIMEOUT_SEC))
			{
				//连接超时
				return false;
			}
			if (! $redis->auth($cfg['pwd']))
			{
				//密码错误
				return false;
			}
			$this->connectList[$name] = $redis;
        }
        return $this->connectList[$name];
    }

	public function query($op, $pre, $key, $param1, $param2=false)
	{
		$cfg = $this->getKeyCfg($key);
		$realKey = $pre . "_" . $key;
		$expire = timeUtil::instance()->getExpireTs($cfg['diyExpire']);

		$redis = $this->getDb($cfg['db']);
		$redis->$op($realKey, $param1, $param2);
		$redis->expire($realKey, $expire);
	}

	public function hset($pre, $key, $field, $value)
	{
		$cfg = $this->getKeyCfg($key);
		$realKey = $pre . "_" . $key;
		$this->getDb($cfg['db'])->hset($realKey, $field, $value);
	}
};

