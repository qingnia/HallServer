<?php
class timeUtil extends single
{
	protected static $_instance = null;

	//0:基准时间，1:偏移，2:特殊条件的额外参数，如活动id
	public static function getExpireTs($infoStr)
	{
		$info = explode(',', $infoStr);

		switch($info[0])
		{
			case "normal":
				$baseTs = time();
				break;
			case "diy":
				$baseTs = 0;
				break;
			case "activity":
				//要先拿到活动相关配置
				break;
			case "forever":
				$baseTs = -1;
				$info[1] = 0;
				break;
			case "dayStart":
				$baseTs = strtotime(date('Y-m-d'));
				break;
			case "dayEnd":
				$baseTs = strtotime(date('Y-m-d')) + 86400;
				break;
			case "weekStart":
				break;
			case "weekEnd":
				break;
			case "monthStart":
				$baseTs = strtotime(date('Y-m'));
				break;
			case "monthEnd":
				break;
			default:
				$strace = debug_backtrace();
				$key = $strace[1]['args'][2];
				die("配置缺失：redis key:$key, 过期时间类型不存在:$infoStr");
		}
		$expireTs = $baseTs + $info[1];
		return $expireTs;
	}
}
