<?php
class timeMgr extends single
{
	protected static $_instance = null;

	public function modifyDbPartition()
	{
		$dayTs = strtotime(date('Y-m-d')) + 1;
		$dayNum = ($dayTs / 86400);
		$cfg = getDbPartitionCfg();
		foreach($cfg as $tb => $info)
		{
			//添加新分区
			$sql = "alter table $tb reorganize partition pother into (partition p$dayNum values less than($dayTs),  PARTITION pother VALUES LESS THAN MAXVALUE);"
			dbAgent::instance()->db($info['db'])->query($sql);

			//删除老分区
		}
	}
}
