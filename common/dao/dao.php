<?php
class dao extends single
{
	protected static $_instance = null;

	private $daoFieldCfg = null;

	public $roleID = 0;
	public function setRoleID($roleID)
	{
		$this->roleID = $roleID;
	}
	//简单个人相关信息都从这里拿，复杂的就单独搞，如邮件、任务
	private function getDaoFieldCfg($field)
	{
		if (empty($this->daoFieldCfg))
		{
			$this->daoFieldCfg = array(
					'roleID' => array('type'=>'int', 'tb'=>'account_tb', 'redis'=>'hashF,detail'),
					'name' => array('type'=>'string', 'tb'=>'account_tb', 'redis'=>'hashF,detail'),
					'skins' => array('type'=>'jsonWithTimeout', 'redis'=>'hashF,detail', 'relateK'=>'detail,curSkin'),
					'gold' => array('type'=>'int', 'redis'=>'hashF,detail'),
					'test' => array('type'=>'json', 'redis'=>'stringK'),
					);
		}
		if (!isset($daoFields[$field]) || !isset($daoFields[$field]['redis']))
		{
			die("配置缺失：dao, $field");
		}
		return $daoFields[$field];
	}
	private function parsePInfo($k, $v)
	{
		switch($daoFieldCfg[$k]['type'])
		{
		case 'int':
			return intval($v);
			break;
		case 'string':
			return stripslashes($v);
			break;
		case 'json':
			return json_decode($v, true);
			break;
		case 'jsonWithTimeout':
			$info = json_decode(stripslashes($v), true);
			return $this->filterTimeout($k, $v);
			break;
		default:
			die("配置缺失，$k, $v");
			break;
		}
	}
	private function filterTimeout($k, $list)
	{
		$newList = array();
		$now = time();
		foreach($list as $id => $row)
		{
			if (0 == $row[COMMON_EXP_AT] || $row[COMMON_EXP_AT] > $now)
			{
				$newList[$id] = $row;
			}
		}
		//'skins' => array('type'=>'jsonWithTimeout', 'redis'=>'hashF,detail', 'relateK'=>'detail,curSkin'),
		if (count($list) != count($newList))
		{
			$this->$k = $newList;

			//有东西过期，新数据重写回数据库
			save_common_list($role_id, $field, $new_list);
			$single_info = get_snake_detail_info_fields_with_default_value($role_id, array($key));
			//如果涉及到个人装扮，需要同步更改
			if ($type == "single_key")
			{
				if (!isset($new_list[$single_info[$key]]))
				{
					$default_value = get_snake_detail_default_value($key);
					set_snake_detail_info_fields_redis($role_id, array($key=>$default_value) );
					$role_info[$key] = $default_value;
				}
			}
		}
		return $newList;
	}

	//将要查的数据分类，先查detail，再查其他key，最后查数据库
	public function getPlayerInfo($fields)
	{
		$ret = array();
		//需要考虑从数据库恢复数据

		$hashFields = array();
		$stringKeys = array();
		$otherFields = array();
		foreach($fields as $f)
		{
			$cfg = getDaoFieldCfg($f);
			$redisCfg = explode(',', $cfg['redis']);
			if(isset($this->$f))
			{
				$ret[$f] = $this->$f;
			}
			elseif ($redisCfg[0] == 'hashF')
			{
				$hashKey = $redisCfg[1];
				$hashFields[$hashKey][] = $f;
			}
			elseif ($redisCfg[0] == 'stringK')
			{
				array_push($stringKeys, $f);
			}
			else
			{
				die("逻辑缺失，暂不支持list和set的整合查");
				array_push($otherFields, $f);
			}
		}
		$pInfos = array();
		foreach($hashFields as $oneKey => $oneFields)
		{
			$hashValues = redisAgent::instance()->query("hmget", $this->roleID, $oneKey, $oneFields);
			$pInfos = array_merge($pInfos, $hashValues);
		}
		//mget的批量需要特定修改才能支持
		$stringValues = redisAgent::instance()->query("mget", $this->roleID, $stringKeys);
		foreach($stringKeys as $index => $k)
		{
			$pInfos[$k] = $stringValues[$index];
		}

		//根据预设类型，对字段值做预处理
		foreach($pInfos as $k => $v)
		{
			$ret[$k] = $this->parsePInfo($k, $v);
		}
		return $ret;
	}

	public function setPlayerInfo($newPInfo)
	{
		$mysqlInfos = array();
		$hashInfos = array();
		$stringInfos = array();
		$otherInfos = array();
		foreach($newPInfo as $field => $value)
		{
			//先更新内存
			$this->$field = $value;

			$cfg = getDaoFieldCfg($field);
			//mysql
			$tb = $cfg['tb'];
			$mysqlInfos[$tb][] = $field . "=" . $value;

			//redis
			$redisCfg = explode(',', $cfg['redis']);
			if ($redisCfg[0] == 'hashF')
			{
				$hashKey = $redisCfg[1];
				$hashFields[$hashKey][$field] = $value;
			}
			elseif ($redisCfg[0] == 'stringK')
			{
				$stringInfos[$field] = $value;
			}
			else
			{
				die("逻辑缺失，暂不支持list和set的整合查");
				$otherInfos[$field] = $value;
			}
		}

		//先写缓存
		foreach($hashInfos as $key => $infos)
		{
			redisAgent::instance()->query("hmset", $this->roleID, $key, $infos);
		}
		//mset需要特殊支持
		redisAgent::instance()->query("mset", $this->roleID, $stringInfos);

		//数据库有的字段也要同步更新
		foreach($mysqlInfos as $tb => $infos)
		{
			$updateStrs = implode(',', $infos);
			$sql = "update $tb set $updateStrs where roleID = $this->roleID";
			dbAgent::instance()->db('main')->query($sql);
		}
		return true;
	}
}
