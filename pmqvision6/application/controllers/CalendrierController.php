<?php
	class CalendrierController extends Zend_Controller_Action {
	
		function init(){
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/js/fullcalendar-1.4.5/redmond/theme.css');
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/js/fullcalendar-1.4.5/fullcalendar.css');
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/css/calendrier.css');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/fullcalendar-1.4.5/fullcalendar.js');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.tools.min.js');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/calendrier.js');
		}
		
		function indexAction(){
			//$this->_redirect('/calendrier/visualiser/');
			$this->view->title = 'Calendrier';
			
			$outil = new Model_Outil();
			$listeCQP = $outil->getAllOutils('cqp');
			$listeDiplome = $outil->getAllOutils('diplome');
			$listeCCSP = $outil->getAllOutils('ccsp');
			
			$this->view->outilsCqp = $listeCQP;
			$this->view->outilsDip = $listeDiplome;
			$this->view->outilsCcsp = $listeCCSP;
		}
		
	    public function getdatesoperationsAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
	    	
			$debut = $_POST['debut'];
			$fin = $_POST['fin'];
			$id = $_POST['id'];
			
			$html = "";
			$listeEvents = array();
	    	
			$calendar = new Model_Calendrier();
			$listeDates = $calendar->getDatesOperations($debut,$fin,$id);
			
			foreach($listeDates AS $date){
				$html .= $date->fiche_date_meo.'/'.$date->nb.'*';
			}
			
			echo $html;

	    }
		public function getdatesenvoidossierAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
	    	
			$debut = $_POST['debut'];
			$fin = $_POST['fin'];
			$id = $_POST['id'];
			
			$html = "";
			$listeEvents = array();
	    	
			$calendar = new Model_Calendrier();
			$listeDates = $calendar->getDatesEnvoiDossier($debut,$fin,$id);

			
			foreach($listeDates AS $date){
				$html .= $date->metier_id.'/'.$date->metier_date_envoi_dossiers.'/'.$date->nb.'*';
			}
			
			echo $html;
	    }
	    public function getdatesjuryAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$debut = $_POST['debut'];
			$fin = $_POST['fin'];
			$id = $_POST['id'];
			
			$html = "";
			$listeEvents = array();
	    	
			$calendar = new Model_Calendrier();
			$listeDates = $calendar->getDatesJury($debut,$fin,$id);

					
			
			foreach($listeDates AS $date){
				$html .= $date->nb.'/'.$date->jury_date.'*';
			}
			
			echo $html;
	    }
	    public function getdatesoutilAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$debut = $_POST['debut'];
			$fin = $_POST['fin'];
			$id = $_POST['id'];
			
			$html = "";
			$listeEvents = array();
	    	
			$calendar = new Model_Calendrier();
			$listeDates = $calendar->getDatesOutils($_POST['type'],$debut,$fin,$id);

					
			
			foreach($listeDates AS $date){
				$html .= $date->outil_libelle.'/'.$date->resultat_date.'/'.$date->nb.'*';
			}
			
			echo $html;
			
	    }

		public function getalldatesoutilAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$debut = $_POST['debut'];
			$fin = $_POST['fin'];
			$id = $_POST['id'];
			$type = $_POST['type'];
			
			$html = "";
			$listeEvents = array();
	    	
			$calendar = new Model_Calendrier();
			$listeDates = $calendar->getAllDatesOutils($type,$debut,$fin,$id);

					
			
			foreach($listeDates AS $date){
				$html .= $date->type.'/'.$date->resultat_date.'/'.$date->nb.'/'.$date->type.'*';
			}
			
			echo $html;
	    }
	    
	    public function getinfosdateAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
	    	$type = $_POST['type'];
	    	$id = $_POST['id'];
	    	$auth = $_POST['auth'];
	    	
	    	$calendar = new Model_Calendrier();
			$fDates = new Fonctions_Dates();
	    	$infos = "";
	    	
	    	switch ($type){
	    		case 'op':
	    			$date = $_POST['date'];
					$operations = $calendar->getInfosOperation($date,$auth);
					$infos = '<label>Mise en oeuvre le </label><span>'.$fDates->formatDate( $date ).'</span><br class="clear" />';
					$infos .= '<label>Opérations :</label><br class="clear" />';
					foreach($operations AS $operation){
						$infos .= '<span style="margin-left: 100px;">Opération : '.$operation->fiche_id.' (<a href="operations/visu/num/'.$operation->fiche_id.'" >Voir</a>)</span><br class="clear" />';
					}
					
	    		break;
	    		
	    		case 'dossier':

	    			$date = $_POST['date'];
	    			$metier = new Model_Metier();
	    			$infoMetier = $metier->getTitre($id);
					$infosDossier = $calendar->getInfosDossier($date,$auth);
					$infos = '<label>Date du jour :</label><span>'.$fDates->formatDate( $date ).'</span><br class="clear" />';
					$infos .= '<label>Dossier a envoyer :</label><br class="clear" />';
					foreach($infosDossier AS $dossier){
						$infos .= '<span style="margin-left: 100px;">Métier : '.strtoupper($infoMetier["demarche_abrege"]).' '.$infoMetier["bloc1_ab"].' (Opération <a href="operations/visu/num/'.$dossier->fiche_id.'" >'.$dossier->fiche_id.'</a>)</span><br class="clear" />';
					}
	    		break;
	    		
	    		case 'jury':
	    			$date = $_POST['date'];
					$jurys = $calendar->getInfosJury($date,$auth);
					$infos = '<label>Date du jour :</label><span>'.$fDates->formatDate( $date ).'</span><br class="clear" />';
					$infos .= '<label>Liste des jurys :</label><br class="clear" />';
					foreach($jurys AS $jury){
						$infos .= '<span style="margin-left: 100px;">Ville : '.ucwords( $jury->jury_ville ).' (<a href="jurys/details/?id='.$jury->jury_id.'" >Voir</a>)</span><br class="clear" />';
					}
					
					
	    		break;
	    		
	    		case 'outils':
	    			$date = $_POST['date'];
	    			$outil = $_POST['id'];
					$outils = $calendar->getInfosOutils($date,$outil,$auth);
					$infos = '<label>Date du jour :</label><span>'.$fDates->formatDate( $date ).'</span><br class="clear" />';
					$infos .= '<label>Type d\'outils :</label><span>'.ucfirst( $outil ).'</span><br class="clear" />';
					$infos .= '<label>Liste des '.$type.' :</label><br class="clear" />';
					foreach($outils AS $outil){
						$infos .= '<span style="margin-left: 5px;">Candidat : '.ucwords( $outil->personne_prenom.' '.$outil->personne_nom ).' (Opération <a href="operations/visu/num/'.$outil->fiche_id.'" >'.$outil->fiche_id.'</a>)</span><br class="clear" />';
					}
	    		break;    		
	    	}
	    	
	    	echo $infos;
	    }

	}