<?php
class gm extends single
{
	protected static $_instance = null;

	public function dynamicStore()
	{
	}

	public function diyStats()
	{
	}

	public function test()
	{
		redisAgent::instance()->query("hset", "public", "store1", "aaa", "111");
	}
}
