<?php
class LoginController extends Zend_Controller_Action
{
	public function init(){
		$this->view->title = 'Identification';
	}
	
	public function indexAction(){

//----------------------Debut de identication portail -------------

$url = 'http://192.168.253.200:80/forthac20/ft/verifier';
$app_id = $_GET['ft_app_id'];
$auth_token = $_GET['ft_auth_token'];
$sid = $_GET['ft_sid'];
//die();

  $ch = curl_init ("$url?ft_auth_token=$auth_token&ft_app_id=$app_id");
  curl_setopt ($ch, CURLOPT_COOKIE, "JSESSIONID=$sid");
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
  $output = curl_exec ($ch);
  $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if (200 == $http_status_code)
  {
$identity_card = $output;
$xml = simplexml_load_string($identity_card);
$k = 0;
//recherche global dans les propriétés global du USER
foreach($xml->properties->children() as $child1)
{
	//proprietés des informations USER identifiés
	$proprietes_user[$k] = $child1['name'];
	$k++;
}



$p_cnt = count($xml->properties->property); 
for($i = 0; $i < $p_cnt; $i++) { 
	//données des informations USER identifiés
  $LigneElement1[$i] = $xml->properties->property[$i];
  $donnees_user[$i] = $LigneElement1[$i];

} 


$count_info_user =  count($proprietes_user);
for($i = 0; $i < $count_info_user; $i++) 
	{ 
		//transfert des informations dans des variables globales pour le portail
		if($proprietes_user[$i] == 'lastname'){$nom_user_portail = $donnees_user[$i];}
		if($proprietes_user[$i] == 'firstname'){$prenom_user_portail = $donnees_user[$i];}
		if($proprietes_user[$i] == 'email'){$email_user_portail = $donnees_user[$i];}
	}



$k = 0;
foreach($xml->policy->xacl->children() as $child1)
{	
$j=0;
	//recupération des informations pour OSE
if($child1['id'] == "pmqvision")
{
		foreach($xml->policy->xacl->object->properties->children() as $child)
	 	 {	
		 	 //récupérations du login de l'application
		 	 		$login_user = $xml->policy->xacl->object[$k]->properties->property[0];
	 		 $j++; 		 
	  	 }
}  
  $k++;	
}


$this->_redirect('login/login/?username='.$login_user);
  }else
  {
echo $http_status_code;
    //manage bad response...
  }


//----------------------Fin de identication portail -------------


		//$front = Zend_Controller_Front::getInstance();
		//if( isset( $_GET['username'] ) ){
		//	$this->_redirect('login/login/?username='.$_GET['username']);
		//}

	}
	
	public function loginAction(){
		
		$login = $this->_request->getParam('username');
		$pass = $this->_request->getParam('username');
		
		$auth = new Model_Login($login, $pass);
		$code = $auth->authenticate();

		$redacteur = new Zend_Log_Writer_Stream('log.txt');
		$logger = new Zend_Log($redacteur);
		
		switch ($code) {
		
		    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
		    	$log = Zend_Auth::getInstance();
				$log->clearIdentity();
		        $this->view->error = 'L\'identifiant n\'existe pas.';
				$logger->err("L'identifiant n'existe pas ( $login ). IP : ".$_SERVER['REMOTE_ADDR']);
		        $this->render('index');
		        break;
		
		    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
		    	$log = Zend_Auth::getInstance();
				$log->clearIdentity();
		    	$this->view->error = 'Mot de passe incorrect.';
				$logger->err("Mot de passe incorrect. IP : ".$_SERVER['REMOTE_ADDR']);
		    	$this->render('index');
		        // mauvaise authentification
		        break;
		
		    case Zend_Auth_Result::SUCCESS:
		        // authentification acceptï¿½e
				$a = Zend_Auth::getInstance()->getIdentity();
				$logger->info("Connexion rÃ©ussie ( $login - entite_id = ".$a->entite_id." ). IP : ".$_SERVER['REMOTE_ADDR']);
				$this->_redirect('/');
		        break;
		        
		    case 2:
		    	$log = Zend_Auth::getInstance();
				$log->clearIdentity();
		    	$this->view->error = 'Veuillez remplir les champs !';
				$logger->err("Champs vides. IP : ".$_SERVER['REMOTE_ADDR']);
		    	$this->render('index');
		    	break;
		    	
		    case -2:
		    	$log = Zend_Auth::getInstance();
				$log->clearIdentity();
		    	$this->view->error = 'Plusieurs identifiants correspondent !';
				$logger->err("Plusieurs identifiants correspondent ( $login ). IP : ".$_SERVER['REMOTE_ADDR']."");
		    	$this->render('index');
		    	break;
		
		    default:
		    	$log = Zend_Auth::getInstance();
				$log->clearIdentity();
		    	$this->view->error = 'Erreur de connexion';
				$logger->err("ProblÃ¨me de connexion. IP : ".$_SERVER['REMOTE_ADDR']."");
		    	$this->render('index');
		        //autres cas
		        break;
		}

	}
	
	public function logoutAction(){
		$log = Zend_Auth::getInstance();
		$log->clearIdentity();
		unset($_SESSION);
		$this->_redirect('/login/');
		return $log;
	}

}
