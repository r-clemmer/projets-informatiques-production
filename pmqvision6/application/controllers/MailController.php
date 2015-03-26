<?php

	class MailController extends Zend_Controller_Action {

		public function init(){

		}

		public function indexAction(){

			$this->view->title = "Envoi d'un mail";

			$type = $this->_request->getParam('type');
			$operation_id = $this->_request->getParam('operation_id');
			$this->view->operation_id = $operation_id;

			$mOperations = new Model_Fiche();
			$mEntites = new Model_Entite();
			$mContacts = new Model_Contact();
			$mObjectifs = new Model_Objectif();
			$mMetiers = new Model_Metier();
			$mTitres = new Model_Titre();
			$fDates = new Fonctions_Dates();

			$contacts = $mOperations->getEntites($operation_id);
			$entreprise = $mEntites->get($contacts['entreprise_id']);
			$org_ref = $mEntites->get($contacts['org_ref_id']);
			$del = $mEntites->get($contacts['del_id']);

			$this->view->subject = "Opération n°$operation_id - Entreprise ".ucwords( $entreprise['entite_nom'] );

			$operation = $mOperations->get($operation_id);

			$metiers = $mOperations->getMetiers( $operation_id );
			$titres = array();
			foreach( $metiers as $metier ){
				$titres[] = $mTitres->get($metier['demarche_id'], $metier['bloc1_id'], $metier['bloc2_id']);
				
				$experts[] = $mMetiers->getExperts2($metier['metier_id']);
				$evaluateurs[] = $mMetiers->getEvaluateurs2($metier['metier_id']);
				$organisme_spe[] = $mMetiers->getOrganisme($metier['metier_id']);
				
				$refEntreprise[] = $mMetiers->getReferentEntreprise($metier['metier_id']);
			}

			$creation = $fDates->formatDateMoisLettres( $operation['fiche_date_creation'] );
			$objectif = $mObjectifs->get( $operation['objectif_id'] );
			$objectif = ucfirst( $objectif['objectif_libelle'] );
			$branche = $mEntites->get($entreprise['parent_id']);
			$branche = ucfirst( $branche['entite_nom'] );

			$this->view->email = '';
			$contact_entreprise = $mContacts->getPersonne( $contacts['contact_entreprise_id'] );
			$c_entreprise = ucwords( $contact_entreprise['personne_prenom'].' '.$contact_entreprise['personne_nom'] );
			if( !empty($contact_entreprise['personne_mail']) ) $this->view->email = '';

			$contact_org_ref = $mContacts->getPersonne( $contacts['contact_org_ref_id'] );
			$c_org_ref = ucwords( $contact_org_ref['personne_prenom'].' '.$contact_org_ref['personne_nom'] );
			if( !empty($contact_org_ref['personne_mail']) ) $this->view->email .= $contact_org_ref['personne_mail'].';';
			
			$contact_del = $mContacts->getPersonne( $contacts['contact_del_id'] );
			$c_del = ucwords( $contact_del['personne_prenom'].' '.$contact_del['personne_nom'] );
			if( !empty($contact_del['personne_mail']) ) $this->view->email .= $contact_del['personne_mail'].';';

			switch( $type ){
				case 'validation' :
					
					$this->view->subject .= " [validation]";
					$this->view->message = "Bonjour,<br />
							l opération n°$operation_id pour l entreprise ".ucwords( $entreprise['entite_nom'] )." est conforme.<br />
							Vous pouvez vous connecter au logiciel PMQVision à l'aide votre mot de passe et identifiant et ainsi accéder à la gestion des candidats.<br />
							<br />Bonne continuation.<br />
							<br />
							En cas de besoin, n'hésitez pas à contacter le Département Etudes & Développement de Opcalia,<br />
							Flonia Dewevre :	01 76 53 55 02 flonia.dewevre@opcalia.com<br />
							Anne-Francoise Saladin : 01 76 53 55 16 anne-francoise.saladin@opcalia.com<br />
							<br />
							<strong>Opération n°$operation_id</strong><br />
							Créée le : $creation<br />
							Objectif : $objectif<br />
							Titres visés :
							<ul>";
					$index = 0;
					foreach( $titres as $titre ){
						$this->view->message .= "<br /><li>".ucwords( $titre['bloc1']['libelle'] )."</li>";
						$this->view->message .= "<u>Expert métier :</u><br />";
						$this->view->message .= "<ul>";
						
							foreach($experts[$index] AS $ex){
								$this->view->message .= "<li>".ucwords($ex['personne_nom']).' '.ucwords($ex['personne_prenom'])."</li>";
							}
						$this->view->message .= "</ul>";
						
						if(ucwords($titre['bloc1']['libelle'])== 'Compétences Clés En Situation Professionnelle')
						{
							$this->view->message .= "<u>Référent entreprise :</u><br />";
						$this->view->message .= "<ul>";
							foreach($refEntreprise[$index] AS $re){
								$this->view->message .= "<li>".ucwords($re['personne_nom']).' '.ucwords($re['personne_prenom'])."</li>";
							}
						$this->view->message .= "</ul>";
							
							$this->view->message .= "<u>Organisme spécialisé :</u><br />";
						$this->view->message .= "<ul>";
							foreach($organisme_spe[$index] AS $os){
								$this->view->message .= "<li>".ucwords($os['personne_nom']).' '.ucwords($os['personne_prenom'])."</li>";
							}
						$this->view->message .= "</ul>";
						}else{
							$this->view->message .= "<u>Référent évaluateur :</u><br />";
						$this->view->message .= "<ul>";
							foreach($evaluateurs[$index] AS $ev){
								$this->view->message .= "<li>".ucwords($ev['personne_nom']).' '.ucwords($ev['personne_prenom'])."</li>";
							}
						$this->view->message .= "</ul>";
						}
						
						$index++;
					}
					$this->view->message .= "
							</ul>
					";
					$this->view->message .= "
							<strong>Entreprise :</strong><br />
							Nom : ".ucwords( $entreprise['entite_nom'] )."<br />
							Branche : $branche<br />
							Activité : ".ucwords( $entreprise['entite_activite'] )."<br />
							Adresse : ".ucwords( $entreprise['entite_adresse'].', '.$entreprise['entite_ville'].'( '.$entreprise['entite_cp'].' )'  )."<br />
							Contact : $c_entreprise<br />
							Téléphone : ".$contact_entreprise['personne_tel']."<br />
							Mail : ".$contact_entreprise['personne_mail']."<br />
							<br />
							<strong>Organisme référent :</strong><br />
							Nom : ".ucwords( $org_ref['entite_nom'] )."<br />
							Adresse : ".ucwords( $org_ref['entite_adresse'].', '.$org_ref['entite_ville'].'( '.$org_ref['entite_cp'].' )'  )."<br />
							Contact : $c_org_ref<br />
							Téléphone : ".$contact_org_ref['personne_tel']."<br />
							Mail : ".$contact_org_ref['personne_mail']."<br />
							<br />
							<strong>Entité Opcalia :</strong><br />
							Nom : ".ucwords( $del['entite_nom'] )."<br />
							Adresse : ".ucwords( $del['entite_adresse'].', '.$del['entite_ville'].'( '.$del['entite_cp'].' )'  )."<br />
							Contact : $c_del<br />
							Téléphone : ".$contact_del['personne_tel']."<br />
							Mail : ".$contact_del['personne_mail']."<br />
							
					";
					$mOperations->updateDateMEO($operation_id);
					break;
				case 'demandeinformations' :
					$this->view->subject .= " [informations manquantes]";
					$this->view->message = "Bonjour,<br />
							il manque des informations pour l opération n°$operation_id, entreprise ".ucwords( $entreprise['entite_nom'] )."<br />
							Afin d activer l opération, merci de vous connecter au logiciel PMQVision et de renseigner les champs manquants ( liste ci-dessous ).<br />
							<br />Bonne continuation.<br />
							<br />
							En cas de besoin, n'hésitez pas à contacter le Département Etudes & Développement de Opcalia,<br />
							Flonia Dewevre :	01 76 53 55 02 flonia.dewevre@opcalia.com<br />
							Anne-Francoise Saladin : 01 76 53 55 16 anne-francoise.saladin@opcalia.com<br />
							<br />
							<strong>Opération n°$operation_id</strong><br />
							Créée le : $creation<br />";
					if( !$objectif ) $this->view->message .= "Objectif : <span class=\"error\" >non renseigné</span><br />";
					$this->view->message .= "
							<br />
							Titres visés :
							<ul>";
					foreach( $titres as $titre ){
						$this->view->message .= "<li>".ucwords( $titre['bloc1']['libelle'] )."</li>";
					}
					$this->view->message .= "
							</ul>
					";
					$this->view->message .= "
							<strong>Entreprise :</strong><br />";
					if( !$entreprise['entite_activite'] ) $this->view->message .= "Activité : <span class=\"error\">non renseignée</span><br />";
					if( !$entreprise['entite_adresse'] && $entreprise['entite_ville'] && $entreprise['entite_cp'] ) $this->view->message .= "Adresse : <span class=\"error\">non renseignée</span><br />";
					if( !$c_entreprise ) $this->view->message .= "Contact : <span class=\"error\">non renseigné</span><br />";
					if( !$contact_entreprise['personne_tel'] ) $this->view->message .= "Téléphone : <span class=\"error\">non renseigné</span><br />";
					if( !$contact_entreprise['personne_mail'] ) $this->view->message .= "Mail : <span class=\"error\">non renseigné</span><br />";
					$this->view->message .= "
							<br />
							<strong>Organisme référent :</strong><br />";
					if( !$org_ref['entite_activite'] ) $this->view->message .= "Activité : <span class=\"error\">non renseignée</span><br />";
					if( !$org_ref['entite_adresse'] && $org_ref['entite_ville'] && $org_ref['entite_cp'] ) $this->view->message .= "Adresse : <span class=\"error\">non renseignée</span><br />";
					if( !$c_org_ref ) $this->view->message .= "Contact : <span class=\"error\">non renseigné</span><br />";
					if( !$contact_org_ref['personne_tel'] ) $this->view->message .= "Téléphone : <span class=\"error\">non renseigné</span><br />";
					if( !$contact_org_ref['personne_mail'] ) $this->view->message .= "Mail : <span class=\"error\">non renseigné</span><br />";
					$this->view->message .= "
							<br />
							<strong>Entité Opcalia :</strong><br />";
					if( !$del['entite_activite'] ) $this->view->message .= "Activité : <span class=\"error\">non renseignée</span><br />";
					if( !$del['entite_adresse'] && $del['entite_ville'] && $del['entite_cp'] ) $this->view->message .= "Adresse : <span class=\"error\">non renseignée</span><br />";
					if( !$c_del ) $this->view->message .= "Contact : <span class=\"error\">non renseigné</span><br />";
					if( !$contact_del['personne_tel'] ) $this->view->message .= "Téléphone : <span class=\"error\">non renseigné</span><br />";
					if( !$contact_del['personne_mail'] ) $this->view->message .= "Mail : <span class=\"error\">non renseigné</span><br />";
					break;
				case 'demandevalidation' :
					$conf = Zend_Registry::getInstance()->config;
					$this->view->email = $conf->mails->default;
					$this->view->subject .= " [demande de validation]";
					$this->view->message = "Bonjour,<br />
							veuillez trouvez ci-dessous les informations relatives à l'opération n°$operation_id
							<br />
							<strong>Opération n°$operation_id</strong><br />
							Créée le : $creation<br />
							Objectif : $objectif<br />
							<br />
							Titres visés :
							<ul>";
					foreach( $titres as $titre ){
						$this->view->message .= "<li>".ucwords( $titre['bloc1']['libelle'] )."</li>";
					}
					$this->view->message .= "
							</ul>
					";
					$this->view->message .= "
							<strong>Entreprise :</strong><br />
							Nom : ".ucwords( $entreprise['entite_nom'] )."<br />
							Branche : $branche<br />
							Activité : ".ucwords( $entreprise['entite_activite'] )."<br />
							Adresse : ".ucwords( $entreprise['entite_adresse'].', '.$entreprise['entite_ville'].'( '.$entreprise['entite_cp'].' )'  )."<br />
							Contact : $c_entreprise<br />
							Téléphone : ".$contact_entreprise['personne_tel']."<br />
							Mail : ".$contact_entreprise['personne_mail']."<br />
							<br />
							<strong>Organisme référent :</strong><br />
							Nom : ".ucwords( $org_ref['entite_nom'] )."<br />
							Adresse : ".ucwords( $org_ref['entite_adresse'].', '.$org_ref['entite_ville'].'( '.$org_ref['entite_cp'].' )'  )."<br />
							Contact : $c_org_ref<br />
							Téléphone : ".$contact_org_ref['personne_tel']."<br />
							Mail : ".$contact_org_ref['personne_mail']."<br />
							<br />
							<strong>Entité Opcalia :</strong><br />
							Nom : ".ucwords( $del['entite_nom'] )."<br />
							Adresse : ".ucwords( $del['entite_adresse'].', '.$del['entite_ville'].'( '.$del['entite_cp'].' )'  )."<br />
							Contact : $c_del<br />
							Téléphone : ".$contact_del['personne_tel']."<br />
							Mail : ".$contact_del['personne_mail']."<br />
					";
					break;
			}

		}

		public function sendAction(){

			
		$config = array();
		$config = array('ssl' => 'tls','port' => 587,'auth' => 'login',
				'username' => 'e-services@opcalia.com',
				'password' => 'Opcalia878!'); // Port optionel fourni
		

		$mail = new Zend_Mail('UTF-8');
		$transport = new Zend_Mail_Transport_Smtp('pod51015.outlook.com', $config);
		$mail = new Zend_Mail();
		$mail->setSubject( utf8_decode($this->_request->getParam('subject') ));
		$mail->setBodyHtml(utf8_decode('Commentaire : '.$this->_request->getParam('texte').'<br><br>'.$this->_request->getParam('message') ));
		$mail->setFrom('e-services@opcalia.com', 'Plateforrme Certification');
	
		$mails = explode(';', $this->_request->getParam('email'));
			foreach( $mails as $m ){
				if( $m!='' ) $mail->addTo( $m );
			}

			//$mail->addTo("flonia.dewevre@opcalia.com");
			//$mail->addTo("anne.lebourgeois@opcalia.com");
			$mail->send($transport);

			$this->_redirect( $this->_request->getParam('url') );
			
		}

	}