<?php

class IndexController extends Zend_Controller_Action
{

	protected $_imageModel;
	
    public function init()
    {
    	$this->_imageModel = new Application_Model_Image();
    }

    public function indexAction()
    {
        $this->view->recentImages = $this->_imageModel->fetchRecent(5); 
    }


}

