<?php

class Login_ControllerPlugin extends Zend_Controller_Plugin_Abstract
{
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity() && $request->getControllerName() !== 'login')
		{
			$request->setActionName('index');
			$request->setControllerName('login');
			$request->setDispatched(false);
		}
	}
	
}