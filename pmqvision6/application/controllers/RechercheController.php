<?php
	class RechercheController extends Zend_Controller_Action {
	
		function init(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
		
		}
		
		function indexAction(){
			$this->_redirect("/recherche/num/");
		}
		
		public function nonvalideeAction(){
			
			$this->view->title = "Liste des opérations en attente de validation";
			
		}
		
		public function numAction(){
			
			$this->view->title = "Rechercher une opération par son numéro";
			
			if( $this->_request->isPost() ){
				
				$num = $this->_request->getParam('num');
				
				$mOperation = new Model_Fiche();
				if( $num == '' ){
					$this->view->error = "Vous n'avez rien saisit !";
				}elseif( $mOperation->exist($num) == 1 ){
					$this->_redirect("/operations/visu/num/$num");
				}else{
					$this->view->num = $num;
					$this->view->error = "Cette opération n'existe pas !";
				}
				
			}
			
		}
		
		public function dateAction(){

			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');
			
			$this->view->title = "Rechercher par dates";
			
			if( $this->_request->isPost() ){
				
				$critere = $_POST['critere'];
				$date1 = $_POST['date1'];
				$date2 = $_POST['date2'];
				
				$this->view->critere = $critere;
				$this->view->date1 = $date1;
				$this->view->date2 = $date2;
				
				$fDates = new Fonctions_Dates();
				
				$ordre = $fDates->checkOrdreDate($date1, $date2);
				
				if( $fDates->checkDate($date1) == false || $fDates->checkDate($date2) == false ){
					
					$this->view->error = "L'une des dates saisies n'est pas correcte !";
					
				}elseif( $ordre == '>' ){
					
					$this->view->error = "La première date doit être inférieure ou égale à la seconde !";
					
				}else{
					
					$this->view->criteres = "valides";
					
				}
				
			}
			
		}
		
		public function nomAction(){
			
			$this->view->title = "Rechercher par nom";
			
			if( $this->_request->isPost() ){
				
				$this->view->resultats = 'resultats';
				
			}
			
		}
		
		public function demarchetitreAction(){

			$this->view->title = "Recherche par démarche/titre";

			$mDemarches = new Model_Demarche();
			$demarches = $mDemarches->getAll();
			$this->view->demarches = $demarches;

		}
	    
	}