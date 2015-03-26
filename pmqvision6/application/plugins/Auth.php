<?php
class Plugin_Auth extends Zend_Controller_Plugin_Abstract{
	
	public function preDispatch(Zend_Controller_Request_Abstract $request){
		
		$auth = Zend_Auth::getInstance();
		
		if(!$auth->hasIdentity()){
			if($request->getControllerName() != 'login'){
				$request->setControllerName('login');
				$request->setActionName('index');
			}
		}else{
			$auth->getIdentity();
		}
	}
	
}