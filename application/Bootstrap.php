<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initLoginPlugin()
	{
		$front   = Zend_Controller_Front::getInstance();
		$plugin = new Login_ControllerPlugin();
		$front->registerPlugin($plugin);
		return $plugin;
	}
	
	
}

