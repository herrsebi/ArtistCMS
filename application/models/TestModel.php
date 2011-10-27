<?php
require_once 'ActiveRecord.php';


class TestModel extends ActiveRecord
{

	protected static $TABLE = 'test';
	
	public function __construct($id)
	{
		parent::__construct('test',$id);
	}
	
}