<?php

	class Model_Fiche extends Zend_Db_Table_Abstract{
		
		protected $_name = 'fiche';
		protected $_primary = 'fiche_id';
		protected $_dependentTables = array('Model_Metier', 'Model_ContactsFiche');
		protected $_referenceMap = array(
	        'objectif'	=> array(
	            'columns'		=> 'objectif_id',
	            'refTableClass' => 'Model_Objectif',
				'refColumns'	=> 'objectif_id'
	        )
	    );
	    
	    public function get( $fiche_id ){
	    	
	    	$fiche = $this->find($fiche_id)->current();
	    	return $fiche;
	    }
	    
	    public function getByEntite($entite_id){
	    	
	    	$sql = "
	    		SELECT f.*
	    		FROM fiche AS f, contacts_fiche AS cf
	    		WHERE cf.fiche_id = f.fiche_id
	    		AND cf.entite_id = $entite_id
	    	";
	    	
	    	$stmt = $this->_db->query($sql);
	    	$fiches = $stmt->fetchAll();
	    	
	    	return $fiches;
	    }
	    
	    public function count( $entite_id = null, $date1 = null, $date2 = null ){
	    	
	    	if($entite_id != null){
		    	$sql = "
		    		SELECT count(*) AS count
					FROM fiche AS f, contacts_fiche AS cf
					WHERE f.fiche_id = cf.fiche_id
					AND cf.entite_id = $entite_id
		    	";
	    	}else{
	    		if( $date1 == null && $date2 == null ){
		    		$sql = "
		    			SELECT count(*) AS count
		    			FROM fiche
		    		";
	    		}else{
	    			$sql = "
		    			SELECT count(*) AS count
		    			FROM fiche
		    			WHERE fiche_date_creation >= '$date1'
						AND fiche_date_creation <= '$date2'
		    		";
	    		}
	    	}
	    	$stmt = $this->_db->query($sql);
	    	$fiches = $stmt->fetchAll();
	    	$count = $fiches[0]['count'];
	    	return $count;
	    }
	    
		public function countprojet( $entite_id = null ){
	    	
	    	if($entite_id != null){
		    	$sql = "
		    		SELECT count(*) AS count
					FROM fiche AS f, contacts_fiche AS cf
					WHERE f.fiche_id = cf.fiche_id
					AND cf.entite_id = $entite_id
					AND f.fiche_projet = 1
		    	";
	    	}else{
	    		$sql = "
	    			SELECT count(*) AS count
	    			FROM fiche
	    			WHERE fiche_projet = 1
	    		";
	    	}
	    	$stmt = $this->_db->query($sql);
	    	$fiches = $stmt->fetchAll();
	    	$count = $fiches[0]['count'];
	    	return $count;
	    }
	    
		public function countnonvalidee( $entite_id = null ){
	    	
	    	if($entite_id != null){
		    	$sql = "
		    		SELECT count(*) AS count
					FROM fiche AS f, contacts_fiche AS cf
					WHERE f.fiche_id = cf.fiche_id
					AND cf.entite_id = $entite_id
					AND f.fiche_acces_candidats != 1
		    	";
	    	}else{
	    		$sql = "
	    			SELECT count(*) AS count
	    			FROM fiche
	    			WHERE fiche_acces_candidats != 1
	    		";
	    	}
	    	$stmt = $this->_db->query($sql);
	    	$fiches = $stmt->fetchAll();
	    	$count = $fiches[0]['count'];
	    	return $count;
	    }
	    
	    public function create(){
	    	
	    	$date = date("Y-m-d");
	    	$sql = "
		    	INSERT INTO `fiche` (
					`fiche_acces_candidats` ,
					`fiche_projet` ,
					`fiche_date_creation` ,
					`fiche_date_modif` ,
					`objectif_id`
				)VALUES (
					'0', '0', '$date', '$date', NULL
				);
	    	";
	    	$this->_db->query($sql);
	    	return $fiche_id = $this->_db->lastInsertId();
	    }
	    
	    public function addcontact( $fiche_id, $entite_id, $contact_id ){
	    	
	    	$sql = "
	    		INSERT INTO contacts_fiche(
	    			fiche_id,
	    			entite_id,
	    			contact_id
	    		)VALUES(
	    			$fiche_id,
	    			$entite_id,
	    			$contact_id
	    		);
	    	";
	    	$this->_db->query($sql);
	    }
	    
	    public function getListe($sidx, $sord, $start, $limit, $entite_id = null, $date1 = '', $date2 = ''){

			//echo $start.' '.$limit;

			if( $date1 == '' || $date2 == '' ){
				$wheredates = "";
			}else{
				$wheredates = " fiche_date_creation >= '$date1' AND fiche_date_creation <= '$date2'";
			}
	    	
	    	if($entite_id == null){

				if( $wheredates != '' ) $wheredates = ' WHERE '.$wheredates;
	    		$sql = "
				SELECT
					fiche_id, fiche_date_creation
				FROM fiche
				$wheredates
				ORDER BY ".$sidx." ".$sord."
				LIMIT ".$start.", ".$limit." ";
				$stmt = $this->_db->query($sql);
				$operations = $stmt->fetchAll();
				return $operations;
				
	    	}else{

				if( $wheredates != '' ) $wheredates = ' AND '.$wheredates;
	    		$sql = "
				SELECT
				  f.fiche_id,
				  f.fiche_date_creation
				FROM
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE f.fiche_id = cf.fiche_id
				AND cf.entite_id = $entite_id
				$wheredates
				ORDER BY ".$sidx." ".$sord."
				LIMIT ".$start.", ".$limit." ";
				
				$stmt = $this->_db->query($sql);
				$operations = $stmt->fetchAll();
				return $operations;
	    		
	    	}
	    	
	    }
	    
		public function getListeNonValidees($sidx, $sord, $start, $limit){
						
			$sql = "
				SELECT
					fiche_id, fiche_date_creation
				FROM fiche
				WHERE fiche_acces_candidats != 1
				ORDER BY ".$sidx." ".$sord."
				LIMIT ".$start.", ".$limit;
			$stmt = $this->_db->query($sql);
			$operations = $stmt->fetchAll();
			return $operations;
		}
		
		public function getListeNonValideesByEntite($sidx, $sord, $start, $limit, $entite_id){
						
			$sql = "
				SELECT
				  f.fiche_id,
				  f.fiche_date_creation
				FROM
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE fiche_acces_candidats != 1
				AND f.fiche_id = cf.fiche_id
				AND cf.entite_id = $entite_id
				ORDER BY ".$sidx." ".$sord."
				LIMIT ".$start.", ".$limit;
			$stmt = $this->_db->query($sql);
			$operations = $stmt->fetchAll();
			return $operations;
		}
		
		public function getEntites( $fiche_id ){
			$tab= array();
			$sql = "
				SELECT
				  cf.entite_id, cf.contact_id
				FROM
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE
				  cf.fiche_id = f.fiche_id
				  AND f.fiche_id = $fiche_id
			";
			$stmt = $this->_db->query($sql);
			$contacts = $stmt->fetchAll();
			
			$mEntite = new Model_Entite();
			foreach( $contacts as $contact ){
				if( $contact['entite_id'] != NULL ){
					if( $mEntite->checkType( $contact['entite_id'], 'entreprise' ) == 1 ){
						$tab['entreprise_id'] = $contact['entite_id'];
						$tab['contact_entreprise_id'] = $contact['contact_id'];
					}elseif( $mEntite->checkType( $contact['entite_id'], 'organisme référent' ) == 1 ){
						$tab['org_ref_id'] = $contact['entite_id'];
						$tab['contact_org_ref_id'] = $contact['contact_id'];
					}elseif( $mEntite->checkType( $contact['entite_id'], 'délégation' ) == 1 ){
						$tab['del_id'] = $contact['entite_id'];
						$tab['contact_del_id'] = $contact['contact_id'];
					}elseif( $mEntite->checkType( $contact['entite_id'], 'greta' ) == 1 ){
						$tab['greta_ref_id'] = $contact['entite_id'];
						$tab['contact_greta_ref_id'] = $contact['contact_id'];
					}
					
				}
			}
			
			return $tab;
		}

		public function getContactEntreprise( $fiche_id ){

			$sql = "
				SELECT
				  cf.*
				FROM
					`contacts_fiche` AS cf,
				  `entite` AS e,
				  `entite_type_entite` AS ete,
				  `type_entite` AS te
				WHERE
				  cf.entite_id = e.entite_id
				  AND e.entite_id = ete.entite_id
				  AND ete.type_entite_id = te.type_entite_id
				  AND te.type_entite_libelle = 'entreprise'
				  AND cf.fiche_id = $fiche_id
			";
			return $this->_db->query($sql)->fetch();
		}

		public function getContactOrgRef( $fiche_id ){

			$sql = "
				SELECT
				  cf.*
				FROM
					`contacts_fiche` AS cf,
					`entite` AS e,
					`entite_type_entite` AS ete,
					`type_entite` AS te
				WHERE
				  cf.entite_id = e.entite_id
				  AND e.entite_id = ete.entite_id
				  AND ete.type_entite_id = te.type_entite_id
				  AND te.type_entite_libelle = 'organisme référent'
				  AND cf.fiche_id = $fiche_id
			";
			return $this->_db->query($sql)->fetch();
		}

		public function getContactDelegation( $fiche_id ){

			$sql = "
				SELECT
				  cf.*
				FROM
					`contacts_fiche` AS cf,
				  `entite` AS e,
				  `entite_type_entite` AS ete,
				  `type_entite` AS te
				WHERE
				  cf.entite_id = e.entite_id
				  AND e.entite_id = ete.entite_id
				  AND ete.type_entite_id = te.type_entite_id
				  AND te.type_entite_libelle = 'délégation'
				  AND cf.fiche_id = $fiche_id
			";
			return $this->_db->query($sql)->fetch();
		}
		
		
		public function getContactGreta( $fiche_id ){
		
			$sql = "
			SELECT
			cf.*
			FROM
			`contacts_fiche` AS cf,
			`entite` AS e,
			`entite_type_entite` AS ete,
			`type_entite` AS te
			WHERE
			cf.entite_id = e.entite_id
			AND e.entite_id = ete.entite_id
			AND ete.type_entite_id = te.type_entite_id
			AND te.type_entite_libelle = 'greta'
			AND cf.fiche_id = $fiche_id
			";
			return $this->_db->query($sql)->fetch();
		}
		
		
		public function exist( $fiche_id = 0 ){
			
			if( $fiche_id > 0 ){
				$fiche = $this->get( $fiche_id );
				if( isset($fiche) ){
					return 1;
				}else{
					return 0;
				}
			}else{
				return 0;
			}
			
		}

		public function getListeByTitre( $demarche_id = 0, $bloc1 = 0, $bloc2 = 0, $sidx = 'cf.fiche_id', $sord = 'ASC', $start = 0, $limit = 0 ){

			$role = Zend_Auth::getInstance()->getIdentity()->role;
			$entite_id = Zend_Auth::getInstance()->getIdentity()->entite_id;

			$select = $this->_db->select();
			$select->from( 'contacts_fiche AS cf' );
			$select->joinLeft('fiche AS f', 'cf.fiche_id = f.fiche_id', null);
			$select->joinLeft('metier AS m', 'f.fiche_id = m.fiche_id', null);
			$select->where('m.demarche_id = ?', $demarche_id);
			if( $bloc1 > 0 ) $select->where ('m.bloc1_id = ?', $bloc1);
			if( $bloc2 > 0 ) $select->where ('m.bloc2_id = ?', $bloc2);
			if( $role != 'forthac' && $role != 'branche' ) $select->where( 'entite_id = ?', $entite_id );
			$select->group('cf.fiche_id');
			if( !is_null($sidx) && !is_null($sord) ) $select->order(" $sidx $sord ");
			$operations = $select->query()->fetchAll();

			if( $role == 'branche' ){
				$mEntites = new Model_Entite();
				foreach( $operations as $key => $operation ){
					$entite = $mEntites->get($operation['entite_id']);
					if( $entite['parent_id'] != $entite_id ) unset( $operations[$key] );
				}
			}
			
			$data = array();

			$j=0;
			foreach( $operations as $op ){

				if( ($j>=$start && $j<=$start+$limit) || $limit == 0  ){
					$query = "SELECT cf.fiche_id, e1.entite_id, e1.entite_nom, ete1.type_entite_id ";
					$query .= " FROM `contacts_fiche` cf ";
					$query .= " LEFT JOIN entite e1 ON e1.entite_id = cf.entite_id ";
					$query .= " LEFT JOIN entite_type_entite ete1 ON ete1.entite_id = e1.entite_id ";
					$query .= " LEFT JOIN type_entite te1 ON te1.type_entite_id = ete1.type_entite_id ";
					$query .= " WHERE cf.fiche_id = ".$op['fiche_id'] ;

					$results = $this->_db->query($query)->fetchAll();
					$tmp = array();
					$tmp['1'] =array();
					$tmp['2'] = array();
					$tmp['4'] = array();
					for($i = 0; $i <= count($results) - 1; $i++){
						foreach($tmp  as $key => $value){
							if($results[$i]['type_entite_id'] == $key){
								$tmp[$key]= array('fiche_id' => $results[$i]['fiche_id'],'entite_id' => $results[$i]['entite_id'], 'entite_nom' => $results[$i]['entite_nom'] );
							}
						}
					}
					array_push($data, $tmp);
				}
				$j++;

			}

			return $data;

		}

		public function getMetiers( $fiche_id = 0 ){

			if( $fiche_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`metier` AS m
					WHERE
						m.fiche_id = $fiche_id
				";
				return $metiers = $this->_db->query($sql)->fetchAll();
			}

		}

		public function getCandidats( $fiche_id = 0, $metier_id = 0, $sidx = 'personne_nom', $sord = 'ASC', $start = 0, $limit = 0 ){

			if( $fiche_id > 0 ){

				
				$where = "";
				if( $metier_id > 0 ){
					$where .= " AND m.metier_id = $metier_id ";
				}

				$lim = "";
				if( $start > 0 && $limit > 0 ){
					$lim = "LIMIT $start, $limit";
				}

				$sql = "
					SELECT
					  civ.civilite_abrege,
					  p.personne_nom,
					  p.personne_prenom,
					  p.personne_date_naissance,
					  c.candidat_id,
					  c.candidat_anciennete,
					  c.candidat_contrat,
					  cm.candidat_metier_id,
					  (
						SELECT
						  MAX(r.resultat_num_passage)
						FROM
						  `resultat` AS r
						WHERE
						  r.candidat_metier_id = cm.candidat_metier_id
					  ) AS nb_passage,
					  e.etat_libelle
					FROM
					  `metier` AS m,
					  `candidat_metier` AS cm,
					  `candidat` AS c,
					  `personne` AS p,
					  `civilite` AS civ,
					  `etat` AS e
					WHERE
					  cm.metier_id = m.metier_id
					  AND cm.candidat_id = c.candidat_id
					  AND c.personne_id = p.personne_id
					  AND civ.civilite_id = p.civilite_id
					  AND cm.etat_id = e.etat_id
					  AND m.fiche_id = $fiche_id
					  $where
					ORDER BY $sidx $sord
					$lim
				";
				return $candidats = $this->_db->query($sql)->fetchAll();
			}

		}

		public function getProjet( $fiche_id = 0 ){


			if( $fiche_id > 0 ){
				$sql = "
					SELECT
						fiche_projet
					FROM
						`fiche`
					WHERE
						fiche_id = $fiche_id
				";
				return $fiche_projet = $this->_db->query($sql)->fetchColumn(0);
			}

		}

		public function setProjet( $fiche_id = 0 ){

			if( $fiche_id > 0 ){

				$today = date( "Y-m-d" );

				if( $this->getProjet($fiche_id) == 0 ){
					$p = 1;
				}else{
					$p = 0;
				}

				$sql = "
					UPDATE `fiche`
					SET
						fiche_projet = $p,
						fiche_date_modif = '$today'
					WHERE fiche_id = $fiche_id
				";
				$this->_db->query($sql);
			}

		}

		public function getAccesCandidats( $fiche_id = 0 ){


			if( $fiche_id > 0 ){
				$sql = "
					SELECT
						fiche_acces_candidats
					FROM
						`fiche`
					WHERE
						fiche_id = $fiche_id
				";
				return $fiche_projet = $this->_db->query($sql)->fetchColumn(0);
			}

		}

		public function setAccesCandidats( $fiche_id = 0 ){

			if( $fiche_id > 0 ){

				$today = date( "Y-m-d" );

				if( $this->getAccesCandidats($fiche_id) == 0 ){
					$p = 1;
				}else{
					$p = 0;
				}

				$sql = "
					UPDATE `fiche`
					SET
						fiche_acces_candidats = $p,
						fiche_date_modif = '$today'
					WHERE fiche_id = $fiche_id
				";
				$results = $this->_db->query($sql);
			}

		}

		public function getObjectif( $fiche_id = 0 ){

			if( $fiche_id > 0 ){

				$sql = "
					SELECT
						o.*
					FROM
						`fiche` AS f,
						`objectif` AS o
					WHERE
						f.objectif_id = o.objectif_id
						AND f.fiche_id = $fiche_id
				";
				return $this->_db->query( $sql )->fetch();
			}

		}

		public function setObjectif( $fiche_id = 0, $objectif_id = 0 ){

			if( $fiche_id > 0 && $objectif_id > 0 ){

				$today = date( "Y-m-d" );

				$sql = "
					UPDATE `fiche`
					SET
						objectif_id = $objectif_id,
						fiche_date_modif = '$today'
					WHERE fiche_id = $fiche_id
				";
				$this->_db->query($sql);
			}

		}

		public function getRemarque( $fiche_id = 0 ){

			if( $fiche_id > 0 ){

				$fiche = $this->fetchRow( " fiche_id = $fiche_id " );
				return  $fiche['fiche_remarque'];
			}

		}

		public function setRemarque( $fiche_id = 0, $fiche_remarque = '' ){

			if( $fiche_id > 0 ){

				$today = date( "Y-m-d" );

				$sql = "
					UPDATE `fiche`
					SET
						fiche_remarque = '$fiche_remarque',
						fiche_date_modif = '$today'
					WHERE fiche_id = $fiche_id
				";
				$this->_db->query($sql);

			}

		}

		public function updateDateModif( $fiche_id = 0 ){

			if( $fiche_id > 0 ){

				$today = date( "Y-m-d" );

				$sql = "
					UPDATE `fiche`
					SET
						fiche_date_modif = '$today'
					WHERE fiche_id = $fiche_id
				";
				$this->_db->query($sql);

			}

		}

		public function updateDateMEO( $fiche_id = 0, $date = '' ){

			if( $fiche_id > 0 ){

				if( $date == '' ) $date = date( "Y-m-d" );

				$sql = "
					UPDATE `fiche`
					SET
						fiche_date_meo = '$date'
					WHERE fiche_id = $fiche_id
				";
				$this->_db->query($sql);

			}

		}
		
		public function getInfosForStats($candidatsM){
			
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_id'));
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array('f0.fiche_id AS fiche','f0.fiche_date_creation AS creation','f0.fiche_date_meo AS meo'));
	
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());
		
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			$query = $req->query();
//			echo $req->assemble();
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}

		public function getByBranche( $branche_id, $projet = NULL, $sidx = 'fiche_date_creation', $sord = 'DESC', $start = 0, $limit = 10000, $date1 = NULL, $date2 = NULL){
			$req = $this->select()->setIntegrityCheck(false);
			$req
				->from('entite AS e', null)
				->joinLeft('contacts_fiche AS cf', 'e.entite_id = cf.entite_id', null)
				->joinLeft('fiche AS f', 'f.fiche_id = cf.fiche_id')
				->where('e.parent_id = ?', $branche_id)
				->where('f.fiche_id > 0');

			if( !is_null($projet) ) $req->where("f.fiche_projet = $projet");

			if( !is_null($date1) && !is_null($date2) ) $req->where(" f.fiche_date_creation >= '$date1' AND f.fiche_date_creation <= '$date2' ");

			if( !is_null($sidx) && !is_null($sord) ) $req->order(" $sidx $sord ");
			$req->limit($limit, $start);
			
			$query = $req->query();
			return $query->fetchAll();
		}
	    
	}