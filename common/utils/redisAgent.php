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
				diyType::commonRet(diyType::REDIS_FAILURE, "redis:$name auth error!");
			}
			$this->connectList[$name] = $redis;
        }
        return $this->connectList[$name];
    }

	private function needExpireOp($op)
	{
		$needExpireOps = array("set", "hset", "hmset", "hincrby", "lpush", "lpop", "rpush", "rpop", "lpoprpush", "zset", "zadd", "sadd");
		return in_array($op, $needExpireOps);
	}

	public function query($op, $pre, $key, $param1=null, $param2=null)
	{
		//mget和mset也属于批量操作，需要走批量
		if ($op == "mget" || $op == "mset")
		{
			diyType::commonRet(diyType::REDIS_FAILURE, "mget、mset need call queyrBatch!");
		}
		$cfg = $this->getKeyCfg($key);
		$realKey = $pre . "_" . $key;
		$expire = timeUtil::getExpireTs($cfg['diyExpire']);

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
			diyType::commonRet(diyType::REDIS_FAILURE, "redis connect error, redis:{$cfg['db']}");
		}
		//列举需要重置过期时间的命令，执行expire
		if ($this->needExpireOp($op))
		{
			$redis->expire($realKey, $expire);
		}
		return $ret;
	}

	public function queryBatch($queryOps)
	{
		$batchOps = array();
		//先根据redis位置分开
		foreach($queryParams as $params)
		{
			$cfg = $this->getKeyCfg($params[2]);
			$redisName = $cfg['db'];
			if (!isset($batchOps[$redisName]))
			{
				$batchOps[$redisName] = array();
			}
			$op = $param[0];
			$expire = timeUtil::getExpireTs($cfg['diyExpire']);
			if ($op == "set")
			{
				$op = "setex";
				$param[4] = $param[3];
				$param[3] = $expire;
			}
			$key = $param[1] . "_" . $param[2];
			array_push($batchOps[$redisName], array($op, $key, array_slice($params, 3)));
			if ($this->needExpireOp($op))
			{
				array_push($batchOps[$redisName], array("expire", $key, $expire));
			}
		}
		foreach($batchOps as $redisName => $ops)
		{
			$redis = $this->getDb($redisName);
			$pipe = $redis::pipe();
			foreach($ops as $one)
			{
				if (!isset($one[2]))
				{
					$pipe->$one[0]($one[1]);
				}
				elseif (!isset($one[3]))
				{
					$pipe->$one[0]($one[1], $one[2]);
				}
				elseif (!isset($one[3]))
				{
					$pipe->$one[0]($one[1], $one[2], $one[3]);
				}
				else
				{
					diyType::commonRet(diyType::REDIS_FAILURE, "batch error, redis:$redisName, ops is too many!" . json_encode($ops));
					die("redis操作参数太多了，检查下");
				}
			}
			$ret = $pipe->exec();
			if ($ret === false)
			{
				diyType::commonRet(diyType::REDIS_FAILURE, "batch error, redis:$redisName" . json_encode($ops));
			}
		}
	}

	public function hset($pre, $key, $field, $value)
	{
		$cfg = $this->getKeyCfg($key);
		$realKey = $pre . "_" . $key;
		$this->getDb($cfg['db'])->hset($realKey, $field, $value);
	}
};

