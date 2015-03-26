<?php

	class EntitesController extends Zend_Controller_Action {

		public function init(){
		}

		public function indexAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/suggestion.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/suggestion_entites.js','text/javascript');

			$this->view->title = "Gestion des entités";

	    }

		public function detailsAction(){

			$mEntites = new Model_Entite();
			$fDates = new Fonctions_Dates();

			if( $this->_request->getParam('entite_id') > 0 ){

				if( $mEntites->exist( $this->_request->getParam('entite_id') ) ){
					$entite_id = $this->_request->getParam('entite_id');
				}else{
					$this->_redirect('/entites/');
				}

			}elseif( $this->_request->getParam('entite_nom') != null ){

				if( $mEntites->existByString($this->_request->getParam('entite_nom')) ){
					$entite = $mEntites->getByLibelleStrict( $this->_request->getParam('entite_nom') );
					$entite_id = $entite['entite_id'];
				}else{
					$this->_redirect('/entites/');
				}

			}else{
				$this->_redirect('/entites/');
			}

			$this->view->title = "Détails de l'entité";

			$entite = $mEntites->get($entite_id);
			$entite['entite_date_creation'] = $fDates->formatDate($entite['entite_date_creation']);
			$this->view->entite = $entite;

			$this->view->types = $mEntites->getTypesEntite($entite_id);

			$this->view->parent = $mEntites->get($entite['parent_id']);

	    }

		public function addAction(){
			
			$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;
			$this->view->role = $role;
			
			$this->view->title = "Création d'une nouvelle entité";

	    	$mEntite = new Model_Entite();
	    	$branches = $mEntite->getByTypeEntite('branche');
	    	$this->view->branches = $branches;

	    	$mTypeEntite = new Model_TypeEntite();
	    	$types = $mTypeEntite->getListe();
	    	$this->view->types = $types;
		
	    	if($this->_request->isPost()){

	    		$data = $this->_request->getPost();

				$row = $mEntite->createRow();
	    		if($data['visible'] == 'on'){$visible = 'oui';}else{$visible ='non';}
	    		
				$row->entite_nom = $data['entite_nom'];
                $row->entite_code = $data['entite_code'];
                $row->entite_adresse = $data['entite_adresse'];
                $row->entite_ville = $data['entite_ville'];
                $row->entite_cp = $data['entite_cp'];
                $row->entite_activite = $data['entite_activite'];
                $row->entite_tel = $data['entite_tel'];
                $row->entite_date_creation = date('Y-m-d');
                $row->visible = $visible;
                if(isset($data['parent_id'])){
                	$row->parent_id = $data['parent_id'];
                }
                $row->entite_login = $data['entite_login'];
				
                $entite_id = $row->save();

                foreach($data['type_entite_id'] as $type){
                	$mEntiteTypeEntite = new Model_EntiteTypeEntite();
                	$mEntiteTypeEntite->set($entite_id, $type);
                }

				$this->_redirect('/entites/details/?entite_id='.$entite_id);

	    	}

	    }

		public function updateAction(){

			$mEntites = new Model_Entite();
			$fDates = new Fonctions_Dates();
			$mTypesEntite = new Model_TypeEntite();
			$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;
			$this->view->role = $role;

			if( $this->_request->getParam('entite_id') > 0 ){

				if( $mEntites->exist( $this->_request->getParam('entite_id') ) ){
					$entite_id = $this->_request->getParam('entite_id');
				}else{
					$this->_redirect('/entites/');
				}

			}else{
				$this->_redirect('/entites/');
			}

			if( $this->_request->isPost() ){
					if($this->_request->getParam('visible') == 'on'){$visible = 'oui';}else{$visible ='non';}
					$mEntites->update(
					$this->_request->getParam('entite_id'),
					addslashes($this->_request->getParam('entite_nom')),
					$this->_request->getParam('entite_code'),
					addslashes($this->_request->getParam('entite_adresse')),
					addslashes($this->_request->getParam('entite_ville')),
					addslashes($this->_request->getParam('entite_cp')),
					addslashes($this->_request->getParam('entite_activite')),
					addslashes($this->_request->getParam('entite_tel')),
					$this->_request->getParam('parent_id'),
					$this->_request->getParam('entite_login'),
//					$this->_request->getParam('entite_password'),
					$this->_request->getParam('type_entite_id'),
					$visible
				);

				$this->_redirect('/entites/details/?entite_id='.$this->_request->getParam('entite_id'));
				
			}else{

				$this->view->title = "Modification de l'entité";

				$entite = $mEntites->get($entite_id);
				$entite['entite_date_creation'] = $fDates->formatDate($entite['entite_date_creation']);
				$this->view->entite = $entite;

				$this->view->types = $mTypesEntite->fetchAll();

				$this->view->types_entite = $mEntites->getTypesEntite($entite_id);

				$branches = $mEntites->getByTypeEntite('branche');
				$this->view->branches = $branches;

			}

	    }

		public function deleteAction(){

			if( $this->_getParam('entite_id') > 0 ){
				$entite_id = $this->_getParam('entite_id');

				$entites = new Model_Entite();
				$entite = $entites->get($entite_id);

				$mPersonne = new Model_Personne();
				$personnes = $mPersonne->getListeByEntite($entite_id);
				$mCivilite = new Model_Civilite();
				foreach($personnes as &$personne){
					$civilite = $mCivilite->get($personne['civilite_id']);
					$personne['civilite'] = ucwords($civilite->civilite_libelle);
				}
				$this->view->personnes = $personnes;

				$mOperation = new Model_Fiche();
				$operations = $mOperation->getByEntite($entite_id);
				$this->view->operations = $operations;

				if($this->_request->isPost()) {
					$del = $this->_request->getPost('del');
					if ($del == 'Oui' && $entite_id > 0) {

						//supprimer dans entite_type_entite
						$mEntiteTypeEntite = new Model_EntiteTypeEntite();
						$mEntiteTypeEntite->deleteByEntite($entite_id);

						//supprimer dans contacts_fiche
						$mContactsFiche = new Model_ContactsFiche();
						$mContactsFiche->deleteEntite($entite_id);

						//supprimer dans personne
						$mPersonne->deleteEntite($entite_id);

						//supprimer entite
						$where = 'entite_id = '.$entite_id;
						$entites->delete($where);

						$this->_redirect('/entites/');
					}else{
						$this->_redirect('/entites/details/?entite_id='.$entite_id);
					}
				}

				$this->view->title = 'Supprimer l\'entité ?';
				$this->view->entite = $entite;
			}else{
				$this->_redirect('/entites/');
			}

	    }

	}