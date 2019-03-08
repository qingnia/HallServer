<?php
//远程http调用工具
class apc extends single
{
	protected static $_instance = null;
	private apcEnable = false;

	const APC_EXPIRE = 10;

	protected function __construct()
	{
		if (!extension_loaded("apc")) {
			log::instance()->normal("APC not installed");
			return;

		} elseif ('cli' === PHP_SAPI && !ini_get('apc.enable_cli')) {
			log::instance()->normal("APC cli is not enabled");
			return;
		}

		if (!ini_get("apc.enabled")) {
			log::instance()->normal("APC is installed but not enabled");
			return;
		}
		$this->apcEnable = true;
	}
    public function clear_all() { 
        apc_clear_cache('user'); //清除用户缓存 
        return apc_clear_cache(); //清楚缓存 
    } 

	//apc函数
	/*
        return apc_store($key, $value, $time);; 
        return apc_fetch($key); 
        return apc_delete($key); 
        return apc_exists($key); 
        return apc_inc($key, $step); 
        return apc_dec($key, $step); 
        return apc_cache_info(); 
	*/

	//apc服务配置共享，暂不考虑加额外参数，如果要加，放在配置里就行
	private function getApcCfg($key)
	{
		$cfg = array();
		switch($key)
		{
		case 'apc1':
			$cfg = array(
				'from' => 'redis',
				'func' => 'hgetall',
				'needHash' => true,
			);
			break;
		case 'apc2':
			$cfg = array(
				'from' => 'redis',
				'func' => 'get'
			);
			break;
		case 'apc3':
			$cfg = array(
				'from' => 'function',
				'func' => 'apc::getCfgTest'
			);
			break;
		default:
			die("配置缺失！apc:$key");
			break;
		}
		return $cfg;
	}

	private function getDataHard($key, $cfg)
	{
		$ret = array();
		switch($cfg['from'])
		{
		case 'redis':
			$ret = redisAgent::instance()->query($cfg['func'], $key);
			break;
		case 'function':
			$ret = $cfg['func']();
			break;
		default:
			die("配置异常！暂不支持读配置方式：" . $cfg['from']);
			break;
		}
		return $ret;
	}

	//serializer貌似可以自动序列化和反序列化
	public function getApcCache($key, $param="")
	{
		if ($this->apcEnable)
		{
			$ret = apc_fetch($key . $param);
			if (!empty($ret))
			{
				return $ret;
			}
		}
		$cfg = $this->getApcCfg($key);
		$ret = $this->getDataHard($cfg);
		if ($this->apcEnable)
		{
			if ($cfg['needHash'])
			{
				foreach($ret as $field => $value)
				{
					apc_store($key . $field, $value, self::APC_EXPIRE);
				}
			}
			apc_store($key, $value, $self::APC_EXPIRE);
		}

		if (!empty($param))
		{
			return $ret[$param];
		}
		return $ret;
	}
}
