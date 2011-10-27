<?php


class Application_Model_Image
{
	
	protected $_mapper;
	
	public function __construct()
	{
		$this->_mapper = new Cms_Model_Image();
	}
	
	/**
	 * 
	 * Returns all images in the database
	 */
	public function fetchAll()
	{
		return $this->_mapper->fetchAll($this->_mapper->select());
	}
	
	/**
	 * 
	 * @param unknown_type $limit
	 * @return Ambigous <Zend_Db_Table_Rowset_Abstract, unknown>
	 */
	public function fetchRecent($limit = false)
	{
		$select = $this->_mapper->select()
			->order("added DESC");
		if (is_numeric($limit))
		{
			$select->limit($limit);
		}
		return $this->_mapper->fetchAll($select);
	}
	
	public function addImage($name)
	{
		$row = $this->_mapper->createRow();
		$row->filename = $this->_reduceDirPath($name);
		$row->added = new Zend_Db_Expr("NOW()");
		$row->save();		
	}
	
	public function removeImage($id)
	{
		$rows = $this->_mapper->find($id);
		foreach ($rows as $row)
			$row->delete();
		return true;	 
	}
	
	public function editImage($id, $params)
	{
		$row = $this->_mapper->find($id);
		foreach ($params as $key => $value)
		{
			if (isset($row->$key))
				$row->$key  = $value;
		}
		$row->save();
	}
	
	public function imageExistsInDb( $name )
	{
		$select = $this->_mapper->select()->where('filename = "?"', $this->_reduceDirPath($name));
		if (!$this->_mapper->fetchRow($select))
			return false;
		return true;
	}
	
	private function _reduceDirPath($name)
	{
		$fileNameArray = explode(DIRECTORY_SEPARATOR, $name);
		return $fileNameArray[count($fileNameArray) - 1];
	}
}