<?php

class Model_Login implements Zend_Auth_Adapter_Interface{
	
	private $_login;
	private $_pass;
	
	public $_table;
	public $_identityColumn;
	public $_passwordColumn;
	
	public function __construct($login, $pass, $table = 'entite', $loginCol = 'entite_login', $passCol = 'entite_login'){
		$this->_login = $login;
		$this->_pass = $pass;
		
		$this->_table = $table;
		$this->_identityColumn = $loginCol;
		$this->_passwordColumn = $passCol;
		
	}
	
	public function authenticate(){
		
		if(!empty($this->_login)){
			
			$dbAdapter = Zend_Registry::get('db');
			
			$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
			$authAdapter->setTableName($this->_table);
			$authAdapter->setIdentityColumn($this->_identityColumn);
			$authAdapter->setCredentialColumn($this->_passwordColumn);
			
			$authAdapter->setIdentity($this->_login);
			$authAdapter->setCredential($this->_pass);
			
			$authInstance = Zend_Auth::getInstance();
			$getAuth = $authInstance->authenticate($authAdapter);
			$data = $authAdapter->getResultRowObject('entite_id', null);
			$authInstance->getStorage()->write($data);
			
			$mEntite = new Model_Entite();
			if( $_SESSION['Zend_Auth']['storage'] != false && $mEntite->get($_SESSION['Zend_Auth']['storage']->entite_id) != '' ){
				
				$droits = $mEntite->getTypesEntite($_SESSION['Zend_Auth']['storage']->entite_id);
				
				if( count($droits) == 1 ){
					
					$_SESSION['Zend_Auth']['storage']->role = $droits[0]['type_entite_libelle'];
					
				}else{
					
					foreach($droits as $droit){

						if($droit['type_entite_libelle'] == "forthac"){
							$_SESSION['Zend_Auth']['storage']->role = $droit['type_entite_libelle'];
						}elseif($droit['type_entite_libelle'] == "organisme référent"){
							$_SESSION['Zend_Auth']['storage']->role = $droit['type_entite_libelle'];
						}
						
					}
					
				}
				
			}
			
			$code = $getAuth->getCode();
		}else {
			$code = 2;
		}
		return $code;
	}
	
}