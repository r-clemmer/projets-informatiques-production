<?php

	class AjaxController extends Zend_Controller_Action {
	
		public function init(){
			
			$this->_helper->viewRenderer->setNoRender();
			Zend_Layout::getMvcInstance()->disableLayout();
			
		}
		
		public function autocompletionentitesAction(){
			
			$mEntite = new Model_Entite();
			$liste = $mEntite->getByLibelle($_POST['queryString']);
			
			foreach($liste as $entite){
				echo '<li onClick="javascript:fill('.$entite['entite_id'].');">'.ucwords($entite ['entite_nom']).' ( '.ucwords($entite ['entite_ville']).')</li>';
			}
		}
		
		public function autocompletioncontactsentitesAction(){
			
			$mEntite = new Model_Entite();
			$liste = $mEntite->getByLibelle($_POST['queryString']);
			
			foreach($liste as $entite){
				echo '<li onClick="javascript:fill('.$entite['entite_id'].', \''.$entite['entite_nom'].'\');">'.ucwords($entite ['entite_nom']).' ( '.ucwords($entite ['entite_ville']).')</li>';
			}
		}

		public function autocompletionentreprisesAction(){

			$mEntites = new Model_Entite();
			if( $_POST['queryString'] ) $liste = $mEntites->getEntreprisesByNom( $_POST['queryString'] );
			else $liste = $mEntites->getEntreprisesByNom();

			foreach($liste as $entite){
				echo '<li onClick="javascript:fill('.$entite['entite_id'].', \''.$entite['entite_nom'].'\');">'.ucwords($entite ['entite_nom']).' ( '.ucwords($entite ['entite_ville']).')</li>';
			}
		}
		
		public function getfonctionsbyentiteAction(){
			
			$entite_id = $_POST['entite_id'];
			
			$mEntite = new Model_Entite();
			$types = $mEntite->getTypesEntite($entite_id);
			
			foreach($types as $type){
				$mFonction = new Model_Fonction();
				$tfonctions[] = $mFonction->getListeByTypeEntite($type['type_entite_id']);
			}
			
			if(isset($_POST['contact_id'])){
				$contact_id = $_POST['contact_id'];
				$mContact = new Model_Contact();
				$fs = $mContact->getFonctions($contact_id);
			}
			
			foreach($tfonctions as $fonctions){
				foreach($fonctions as $fonction){
					$select = "";
					if(isset($fs)){
						foreach($fs as $f){
							if($f['fonction_id'] == $fonction['fonction_id']){
								$select = " selected ";
							}
						}
					}
					echo '<option value="'.$fonction['fonction_id'].'" '.$select.' >'.$fonction['fonction_libelle'].'</option>';
				}
			}
			
		}
		
		public function autocompletioncontactsAction(){
			
			$mPersonne = new Model_Personne();

			$liste = $mPersonne->getListePersonne($_POST['queryString']);
			
			foreach($liste as $personne){

				$contact = $mPersonne->getContact( $personne['personne_id'] );

				if($personne['civilite_abrege'] == 'nc'){
					$civilite = '';
				}else{
					$civilite = $personne['civilite_abrege'];
				}
				echo '<li onClick="javascript:fill('.$contact['contact_id'].');">'.ucwords($civilite.' '.$personne['personne_prenom'].' '.$personne['personne_nom']).'</li>';
			}
		}
		
		public function getlistequestionsAction(){
			
			$mQuestion = new Model_Question();
			$count = $mQuestion->count($_GET['valide']);
			
			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			$questions = $mQuestion->getListe($sidx, $sord, $start, $limit, $_GET['valide']);
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			$i = 0;
			foreach($questions as $question){
				
				$responce->rows[$i]['question_id']=$question['question_id'];
				
				$s = $question['question_severite'];
				$severite = "";
				switch($s){
					case 0 :
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star_empty.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star_empty.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star_empty.png" alt="'.$s.'" title="'.$s.'" />';
						break;
					case 1 :
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star_empty.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star_empty.png" alt="'.$s.'" title="'.$s.'" />';
						break;
					case 2 :
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star_empty.png" alt="'.$s.'" title="'.$s.'" />';
						break;
					case 3 :
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star.png" alt="'.$s.'" title="'.$s.'" />';
						$severite .= '<img src="'.$this->_request->getBaseUrl().'/img/star.png" alt="'.$s.'" title="'.$s.'" />';
						break;
				}
				
				if($question['question_valide'] == 1){
					$valide = '<img src="'.$this->_request->getBaseUrl().'/img/complete.gif" alt="Validée par le Forthac" title="Validée par le Forthac" />';
				}else{
					$valide = '<img src="'.$this->_request->getBaseUrl().'/img/incomplete.gif" alt="Non validée par le Forthac" title="Non validée par le Forthac" />';
				}
				
				$fDates = new Fonctions_Dates();
				$DH = $fDates->formatDateHeure( $question['question_date'] );
				
				$actions = '<a href="'.$this->_request->getBaseUrl().'/faq/afficher/?question='.$question['question_id'].'"><img src="'.$this->_request->getBaseUrl().'/img/eye.png" alt="Afficher" title="Afficher" /></a> '.$valide;
				
				$responce->rows[$i]['cell'] = array(
					$DH['date'].' '.substr($DH['heure'], 0, 5),
					$question['question_auteur'],
					$question['question_objet'],
					$severite,
					$question['count'],
					$actions
				);
				
				$i++;
			}
			
			echo json_encode($responce);
			
		}
		
		public function validemessageAction(){
			
			$question_id = $_POST['question_id'];
			$valide = $_POST['valide'];
			
			$mQuestion = new Model_Question();
			$mQuestion->valider($question_id, $valide);
			
		}
		
		public function getlistecontactsAction(){
			
			$entite_id = $_POST['entite_id'];
			
			$mEntite = new Model_Entite();
			$contacts = $mEntite->getContactsActifs($entite_id);
			
			$mCivilite = new Model_Civilite();
			
			echo '<option value="-1">Veuillez choisir un contact</option>';
			foreach($contacts as $c){
				$civilite = $mCivilite->get($c['civilite_id']);
				$civ = ucwords($civilite->civilite_abrege);
				if( $civ == 'Nc' ) $civ = '';
				$nom = $c['personne_nom'];
				$prenom = $c['personne_prenom'];
				$contact_id = $c['contact_id'];
				echo "<option value='$contact_id'>".ucwords( $civ." ".$prenom." ".$nom )."</option>";
			}
			
		}
		
		public function getlistenonvalideeAction(){
			
			$mOperation = new Model_Fiche();
			$front = Zend_Controller_Front::getInstance();
			
			$auth = Zend_Auth::getInstance();
	    	$role = $_SESSION['Zend_Auth']['storage']->role;
			if( $role == "forthac" ){
	    		$count = $mOperation->countnonvalidee();
	    	}else{
	    		$entite_id = $_SESSION['Zend_Auth']['storage']->entite_id;
	    		$count = $mOperation->countnonvalidee( $entite_id );
	    	}
			
			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			if( $role == "forthac" ){
				$operations = $mOperation->getListeNonValidees($sidx, $sord, $start, $limit);
			}else{
				$operations = $mOperation->getListeNonValideesByEntite($sidx, $sord, $start, $limit, $entite_id);
			}
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$mEntite = new Model_Entite();
			
			
			$i = 0;
			foreach($operations as $operation){
				
				$responce->rows[$i]['id']=$operation['fiche_id'];
				
				$fDates = new Fonctions_Dates();
				$date_creation = $fDates->formatDate($operation['fiche_date_creation']);
				
				$entites = $mOperation->getEntites( $operation['fiche_id'] );
				
				if( isset( $entites['entreprise_id'] ) ){
					$entreprise = $mEntite->get( $entites['entreprise_id'] );
					$l_entreprise = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entites['entreprise_id'].'">'.ucwords( $entreprise['entite_nom'] ).'</a>';
				}
				if( isset( $entites['org_ref_id'] ) ){
					$org_ref = $mEntite->get( $entites['org_ref_id'] );
					$l_org_ref = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entites['org_ref_id'].'">'.ucwords( $org_ref['entite_nom'] ).'</a>';
				}
				if( isset( $entites['del_id'] ) ){
					$del = $mEntite->get( $entites['del_id'] );
					$l_del = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entites['del_id'].'">'.ucwords( $del['entite_nom'] ).'</a>';
				}
				
				$responce->rows[$i]['cell'] = array(
					$date_creation,
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$operation['fiche_id'].'">'.$operation['fiche_id'].'</a>',
					$l_entreprise,
					$l_org_ref,
					$l_del
				);
				
				$i++;
			}
			
			echo json_encode($responce);
			
		}

		public function getlistefichesAction(){


			if( isset( $_POST['page'] ) ) {
				$page = $_POST['page']; // get the requested page
				$limit = $_POST['rows']; // get how many rows we want to have into the grid
				$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				$sord = $_POST['sord']; // get the direction
			}else{
				$page = 1;
				$limit = 10;
				$sidx = 'fiche_date_creation';
				$sord = 'desc';
			}

			$mOperation = new Model_Fiche();
			$mEntites = new Model_Entite();
			$front = Zend_Controller_Front::getInstance();

			$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;
			$entite_id = $auth->entite_id;
			if( $role == "forthac" ){
	    		$count = $mOperation->count();
	    	}elseif( $role == 'branche' ){
				$count = count( $mOperation->getByBranche($entite_id) );
			}else{
	    		$count = $mOperation->count( $entite_id );
	    	}

			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}

			if ($page > $total_pages) $page=$total_pages;

			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			if( $role == "forthac" ){
				$operations = $mOperation->getListe($sidx, $sord, $start, $limit);
			}elseif( $role == 'branche' ){
				$operations = $mOperation->getByBranche($entite_id, null, $sidx, $sord, $start, $limit);
			}else{
				$operations = $mOperation->getListe($sidx, $sord, $start, $limit, $entite_id);
			}

			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$i = 0;
			foreach($operations as $operation){

				$l_entreprise = $l_org_ref = $l_del = '';

				$responce->rows[$i]['id']=$operation['fiche_id'];

				$fDates = new Fonctions_Dates();
				$date_creation = $fDates->formatDate($operation['fiche_date_creation']);

				$entites = $mOperation->getEntites( $operation['fiche_id'] );

				if( isset( $entites['entreprise_id'] ) ){
					$entreprise = $mEntites->get( $entites['entreprise_id'] );
					$l_entreprise = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entites['entreprise_id'].'">'.ucwords( $entreprise['entite_nom'] ).'</a>';
				}
				if( isset( $entites['org_ref_id'] ) ){
					$org_ref = $mEntites->get( $entites['org_ref_id'] );
					$l_org_ref = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entites['org_ref_id'].'">'.ucwords( $org_ref['entite_nom'] ).'</a>';
				}
				if( isset( $entites['del_id'] ) ){
					$del = $mEntites->get( $entites['del_id'] );
					$l_del = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entites['del_id'].'">'.ucwords( $del['entite_nom'] ).'</a>';
				}

				$responce->rows[$i]['cell'] = array(
					$date_creation,
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$operation['fiche_id'].'">'.$operation['fiche_id'].'</a>',
					$l_entreprise,
					$l_org_ref,
					$l_del
				);

				$i++;
			}

			echo json_encode($responce);

		}
		
		public function checkloginAction(){
			
			$login = $_POST['string'];
			$entite_id = null;
			if( isset( $_POST['entite_id'] ) ) $entite_id = $_POST['entite_id'];
			
			$mEntite = new Model_Entite();
			echo $mEntite->existLogin( $login, $entite_id );
			
		}
		
		public function getrechercheoperationsAction(){
			
			if( $_GET['date1'] ){
				$date1 = $_GET['date1'];
			}else{
				$date1 = null;
			}
			
			if( $_GET['date2'] ){
				$date2 = $_GET['date2'];
			}else{
				$date2 = null;
			}
			
			$mOperation = new Model_Fiche();
			$front = Zend_Controller_Front::getInstance();
			
			$role = Zend_Auth::getInstance()->getIdentity()->role;
			$entite_id = Zend_Auth::getInstance()->getIdentity()->entite_id;
			if( $role == "forthac" ){
	    		$count = $mOperation->count( null, $date1, $date2);
	    	}elseif( $role == 'branche' ){
				$count = count( $mOperation->getByBranche($entite_id, NULL, NULL, NULL, NULL, NULL, $date1, $date2) );
			}else{
	    		$count = $mOperation->count( $entite_id, $date1, $date2 );
	    	}

			if( isset( $_POST['page'] ) ){
				$page = $_POST['page']; // get the requested page
				$limit = $_POST['rows']; // get how many rows we want to have into the grid
				$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				$sord = $_POST['sord']; // get the direction
			}else{
				$page = 1;
				$limit = 10;
				$sidx = "fiche_date_creation";
				$sord = 'ASC';
			}
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			if( $role == "forthac" ){
				$operations = $mOperation->getListe($sidx, $sord, $start, $limit, null, $date1, $date2);
			}elseif( $role == 'branche' ){
				$operations = $mOperation->getByBranche($entite_id, NULL, NULL, NULL, 0, 10000, $date1, $date2);
			}else{
				$operations = $mOperation->getListe($sidx, $sord, $start, $limit, $entite_id, $date1, $date2);
			}
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$mEntite = new Model_Entite();
			
			$i = 0;
			foreach($operations as $operation){
				
				$responce->rows[$i]['id']=$operation['fiche_id'];
				
				$fDates = new Fonctions_Dates();
				$date_creation = $fDates->formatDate($operation['fiche_date_creation']);
				
				$entites = $mOperation->getEntites( $operation['fiche_id'] );

				$entreprise=$org_ref=$del=null;
				$link1=$link2=$link3='';

				if( isset($entites['entreprise_id']) ){
					$entreprise = $mEntite->get( $entites['entreprise_id'] );
					$link1 = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entreprise['entite_id'].'">'.ucwords( $entreprise['entite_nom'] ).'</a>';
				}
				if( isset($entites['org_ref_id']) ){
					$org_ref = $mEntite->get( $entites['org_ref_id'] );
					$link2 = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$org_ref['entite_id'].'">'.ucwords( $org_ref['entite_nom'] ).'</a>';
				}
				if( isset($entites['del_id']) ){
					$del = $mEntite->get( $entites['del_id'] );
					$link3 = '<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$del['entite_id'].'">'.ucwords( $del['entite_nom'] ).'</a>';
				}
				
				$responce->rows[$i]['cell'] = array(
					$date_creation,
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$operation['fiche_id'].'">'.$operation['fiche_id'].'</a>',
					$link1,
					$link2,
					$link3
				);
				
				$i++;
			}
			
			echo json_encode($responce);
			
		}
		
		public function getrechercheevaluationsAction(){
			
			if( $_GET['date1'] ){
				$date1 = $_GET['date1'];
			}else{
				$date1 = null;
			}
			
			if( $_GET['date2'] ){
				$date2 = $_GET['date2'];
			}else{
				$date2 = null;
			}
			
			$mResultatOutil = new Model_ResultatOutil();
			$front = Zend_Controller_Front::getInstance();
			
			$auth = Zend_Auth::getInstance();
	    	$role = $_SESSION['Zend_Auth']['storage']->role;
			if( $role == "forthac" ){
	    		$count = $mResultatOutil->countRecherche( null, $date1, $date2);
	    	}else{
	    		$entite_id = $_SESSION['Zend_Auth']['storage']->entite_id;
	    		$count = $mResultatOutil->countRecherche( $entite_id, $date1, $date2 );
	    	}
			
			if( isset( $_POST['page'] ) ){
				$page = $_POST['page']; // get the requested page
				$limit = $_POST['rows']; // get how many rows we want to have into the grid
				$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				$sord = $_POST['sord']; // get the direction
			}else{
				$page = 1;
				$limit = 10;
				$sidx = "fiche_date_creation";
				$sord = 'ASC';
			}

			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			if( $role == "forthac" ){
				$resultatOutil = $mResultatOutil->getListeRecherche($sidx, $sord, $start, $limit, null, $date1, $date2);
			}else{
				$resultatOutil = $mResultatOutil->getListeRecherche($sidx, $sord, $start, $limit, $entite_id, $date1, $date2);
			}
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$fDates = new Fonctions_Dates();
			$mCivilite = new Model_Civilite();
			
			$i = 0;
			foreach($resultatOutil as $ro){
				
				$responce->rows[$i]['id']=$ro['resultat_outil_id'];
				
				$date = $fDates->formatDate($ro['resultat_date']);
				
				$civilite = $mCivilite->get( $ro['civilite_id'] );
				
				$responce->rows[$i]['cell'] = array(
					$date,
					ucfirst($ro['outil_libelle']),
					ucwords( $civilite->civilite_abrege.' '.$ro['personne_prenom'].' '.$ro['personne_nom'] ),
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$ro['fiche_id'].'">'.$ro['fiche_id'].'</a>',
					'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$ro['entite_id'].'">'.ucwords( $ro['entite_nom'] ).'</a>'
				);
				
				$i++;
			}
			
			echo json_encode($responce);
			
		}
		
		public function getrecherchejurysAction(){
			
			if( $_GET['date1'] ){
				$date1 = $_GET['date1'];
			}else{
				$date1 = null;
			}
			
			if( $_GET['date2'] ){
				$date2 = $_GET['date2'];
			}else{
				$date2 = null;
			}
			
			$mJury = new Model_Jury();
			$front = Zend_Controller_Front::getInstance();
			
			$auth = Zend_Auth::getInstance();
	    	$role = $_SESSION['Zend_Auth']['storage']->role;
			if( $role == "forthac" ){
	    		$count = $mJury->countRecherche( null, $date1, $date2);
	    	}else{
	    		$entite_id = $_SESSION['Zend_Auth']['storage']->entite_id;
	    		$count = $mJury->countRecherche( $entite_id, $date1, $date2 );
	    	}
			
			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			if( $role == "forthac" ){
				$jurys = $mJury->getListeRecherche($sidx, $sord, $start, $limit, null, $date1, $date2);
			}else{
				$jurys = $mJury->getListeRecherche($sidx, $sord, $start, $limit, $entite_id, $date1, $date2);
			}
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$fDates = new Fonctions_Dates();
			$mCivilite = new Model_Civilite();
			
			$i = 0;
			foreach($jurys as $jury){
				
				$responce->rows[$i]['id']=$jury['jury_id'];
				
				$date = $fDates->formatDate($jury['jury_date']);
				
				$civilite = $mCivilite->get( $jury['civilite_id'] );
				
				$responce->rows[$i]['cell'] = array(
					$date,
					'<a href="'.$front->getBaseUrl().'/jurys/details/?jury_id='.$jury['jury_id'].'">'.ucfirst($jury['jury_ville']).'</a>',
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$jury['fiche_id'].'">'.$jury['fiche_id'].'</a>',
					ucwords( $civilite->civilite_abrege.' '.$jury['personne_prenom'].' '.$jury['personne_nom'] ),
					'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$jury['entite_id'].'">'.ucwords( $jury['entite_nom'] ).'</a>'
				);
				
				$i++;
			}
			
			echo json_encode($responce);
			
		}
		
		public function getlisteformationsAction(){
			
			$mFormations = new Model_Formation();
			$mEntites = new Model_Entite();
			$front = Zend_Controller_Front::getInstance();
			
			$count = count( $mFormations->fetchAll() );
			
			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			$formations = $mFormations->getListe( $sidx, $sord, $start, $limit );
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$fDates = new Fonctions_Dates();
			
			$i = 0;
			foreach($formations as $formation){
				$EntitesNom ='';
				$responce->rows[$i]['id'] = $formation['formation_id'];
				
				$entites = $mFormations->getEntites( $formation['formation_id'] );
					foreach( $entites as $entite ){
							$EntitesNom = $entite['entite_nom'];
					}
				
					$responce->rows[$i]['cell'] = array(
					ucwords( $formation['formation_libelle'] ),
					$formation['formation_formacode'],
					ucwords( $EntitesNom ),
					'<a href="'.$front->getBaseUrl().'/formations/details/id/'.$formation['formation_id'].'"><span class="ui-icon ui-icon-zoomin" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" >Détails</span></a>'.'<a href="'.$front->getBaseUrl().'/formations/update/id/'.$formation['formation_id'].'"><span class="ui-icon ui-icon-pencil" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Modifier" >Modifier</span></a>'.'<a href="'.$front->getBaseUrl().'/formations/delete/id/'.$formation['formation_id'].'"><span class="ui-icon ui-icon-close" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Supprimer" >Supprimer</span></a>'.'<a href="'.$front->getBaseUrl().'/formations/candidats/id/'.$formation['formation_id'].'"><span class="ui-icon ui-icon-script" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Afficher la liste des candidats" >Candidats</span></a>'
				);
				$i++;
			}
			
			echo json_encode($responce);
			
		}
		
		public function getlistejurysAction(){
			
			$mJurys = new Model_Jury();
			$fDates = new Fonctions_Dates();
			$mEntites = new Model_Entite();
			$front = Zend_Controller_Front::getInstance();
			
			$count = count( $mJurys->fetchAll() );
			
			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			$jurys = $mJurys->getListe( $sidx, $sord, $start, $limit );
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;
			
			$i = 0;
			foreach($jurys as $jury){
				
				$responce->rows[$i]['id'] = $jury['jury_id'];
				
				$branche = $mEntites->get( $jury['branche_id'] );
				
				
			$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;	
			if($role != "organisme référent" && $role != "organisme de formation" && $role != "greta" )
			{
				$url_tableau = '<a href="'.$front->getBaseUrl().'/jurys/details/?id='.$jury['jury_id'].'"><span class="ui-icon ui-icon-zoomin" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" >Détails</span></a>'.
				'<a href="'.$front->getBaseUrl().'/jurys/update/?id='.$jury['jury_id'].'"><span class="ui-icon ui-icon-pencil" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Modifier" >Modifier</span></a>'.
				'<a href="'.$front->getBaseUrl().'/jurys/delete/?id='.$jury['jury_id'].'"><span class="ui-icon ui-icon-close" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Supprimer" >Supprimer</span></a>';
				
			}else{
				$url_tableau = '<a href="'.$front->getBaseUrl().'/jurys/details/?id='.$jury['jury_id'].'"><span class="ui-icon ui-icon-zoomin" style="float:left; margin-left:20px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" >Détails</span></a>';
			}
				
				$responce->rows[$i]['cell'] = array(
					$fDates->formatDate( $jury['jury_date'] ),
					ucwords( $jury['jury_ville'] ),
					ucwords( $branche['entite_nom'] ),
					$url_tableau);
				
				$i++;
			}
			
			echo json_encode($responce);
			
		}
		
		public function gettypesentiteAction(){
			
			$mTypeEntite = new Model_TypeEntite();
			$types = $mTypeEntite->fetchAll();
			echo '<option value="">Choisissez un type d\'entité</option>';
			foreach( $types as $type ){

				if($type['type_entite_libelle'] == 'forthac')
				{
					echo '<option value="'.$type['type_entite_id'].'" id="'.$type['type_entite_id'].'">'.ucwords( 'opcalia' ).'</option>';
				}else{
					echo '<option value="'.$type['type_entite_id'].'" id="'.$type['type_entite_id'].'">'.ucwords( $type['type_entite_libelle'] ).'</option>';
				}
				
			}
			
		}
		
		public function gettypespersonneAction(){
			
			echo '<option value="">Choisissez un type de personne</option>';
			echo '<option value="candidat" id="candidat">Candidat</option>';
			$mFonctions = new Model_Fonction();
			$fonctions = $mFonctions->fetchAll();
			foreach( $fonctions as $fonction ){
				echo '<option value="'.$fonction['fonction_id'].'" id="'.$fonction['fonction_id'].'" >'.ucwords( $fonction['fonction_libelle'] ).'</option>';
			}
			
		}
		
		public function getrecherchenomAction(){
			
			$string = $_GET['search'];
			$choice1 = $_GET['choice1'];
			$choice2 = $_GET['choice2'];

			$mPersonnes = new Model_Personne();
			$mCandidats = new Model_Candidat();
			$mContacts = new Model_Contact();
			$mEntites = new Model_Entite();
			$front = Zend_Controller_Front::getInstance();
			$mMetiers = new Model_Metier();

			switch( $choice1 ){
				case 'personne' :
					if( $choice2 == null ){
						$count = count( $mPersonnes->getListeRechercheNom($string) );
					}elseif( $choice2 == 'candidat' ){
						$count = count( $mCandidats->getListeRechercheNom( $string ) );
					}else{
						$count = count( $mContacts->getListeRechercheNom($string, $choice2) );
					}
					break;
				case 'entite' :
					if( $choice2 == null ){
						$count = count( $mEntites->getListe($string) );
					}else{
						$count = count( $mEntites->getListe($string, $choice2 ) );
					}
					break;
			}
			
			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction
			
			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}
			
			if ($page > $total_pages) $page=$total_pages;
			
			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
            if( $start < 0 ) $start = 0;
			switch( $choice1 ){
				case 'personne' :
					if( $choice2 == null ){
						$personnes = $mPersonnes->getListeRechercheNom($string, $sidx, $sord, $start, $limit);
					}elseif( $choice2 == 'candidat' ){
						$candidats = $mCandidats->getListeRechercheNom($string, $sidx, $sord, $start, $limit);
					}else{
						$contacts = $mContacts->getListeRechercheNom($string, $choice2, $sidx, $sord, $start, $limit);
					}
					break;
				case 'entite' :
					if( $choice2 == null ){
						$entites = $mEntites->getListe($string, null, $sidx, $sord, $start, $limit);
					}else{
						$entites = $mEntites->getListe($string, $choice2, $sidx, $sord, $start, $limit);
					}
					break;
			}
			
			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;

			switch( $choice1 ){
				case 'personne' :
					if( $choice2 == null ){
						$i = 0;
						foreach ( $personnes as $personne ){
							$entite = $mEntites->get($personne['entite_id']);
							$responce->rows[$i]['id'] = $personne['personne_id'];
							$links = '';
							if( $mPersonnes->getContact( $personne['personne_id'] ) ){
								$contact = $mPersonnes->getContact( $personne['personne_id'] );
								$links .= '<a href="'.$front->getBaseUrl().'/contacts/details/?contact_id='.$contact['contact_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails du contact" ></p></a> ';
							}
							if( $mPersonnes->getCandidat( $personne['personne_id'] ) ){
								$candidat = $mPersonnes->getCandidat( $personne['personne_id'] );
								$links .= '<a href="'.$front->getBaseUrl().'/candidat/details/?id='.$candidat['candidat_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails du candidat" ></p></a> ';
							}
							$responce->rows[$i]['cell'] = array(
								$links.ucwords( $personne['personne_prenom'].' '.$personne['personne_nom'] ),
								'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$personne['entite_id'].'" >'.ucwords( $entite['entite_nom'] ).'</a>'
							);
							$i++;
						}
					}elseif( $choice2 == 'candidat' ){
						$i = 0;
						foreach ( $candidats as $candidat){
							$entite = $mEntites->get($candidat['entite_id']);
							$metiers = $mCandidats->getMetiers($candidat['candidat_id']);
							$j = 0;
							foreach( $metiers as $metier ){
								$j++;
								$m = "";
								$titre = $mMetiers->getTitre($metier['metier_id']);
								if( isset( $titre['bloc2_lib'] ) ) $bloc2 = ' / '.$titre['bloc2_lib'];
								else $bloc2 = "";
								$m .= ucwords( $titre['demarche_abrege'].' '.$titre['bloc1_ab'].$bloc2 );
								if( $j != count( $metiers ) )
									$m .= ", ";
							}
							$fiches = $mCandidats->getFiches($candidat['candidat_id']);
							$j = 0;
							foreach( $fiches as $fiche ){
								$j++;
								$f = "";
								$f .= '<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$fiche['fiche_id'].'">'.$fiche['fiche_id'].'</a>';
								if( $j != count( $fiches ) )
									$f .= ", ";
							}
							$responce->rows[$i]['id'] = $candidat['personne_id'];
							$responce->rows[$i]['cell'] = array(
								'<a href="'.$front->getBaseUrl().'/candidat/details/?id='.$candidat['candidat_id'].'">'.ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ).'</a>',
								'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entite['entite_id'].'">'.ucwords( $entite['entite_nom'] ).'</a>',
								$f,
								$m
							);
							$i++;
						}
					}else{
						$i = 0;
						foreach ( $contacts as $contact ){
							$entite = $mEntites->get($contact['entite_id']);
							$fiches = $mContacts->getFiches($contact['contact_id']);
							$j = 0;
							foreach( $fiches as $fiche ){
								$j++;
								$f = "";
								$f .= '<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$fiche['fiche_id'].'">'.$fiche['fiche_id'].'</a>';
								if( $j != count( $fiches ) )
									$f .= ", ";
							}
							$responce->rows[$i]['id'] = $contact['personne_id'];
							$responce->rows[$i]['cell'] = array(
								'<a href="'.$front->getBaseUrl().'/contacts/details/?contact_id='.$contact['contact_id'].'">'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'] ).'</a>',
								'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entite['entite_id'].'">'.ucwords( $entite['entite_nom'] ).'</a>',
								$f
							);
							$i++;
						}
					}
					break;
				case 'entite' :
					if( $choice2 == null ){
						$i = 0;
						foreach ( $entites as $entite ){
							$fiches = $mEntites->getFiches($entite['entite_id']);
							$j = 0;
							$f = "";
							foreach( $fiches as $fiche ){
								$j++;
								$f .= '<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$fiche['fiche_id'].'">'.$fiche['fiche_id'].'</a>';
								if( $j != count( $fiches ) )
									$f .= ", ";
							}
							$types = $mEntites->getTypesEntite($entite['entite_id']);
							$j = 0;
							foreach( $types as $type ){
								$j++;
								$t = "";
								$t .= $type['type_entite_libelle'];
								if( $j != count( $types ) )
									$t .= ", ";
							}
							$responce->rows[$i]['id'] = $entite['entite_id'];
							$responce->rows[$i]['cell'] = array(
								'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entite['entite_id'].'">'.ucwords( $entite['entite_nom'] ).'</a>',
								ucwords($t),
								$f
							);
							$i++;
						}
					}else{
						$i = 0;
						foreach ( $entites as $entite ){
							$fiches = $mEntites->getFiches($entite['entite_id']);
							$j = 0;
							$f = "";
							foreach( $fiches as $fiche ){
								$j++;
								$f .= '<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$fiche['fiche_id'].'">'.$fiche['fiche_id'].'</a>';
								if( $j != count( $fiches ) )
									$f .= ", ";
							}
							$responce->rows[$i]['id'] = $entite['entite_id'];
							$responce->rows[$i]['cell'] = array(
								'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entite['entite_id'].'">'.ucwords( $entite['entite_nom'] ).'</a>',
								$f
							);
							$i++;
						}
					}
					break;
			}
			
			echo json_encode($responce);
			
		}

		public function getcandidatsbyformationAction(){

			$formation_id = $_GET['formation_id'];

			$mFormations = new Model_Formation();
			$mEntites = new Model_Entite();
			$mCandidatMetier = new Model_CandidatMetier();
			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$count = count( $mFormations->getCandidats($formation_id) );

			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction

			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}

			if ($page > $total_pages) $page=$total_pages;

			$start = $limit*$page - $limit; // do not put $limit*($page - 1)
			$candidats = $mFormations->getCandidats($formation_id, $sidx, $sord, $start, $limit);

			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;

			$i = 0;
			foreach($candidats as $candidat){

				$entite = $mEntites->get($candidat['entite_id']);
				$fiche = $mCandidatMetier->getFiche( $candidat['candidat_metier_id'] );
				$metier = $mCandidatMetier->getMetier( $candidat['candidat_metier_id'] );
				$titre = $mMetier->getTitre($metier['metier_id']);
				$s_titre = $titre['demarche_abrege'].' - '.$titre['bloc1_ab'];
				if( $titre['bloc2_id'] > 0 ) $s_titre .= '/'.$titre['bloc2_id'];

				$responce->rows[$i]['id'] = $candidat['candidat_metier_id'];

				$responce->rows[$i]['cell'] = array(
					ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
					'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$entite['entite_id'].'">'.ucwords( $entite['entite_nom'] ).'</a>',
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$fiche['fiche_id'].'">'.$fiche['fiche_id'].'</a>',
					ucwords( $s_titre ),
					$candidat['candidat_metier_formation_duree_estimee'].' h',
					$candidat['candidat_metier_formation_duree_realisee'].' h'
				);

				$i++;
			}

			echo json_encode($responce);

		}

		public function getlistedemarchesAction(){

			$mDemarches = new Model_Demarche();
			
			$demarches = $mDemarches->getAll();
			foreach( $demarches as $demarche ){
				echo '<option value="'.$demarche['demarche_id'].'" >'.ucfirst( $demarche['demarche_abrege'] ).'</option>';
			}

		}

		public function getlistebloc1Action(){
			
			if( $this->_request->getParam('demarche_id') > 0 ){

				$demarche_id = $this->_request->getParam('demarche_id');

				$mTitres = new Model_Titre();
				$listebloc1 = $mTitres->getListe($demarche_id);

				/*if( count($listebloc1) > 0 ){
					echo '<option value="">Aucun</option>';
				}*/
				echo '<option value="">S&eacute;lectionner un titre</option>';
				foreach( $listebloc1 as $bloc1 ){
					echo '<option value="'.$bloc1['id'].'" >'.$bloc1['nom'].'</option>';
				}
				
			}

		}

		public function getlistebloc2Action(){

			if( $this->_request->getParam('demarche_id') > 0 && $this->_request->getParam('bloc1_id') > 0 ){

				$demarche_id = $this->_request->getParam('demarche_id');
				$bloc1_id = $this->_request->getParam('bloc1_id');

				$mTitres = new Model_Titre();
				$liste = $mTitres->getListeBloc2($demarche_id, $bloc1_id);
				
				if( $liste == null ){
					echo '';
				}else{
					foreach( $liste as $bloc ){
						echo '<option value="'.$bloc['num'].'" >'.$bloc['nom'].'</option>';
					}
				}

			}

		}

		public function getrecherchebytitreAction(){

			if( $this->_request->getParam('demarche_id') > 0 ){

				$demarche_id = $this->_request->getParam('demarche_id');
				$bloc1_id = $this->_request->getParam('bloc1_id');
				$bloc2_id = $this->_request->getParam('bloc2_id');

				$mFiches = new Model_Fiche();
				$mEntites = new Model_Entite();

				$count = count( $mFiches->getListeByTitre($demarche_id, $bloc1_id, $bloc2_id) );

				if( isset( $_POST['page'] ) ){
					$page = $_POST['page']; // get the requested page
					$limit = $_POST['rows']; // get how many rows we want to have into the grid
					$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
					$sord = $_POST['sord']; // get the direction
				}else{
					$page = 1;
					$limit = 10;
					$sidx = "cf.fiche_id";
					$sord = 'ASC';
				}

				if( $count > 0 ) {
					$total_pages = ceil($count/$limit);
				} else {
					$total_pages = 0;
				}

				if ($page > $total_pages) $page=$total_pages;

				$start = $limit*$page - $limit; // do not put $limit*($page - 1)

				$operations = $mFiches->getListeByTitre($demarche_id, $bloc1_id, $bloc2_id, $sidx, $sord, $start, $limit);

				$responce->page = $page;
				$responce->total = $total_pages;
				$responce->records = $count;

				$i = 0;
				foreach($operations as $operation){

					$responce->rows[$i]['id'] = $operation['1']['fiche_id'];

					$link1=$link2=$link3='';
					if( isset( $operation['1']['entite_id'] ) ) $link1 = '<a href="'.$this->view->baseUrl().'/entites/details/?entite_id='.$operation['1']['entite_id'].'">'.ucwords( $operation['1']['entite_nom'] ).'</a>';
					if( isset( $operation['2']['entite_id'] ) ) $link2 = '<a href="'.$this->view->baseUrl().'/entites/details/?entite_id='.$operation['2']['entite_id'].'">'.ucwords( $operation['2']['entite_nom'] ).'</a>';
					if( isset( $operation['4']['entite_id'] ) ) $link3 = '<a href="'.$this->view->baseUrl().'/entites/details/?entite_id='.$operation['4']['entite_id'].'">'.ucwords( $operation['4']['entite_nom'] ).'</a>';
					$responce->rows[$i]['cell'] = array(
						'<a href="'.$this->view->baseUrl().'/operations/visu/num/'.$operation['1']['fiche_id'].'">'.$operation['1']['fiche_id'].'</a>',
						$link1,
						$link2,
						$link3
					);

					$i++;
				}

			}

			echo json_encode($responce);

		}

		public function getcandidatsbymetierAction(){

			if( $this->_request->getParam('fiche_id') > 0 ){

				$mFiches = new Model_Fiche();
				$mResultats = new Model_Resultat();
				$mOutils = new Model_Outil();
				$mCandidatMetiers = new Model_CandidatMetier();
				$mMetiers = new Model_Metier();
				$mDemarches = new Model_Demarche();

				$front = Zend_Controller_Front::getInstance();
				$fDates = new Fonctions_Dates();

				$role = Zend_Auth::getInstance()->getIdentity()->role;

				$fiche_id = $this->_request->getParam('fiche_id');

				if( $this->_request->getParam('metier_id') > 0 ){
					$metier_id = $this->_request->getParam('metier_id');
				}else{
					$metier_id = 0;
				}

				$count = count( $mFiches->getCandidats($fiche_id, $metier_id) );
				$page = '';
				$limit= '' ;
				$sidx= '';
				$sord ='';
				if(isset($_POST['page'])) $page = $_POST['page']; // get the requested page
				if(isset($_POST['sidx'])) $limit = $_POST['rows']; // get how many rows we want to have into the grid
				if(isset($_POST['sidx'])) $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				if(isset($_POST['sord'])) $sord = $_POST['sord']; // get the direction

				
				if($sidx =='titre')	{$sidx = 'personne_nom';}
				
				
				if($limit>0)
				{
				if( $count > 0 ) $total_pages = ceil($count/$limit); else $total_pages = 0;
				if ($page > $total_pages) $page=$total_pages;
				}else{
					$page = 0;
					$total_pages = 0;
				}

				$start = $limit*$page - $limit; // do not put $limit*($page - 1)
				//$candidats = $mFiches->getCandidats($fiche_id, $metier_id, $sidx, $sord, $start, $limit);
				$candidats = $mFiches->getCandidats($fiche_id, $metier_id);
				$responce->page = $page;
				$responce->total = $total_pages;
				$responce->records = $count;

				$i = 0;
				foreach($candidats as $candidat){

					$candidat_metier = $mCandidatMetiers->get($candidat['candidat_metier_id']);

					$etat_libelle = $candidat['etat_libelle'];
					$metier = $mMetiers->get($candidat_metier['metier_id']);
					$titres = $mMetiers->getTitre($candidat_metier['metier_id']);
					$demarche = $mDemarches->get( $metier['demarche_id'] );
			        $titres_test = explode( ':', $titres['bloc1_ab']);
					if( $candidat['civilite_abrege'] == 'nc' ) $candidat['civilite_abrege'] = '';

					$naiss = $fDates->getNbYears( $candidat['personne_date_naissance'] );
					$anc = $fDates->getNbYears( $candidat['candidat_anciennete'] );
					$titre_affiche = $titres_test[0];


					$responce->rows[$i]['id'] = $candidat['candidat_metier_id'];

					$links = '<a href="'.$front->getBaseUrl().'/candidat/details/?id='.$candidat['candidat_id'].'&operation_id='.$fiche_id.'&candidat_metier_id='.$candidat['candidat_metier_id'].'" ><span class="ui-icon ui-icon-zoomin" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" >Détails</span></a>';
					if( $role != 'branche' && $role != 'délégation' ) $links .= '<a href="'.$front->getBaseUrl().'/operations/delcandidat/?candidat_metier_id='.$candidat['candidat_metier_id'].'" ><span class="ui-icon ui-icon-trash" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Enlever" >Enlever</span></a>';
					if( $role != 'branche' && $role != 'délégation' ) $links .= '<a href="'.$front->getBaseUrl().'/saisie/index/metier/'.$candidat['candidat_metier_id'].'/"><span class="ui-icon ui-icon-pencil" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Saisie" >Saisie</span></a>';
					if( $mResultats->getResultatsCandidat($candidat['candidat_metier_id']) != false ){
						if( $demarche['demarche_abrege'] == 'cqp' || $demarche['demarche_abrege'] == 'ccsp' ){
							$links .= '<a href="'.$front->getBaseUrl().'/tableauresultats/index/?id='.$candidat['candidat_metier_id'].'&passage='.$candidat['nb_passage'].'"><span class="ui-icon ui-icon-image" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Tableau de résultats" >Tableau de résultats</span></a>';
						}elseif( $demarche['demarche_abrege'] == 'diplome' ){
							$links .= '<a href="'.$front->getBaseUrl().'/tableauresultats/index/?id='.$candidat['candidat_metier_id'].'&passage=1"><span class="ui-icon ui-icon-image" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Tableau de résultats" >Tableau de résultats</span></a>';
						}
						elseif( $demarche['demarche_abrege'] == 'cqpbranche' ){
							$links .= '<a href="'.$front->getBaseUrl().'/tableauresultats/index/?id='.$candidat['candidat_metier_id'].'&passage=1"><span class="ui-icon ui-icon-image" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Tableau de résultats" >Tableau de résultats</span></a>';
						}
						
					}
					$resultats = $mResultats->getResultatsCandidat($candidat['candidat_metier_id']);

					$res = $mResultats->getLast($candidat['candidat_metier_id']);


					if( $candidat['etat_libelle'] == 'certifié' ||  $candidat['etat_libelle'] == 'en cours acquisition' || ($demarche['demarche_abrege'] == 'cqpbranche' && $candidat['etat_libelle'] == 'admissible' )){
						if( $role != 'branche' && $role != 'délégation' && $role != "organisme référent" && $role != "organisme de formation" && $role != "greta" && $demarche['demarche_abrege'] != 'diplome' ) {
							
						}
						if($demarche['demarche_abrege'] == 'cqpbranche')
						{
							$links .= '<a Target="_blank" href="'.$front->getBaseUrl().'/impression/diplomebranche/?candidat_metier_id='.$candidat['candidat_metier_id'].'"><span class="ui-icon ui-icon-script" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Imprimer le certificat" >Impression du certificat</span></a>';
							
						}else{
							$links .= '<a Target="_blank" href="'.$front->getBaseUrl().'/impression/diplome/?candidat_metier_id='.$candidat['candidat_metier_id'].'"><span class="ui-icon ui-icon-script" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Imprimer le diplôme" >Impression du diplôme</span></a>';
						}
							
							
							
					}

					$livret = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="livret : non saisi" ></img>';
					$questionnaire = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="questionnaire : non saisi" ></img>';
					$observation = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="observation : non saisie" ></img>';
					$entretien = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="entretien : non saisi" ></img>';

					if( $candidat['etat_libelle'] == 'abandon' ){
						$livret ='';
						$questionnaire = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="Candidat à abandonné"  ></img>';
						$observation = '';
						$entretien = '';
					}
					
					
					
					$livret1 = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="livret1 : non saisi"  width="13"></img>';
					$livret2 = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="livret2 : non saisi"  width="13"></img>';
					$questionDiplome = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="question : non saisi"  width="13"></img>';
					$observationDiplome ='<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="observation : non saisi"  width="13"></img>';
					$entretienDiplome = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="entretien : non saisi"  width="13"></img>';
					$bilanDiplome ='<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="bilan : non saisi" width="13"></img>';
					
					$dateformation1 = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="date de debut de formation : non saisi" ></img>';
					$dateformation2 = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="date de fin de formation : non saisi"  ></img>';
					$dateentretien = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="date entretien : non saisi" ></img>';
					$datejury = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="date de jury : non saisi"  ></img>';
						
					if( $candidat['etat_libelle'] == 'certifié' || $candidat['etat_libelle'] == 'admissible'  ){
						$datejury = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="candidat '.$candidat['etat_libelle'].'" ></img>';
					}

					if( $candidat['etat_libelle'] == 'abandon' ){
						$dateformation1 ='';
						$dateformation2 = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="Candidat à abandonné"  ></img>';
						$dateentretien = '';
						$datejury = '';		
					}
					
					$commentaires = 0;
					$ccspCommentaireReprage = 0;
					$ccspCommentaire=0;
					if( $resultats != false ){
						foreach( $resultats as $resultat ){
							$outil = $mOutils->get( $resultat->outil_id );
							$res = '';
							switch( $outil['outil_libelle'] ){
								

								case 'debutFormationBanche' :
									if( $resultat->resultat_date != '' ) $dateformation1 = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="Date de debut de formation : saisie"  ></img>';
									break;

								case 'finFormationBanche' :
										if( $resultat->resultat_date != '' ) $dateformation2 = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="Date de fin de formation : saisie"  ></img>';
										break;
								case 'entretienNoteBanche' :
										if( $resultat->resultat_date != '' ) $dateentretien = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="Date entretien : saisie" ></img>';
										break;
								case 'livret' :
									if( $resultat->resultat_date != '' ) $livret = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="livret : saisie" ></img>';									
									break;
								
								case 'questionnaire' :
									$test=0;
									$notes = explode( '@', $resultat->resultat_valeur );
									foreach( $notes as $note ){
										$note = explode( '_', $note );
										$test += $note[0];
									}
									if( $test > 0 && $demarche['demarche_abrege'] != 'ccsp' ){
										$questionnaire = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="questionnaire : saisi" ></img>';

									}
									if($resultat->resultat_date!='' && $demarche['demarche_abrege'] == 'ccsp')
									{
									//$questionnaire = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="questionnaire : non saisi" ></img>';
									$questionnaire_croix= 'ok';
									
									}else{
										if( $demarche['demarche_abrege'] == 'ccsp')
										{
										//$questionnaire = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="questionnaire : non saisi" ></img>';
										$questionnaire_croix= 'Nok';
										}
										
										}
									
									
									break;
								case 'observation' :
									$res = str_replace( '@', '', $resultat->resultat_valeur );
									$res = str_replace( '_', '', $res );
									$res = trim( $res );
									if( $res != '' && $demarche['demarche_abrege'] != 'ccsp' )
									$observation = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="observation : saisie" ></img>';
									
									if($resultat->resultat_date!='' && $demarche['demarche_abrege'] == 'ccsp' && $questionnaire_croix =='ok')
									{
										
										$questionnaire = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="observation et questionnaire : saisie" ></img>';
									
									}else{
										if( $demarche['demarche_abrege'] == 'ccsp' && $questionnaire_croix =='ok')
										{
										$questionnaire = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="observation et questionnaire : non saisie" ></img>';
										}
									}
									
									break;
								case 'entretien' :
									if( $demarche['demarche_abrege'] == 'cqp' ){
										$test=0;
										$notes = explode( '@', $resultat->resultat_valeur );
										foreach( $notes as $note ){
											$test += $note;
										}
										if( $test > 0 ){
											$entretien = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="entretien : saisi" ></img>';
										}
										
									}
									
								if($candidat_metier['candidat_metier_formation_duree_estimee'] > 0 &&$demarche['demarche_abrege'] == 'ccsp' )
										{
											$observation = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="dur&eacute;e estim&eacute;e : saisie" ></img>';
											
										}

								if($candidat_metier['candidat_metier_formation_duree_estimee'] == 0 &&$demarche['demarche_abrege'] == 'ccsp' )
										{

											$observation = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="dur&eacute;e estim&eacute;e : non saisie" ></img>';
											
										}
									
									
									if($resultat->resultat_date!='' && $demarche['demarche_abrege'] == 'ccsp')
									{
										

										
										$entretien = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="entretien : saisi" ></img>';
									
									}else{
										if( $demarche['demarche_abrege'] == 'ccsp')
										{
										$entretien = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="entretien : non saisie" ></img>';
										}
									}
									

									
									
									
									
									
										
									
									
									
									break;
								case 'compréhension orale' :
									if( $resultat->resultat_valeur != '' ) $commentaires++;
									if( $resultat->resultat_valeur != '' && $demarche['demarche_abrege'] == 'ccsp') $ccspCommentaire++;
									break;
								case 'expression orale' :
									if( $resultat->resultat_valeur != '' ) $commentaires++;
									break;
								case 'compréhension écrite' :
									if( $resultat->resultat_valeur != '' ) $commentaires++;
									break;
								case 'expression écrite' :
									if( $resultat->resultat_valeur != '' ) $commentaires++;
									break;
								case 'raisonnement cognitif, logique et numérique' :
									if( $resultat->resultat_valeur != '' ) $commentaires++;
									break;
								case 'repères spatio-temporels' :
									if( $resultat->resultat_valeur != '' ) $commentaires++;
									break;
							
									
									
									
								case 'livret1Diplome' :
									if( $demarche['demarche_abrege'] == 'diplome' && $resultat->resultat_date!='')
									{
										$livret1 = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="livret1 saisi" width="13" ></img>';
									}
								break;
										
								case 'livret2Diplome' :
								
									if( $demarche['demarche_abrege'] == 'diplome' && $resultat->resultat_date!='')
									{
										$livret2 = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="livret2 saisi"  width="13"></img>';
									}
								break;
											
								case 'questionDiplome' :
											
										if( $demarche['demarche_abrege'] == 'diplome' && $resultat->resultat_date!='')
										{
											$questionDiplome = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="Question saisie"  width="13"></img>';
										}
								break;
								case 'observationDiplome' :				
									if( $demarche['demarche_abrege'] == 'diplome' && $resultat->resultat_date!='')
									{
										$observationDiplome = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="Observation saisie"  width="13"></img>';
									}
								break;
								
								
								case 'entretienDiplome' :
										
									if( $demarche['demarche_abrege'] == 'diplome' && $resultat->resultat_date!='')
									{
										$entretienDiplome = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="entretien saisie"  width="13"></img>';
									}
									break;
								case 'bilanDiplome' :
									if( $demarche['demarche_abrege'] == 'diplome' && $resultat->resultat_date!='')
									{
										$bilanDiplome = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="bilan saisi"  width="13"></img>';
									}
									break;
								
								
								
											
										
							
							
							}
						}
					}

					
					
					
					if( $commentaires < 6 ){
						$commentaires = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="commentaires : non saisis" ></img>';
					}else{
						$commentaires = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="commentaires : saisis" ></img>';
					}
					
					if( $ccspCommentaire > 0 )
					{
						//$commentaires = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="commentaires : saisis" ></img>';
					}else{
						//$commentaires = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="commentaires : non saisis" ></img>';
					}
					
					if($candidat_metier['candidat_metier_formation_duree_realisee'] > 0 && $demarche['demarche_abrege'] == 'ccsp' )
						{
							$commentaires = '<img src="'.$front->getBaseUrl().'/img/complete.gif" style="vertical-align:middle;" title="dur&eacute;e r&eacute;alis&eacute; : saisie" ></img>';
						
					}

					if($candidat_metier['candidat_metier_formation_duree_realisee'] == 0 && $demarche['demarche_abrege'] == 'ccsp' )
						{

							$commentaires = '<img src="'.$front->getBaseUrl().'/img/incomplete.gif" style="vertical-align:middle;" title="dur&eacute;e r&eacute;alis&eacute; : : non saisie" ></img>';
											
						}
					
						
						
					

					if( $demarche['demarche_abrege'] == 'cqp' ){

						$responce->rows[$i]['cell'] = array(
							ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
							$naiss.' ans',
							$anc.' ans',
							$candidat['candidat_contrat'],
							'CQP '.$titre_affiche,
							$candidat['nb_passage'].' '.$livret.$questionnaire.$observation.$entretien,
							$etat_libelle,
							$candidat['candidat_metier_id'],
							$links
						);

					}elseif( $demarche['demarche_abrege'] == 'ccsp' ){

						$responce->rows[$i]['cell'] = array(
							ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
							$naiss.' ans',
							$anc.' ans',
							$candidat['candidat_contrat'],
							$titre_affiche,
							$candidat['nb_passage'].' '.$questionnaire.$observation.$entretien.$commentaires,
							$etat_libelle,
							$candidat['candidat_metier_id'],
							$links
						);

					}elseif( $demarche['demarche_abrege'] == 'diplome' ){

						$responce->rows[$i]['cell'] = array(
							ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
							$naiss.' ans',
							$anc.' ans',
							$candidat['candidat_contrat'],
							$titre_affiche,
							''.$livret1.$livret2.$questionDiplome.$observationDiplome.$entretienDiplome.$bilanDiplome,
							$etat_libelle,
							$candidat['candidat_metier_id'],
							$links
						);

					}
					
					elseif( $demarche['demarche_abrege'] == 'cqpbranche' ){
					
						$responce->rows[$i]['cell'] = array(
								ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
								$naiss.' ans',
								$anc.' ans',
								$candidat['candidat_contrat'],
								'CQP '.$titre_affiche,
								$dateformation1.$dateformation2.$dateentretien.$datejury,
								$etat_libelle,
								$candidat['candidat_metier_id'],
								$links
						);
					
					}
					
					

					$i++;
				}

				echo json_encode($responce);

			}

		}

		public function updateoperationAction(){

			$fiche_id = $this->_request->getParam('fiche_id');
			$field = $this->_request->getParam('field');

			$mOperations = new Model_Fiche();
			switch( $field ){
				case 'fiche_projet':
					$mOperations->setProjet($fiche_id);
					break;
				case 'fiche_acces_candidats':
					$mOperations->setAccesCandidats($fiche_id);
					break;
				case 'objectif_id' :
					$objectif_id = $this->_request->getParam('value');
					$mOperations->setObjectif($fiche_id, $objectif_id);
					$objectif = $mOperations->getObjectif($fiche_id);
					echo ucfirst( $objectif['objectif_libelle'] );
					break;
				case 'fiche_remarque' :
					$fiche_remarque = $this->_request->getParam('value');
					$mOperations->setRemarque($fiche_id, $fiche_remarque);
					echo $remarque = $mOperations->getRemarque($fiche_id);
					break;
				case 'entite' :
					$type = $this->_request->getParam('type');
					$entite_id = $this->_request->getParam('entite_id');
					$contact_id = $this->_request->getParam('contact_id');
					switch( $type ){
						case 'entreprise' :
							$contact = $mOperations->getContactEntreprise( $fiche_id );
							break;
						case 'organisme référent' :
							$contact = $mOperations->getContactOrgRef( $fiche_id );
							break;
						case 'délégation' :
							$contact = $mOperations->getContactDelegation( $fiche_id );
							break;
						case 'greta' :
								$contact = $mOperations->getContactGreta( $fiche_id );
								break;
					}
					$mContactsFiche = new Model_ContactsFiche();
					if( $contact ){
						$data = array(
							'fiche_id'	=>	$fiche_id,
							'entite_id'	=>	$entite_id,
							'contact_id'=>	$contact_id
						);
						$where = " fiche_id = $fiche_id AND entite_id = ".$contact['entite_id']." AND contact_id = ".$contact['contact_id'];
						$mContactsFiche->update($data, $where);
					}else{
						$mOperations->addcontact($fiche_id, $entite_id, $contact_id);
					}
					$mEntites = new Model_Entite();
					$entite = $mEntites->get($entite_id);
					$mContacts = new Model_Contact();
					$contact = $mContacts->getPersonne($contact_id);
					$mCivilites = new Model_Civilite();
					$civ = $mCivilites->get($contact['civilite_id']);
					$data = ucwords( $entite['entite_nom'] ).'***'.ucwords( $civ->civilite_abrege.' '.$contact['personne_prenom'].' '.$contact['personne_nom'] );
					$mOperations->updateDateModif($fiche_id);
					echo $data;
					break;
			}

		}

		public function getcontactsAction(){

			if( $this->_request->getParam('entite_id') ){

				$entite_id = $this->_request->getParam('entite_id');

				$mEntites = new Model_Entite();
				$contacts = $mEntites->getContactsActifs($entite_id);

				foreach( $contacts as $contact ){
					echo '<option value="'.$contact['contact_id'].'" >'.$contact['personne_prenom'].' '.$contact['personne_nom'].'</option>';
				}

			}

		}

		public function addmetierAction(){

			$mMetier = new Model_Metier();
			$fDates = new Fonctions_Dates();
			$mTitres = new Model_Titre();
			$mOperations = new Model_Fiche();

			$file_name = $mTitres->getLastXML($_POST['demarche_id']);
			$num = $mTitres->getNumXML( $file_name );

			if( !$_POST['bloc2_id'] ){
				$_POST['bloc2_id'] = "0";
			}else{
				if($_POST['bloc2_id'] == 'undefined')
				{$_POST['bloc2_id'] = "0";}
			}



			$_POST['metier_date_envoi_dossiers'] = $fDates->unformatDate($_POST['metier_date_envoi_dossiers']);

			$data = array(
				'metier_effectif'				=>	$_POST['metier_nb_dossiers_candidats'],
				'metier_nb_dossiers_candidats'	=>	$_POST['metier_nb_dossiers_candidats'],
				'metier_nb_dossiers_tuteurs'	=>	$_POST['metier_nb_dossiers_tuteurs'],
				'metier_date_envoi_dossiers'	=>	$_POST['metier_date_envoi_dossiers'],
				'fiche_id'						=>	$_POST['fiche_id'],
				'demarche_id'					=>	$_POST['demarche_id'],
				'bloc1_id'						=>	$_POST['bloc1_id'],
				'bloc2_id'						=>	$_POST['bloc2_id'],
				'metier_xml'					=>	$num
			);
			$mMetier->insert($data);

			$mOperations->updateDateModif( $_POST['fiche_id'] );

		}

		public function updatemetierAction(){

			$mMetier = new Model_Metier();
			$mOperations = new Model_Fiche();
			$fDates = new Fonctions_Dates();

			if( !$_POST['bloc2_id'] ){
				$_POST['bloc2_id'] = "";
			}

			$_POST['metier_date_envoi_dossiers'] = $fDates->unformatDate($_POST['metier_date_envoi_dossiers']);

			$data = array(
				'metier_effectif'				=>	$_POST['metier_nb_dossiers_candidats'],
				'metier_nb_dossiers_candidats'	=>	$_POST['metier_nb_dossiers_candidats'],
				'metier_nb_dossiers_tuteurs'	=>	$_POST['metier_nb_dossiers_tuteurs'],
				'metier_date_envoi_dossiers'	=>	$_POST['metier_date_envoi_dossiers'],
				'demarche_id'					=>	$_POST['demarche_id'],
				'bloc1_id'						=>	$_POST['bloc1_id'],
				'bloc2_id'						=>	$_POST['bloc2_id']
			);
			$mMetier->update($data, " metier_id = ".$_POST['metier_id']);

			$mOperations->updateDateModif($_POST['fiche_id']);

		}

		public function deleteoperationAction(){

			if( $_POST['operation_id'] ){

				$operation_id = $_POST['operation_id'];

				$mOperations = new Model_Fiche();
				$mBinomes = new Model_Binome();
				$mMetiers = new Model_Metier();
				$mContactsFiche =  new Model_ContactsFiche();
				$mCandidatsMetier = new Model_CandidatMetier();

				$metiers = $mOperations->getMetiers($operation_id);

				$test = 0;
				foreach( $metiers as $metier ){

					$metier_id = $metier['metier_id'];

					if( count( $mCandidatsMetier->fetchAll('metier_id ='.$metier_id) ) > 0 ){
						$test++;
					}

				}

				if( $test == 0 ){

					//*****SUPPRESSIONS****//

					foreach( $metiers as $metier ){

						$metier_id = $metier['metier_id'];

						//binome
						$mBinomes->delete( ' metier_id ='.$metier_id );

						//metier
						$mMetiers->delete( ' metier_id ='.$metier_id );

					}

					//contacts_fiche
					$mContactsFiche->delete( ' fiche_id ='.$operation_id );

					//fiche
					$mOperations->delete( ' fiche_id ='.$operation_id );

					//*********//

					echo 'delete';

				}else{
					echo 'Impossible de supprimer l\'opération : des candidats y sont rattachés !';
				}

			}

		}

		public function deletemetierAction(){

			if( $_POST['metier_id'] ){

				$metier_id = $_POST['metier_id'];

				$mCandidatsMetier = new Model_CandidatMetier();
				$mBinomes = new Model_Binome();
				$mMetiers = new Model_Metier();
				$mOperations = new Model_Fiche();

				$metier = $mMetiers->get($metier_id);
				$fiche_id = $metier['fiche_id'];

				$test = 0;
				if( count( $mCandidatsMetier->fetchAll('metier_id ='.$metier_id) ) > 0 ){
					echo 'Impossible de supprimer le titre : des candidats y sont rattachés !';
					$test++;
				}

				if( $test == 0 ){

					//suppression table binome
					$mBinomes->delete( 'metier_id ='.$metier_id );

					//suppression table metier
					$mMetiers->delete( 'metier_id ='.$metier_id );

					$mOperations->updateDateModif($fiche_id);

					echo 'delete';

				}
				
			}

		}

		public function getlistemembresjuryAction(){

			if( $this->_request->getParam('id') > 0 ){

				$jury_id = $this->_request->getParam('id');
				
				$mJurys = new Model_Jury();
				$mEntites = new Model_Entite();
				$front = Zend_Controller_Front::getInstance();

				$count = count( $mJurys->getMembres($jury_id) );

				$page = $_POST['page']; // get the requested page
				$limit = $_POST['rows']; // get how many rows we want to have into the grid
				$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				$sord = $_POST['sord']; // get the direction

				if( $count > 0 ) {
					$total_pages = ceil($count/$limit);
				} else {
					$total_pages = 0;
				}

				if ($page > $total_pages) $page=$total_pages;

				$start = $limit*$page - $limit; // do not put $limit*($page - 1)

				$membres = $mJurys->getMembres($jury_id, $sidx, $sord, $start, $limit);

				$responce->page = $page;
				$responce->total = $total_pages;
				$responce->records = $count;

				$i = 0;
				foreach($membres as $membre){

					$entite = $mEntites->get( $membre['entite_id'] );

					$responce->rows[$i]['id'] = $membre['contact_id'];

					$responce->rows[$i]['cell'] = array(
						ucwords( $membre['civilite_abrege'].' '.$membre['personne_prenom'].' '.$membre['personne_nom'] ),
						ucwords( $membre['type_membre_jury_libelle'] ),
						ucwords( $entite['entite_nom'] )
					);

					$i++;
				}

				echo json_encode($responce);

			}

		}

		public function getlistecandidatsjuryAction(){

			if( $this->_request->getParam('id') > 0 ){

				$jury_id = $this->_request->getParam('id');

				$mJurys = new Model_Jury();
				$mMetiers = new Model_Metier();
				$front = Zend_Controller_Front::getInstance();
				$auth = Zend_Auth::getInstance()->getIdentity();
				$entiteId = $auth->entite_id;	
				$role = $auth->role;	
				if($role != "organisme référent" && $role != "organisme de formation" && $role != "greta" )
				{
										$count = count( $mJurys->getCandidats($jury_id) );

				}else{
					$count = count( $mJurys->getCandidatsEntite($jury_id, '', '', '', '',$entiteId) );
				}
				if( isset($_POST) ){
					$page = $_POST['page']; // get the requested page
					$limit = $_POST['rows']; // get how many rows we want to have into the grid
					$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
					$sord = $_POST['sord']; // get the direction
				}else{
					$page = 1;
					$limit = 1000;
					$sidx = 'ASC';
					$sord = 'personne_nom';
				}

				if( $count > 0 ) {
					$total_pages = ceil($count/$limit);
				} else {
					$total_pages = 0;
				}

				if ($page > $total_pages) $page=$total_pages;

				$start = $limit*$page - $limit; // do not put $limit*($page - 1)
				if($role != "organisme référent" && $role != "organisme de formation" && $role != "greta" )
				{
				$candidats = $mJurys->getCandidats($jury_id, $sidx, $sord, $start, $limit);
				}else{

				$candidats = $mJurys->getCandidatsEntite($jury_id, $sidx, $sord, $start, $limit,$entiteId);
				}
				$responce->page = $page;
				$responce->total = $total_pages;
				$responce->records = $count;

				$i = 0;
				foreach($candidats as $candidat){

					$titre = $mMetiers->getTitre($candidat['metier_id']);
					if( !isset($titre['bloc2_lib']) ) $titre['bloc2_lib'] = "";

					$responce->rows[$i]['id'] = $candidat['resultat_id'];
					
					if($role == 'forthac')
					{
						$var = '<a href="'.$front->getBaseUrl().'/candidat/details/?id='.$candidat['candidat_id'].'&operation_id='.$candidat['fiche_id'].'&candidat_metier_id='.$candidat['candidat_metier_id'].'" ><span class="ui-icon ui-icon-zoomin" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" >Détails</span></a>';
					}else{
						$var = '&nbsp;';
					}
if($role == 'forthac' && $candidat['etat_id'] == 5)
{
						$responce->rows[$i]['cell'] = array(
						ucwords( $candidat['civilite_abrege'].' '.$candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
						'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$candidat['entite_id'].'" >'.ucwords( $candidat['entite_nom'] ).'</a>',
						'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$candidat['fiche_id'].'">'.$candidat['fiche_id'].'</a>',
						ucwords( $titre['demarche_abrege'].' '.$titre['bloc1_ab'].' '.$titre['bloc2_lib'] ),
						$var.'<a Target="_blank" href="'.$front->getBaseUrl().'/impression/diplome/?candidat_metier_id='.$candidat['candidat_metier_id'].'"><span class="ui-icon ui-icon-script" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Imprimer le diplôme" >Impression du diplôme</span></a><a Target="_blank" href="'.$front->getBaseUrl().'/impression/tableauresultatscqp/?candidat_metier_id='.$candidat['candidat_metier_id'].'&passage='.$candidat['nb_passage'].'"><span class="ui-icon ui-icon-image" style="float:left; margin-left:5px; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Tableau de résultats" >Tableau de résultats</span></a>');
}else{
						$responce->rows[$i]['cell'] = array(
						ucwords( $candidat['civilite_abrege'].' '.$candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
						'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$candidat['entite_id'].'" >'.ucwords( $candidat['entite_nom'] ).'</a>',
						'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$candidat['fiche_id'].'">'.$candidat['fiche_id'].'</a>',
						ucwords( $titre['demarche_abrege'].' '.$titre['bloc1_ab'].' '.$titre['bloc2_lib'] ),
						$var);
}						
					$i++;
				}

				echo json_encode($responce);

			}

		}

		public function getlistecandidatssansjuryAction(){

			$mJurys = new Model_Jury();
			$mMetiers = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$count = count( $mJurys->getCandidatsSansJury( $this->_request->getParam('branche_id') ) );

			$page = $_POST['page']; // get the requested page
			$limit = $_POST['rows']; // get how many rows we want to have into the grid
			$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
			$sord = $_POST['sord']; // get the direction

			/*if( $_POST ){
				$page = $_POST['page']; // get the requested page
				$limit = $_POST['rows']; // get how many rows we want to have into the grid
				$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				$sord = $_POST['sord']; // get the direction
			}else{
				$page = 0;
				$limit = 0;
				$sidx = 'personne_nom';
				$sord = 'ASC';
			}*/

			////////////////////////
//echo $limit;
			if( $limit == 'Tous' ){

				$candidats = $mJurys->getCandidatsSansJury($this->_request->getParam('branche_id'),$sidx, $sord);

				$responce->page = 1;
				$responce->total = 1;
				$responce->records = count($candidats);

			}else{

				if( $count > 0 ) {
					$total_pages = ceil($count/$limit);
				} else {
					$total_pages = 0;
				}

				if ($page > $total_pages) $page=$total_pages;

				$start = $limit*$page - $limit; // do not put $limit*($page - 1)

				$candidats = $mJurys->getCandidatsSansJury($this->_request->getParam('branche_id'),$sidx, $sord, $start, $limit);

				$responce->page = $page;
				$responce->total = $total_pages;
				$responce->records = $count;

			}

			$i = 0;
			foreach($candidats as $candidat){

				$titre = $mMetiers->getTitre($candidat['metier_id']);
				if( !isset($titre['bloc2_lib']) ) $titre['bloc2_lib'] = "";

				$responce->rows[$i]['id'] = $candidat['resultat_id'];

				$responce->rows[$i]['cell'] = array(
					ucwords( $candidat['civilite_abrege'].' '.$candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
					'<a href="'.$front->getBaseUrl().'/entites/details/?entite_id='.$candidat['entite_id'].'" >'.ucwords( $candidat['entite_nom'] ).'</a>',
					'<a href="'.$front->getBaseUrl().'/operations/visu/num/'.$candidat['fiche_id'].'">'.$candidat['fiche_id'].'</a>',
					ucwords( $titre['demarche_abrege'].' '.$titre['bloc1_ab'].' '.$titre['bloc2_lib'] )
				);

				$i++;
			}

			echo json_encode($responce);

		}

		public function deletemembrejuryAction(){

			$mMembresJury = new Model_MembreJury();

			$jury_id = $this->_request->getParam('jury_id');
			$contact_ids = $this->_request->getParam('contact_ids');

			foreach( explode(',', $contact_ids) as $contact_id ){
				$mMembresJury->delete( " jury_id = $jury_id AND contact_id = $contact_id " );
			}

		}

		public function attributecandidatstojuryAction(){

			$mResultats = new Model_Resultat();

			$jury_id = $this->_request->getParam('jury_id');
			$resultat_ids = $this->_request->getParam('resultat_ids');

			foreach( explode(',', $resultat_ids) as $resultat_id ){
				$data = array(
					'jury_id'	=>	$jury_id
				);
				$where = " resultat_id = $resultat_id ";
				$mResultats->update($data, $where);
			}

		}

		public function attributecandidatstoformationAction(){

			$mResultats = new Model_Resultat();
			$mCandidatsMetier = new Model_CandidatMetier();

			$formation_id = $this->_request->getParam('formation_id');
			$candidat_metier_ids = $this->_request->getParam('ids');

			foreach( explode( ',', $candidat_metier_ids ) as $candidat_metier_id ){
				$data = array(
					'formation_id' => $formation_id
				);
				$where = " candidat_metier_id = $candidat_metier_id ";
				$mCandidatsMetier->update($data, $where);
			}

		}

		public function deletecandidatjuryAction(){

			$mResultats = new Model_Resultat();

			$jury_id = $this->_request->getParam('jury_id');
			$resultat_ids = $this->_request->getParam('resultat_ids');

			foreach( explode(',', $resultat_ids) as $resultat_id ){
				$data = array(
					'jury_id'	=>	null
				);
				$where = " resultat_id = $resultat_id ";
				$mResultats->update($data, $where);
			}

		}

		public function deletecandidatformationAction(){

			$mCandidatsMetier = new Model_CandidatMetier();

			$formation_id = $this->_request->getParam('formation_id');
			$candidat_metier_ids = $this->_request->getParam('ids');

			foreach( explode(',', $candidat_metier_ids) as $candidat_metier_id ){
				$data = array(
					'formation_id'	=>	null
				);
				$where = " candidat_metier_id = $candidat_metier_id ";
				$mCandidatsMetier->update($data, $where);
			}

		}

		public function getcontactssameexpertiseAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$fonction = $this->_request->getParam('fonction');
			$mMetier = new Model_Metier();
		    $numfiche = $mMetier->getFicheMetier($metier_id);
			$contactFiche = new Model_ContactsFiche();
            $entites = $contactFiche->get($numfiche->fiche_id,0,0);
            $contacts = $mMetier->getContactsSameExpertise($metier_id, $fonction);
            
           
           	 	
           foreach( $contacts as $contact ){
           
           foreach( $entites as $entite ){
           	 $id_entite = $entite['entite_id'];
           	 if($contact['entite_id'] == $id_entite)
             {
				echo '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' )</option>';
			}
           }
           	 
           }
		}
		
		public function getcontactsreferentvaeAction(){
		
			$metier_id = $this->_request->getParam('metier_id');
			$fonction = $this->_request->getParam('fonction');
			$mMetier = new Model_Metier();
			$numfiche = $mMetier->getFicheMetier($metier_id);
			$contactFiche = new Model_ContactsFiche();
			$entites = $contactFiche->get($numfiche->fiche_id,0,0);
			$contacts2 = $mMetier->getReferentsVAE($metier_id);
			foreach( $contacts2 as $contact2 ){
				 
				foreach( $entites as $entite ){
					$id_entite = $entite['entite_id'];
					if($contact2['entite_id'] == $id_entite)
					{
						echo '<option value="'.$contact2['contact_id'].'" >'.ucwords( $contact2['personne_prenom'].' '.$contact2['personne_nom'].' ( '.$contact2['entite_nom'] ).' )</option>';
					}
				}
				 
			}
		}
		
		

		public function getexpertsAction(){

			$role = Zend_Auth::getInstance()->getIdentity()->role;

			$metier_id = $this->_request->getParam('metier_id');

			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$contacts = $mMetier->getExperts($metier_id);

			foreach( $contacts as $contact ){
				$select = "";
				if( $contact['binome_defaut'] != 0 ) $select = ' checked = "checked" ';
				echo '<li>'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
				if( $role != 'branche' && $role != 'délégation' ) echo '<a href="#" class="experts delete" id="'.$contact['contact_id'].'" ><p class="ui-icon ui-icon-trash" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Enlever" ></p></a> ';
				echo '<a href="'.$front->getBaseUrl().'/contacts/details/?contact_id='.$contact['contact_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" ></p></a> ';
				if( $role != 'branche' && $role != 'délégation' ) echo '<input type="checkbox" class="expert" id="'.$metier_id.'" title="Si vous cocher cette case, cette personne sera l\'expert métier par défaut" value="'.$contact['contact_id'].'" '.$select.' /> </li>';
			}

		}

		public function getexpertsselectelementAction(){

			$metier_id = $this->_request->getParam('metier_id');

			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$contacts = $mMetier->getExperts($metier_id);

			foreach( $contacts as $contact ){
				echo '<option value="'.$contact['binome_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' )</option>';
			}

		}

		public function getevaluateursAction(){

			$role = Zend_Auth::getInstance()->getIdentity()->role;

			$metier_id = $this->_request->getParam('metier_id');

			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$contacts = $mMetier->getEvaluateurs($metier_id);

			foreach( $contacts as $contact ){
				$select = "";
				if( $contact['binome_defaut'] != 0 ) $select = ' checked = "checked" ';
				echo '<li>'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
				if( $role != 'branche' && $role != 'délégation' ) echo '<a href="#" class="evaluateurs delete" id="'.$contact['contact_id'].'" ><p class="ui-icon ui-icon-trash" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Enlever" ></p></a> ';
				echo '<a href="'.$front->getBaseUrl().'/contacts/details/?contact_id='.$contact['contact_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" ></p></a> ';
				if( $role != 'branche' && $role != 'délégation' ) echo '<input type="checkbox" class="evaluateur" id="'.$metier_id.'" title="Si vous cocher cette case, cette personne sera le référent par défaut" value="'.$contact['contact_id'].'" '.$select.' /> </li>';
			}

		}
		
	   public function getorganismesAction(){

			$role = Zend_Auth::getInstance()->getIdentity()->role;

			$metier_id = $this->_request->getParam('metier_id');

			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$contacts = $mMetier->getOrganisme($metier_id);

			foreach( $contacts as $contact ){
				$select = "";
				if( $contact['orga_spe_defaut'] != 0 ) $select = ' checked = "checked" ';
				echo '<li>'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
				if( $role != 'branche' && $role != 'délégation' ) echo '<a href="#" class="organismes delete" id="'.$contact['contact_id'].'" ><p class="ui-icon ui-icon-trash" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Enlever" ></p></a> ';
				echo '<a href="'.$front->getBaseUrl().'/contacts/details/?contact_id='.$contact['contact_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" ></p></a> ';
				if( $role != 'branche' && $role != 'délégation' ) echo '</li>';
			}

		}
		
		public function getreferentsAction(){
		
			$role = Zend_Auth::getInstance()->getIdentity()->role;
		
			$metier_id = $this->_request->getParam('metier_id');
		
			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();
		
			$contacts = $mMetier->getReferents($metier_id);
			
			
			foreach( $contacts as $contact ){
				$select = "";
				echo '<li>'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
				if( $role != 'branche' && $role != 'délégation' ) echo '<a href="#" class="referentsvae delete" id="'.$contact['contact_id'].'" ><p class="ui-icon ui-icon-trash" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Enlever" ></p></a> ';
				echo '<a href="'.$front->getBaseUrl().'/contacts/details/?contact_id='.$contact['contact_id'].'"><p class="ui-icon ui-icon-zoomin" style="margin: 0; display: inline-block; vertical-align: middle; background-image: url('.$front->getBaseUrl().'/css/jquery-forthac/images/ui-icons_007dc5_256x240.png);" title="Détails" ></p></a> ';
				if( $role != 'branche' && $role != 'délégation' ) echo '</li>';
			}
		
		}
		
		
		

		public function getevaluateursselectelementAction(){

			$metier_id = $this->_request->getParam('metier_id');

			$mMetier = new Model_Metier();
			$front = Zend_Controller_Front::getInstance();

			$contacts = $mMetier->getEvaluateurs($metier_id);

			foreach( $contacts as $contact ){
				echo '<option value="'.$contact['binome_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' )</option>';
			}

		}

		public function addbinomeAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');
			$type_binome = $this->_request->getParam('type');

			$mBinomes = new Model_Binome();
			$mOperations = new Model_Fiche();
			$mMetiers = new Model_Metier();
			$testBinome = $mBinomes->get(0,$metier_id,$contact_id);
			if($testBinome == false)
			{
				$mBinomes->add($metier_id, $contact_id,false,$type_binome);
				$metier = $mMetiers->get($metier_id);
				$fiche_id = $metier['fiche_id'];
				$mOperations->updateDateModif($fiche_id);
			}

		}
		
		public function addorganismeAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');

			$mOrgaSpe= new Model_OrgaSpe();
			$mOperations = new Model_Fiche();
			$mMetiers = new Model_Metier();

			$testOrga = $mOrgaSpe->get(0,$metier_id,$contact_id);
			if($testOrga == false)
			{
				$mOrgaSpe->add($metier_id, $contact_id);
				$metier = $mOrgaSpe->get($metier_id);
				$fiche_id = $metier['fiche_id'];
				$mOperations->updateDateModif($fiche_id);
			}
		}
		
		
		
		public function addreferentAction(){
		
			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');
		
			$mReferentsVAE= new Model_Referentsvae();
			$mOperations = new Model_Fiche();
			$mMetiers = new Model_Metier();
			$testRef= $mReferentsVAE->get($metier_id,$contact_id);
			
			if($testRef == false)
			{
				$mReferentsVAE->add($metier_id, $contact_id);
			}
		}
		
		
		public function deletereferentAction(){
		
			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');
		
			$mReferentsVAE= new Model_Referentsvae();
			$mReferentsVAE->delete(" metier_id = $metier_id AND contact_id = $contact_id ");
		}
		
		

		public function deletebinomeAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');

			$mBinomes = new Model_Binome();
			$mOperations = new Model_Fiche();
			$mMetiers = new Model_Metier();

			$mBinomes->delete(" metier_id = $metier_id AND contact_id = $contact_id ");

			$metier = $mMetiers->get($metier_id);
			$fiche_id = $metier['fiche_id'];

			$mOperations->updateDateModif($fiche_id);

		}
		
	    public function deleteorganismeAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');

			$mOrgaSpe = new Model_OrgaSpe();
			$mOperations = new Model_Fiche();
			$mMetiers = new Model_Metier();

			$mOrgaSpe->delete(" metier_id = $metier_id AND contact_id = $contact_id ");

			$metier = $mMetiers->get($metier_id);
			$fiche_id = $metier['fiche_id'];

			$mOperations->updateDateModif($fiche_id);

		}
		

		public function updatebinomedefaultAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$contact_id = $this->_request->getParam('contact_id');
			$type = $this->_request->getParam('type');
			$value = $this->_request->getParam('check');

			$mMetiers = new Model_Metier();
			$mBinomes = new Model_Binome();
			$mOperations = new Model_Fiche();

			if( $type == 'expert' ) $contacts = $mMetiers->getExperts($metier_id);
			elseif( $type == 'evaluateur' ) $contacts = $mMetiers->getEvaluateurs($metier_id);

			if( $value == 'true' ) $value = 1;
			else $value = 0;

			foreach( $contacts as $contact ){
				if( $contact['contact_id'] == $contact_id ) $mBinomes->setDefault($metier_id, $contact['contact_id'], $value);
				else $mBinomes->setDefault($metier_id, $contact['contact_id']);
			}

			$metier = $mMetiers->get($metier_id);
			$fiche_id = $metier['fiche_id'];

			$mOperations->updateDateModif($fiche_id);

		}

		public function getexpertisesAction(){

			$mContacts = new Model_Contact();
			$mTitres = new Model_Titre();

			$contact_id = $this->_request->getParam('contact_id');

			$expertises = $mContacts->getExpertises($contact_id);

			$s_expertises = "";
			foreach( $expertises as $expertise ){
				$s_expertises .= "<li>";
					$titre = $mTitres->get($expertise['demarche_id'], $expertise['bloc1_id'], $expertise['bloc2_id']);
					$s_expertises .= $titre['bloc1']['libelle'];
					if( isset( $titre['bloc2'] ) ){
						$s_expertises .= ' / '.$titre['bloc2']['libelle'];
					}
					$s_expertises .= ' <p class="ui-icon ui-icon-trash delete expertise" style="margin: 0pt; display: inline-block; vertical-align: middle;" title="Supprimer" id="'.$expertise['expertise_id'].'" ></p>';
				$s_expertises .= "</li>";
			}
			echo $s_expertises;

		}

		public function addexpertiseAction(){

			$contact_id = $this->_request->getParam('contact_id');
			$demarche_id = $this->_request->getParam('demarche_id');
			$bloc1_id = $this->_request->getParam('bloc1_id');
			$bloc2_id = $this->_request->getParam('bloc2_id');

			if( $bloc2_id == null ) $bloc2_id = 0;

			$mExpertises = new Model_Expertise();

			$mExpertises->add($contact_id, $demarche_id, $bloc1_id, $bloc2_id);

		}

		public function deleteexpertiseAction(){

			$expertise_id = $this->_request->getParam('expertise_id');

			$mExpertises = new Model_Expertise();

			$mExpertises->delete( " expertise_id = $expertise_id " );

		}

		public function checkpersonneexistAction(){

			if( $this->_request->getParam('nom') && $this->_request->getParam('prenom') ){

				$nom = $this->_request->getParam('nom');
				$prenom = $this->_request->getParam('prenom');

				$mPersonnes = new Model_Personne();
				$mEntites = new Model_Entite();
				$mCandidats = new Model_Candidat();

				$personnes = $mPersonnes->checkPersonneExist($nom, $prenom);
				$list = "";
				if( count( $personnes ) > 0 ) $list .= "<ul>";
				foreach( $personnes as $personne ){
					$entite = $mEntites->get($personne['entite_id']);
					$candidat = $mCandidats->getByPersonneID($personne['personne_id']);
					$candidat_id = $candidat['candidat_id'];
					$list .= "<li>";
					$list .= ucwords( $personne['personne_prenom'].' '.$personne['personne_nom'].' ( '.$entite['entite_nom'].' )' );
					$list .= ' candidat ( '.$candidat_id.' )';
					$list .= '<a href="#" class="add" id="'.$candidat_id.'" ><p class="ui-icon ui-icon-check" style="margin: 0px; display: inline-block; vertical-align: middle;" title="Choisir cette personne" ></p></a>';
					$list .= "</li>";
				}
				if( count( $personnes ) > 0 ) $list .= "</ul>";
				echo $list;
			}

		}

		public function getlistecandidatsAction(){

			$string = $this->_request->getParam('string');

			$mCandidats = new Model_Candidat();
			$mMetiers = new Model_Metier();
		
			        $sidx ='';
        			$sord ='';
        			$start='';
        			$limit ='';
        
			$contactFiche = new Model_ContactsFiche();
			$mEntite = new Model_Entite();
            $entites = $contactFiche->get($this->_request->getParam('operation'),0,0);
           
             foreach( $entites as $entite ){
           	 $id_entite = $entite['entite_id'];
        
	         $entiteDonnees = $mEntite->getEntreprisesById($id_entite);
	           	if(count($entiteDonnees) >0)
			           	{
							$entite_id_auto = $entiteDonnees[0]['entite_id'];
			           	}
	              }
			$count = count( $mCandidats->getListeCandidats($string, $sidx, $sord, 0, 0,$entite_id_auto) );

			if( isset( $_POST['page'] ) ){
				$page = $_POST['page']; // get the requested page
				$limit = $_POST['rows']; // get how many rows we want to have into the grid
				$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
				$sord = $_POST['sord']; // get the direction
			}else{
				$page = 1;
				$limit = 10;
				$sidx = "personne_nom";
				$sord = 'ASC';
			}

			if( $count > 0 ) {
				$total_pages = ceil($count/$limit);
			} else {
				$total_pages = 0;
			}

			if ($page > $total_pages) $page=$total_pages;

			$start = $limit*$page - $limit; // do not put $limit*($page - 1)

			
			
			
			
			$candidats = $mCandidats->getListeCandidats($string, $sidx, $sord, $start, $limit,$entite_id_auto);

			$responce->page = $page;
			$responce->total = $total_pages;
			$responce->records = $count;

			
			
			
			$i = 0;
			foreach($candidats as $candidat){

				$t='';
				$t = $mMetiers->getTitre($candidat['metier_id']);
				$titre = $t['demarche_abrege'].' '.$t['bloc1_ab'];
				if( $t['bloc2_id'] > 0 )
					$titre .= ' '.$t['bloc2_lib'];

				$responce->rows[$i]['id'] = $candidat['candidat_id'];

				$responce->rows[$i]['cell'] = array(
					ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] ),
					ucwords( $candidat['entite_nom'] ),
					$candidat['fiche_id'],
					ucwords( $titre )
				);

				$i++;
			}

			echo json_encode($responce);
		
		}

		public function addexistcandidatAction(){

			$candidat_id = $this->_request->getParam('candidat_id');
			$metier_id = $this->_request->getParam('metier_id');

			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();
					
			$evaluateur_id = $mMetiers->getEvaluateurIDDefault($metier_id);
			$expert_id = $mMetiers->getExpertIDDefault($metier_id);
			$demarche = $mMetiers->getDemarche($metier_id);
					
					if($demarche['demarche_abrege'] == 'ccsp' || $demarche['demarche_abrege'] == 'cqpbranche')
					{
					$etatbase = 10;
					}else{
					$etatbase = 1;	
					}
			$data = array(
				'candidat_id'	=>	$candidat_id,
				'metier_id'		=>	$metier_id,
				'etat_id'		=>	$etatbase,
				'tuteur_id'		=>	$evaluateur_id,
				'expert_id'		=>	$expert_id
			);
			$mCandidatMetiers->insert($data);
			$mMetiers->getFicheMetier($metier_id)->fiche_id;

		}

		public function updatecandidatmetierAction(){

			//Zend_Debug::dump($_POST);
			$mCandidatsMetier = new Model_CandidatMetier();
			$mResultats = new Model_Resultat();
			$mEtats = new Model_Etat();
			$mMetiers = new Model_Metier();

			//fiche enquete
			if( $_POST['candidat_metier_fiche_enquete'] == 'on' ) $fiche_enquete = 1; else $fiche_enquete = 0;

			//abandon
			if( $_POST['abandon'] == 'on' ){
				$mEtats = new Model_Etat();
				$etats = $mEtats->getByLibelle( 'abandon' );
				$etat_id = $etats[0]['etat_id'];
			}else{
				$etat = $mCandidatsMetier->getEtat( $_POST['candidat_metier_id'] );
				$etat_id = $etat['etat_id'];
				//certifié
				$e = $mEtats->get($etat_id);
				if( $e['etat_libelle'] == 'admissible' && $_POST['jury_id'] > 0 ){
					$c = $mEtats->getByLibelle('certifié');
					$etat_id = $c[0]['etat_id'];
				}
			}


		
			//tuteur
			if( $_POST['tuteur_id'] == 0 ) $tuteur = null; else $tuteur = $_POST['tuteur_id'];
			//expert
			if( $_POST['expert_id'] == 0 ) $expert = null; else $expert = $_POST['expert_id'];
			//raison
			if( $_POST['raison_id'] == 0 ) $raison = null; else $raison = $_POST['raison_id'];
			//formation
			if( $_POST['formation_id'] == 0 ) $formation = null; else $formation = $_POST['formation_id'];
			//organisme de formation
			if( $_POST['org_formation_id'] == 0 ) $org_formation = null; else $org_formation = $_POST['org_formation_id'];
			//formateur
			if( $_POST['formateur_id'] == 0 ) $formateur = null; else $formateur = $_POST['formateur_id'];
			$data = array(
				'metier_id'									=>	$_POST['metier_id'],
				'tuteur_id'									=>	$tuteur,
				'expert_id'									=>	$expert,
				'candidat_metier_fiche_enquete'				=>	$fiche_enquete,
				'etat_id'									=>	$etat_id,
				'raison_id'									=>	$raison,
				'formation_id'								=>	$formation,
				'candidat_metier_formation_duree_estimee'	=>	$_POST['candidat_metier_formation_duree_estimee'],
				'candidat_metier_formation_duree_realisee'	=>	$_POST['candidat_metier_formation_duree_realisee'],
				'candidat_metier_formation_remarque'		=>	$_POST['candidat_metier_formation_remarque'],
				'candidat_metier_formation_remarque2'		=>	$_POST['candidat_metier_formation_remarque2'],
				'org_formation_id'							=>	$org_formation,
				'formateur_id'								=>	$formateur
			);
			$where = ' candidat_metier_id = '.$_POST['candidat_metier_id'];
			$mCandidatsMetier->update($data, $where);

			
			
				$metier = $mCandidatsMetier->getMetier($_POST['candidat_metier_id']);

			$titre = $mMetiers->getTitre($metier['metier_id']);		
			
			
	if($titre['bloc1_ab'] == 'CCSP')
	{

		if($_POST['candidat_metier_formation_duree_estimee'] > 0)
			{
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], '2');
			}
		if($_POST['candidat_metier_formation_duree_realisee'] > 0)
			{
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], '4');
			}
	}	
	
	
	
			
			
			
			
			
			
			if( isset($_POST['jury_id']) && $_POST['jury_id'] > 0 ){
				$resultat = $mResultats->getLast($_POST['candidat_metier_id']);

				$data = array(
					'jury_id'					=>	$_POST['jury_id'],
					'resultat_commentaire_jury' =>	$_POST['resultat_commentaire_jury']
				);
				$where = " resultat_id = ".$resultat['resultat_id'];
				$mResultats->update($data, $where);
			}
			
			if($titre['titre'] == 'DIPLOME')
			{
				if( isset($_POST['jury_id']) && $_POST['jury_id'] > 0 )
				{
					$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], '4');
				}
			
			}
			
			
		}

		public function getformateursAction(){

			$entite_id = $this->_request->getParam('entite_id');

			$mContacts = new Model_Contact();
			$formateurs = $mContacts->getFormateurs($entite_id);

			$select_formateurs = '<option value="0" >Aucun</option>';
			foreach( $formateurs as $formateur ){
				$select_formateurs .= '<option value="'.$formateur['contact_id'].'" >'.$formateur['personne_prenom'].' '.$formateur['personne_nom'].'</option>';
			}
			echo $select_formateurs;

		}

		public function getentreprisesbybrancheAction(){

			$branche_id = $this->_request->getParam('branche_id');

			$mEntites = new Model_Entite();
			$entites = $mEntites->getEntreprisesByBranche( $branche_id );
			foreach( $entites as $entite ){
				echo '<option value="'.$entite['entite_id'].'" >'.ucfirst( $entite['entite_nom'] ).'</option>';
			}

		}

		public function exportAction(){

			$outs = explode('@', $this->_request->getParam('out') );

			echo $this->_request->getParam('operation');

		}

		public function setabandonAction(){

			$candidat_metier_id = $this->_request->getParam('candidat_metier_id');

			$mCandidatsMetier = new Model_CandidatMetier();
			$mEtats = new Model_Etat();

			$etat = $mEtats->getByLibelle('abandon');

			$data = array(
				'etat_id'	=>	$etat[0]['etat_id']
			);
			$where = " candidat_metier_id = $candidat_metier_id ";
			$mCandidatsMetier->update($data, $where);

		}

		public function getcontactssameorganismeAction(){

			$metier_id = $this->_request->getParam('metier_id');
			$fonction = $this->_request->getParam('fonction');

			$mMetier = new Model_Metier();
			$contacts = $mMetier->getContactsSameOrganisme($metier_id, $fonction);

			foreach( $contacts as $contact ){
				echo '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' )</option>';
			}

		}
		
		
		
	}