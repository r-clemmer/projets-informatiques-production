<?php

	class Model_Entite extends Zend_Db_Table_Abstract{
		
		protected $_name = 'entite';
		protected $_primary = 'entite_id';
		protected $_dependentTables = array(
			'Model_ContactsFiche',
			'Model_EntiteTypeEntite',
			'Model_Personne'
		);
		
		public function get($entite_id = 0){
			if( $entite_id > 0 ) return $this->find($entite_id)->current();
		}
		
		public function update($entite_id, $entite_nom, $entite_code = '', $entite_adresse, $entite_ville, $entite_cp, $entite_activite = '', $entite_tel = '', $parent_id = '', $entite_login = '', $types, $visible =''){
			
			$sql = "
    			UPDATE entite
    			SET entite_nom = '$entite_nom',
    			entite_code = '$entite_code',
    			entite_adresse = '$entite_adresse',
    			entite_ville = '$entite_ville',
    			entite_cp = '$entite_cp',
    			entite_activite = '$entite_activite',
    			entite_tel = '$entite_tel',
    			parent_id = '$parent_id',
    			entite_login = '$entite_login',
    			visible = '$visible'
    			WHERE entite_id = $entite_id
    		";
			$this->_db->query($sql);
			
			$sql = "
				DELETE FROM entite_type_entite
				WHERE entite_id = $entite_id
			";
			$this->_db->query($sql);
			
			foreach($types as $type){
				$sql = "
					INSERT INTO entite_type_entite (entite_id, type_entite_id)
					VALUES ($entite_id, $type)
				";
				$this->_db->query($sql);
			}
			
		}
		
		public function getByLibelle($string){
			
			$sql = "
				SELECT *
				FROM entite
				WHERE entite_nom LIKE '$string%'
				ORDER BY entite_nom
				LIMIT 0, 10
			";
			
			$stmt = $this->_db->query($sql);
	    	$entites = $stmt->fetchAll();
	    	
	    	return $entites;
		}
		
		public function getByLibelleStrict($string){
			
			$sql = "
				SELECT *
				FROM entite
				WHERE entite_nom LIKE '$string'
			";
			
			$stmt = $this->_db->query($sql);
	    	$entite = $stmt->fetch();
	    	
	    	return $entite;
		}
		
		public function getTypeEntite($entite_id){
			
			$sql = "
				SELECT te.*
				FROM entite_type_entite as ete, type_entite as te
				WHERE ete.type_entite_id = te.type_entite_id
				AND ete.entite_id = $entite_id
			";
			
			$stmt = $this->_db->query($sql);
	    	$row = $stmt->fetchObject();
	    	return $row;
			
		}
		
		public function getTypesEntite($entite_id){
			
			$sql = "
				SELECT te.*
				FROM entite_type_entite as ete, type_entite as te
				WHERE ete.type_entite_id = te.type_entite_id
				AND ete.entite_id = $entite_id
			";
			
			$stmt = $this->_db->query($sql);
	    	$types = $stmt->fetchAll();
	    	
	    	return $types;
		}
		
		public function getByTypeEntite($type_entite_libelle,$entite_id = 0){
			if($entite_id>0)
			{
			 $where= " AND e.entite_id = '".$entite_id."' ";
			}else{
			$where ='';
			}
			$sql = "
				SELECT e.*
				FROM entite AS e, entite_type_entite AS ete, type_entite AS te
				WHERE e.entite_id = ete.entite_id
				AND ete.type_entite_id = te.type_entite_id
				AND te.type_entite_libelle = '".$type_entite_libelle."'".$where."
				ORDER BY e.entite_nom
			";
			
			$stmt = $this->_db->query($sql);
	    	$entites = $stmt->fetchAll();
	    	return $entites;
		}
		
	public function getByTypeEntiteActives($type_entite_libelle,$entite_id = 0){
			if($entite_id>0)
			{
			 $where= " AND e.entite_id = '".$entite_id."' ";
			}else{
			$where ='';
			}
			$sql = "
				SELECT e.*
				FROM entite AS e, entite_type_entite AS ete, type_entite AS te
				WHERE e.entite_id = ete.entite_id
				AND ete.type_entite_id = te.type_entite_id
				AND e.visible = 'oui'
				AND te.type_entite_libelle = '".$type_entite_libelle."'".$where."
				ORDER BY e.entite_nom
			";
			
			$stmt = $this->_db->query($sql);
	    	$entites = $stmt->fetchAll();
	    	return $entites;
		}
		
		
		
		public function getByTypesEntite( $types = array() ){
			
			$sql = "
				SELECT e.*
				FROM entite AS e, entite_type_entite AS ete, type_entite AS te
				WHERE e.entite_id = ete.entite_id
				AND ete.type_entite_id = te.type_entite_id ";
			foreach( $types as $type ){
				$sql .= " AND te.type_entite_libelle = '$type' ";
			}
			$sql .= " ORDER BY e.entite_nom ";
			
			$stmt = $this->_db->query($sql);
	    	$entites = $stmt->fetchAll();
	    	return $entites;
		}
		
		public function getContacts( $entite_id ){
			
			$sql = "
				SELECT p.*, c.*
				FROM entite AS e, personne AS p, contact AS c
				WHERE p.entite_id = $entite_id
				AND p.entite_id = e.entite_id
				AND c.personne_id = p.personne_id
				ORDER BY p.personne_nom
			";
			$stmt = $this->_db->query($sql);
	    	$contacts = $stmt->fetchAll();
	    	return $contacts;
		}
	public function getContactsActifs( $entite_id ){
			
			$sql = "
				SELECT p.*, c.*
				FROM entite AS e, personne AS p, contact AS c
				WHERE p.entite_id = $entite_id
				AND p.entite_id = e.entite_id
				AND p.visible = 'oui'
				AND c.personne_id = p.personne_id
				ORDER BY p.personne_nom
			";
			$stmt = $this->_db->query($sql);
	    	$contacts = $stmt->fetchAll();
	    	return $contacts;
		}
		
		public function existLogin( $login, $entite_id = 0 ){
			
			$count = 0;
			$entite = "";
			if($entite_id > 0) $entite = " AND entite_id != $entite_id ";
			$sql = "
				SELECT *
				FROM entite
				WHERE entite_login = '$login'
				$entite
			";
			$stmt = $this->_db->query($sql);
	    	$res = $stmt->fetchAll();
	    	$count += count($res);
	    	if( $count > 0 ){
	    		return 1;
	    	}else{
	    		return 0;
	    	}
		}
		
		public function checkType( $entite_id, $type_libelle ){
			
			$sql = "
				SELECT
				  count(*) AS count
				FROM
				  entite_type_entite AS ete,
				  type_entite AS te
				WHERE
				  ete.type_entite_id = te.type_entite_id
				  AND ete.entite_id = $entite_id
				  AND te.type_entite_libelle = '$type_libelle'
			";
			$stmt = $this->_db->query($sql);
	    	$count = $stmt->fetchAll();
	    	return $count[0]['count'];
		}

		public function getListe( $string, $type = null, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){
			
			$strings = explode(' ', $string);
			$where = "";
			$i = 0;
			foreach( $strings as $string ){
				$i++;
				$where .= " AND e.entite_nom LIKE '%$string%' ";
			}

			$where2 = "";
			if( $type != null ){
				$where2 = " AND ete.type_entite_id = $type ";
			}
			
			$sql = "
				SELECT
				  e.*
				FROM
				  `entite` AS e,
				  `entite_type_entite` AS ete
				WHERE
				  ete.entite_id = e.entite_id
				  $where
				  $where2
			";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
//			echo $sql;
			$stmt = $this->_db->query( $sql );
			return $entites = $stmt->fetchAll();
		}

		public function getFiches( $entite_id = 0 ){

			if( $entite_id > 0 ){

				$sql = "
					SELECT
						f.*
					FROM
						`contacts_fiche` AS cf,
						`fiche` AS f
					WHERE
						cf.fiche_id = f.fiche_id
						AND cf.entite_id = $entite_id
				";
				$stmt = $this->_db->query( $sql );
				return $fiches = $stmt->fetchAll();

			}

		}

		public function exist( $entite_id = 0 ){

			if( $entite_id > 0 ){
				$entite = $this->get($entite_id);
				if( $entite ){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}

		}

		public function existByString( $entite_nom = '' ){

			if( $entite_nom != '' ){
				$entite = $this->select()->where(" entite_nom LIKE '$entite_nom' ")->query()->fetch();
				if( $entite ){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}

		}

		public function getEntreprises(){

			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND te.type_entite_libelle = 'entreprise'
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $entreprises = $this->_db->query( $sql )->fetchAll();
		}
	public function getEntreprisesActif(){

			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND e.visible='oui'
					AND te.type_entite_libelle = 'entreprise'
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $entreprises = $this->_db->query( $sql )->fetchAll();
		}

		public function getEntreprisesByBranche( $branche_id = 0 ){

			if( $branche_id > 0 ) $where = " AND e.parent_id = $branche_id ";
			else $where = "";
			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND te.type_entite_libelle = 'entreprise'
					$where
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $entreprises = $this->_db->query( $sql )->fetchAll();
		}

		public function getEntreprisesByNom( $nom = '' ){

			if( $nom != '' ){
				$where = " AND e.entite_nom LIKE '$nom%' ";
			}else{
				$where = "";
			}

			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND te.type_entite_libelle = 'entreprise'
					$where
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $entreprises = $this->_db->query( $sql )->fetchAll();
		}

		public function getEntreprisesById( $id_entite = 0){

			if( $id_entite > 0 ){
				$where = " AND e.entite_id = '".$id_entite."' ";
		
			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND te.type_entite_libelle = 'entreprise'
					$where
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $entreprises = $this->_db->query( $sql )->fetchAll();
			}
		}
		
		
		
		public function getOrganismesReferents($entite_id = 0){
		if($entite_id > 0)
			{
			 $where= " AND e.entite_id = '".$entite_id."' ";
			}else{
			$where ='';
			}
			
			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND te.type_entite_libelle = 'organisme référent'".$where."
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $organimes_ref = $this->_db->query( $sql )->fetchAll();
		}

			public function getOrganismesReferentsActif($entite_id = 0){
		if($entite_id > 0)
			{
			 $where= " AND e.entite_id = '".$entite_id."' ";
			}else{
			$where ='';
			}
			
			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND e.visible='oui'
					AND te.type_entite_libelle = 'organisme référent'".$where."
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $organimes_ref = $this->_db->query( $sql )->fetchAll();
		}
		
		
		public function getgretaReferentsActif(){
		
				
			$sql = "
			SELECT
			e.*
			FROM
			`entite` AS e,
			`entite_type_entite` AS ete,
			`type_entite` AS te
			WHERE
			te.type_entite_id = ete.type_entite_id
			AND ete.entite_id = e.entite_id
			AND e.visible='oui'
			AND te.type_entite_libelle = 'greta' 
			GROUP BY e.entite_id
			ORDER BY e.entite_nom
			";
			return $greta_ref = $this->_db->query( $sql )->fetchAll();
		}
		
		
		public function getDelegationsVisible(){

			$sql = "
				SELECT
					e.*
				FROM
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
					te.type_entite_id = ete.type_entite_id
					AND ete.entite_id = e.entite_id
					AND e.visible='oui'
					AND te.type_entite_libelle = 'délégation'
				GROUP BY e.entite_id
				ORDER BY e.entite_nom
			";
			return $delegations = $this->_db->query( $sql )->fetchAll();
		}
		
		public function getInfosOrgForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array());
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
	
			//Expert metier
			$req->joinLeft('binome AS b0','b0.binome_id=cm0.expert_id',array());
			$req->joinLeft('contact as c1','c1.contact_id=b0.contact_id',array());
			$req->joinLeft('personne as p1','p1.personne_id=c1.personne_id',array('p1.personne_nom AS expert_nom','p1.personne_prenom AS expert_prenom','p1.civilite_id AS expert_civilite'));
			
			//referent
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e0','e0.entite_id=cf0.entite_id',array('e0.entite_nom AS org'));
			$req->joinLeft('entite_type_entite AS ete0','ete0.entite_id= e0.entite_id',array());
			$req->joinLeft('type_entite AS te0','te0.type_entite_id= ete0.type_entite_id',array());
			$req->where('te0.type_entite_id=2');
			
			//Delegation et conseiller
			$req->joinLeft('contacts_fiche AS cf1','cf1.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e1','e1.entite_id=cf1.entite_id',array('e1.entite_nom AS delegation'));
			$req->joinLeft('contact AS c2','c2.contact_id=cf1.contact_id',array());
			$req->joinLeft('personne AS p2','p2.personne_id=c2.personne_id',array('p2.personne_nom AS con_nom','p2.personne_prenom AS con_prenom','p2.civilite_id AS con_civilite'));		
			$req->joinLeft('entite_type_entite AS ete1','ete1.entite_id= e1.entite_id',array());
			$req->joinLeft('type_entite AS te1','te1.type_entite_id= ete1.type_entite_id',array());
			$req->where('te1.type_entite_id=4');
			
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());
		
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			
			$query = $req->query();
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
		
	public function getInfosEntForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array());
		
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());
		
			//Info entreprise
			$req->joinLeft('entite AS e0','e0.entite_id=p0.entite_id',array('e0.entite_nom AS entreprise','e0.entite_ville AS ville'));
			//Info branche
			$req->joinLeft('entite AS e2','e2.entite_id=e0.parent_id',array('e2.entite_nom AS branche'));
			
			
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			$query = $req->query();
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
	    
	}