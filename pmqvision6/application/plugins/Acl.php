<?php
class Plugin_Acl extends Zend_Controller_Plugin_Abstract{
	
	public function preDispatch(Zend_Controller_Request_Abstract $request){
		
		$auth = Zend_Auth::getInstance();
		
		if($auth->hasIdentity()){
			$id = $auth->getIdentity()->entite_id;
		
			$acl = new Model_Droits_Acl();
			$user = new Model_Entite();
						
			//$droit = $_SESSION['Zend_Auth']['storage']->role;
			
			//$droitsUser = $user->getTypeEntite($id);
			$droitsUser = $user->getTypesEntite($id);
			//Zend_Debug::dump($droitsUser);
			
			$f = 0;
			$t = 0;
			foreach($droitsUser as $dU){
				if( $dU['type_entite_libelle'] == "forthac" ){
					$t++;
				}elseif($dU['type_entite_libelle'] == "organisme référent"){
					$f++;
				}
			}
			if( $t > 0 ){
				$droit = "forthac";
			}elseif($f > 0){
				$droit = "organisme référent";
			}else{
				$droit = $droitsUser[0]['type_entite_libelle'];
			}
			//Zend_Debug::dump( $droit );

			$controller = $request->getControllerName();
			$action = $request->getActionName();
			
			if($acl->has($controller)){
				//$droit=$droitsUser;
				
				if($acl->isAllowed($droit, $controller, $action)){
					$isAllow = true;
				}else{
					$isAllow = false;
				}
				
				if(!$isAllow){
					$request->setControllerName('error');
					$request->setActionName('droits');
				}
			}
			
		}
	}
	
}