<?php
require_once 'TestModel.php';


class ImageController extends Zend_Controller_Action
{
	/**
	 * Application Logger instance
	 * @var Zend_Log
	 */
	private $logger;
	/**
	 * @var Application_Model_Image
	 */
	private $imageModel;
	
	public function init()
	{
		$this->logger = $this->getInvokeArg('bootstrap')->getPluginResource('log')->getLog();
		$this->imageModel = new Application_Model_Image();
	}
	
	public function indexAction()
	{
		$this->view->image =$this->imageModel->fetchAll();
		$this->logger->debug('In Index Action');
	}
	
	/**
	 * Handle the information provided by the uploadify Widget, should redirect to home if no post data is
	 * provided. Returns json encoded data.
	 */
	public function uploadAction()
	{
		$this->logger->debug('Starting analysis');
    	$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/paths.ini', 'test');
    	$err = false;
    	try {
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload->setDestination($config->paths->images . '/raw');
			$upload->addValidator('IsImage', false);
			$files = $upload->getFileInfo();
		 	foreach ($files as $file => $info) {
				$this->logger->debug(print_r($info,true));
			    if (!$upload->isUploaded($file)) {
			    	$err = true;
			        continue;
			    }
			    if (!$upload->isValid($file)) {
			        $err = true;
			        continue;
			    }
			}
			if ( !$upload->receive() ) {
				$err = true;
				$messages = $upload->getMessages();
			} else {
				$this->imageModel->addImage($upload->getFileName());
				$messages[] = $upload->getFilename();				
			};
    	} catch (Exception $e) {
    		$err = true;
    		$this->logger->debug($e->getMessage() . ' in ' . $e->getTraceAsString());
    	}
    	$result = array(
    		"status" => $err,
    		"messages" => $messages
    	);
    	$responseObject = $this->getResponse();
    	$this->_helper->json($result);
	}
	
	public function uploadXhrAction()
	{
		$this->logger->debug('Starting XHR Upload');
		$request = $this->getRequest();
		$postData = $request->getRawBody();
		$this->logger->debug('Recieved ' . strlen($postData) . ' Bytes');
		$result = array(
			"size" 		=> strlen($postData),
			"filename" 	=> $request->getHeader('X-Filename'),
		);
		$this->_helper->json($result);
	}
	
	public function deleteAction()
	{
		$this->logger->debug("Delete Image: " . $this->_getParam('id'));	
		$result = $this->imageModel->removeImage($this->_getParam('id'));
    	$this->_helper->json($this->_getParam('id'));
	}
	
}