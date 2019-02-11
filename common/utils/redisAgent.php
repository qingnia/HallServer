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
			case "test1":
				return array('db'=>'config', 'diyExpire'=>"forever");
				break;
			case "test2":
				return array('db'=>'config', 'diyExpire'=>"forever");
				break;
			default:
				die("配置缺失，redis, key:$key");
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

	public function query($op, $pre, $key, $param1=null, $param2=null)
	{
		$cfg = $this->getKeyCfg($key);
		$realKey = $pre . "_" . $key;
		$expire = timeUtil::instance()->getExpireTs($cfg['diyExpire']);

		$redis = $this->getDb($cfg['db']);
		if ($param1 === null)
		{
			$ret = $redis->$op($realKey);
		}
		elseif ($param2 === null)
		{
			$ret = $redis->$op($realKey, $param1);
		}
		else
		{
			$ret = $redis->$op($realKey, $param1, $param2);
		}
		if ($ret === false)
		{
			die("redis出问题了！");
		}
		$redis->expire($realKey, $expire);
		return $ret;
	}

	public function hset($pre, $key, $field, $value)
	{
		$cfg = $this->getKeyCfg($key);
		$realKey = $pre . "_" . $key;
		$this->getDb($cfg['db'])->hset($realKey, $field, $value);
	}
};

