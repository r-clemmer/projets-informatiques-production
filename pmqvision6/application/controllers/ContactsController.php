<?php

	class ContactsController extends Zend_Controller_Action {

		public function init(){
		}

		public function indexAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/suggestion.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/suggestion_contacts.js','text/javascript');
			$this->view->title = "Gestion des contacts";

	    }

		public function detailsAction(){

			$mContacts = new Model_Contact();
			$fDates = new Fonctions_Dates();
			$mCivilites = new Model_Civilite();
			$mEntites = new Model_Entite();
			$mTitres = new Model_Titre();
			$front = Zend_Controller_Front::getInstance();

			if( $this->_request->getParam('contact_id') > 0 ){
				if( $mContacts->exist( $this->_request->getParam('contact_id') ) ){
					$contact_id = $this->_request->getParam('contact_id');
				}else{
					$this->_redirect('/contacts/');
				}

			}elseif( $this->_request->getParam('contact') != null ){

				if( $mContacts->existByString( $this->_request->getParam('contact') ) ){
					$contact = $mContacts->getByNomStrict($this->_request->getParam('contact'));
					$contact_id = $contact['contact_id'];
				}else{
					$this->_redirect('/contacts/');
				}

			}else{
				$this->_redirect('/contacts/');
			}

			$contact = $mContacts->getPersonne($contact_id);
			$civilite = $mCivilites->get($contact['civilite_id']);
			$contact['civilite_lib'] = $civilite->civilite_libelle;
			$contact['personne_date_creation'] = $fDates->formatDate( $contact['personne_date_creation'] );
			$contact['contact_date_formation'] = $fDates->formatDate( $contact['contact_date_formation'] );
			$this->view->contact = $contact;
			$this->view->contact_id = $contact_id;
			if( $contact['personne_date_naissance'] > 0 ) $this->view->age = $fDates->getNbYears( $contact['personne_date_naissance'] ).' ans';
			else $this->view->age = "non connu";

			$entite = $mEntites->get($contact['entite_id']);
			$this->view->entite = $entite;

			$fonctions = $mContacts->getFonctions($contact['contact_id']);
			$string_fonctions = "";
			if( count($fonctions) > 0 ) $string_fonctions .= "<ul>";
			foreach( $fonctions as $fonction ){
				$string_fonctions .= '<li>'.$fonction['fonction_libelle'].'</li>';
			}
			if( count($fonctions) > 0 ) $string_fonctions .= "</ul>";
			$this->view->fonctions = $string_fonctions;

			$this->view->title = "Détails du contact";

			$expertises = $mContacts->getExpertises($contact_id);

			if( count( $expertises ) > 0 ){
				$s_expertises = "<ul>";
				foreach( $expertises as $expertise ){
					$s_expertises .= "<li>";
						$titre = $mTitres->get($expertise['demarche_id'], $expertise['bloc1_id'], $expertise['bloc2_id']);
						$s_expertises .= $titre['bloc1']['libelle'];
						if( isset( $titre['bloc2'] ) ){
							$s_expertises .= ' / '.$titre['bloc2']['libelle'];
						}
					$s_expertises .= "</li>";
				}
				$s_expertises .= "</ul>";
			}else{
				$s_expertises = " Aucune";
			}
			$this->view->expertises = $s_expertises;

			$dir = './documents/contacts/'.$contact['contact_id'].'/';

			$files = "";
			if (is_dir($dir)) {
				if ( $dh = opendir($dir) ) {
					$files = "<ul>";
					while (($file = readdir($dh)) !== false) {
						if($file != "." && $file != ".." && !is_dir($dir.$file)){
							//$files .= $dir.$file.'*';
							$files .= '<li>';
							$files .= '<a href="'.$front->getBaseUrl().'/outils/download?dir='.$dir.$file.'" rel="'.$dir.$file.'" class="linkFileOnContent" >'.$file.'</a>';
							$files .= '</li>';
						}
					}
					$files .= "</ul>";
					closedir($dh);
				}
			}
			$this->view->files = $files;

	    }

		public function addAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/suggestion.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/suggestion_contacts_entites.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');
			$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;
			$this->view->role = $role;
			
			$mCivilites = new Model_Civilite();
			$mPersonnes = new Model_Personne();
			$mContacts = new Model_Contact();
			$mFonctionContact = new Model_FonctionContact();
			$fDates = new Fonctions_Dates();

			if( $this->_request->isPost() ){

				$post = $_POST;

				//add personne
				$post['personne_date_naissance'] = $fDates->unformatDate( $post['personne_date_naissance'] );
				$post['personne_date_creation'] = date( "Y-m-d" );
				if($post['visible'] == 'on'){$visible = 'oui';}else{$visible ='non';}
				$data = array(
					'civilite_id'				=>	$post['civilite_id'],
					'personne_nom'				=>	strtolower( $post['personne_nom'] ),
					'personne_prenom'			=>	strtolower( $post['personne_prenom'] ),
					'personne_date_naissance'	=>	$post['personne_date_naissance'],
					'personne_tel'				=>	$post['personne_tel'],
					'personne_port'				=>	$post['personne_port'],
					'personne_mail'				=>	$post['personne_mail'],
					'personne_poste'			=>	$post['personne_poste'],
					'personne_date_creation'	=>	$post['personne_date_creation'],
					'entite_id'					=>	$post['entite_id'],
					'visible'					=> $visible
				);
				$personne_id = $mPersonnes->insert($data);

				//add contact
				if( $post['contact_forme'] == 'on' ){
					$post['contact_forme'] = 1;
				}else{
					$post['contact_forme'] = 0;
				}
				if ( $post['contact_date_formation'] == '' ){
					$post['contact_date_formation'] = '0000-00-00';
				}else{
					$post['contact_date_formation'] = $fDates->unformatDate( $post['contact_date_formation'] );
				}
				$data = array(
					'contact_forme'	=>	$post['contact_forme'],
					'contact_date_formation'	=>	$post['contact_date_formation'],
					'personne_id'	=>	$personne_id
				);
				$contact_id = $mContacts->insert($data);

				//add fonctions
				foreach( $post['fonctions'] as $fonction ){
					$mFonctionContact->set($contact_id, $fonction);
				}

			$contact_id = $contact_id;
			$demarche_id = $post['demarche_id'];
			$bloc1_id = $post['bloc1_id'];
			$bloc2_id = $post['bloc2_id'];

			if( $bloc2_id == null ) $bloc2_id = 0;

			$mExpertises = new Model_Expertise();

			$mExpertises->add($contact_id, $demarche_id, $bloc1_id, $bloc2_id);
				
				
				$this->_redirect("/contacts/details/?contact_id=$contact_id");

			}else{

				$this->view->civilites = $mCivilites->getAll();

			}

			$this->view->title = "Création d'un nouveau contact";

	    }

		public function updateAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/suggestion.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/suggestion_contacts_entites.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');
    		$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;
			$this->view->role = $role;
			
			if(isset($_GET['contact_id']) && $_GET['contact_id'] > 0 ){

				$contact_id = $_GET['contact_id'];

				$mCivilite = new Model_Civilite();
				$mContacts = new Model_Contact();
				$mPersonnes = new Model_Personne();
				$mEntite = new Model_Entite();
				$mFonctionContact = new Model_FonctionContact();
				$fDates = new Fonctions_Dates();

				if( $this->_request->isPost() ){
				if($_POST['visible'] == 'on'){$visible = 'oui';}else{$visible ='non';}
					$contact_id = $_GET['contact_id'];

					//table contact
					if( $_POST['contact_forme'] == 'on' ){
						$_POST['contact_forme'] = 1;
					}else{
						$_POST['contact_forme'] = 0;
					}
					$_POST['contact_date_formation'] = $fDates->unformatDate( $_POST['contact_date_formation'] );
					$data = array(
						'contact_forme'				=>	$_POST['contact_forme'],
						'contact_date_formation'	=>	$_POST['contact_date_formation']
					);
					$mContacts->update($data, " contact_id = $contact_id ");

					//table personne
					$contact = $mContacts->get($contact_id);
					$_POST['personne_date_naissance'] = $fDates->unformatDate( $_POST['personne_date_naissance'] );
					
					$data = array(
						'civilite_id'				=>	$_POST['civilite_id'],
						'personne_nom'				=>	strtolower( $_POST['personne_nom'] ),
						'personne_prenom'			=>	strtolower( $_POST['personne_prenom'] ),
						'personne_date_naissance'	=>	$_POST['personne_date_naissance'],
						'personne_tel'				=>	$_POST['personne_tel'],
						'personne_port'				=>	$_POST['personne_port'],
						'personne_mail'				=>	$_POST['personne_mail'],
						'personne_poste'			=>	$_POST['personne_poste'],
						'entite_id'					=>	$_POST['entite_id'],
						'visible'					=>	$visible
					);
					$mPersonnes->update($data, " personne_id = ".$contact['personne_id']);

					//table fonction_contact
					$mFonctionContact->delete( " contact_id = $contact_id " );
					foreach( $_POST['fonctions'] as $fonction ){
						$mFonctionContact->set($contact_id, $fonction);
					}
					$this->_redirect("/contacts/details/?contact_id=$contact_id");

				}else{

					$this->view->civilites = $mCivilite->getAll();

					$contact = $mContacts->getPersonne($contact_id);
					$contact['personne_date_naissance'] = $fDates->formatDate($contact['personne_date_naissance']);
					$contact['personne_date_creation'] = $fDates->formatDate($contact['personne_date_creation']);
					$contact['contact_date_formation'] = $fDates->formatDate($contact['contact_date_formation']);
					$this->view->contact = $contact;

					$entite = $mEntite->get($contact['entite_id']);
					$this->view->entite = $entite;

					$this->view->title = "Modifier le contact";

				}

	    	}else{
	    		$this->_redirect('/contacts/');
	    	}

	    }

		public function deleteAction(){

			$this->view->title = "Supprimer le contact ?";

	    }

	}