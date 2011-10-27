<?php 

class Zend_View_Helper_RecentImages extends Zend_View_Helper_Abstract
{
	
	public function recentImages()
	{
		return $this->view->render('helper/recentImages.phtml');
	}
	
}
