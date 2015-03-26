<?php
	class ErrorController extends Zend_Controller_Action
	{
	
		function init(){
			$auth = Zend_Auth::getInstance();
	    	if(!$auth->hasIdentity()){
	    		$this->_redirect('/login');
	    	}
	    	//$this->view->render('error');
		}
		
	    public function errorAction()
	    {
	        $errors = $this->_getParam('error_handler');
	
	        switch ($errors->type) {
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
	                // 404 error -- controller or action not found
	                $this->getResponse()->setHttpResponseCode(404);
	                $this->view->message = 'Page not found';
	                break;
	            default:
	                // application error
	                $this->getResponse()->setHttpResponseCode(500);
	                $this->view->message = 'Application error';
	                break;
	        }
	
	        Zend_Debug::dump($errors);
	        $this->view->exception = $errors->exception;
	        $this->view->request   = $errors->request;
	    }
	    
	    public function droitsAction(){
			$errors = $this->_getParam('error_handler');
	    	$this->getResponse()->setHttpResponseCode(500);
	        $this->view->message = 'Vous n\'avez pas accès à cette partie !';
	    }
	
	}