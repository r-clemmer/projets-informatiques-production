<?php

	class FormationsController extends Zend_Controller_Action {
	
		public function init(){
			
		}
		
		public function indexAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
			
			$this->view->title = "Gestion des formations";
			
		}
		
		public function detailsAction(){
			
			if( $this->_request->getParam('id') > 0 ){
				
				$formation_id = $this->_request->getParam('id');
				$mFormations = new Model_Formation();
				$front = Zend_Controller_Front::getInstance();

				$formation = $mFormations->get( $formation_id );
				$entites = $mFormations->getEntites( $formation_id );
				
				$this->view->title = "Détails de la formation";
				
				$this->view->formation = $formation;
				//$this->view->entites = $entites;
				$ul_orgs_formation = '';
				foreach( $entites as $entite ){
					$ul_orgs_formation .= '<li>'.$entite['entite_nom'];
					$ul_orgs_formation .= ' <a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entite['entite_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0pt; display: inline-block; vertical-align: middle;" title="Détails" ></p></a>';
					$ul_orgs_formation .= '</li>';
				}
				$this->view->orgs_formation = $ul_orgs_formation;
				
			}else{
				$this->_redirect( '/formations/' );
			}
			
		}
		
		public function addAction(){
			
			if( $this->_request->isPost() ){
				
				$mFormation = new Model_Formation();
				$data = array(
					'formation_libelle' => $_POST['formation_libelle'],
					'formation_formacode' => $_POST['formation_formacode']
				 );
				$formation_id = $mFormation->insert( $data );
				
				$mFormation->addEntites( $formation_id, $_POST['entite_id'] );
				
				$this->_redirect( '/formations/' );
				
			}else{
				$this->view->title = "Création d'une formation";
				
				$mEntite = new Model_Entite();
				$types = array( 'organisme de formation' );
				$this->view->entites = $mEntite->getByTypesEntite( $types );
			}
			
		}
		
		public function updateAction(){
			
			if( $this->_request->getParam('id') > 0 ){
				
				$formation_id = $this->_request->getParam('id');
				
				$mFormation = new Model_Formation();
				$mEntiteFormation = new Model_EntiteFormation();
				
				if( $this->_request->isPost() ){
					
					//MAJ formation
					$data = array(
						'formation_libelle' => $_POST['formation_libelle'],
						'formation_formacode' => $_POST['formation_formacode']
					);
					$mFormation->update( $data, " formation_id = $formation_id " );
					
					//suppression entite formation
					$mEntiteFormation->delete( " formation_id = $formation_id " );
					
					//ajout entite formation
					$mFormation->addEntites( $formation_id, $_POST['entite_id'] );
					
					$this->_redirect( '/formations/' );
					
				}else{
					$this->view->title = "Modification de la formation";
					
					$formation = $mFormation->get( $formation_id );
					$ofs = $mFormation->getEntites( $formation_id );
					$this->view->formation = $formation;
					$this->view->ofs = $ofs;
					
					$mEntite = new Model_Entite();
					$types = array( 'organisme de formation' );
					$this->view->entites = $mEntite->getByTypesEntite( $types );
				}
				
			}else{
				$this->_redirect( '/formations/' );
			}
			
		}
		
		public function deleteAction(){
			
			if( $this->_request->getParam('id') > 0 ){
				
				$formation_id = $this->_request->getParam('id');
				$mFormations = new Model_Formation();
				
				if( $this->_request->isPost() ){
					
					$del = $this->_request->getPost('del');
					if ($del == 'Oui' && $formation_id > 0) {
				
						//suppression dans entite formation
						$mEntiteFormation = new Model_EntiteFormation();
						$mEntiteFormation->delete( " formation_id = $formation_id " );
						
						//suppression formmartion
						$mFormations->delete( " formation_id = $formation_id " );
						
						$this->_redirect( '/formations/' );
						
					}else{
						$this->_redirect( '/formations/' );
					}
				}else{
					
					$formation = $mFormations->get( $formation_id );
					$this->view->formation = $formation;
					
				}
				
			}else{
				$this->_redirect( '/formations/' );
			}
			
		}
		
		public function candidatsAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
			
			if( $this->_request->getParam('id') > 0 ){
				
				$this->view->formation_id = $this->_request->getParam('id');

				$mFormations = new Model_Formation();
				$formation = $mFormations->get($this->_request->getParam('id'));
				$lib = $formation['formation_libelle'];

				$this->view->title = "Liste des candidats liés à la formation \"$lib\"";
				
			}else{
				$this->_redirect( '/formations/' );
			}
				
		}

		public function attributecandidatsAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');

			if( $this->_request->getParam('id') > 0 ){

				$formation_id = $this->_request->getParam('id');

				$this->view->formation_id = $formation_id;

				$mFormations = new Model_Formation();

				$formation = $mFormations->get( $formation_id );

				$this->view->title = "Attribution des candidats pour la formation ".$formation['formation_libelle'];

			}else{
				$this->_redirect('/formations/');
			}

		}
		
	}