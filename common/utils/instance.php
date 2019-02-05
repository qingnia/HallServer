<?php
class single
{
	protected static $_instance = null;
	protected function __construct()
	{

	}
	protected function __clone()
	{
		//disallow clone

	}

	public function instance()
	{
		if (static::$_instance === null) {
			static::$_instance = new static;
		}
		return static::$_instance;
	}
}
