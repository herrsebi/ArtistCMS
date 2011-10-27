<?php

class LoginController extends Zend_Controller_Action
{
	
	private $logger;
	
	public function init()
	{
		$this->logger = $this->getInvokeArg('bootstrap')->getPluginResource('log')->getLog();
	}
	
	public function getForm()
    {
        return new Login_Form(array(
            'action' => '/login/process',
            'method' => 'post',
        ));
    }

    public function getAuthAdapter(array $params)
    {
        $adapter = new Zend_Auth_Adapter_Digest(APPLICATION_PATH . '/configs/auth.txt',
        										'Login',
        										$params['username'],
        										$params['password']);
        return $adapter;
    }
    
 	public function indexAction()
    {
    	$this->logger->debug('Logging in...');
        $this->view->form = $this->getForm();
    }
	
	public function processAction()
    {
        $request = $this->getRequest();

        // Check if we have a POST request
        if (!$request->isPost()) {
            return $this->_helper->redirector('index');
        }

        // Get our form and validate it
        $form = $this->getForm();
        if (!$form->isValid($request->getPost())) {
            // Invalid entries
            $this->view->form = $form;
            return $this->render('index'); // re-render the login form
        }

        // Get our authentication adapter and check credentials
        $adapter = $this->getAuthAdapter($form->getValues());
        $auth    = Zend_Auth::getInstance();
        $result  = $auth->authenticate($adapter);
        if (!$result->isValid()) {
            // Invalid credentials
            $form->setDescription('Invalid credentials provided');
            $this->view->form = $form;
            return $this->render('index'); // re-render the login form
        }
		$this->logger->crit('Logged in!');
        // We're authenticated! Redirect to the home page
        $this->_helper->redirector('index', 'index');
    }
    
    public function logoutAction()
    {
    	$auth = Zend_Auth::getInstance();
    	if ( $auth->hasIdentity() )
    		$auth->clearIdentity();
    	$this->_helper->redirector->goToSimple('index','index');
    }
}