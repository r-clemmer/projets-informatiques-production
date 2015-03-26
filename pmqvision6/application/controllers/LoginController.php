<?php
class LoginController extends Zend_Controller_Action
{
	public function init(){
		$this->view->title = 'Identification';
	}
	
	public function indexAction(){

//----------------------Debut de identication portail -------------

		$url_application = "https://plateforme-certification.opcalia.com/login/response";
		$url_application = urlencode($url_application);
		$url_acces = 'https://sso-primaire.opcalia.com/ifs/profiles/aselect?request=authenticate&app_id=13e8123b-eb89-4e1b-b694-388ac596de3a&app_url='.$url_application.'&a-select-server=opcaliaIfs';

		$tuCurl = curl_init();
		curl_setopt($tuCurl, CURLOPT_URL, $url_acces);
		curl_setopt($tuCurl, CURLOPT_PORT , 443);
		curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
		
		
		$info = curl_exec($tuCurl);
		
		$url = explode('as_url=',$info);
		$url_finale = urldecode($url[1]);
		
		$this->_redirect($url_finale);




  }


//----------------------Fin de identication portail -------------

	
	public function index2Action(){
		
		if(isset($_GET['login'])){
			$login = $_GET['login'];
		}else{	$login = '';
		}
		
		if(isset($_GET['application'])){
			$application = $_GET['application'];
		}else{	$application = '';
		}
		
		$client = @new SoapClient("http://localhost/opcalia-webservices/portailExtranet.wsdl");
			$parametres = array('user_portail'=>$login, 'application'=> $application);
			$response = $client->webService($parametres);

		$this->_redirect('login/login/?username='.$response['login']);
	
	
	}
	
	
	
	public function requestAction(){
	
		
		$url_application = urldecode($_GET["url"]);
		$url_application = urlencode($url_application);
		$url_acces = 'https://sso-primaire.opcalia.com/ifs/profiles/aselect?request=authenticate&app_id=13e8123b-eb89-4e1b-b694-388ac596de3a&app_url='.$url_application.'&a-select-server=opcaliaIfs';

		$tuCurl = curl_init();
		curl_setopt($tuCurl, CURLOPT_URL, $url_acces);
		curl_setopt($tuCurl, CURLOPT_PORT , 443);
		curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
		
		
		$info = curl_exec($tuCurl);
		
		$url = explode('as_url=',$info);
		$url_finale = urldecode($url[1]);
		
		$this->_redirect($url_finale);

	
	
	}
	
	
	
	public function responseAction(){
	
	
	
$url = 'https://sso-primaire.opcalia.com/ifs/profiles/aselect?request=verify_credentials&aselect_credentials='.$_GET["aselect_credentials"].'&rid='.$_GET['rid'].'&a-select-server='.$_GET['a-select-server'];
	
$tuCurl = curl_init();
curl_setopt($tuCurl, CURLOPT_URL, $url);
curl_setopt($tuCurl, CURLOPT_PORT , 443);
curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);


$info = curl_exec($tuCurl);

$reponse_tableau = explode("&",$info);
if (in_array ("result_code=0000", $reponse_tableau)) {
	foreach ($reponse_tableau as $verification)
	{

		if (preg_match("/uid=/i", $verification,$donnees)) {

			$login = explode('=',$verification);
			$login = $login[1];
			
			$client = new SoapClient("https://e-services.opcalia.com/webservices/portailExtranet.wsdl");


			$parametres = array('user_portail'=>$login, 'application'=> 'pmqvision5', 'cle'=> 'd7143ceca971b2cebe47c17a25693142');
			$response = $client->webService($parametres);
			$this->_redirect('login/login/?username='.$response['login']);

		}
	}
}	
	
	
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
		        // authentification accept�e
				$a = Zend_Auth::getInstance()->getIdentity();
				$logger->info("Connexion réussie ( $login - entite_id = ".$a->entite_id." ). IP : ".$_SERVER['REMOTE_ADDR']);
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
				$logger->err("Problème de connexion. IP : ".$_SERVER['REMOTE_ADDR']."");
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
