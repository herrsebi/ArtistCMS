<?php

abstract class ActiveRecord
{
	
	private static $_db;
	private static $_fields;
	private $_params;
	private $_changed;
	private $_id;
	
	public function __construct($table, $id)
	{
		if ( !self::$_db ) {
			self::initDB();	
		} 
		if ( $id ) {
			$this->_params = self::$_db->fetchRow("Select * from `" . static::$TABLE . "` WHERE `" . static::$TABLE ."_id` = $id");
			if ( $this->_params ) {
				$this->_id = $id;
			}
		}
	}
	
	public function __get($name)
	{
		return $this->_params[$name];
	}
	
	public function __set($key, $val)
	{
		if ( $key == static::$TABLE . '_id' || !in_array($key, self::$_fields) ) {
			throw new Exception("Not able to set field $key");
		} 
		$this->params[$key] = $val;
		$this->changed[$key] = true;	
	}
	
	public function save()
	{
		if ( $this->_id ) {
			$this->update();
		} else {
			$this->insert();
		}
	}
	
	public function delete()
	{
		self::$_db->delete(static::$TABLE,"`" . static::$TABLE . "_id` = " . $this->_id);
		$this->_changed = $this->_params = array();
		$this->_id = NULL;
	}
	
	private function update() 
	{
		if ( !emtpy($this->_changed) ) 
		{
			self::$_db->update(static::$TABLE,$this->params,"`" . static::$TABLE . "_id` = " . $this->_id);
		}
		$this->_changed = array();
	}
	
	private function insert()
	{
		self::$_db->insert(static::$TABLE,$this->params);
		$this->_id = self::$_db->lastInsertId();
	}
	
	
	private static function initDB()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/db.ini','testdb');
		self::$_db = Zend_Db::factory($config->db);
		self::$_fields = self::$_db->fetchCol("SHOW COLUMNS FROM `" . static::$TABLE . "`");
	}
}