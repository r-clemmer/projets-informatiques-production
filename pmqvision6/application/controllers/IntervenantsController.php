<?php

	class IntervenantsController extends Zend_Controller_Action {
	
		public function init(){
		}
		
		public function indexAction(){
			
			$this->view->title = "Index des intervenants";

			$this->_redirect('/entites/');
			
	    }
	    
	}