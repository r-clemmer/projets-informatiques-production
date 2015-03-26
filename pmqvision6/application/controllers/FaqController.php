<?php
	class FaqController extends Zend_Controller_Action {
	
		public function init(){
		
			//variable $this->view->valide
			//true	=	affiche tout les messages (forthac)
			//false = 	affiche les messages validés
			
			if( $_SESSION['Zend_Auth']['storage']->role == "forthac" ){
				$this->view->valide = true;
			}else{
				$this->view->valide = false;
			}
			
		}
		
		public function indexAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/faq/index.css");
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/jquery.rating.css");

			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.rating.js','text/javascript');

			$conf = Zend_Registry::getInstance()->config;
			
			$this->view->title = 'FAQ';
			
			if( $this->_request->isPost() ){
				
				$mQuestion = new Model_Question();
				if( !isset( $_POST['question_severite'] ) || $_POST['question_severite'] == '' ) $_POST['question_severite'] = 0;
				$mQuestion->set($_POST['question_auteur'], $_POST['question_objet'], $_POST['question_message'], $_POST['question_severite']);

				$url = urlencode($_SERVER['HTTP_REFERER']);
				$email = $conf->mails->default;
				$subject = base64_encode( "Demande de validation d'un message dans la FAQ" );
				$message = base64_encode( "Un nouveau message doit être validé dans la FAQ de PMQVision." );
				$this->_redirect('/mail/send/?url='.$url.'&email='.$email.'&subject='.$subject.'&message='.$message);
			}
			
	    }
	    
	    public function afficherAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/faq/afficher.css");
	    	
	    	if($_GET['question'] > 0){
		    	$this->view->title = "FAQ - message";
		    	
		    	$mQuestion = new Model_Question();
		    	$question = $mQuestion->get($_GET['question']);
		    	
		    	$fDates = new Fonctions_Dates();
		    	$DH = $fDates->formatDateHeure($question->question_date);
		    	$question->date = $DH['date'];
		    	$question->heure = $DH['heure'];
		    	
		    	$this->view->question = $question;
		    	
		    	$mReponse = new Model_Reponse();
		    	
		    	$reponses = $mReponse->getListeByQuestion($_GET['question']);
		    	
		    	foreach($reponses as &$reponse){
		    		$r = $fDates->formatDateHeure($reponse['reponse_date']);
		    		$reponse['date'] = $r['date'];
		    		$reponse['heure'] = $r['heure'];
		    	}
		    	
		    	$this->view->reponses = $reponses;
		    	
		    	if($this->_request->isPost()){
		    		
		    		$mReponse->set($_POST['reponse_auteur'], $_POST['reponse_message'], $_POST['question_id']);
		    		
		    		$this->_redirect('/faq/afficher/?question='.$_POST['question_id']);
		    		
		    	}
		    	
	    	}else{
	    		$this->_redirect('/faq/');
	    	}
	    	
	    }
	    
	}