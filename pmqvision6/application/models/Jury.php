<?php

	class Model_Jury extends Zend_Db_Table_Abstract{
		
		protected $_name = 'jury';
		protected $_primary = 'jury_id';
		protected $_dependentTables = array(
			'Model_MembreJury',
			'Model_Resultat'
		);

		public function get( $jury_id = 0 ){

			return $this->find($jury_id)->current();
		}
		
		public function countRecherche( $entite_id = null, $date1, $date2 ){

			$role = Zend_Auth::getInstance()->getIdentity()->role;

	    	if($entite_id != null){
				if( $role == 'branche' ){
					$where = " AND e.parent_id = $entite_id ";
				}else{
					$where = " AND cf.entite_id = $entite_id ";
				}
	    	}else{
	    		$where = "";
	    	}
	    	
	    	$sql = "
		    	SELECT
		    	  count( * ) AS count
				FROM
				  jury AS j,
				  resultat AS r,
				  candidat_metier AS cm,
				  metier AS m,
				  candidat AS c,
				  personne AS p,
				  entite AS e,
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE
				  j.jury_date >= '$date1'
				  AND j.jury_date <= '$date2'
				  $where
				  AND j.jury_id = r.jury_id
				  AND r.candidat_metier_id = cm.candidat_metier_id
				  AND cm.metier_id = m.metier_id
				  AND cm.candidat_id = c.candidat_id
				  AND c.personne_id = p.personne_id
				  AND p.entite_id = e.entite_id
				  AND m.fiche_id = f.fiche_id
				  AND f.fiche_id = cf.fiche_id
				GROUP BY j.jury_id
	    	";
	    	$stmt = $this->_db->query($sql);
	    	$fiches = $stmt->fetchAll();
	    	$count = $fiches[0]['count'];
	    	return $count;
	    }
		
		public function getListeRecherche( $sidx, $sord, $start, $limit, $entite_id = null, $date1, $date2 ){

			$role = Zend_Auth::getInstance()->getIdentity()->role;

			if($entite_id != null){
				if( $role == 'branche' ){
					$where = " AND e.parent_id = $entite_id ";
				}else{
					$where = " AND cf.entite_id = $entite_id ";
				}
	    	}else{
	    		$where = "";
	    	}
	    	
	    	$sql = "
		    	SELECT
				  j.jury_id,
				  j.jury_date,
				  j.jury_ville,
				  m.fiche_id,
				  p.civilite_id,
				  p.personne_prenom,
				  p.personne_nom,
				  e.entite_id,
				  e.entite_nom
				FROM
				  jury AS j,
				  resultat AS r,
				  candidat_metier AS cm,
				  metier AS m,
				  candidat AS c,
				  personne AS p,
				  entite AS e,
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE
				  j.jury_date >= '$date1'
				  AND j.jury_date <= '$date2'
				  $where
				  AND j.jury_id = r.jury_id
				  AND r.candidat_metier_id = cm.candidat_metier_id
				  AND cm.metier_id = m.metier_id
				  AND cm.candidat_id = c.candidat_id
				  AND c.personne_id = p.personne_id
				  AND p.entite_id = e.entite_id
				  AND m.fiche_id = f.fiche_id
				  AND f.fiche_id = cf.fiche_id
				GROUP BY j.jury_id
				ORDER BY $sidx $sord
				LIMIT $start, $limit;
	    	";
	    	$stmt = $this->_db->query($sql);
	    	$resultats = $stmt->fetchAll();
	    	return $resultats;
			
		}
		
		public function getListe( $sidx, $sord, $start, $limit ){
			
			$sql = "
				SELECT
					*
				FROM
					`jury`
				ORDER BY $sidx $sord
				LIMIT $start, $limit;
			";
			$stmt = $this->_db->query( $sql );
			$jurys = $stmt->fetchAll();
			return $jurys;
		}

		public function getMembres( $jury_id = 0, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

			$sql = "
				SELECT
				  *
				FROM
				  `contact` AS c,
				  `civilite` AS civ,
				  `personne` AS p,
				  `membre_jury` AS mj,
				  `type_membre_jury` AS tmj
				WHERE
				  c.personne_id = p.personne_id
				  AND c.contact_id = mj.contact_id
				  AND tmj.type_membre_jury_id = mj.type_membre_jury_id
				  AND p.civilite_id = civ.civilite_id
				  AND mj.jury_id = $jury_id
			";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			$stmt = $this->_db->query( $sql );
			return $membres = $stmt->fetchAll();

		}

		public function getCandidats( $jury_id = 0, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

			$sql = "
				SELECT
				  p.personne_nom, p.personne_prenom, civ.*, c.candidat_id, e.entite_id, e.entite_nom, m.metier_id, m.fiche_id, r.resultat_id,cm.candidat_metier_id,cm.etat_id,					  (
						SELECT
						  MAX(r.resultat_num_passage)
						FROM
						  `resultat` AS r
						WHERE
						  r.candidat_metier_id = cm.candidat_metier_id
					  ) AS nb_passage
				FROM
				  `resultat` AS r,
				  `candidat_metier` AS cm,
				  `candidat` AS c,
				  `personne` AS p,
				  `metier` AS m,
				  `entite` AS e,
				  `civilite` AS civ
				WHERE
				  r.candidat_metier_id = cm.candidat_metier_id
				  AND cm.candidat_id = c.candidat_id
				  AND c.personne_id = p.personne_id
				  AND cm.metier_id = m.metier_id
				  AND p.entite_id = e.entite_id
				  AND p.civilite_id = civ.civilite_id
				  AND r.jury_id = $jury_id
			";
//			$sql .= " AND r.resultat_num_passage = ( SELECT MAX( resultat_num_passage ) FROM resultat WHERE r.candidat_metier_id = candidat_metier_id ) ";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			$stmt = $this->_db->query( $sql );
			return $candidats = $stmt->fetchAll();

		}
		public function getCandidatsEntite( $jury_id = 0, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0,$entite_id=0 ){

			$sql = "
				SELECT
				  p.personne_nom, p.personne_prenom, civ.*, c.candidat_id, e.entite_id, e.entite_nom, m.metier_id, m.fiche_id, r.resultat_id,cm.candidat_metier_id,cm.etat_id,					  (
						SELECT
						  MAX(r.resultat_num_passage)
						FROM
						  `resultat` AS r
						WHERE
						  r.candidat_metier_id = cm.candidat_metier_id
					  ) AS nb_passage
				FROM
				  `resultat` AS r,
				  `candidat_metier` AS cm,
				  `candidat` AS c,
				  `personne` AS p,
				  `metier` AS m,
				  `entite` AS e,
				  `civilite` AS civ
				  
				  
				WHERE
				  r.candidat_metier_id = cm.candidat_metier_id
				  AND cm.candidat_id = c.candidat_id
				  AND c.personne_id = p.personne_id
				  AND cm.metier_id = m.metier_id
				  AND p.entite_id = e.entite_id
				  AND p.civilite_id = civ.civilite_id
				  AND r.jury_id = $jury_id
			";
			
			if($entite_id >0)
			{
				 $sql .=" AND cf.entite_id =".$entite_id;
			}
			
			
//			$sql .= " AND r.resultat_num_passage = ( SELECT MAX( resultat_num_passage ) FROM resultat WHERE r.candidat_metier_id = candidat_metier_id ) ";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			$stmt = $this->_db->query( $sql );
			return $candidats = $stmt->fetchAll();

		}
		
		public function getMembreByType( $jury_id = 0, $type_libelle = '' ){

			if( $jury_id > 0 && $type_libelle != '' ){
				$sql = "
					SELECT
						*
					FROM
						`type_membre_jury` AS tmj,
						`membre_jury` AS mj
					WHERE
						tmj.type_membre_jury_id = mj.type_membre_jury_id
						AND mj.jury_id = $jury_id
						AND tmj.type_membre_jury_libelle = \"$type_libelle\"
				";
				return $membre = $this->_db->query($sql)->fetch();
			}
		}

		public function getCandidatsSansJury( $branche_id = 0, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

			if( $branche_id > 0 ){
				$branche = " OR e.parent_id = $branche_id ";
			}else{
				$branche = "";
			}

			$sql = "
				SELECT
				  r.resultat_id, r.resultat_num_passage,
				  p.personne_nom, p.personne_prenom,
					civ.*,
					c.candidat_id,
					cm.candidat_metier_id,
					e.entite_id, e.entite_nom,
				  m.metier_id, m.fiche_id
				FROM
				  `resultat` AS r,
				  `candidat_metier` AS cm,
					`candidat` AS c,
					`personne` AS p,
					`metier` AS m,
				  `entite` AS e,
				  `civilite` AS civ,
				  `etat` AS et
				WHERE
					r.candidat_metier_id = cm.candidat_metier_id
					AND cm.candidat_id = c.candidat_id
					AND c.personne_id = p.personne_id
					AND cm.metier_id = m.metier_id
					AND p.entite_id = e.entite_id
					AND p.civilite_id = civ.civilite_id
					AND cm.etat_id = et.etat_id
					AND r.jury_id IS NULL
					AND r.resultat_num_passage = ( SELECT MAX( resultat_num_passage ) FROM resultat WHERE r.candidat_metier_id = candidat_metier_id )
					AND (
						parent_id = 0
						OR parent_id IS NULL
						$branche
					)
					AND et.etat_libelle = 'admissible'
				GROUP BY r.candidat_metier_id
			";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			return $candidats = $this->_db->query( $sql )->fetchAll();
		}
		
	public function getInfosForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_formation_duree_estimee AS form_est','cm0.candidat_metier_formation_duree_realisee AS form_real','cm0.candidat_metier_formation_remarque AS form_rem'));
		
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());

			$req->joinLeft('resultat AS r0','r0.candidat_metier_id=cm0.candidat_metier_id',array('r0.resultat_commentaire_jury AS com'));
			$req->where('r0.resultat_num_passage= (SELECT MAX(resultat_num_passage) FROM resultat WHERE candidat_metier_id=cm0.candidat_metier_id)');
			$req->joinLeft('jury AS j0','j0.jury_id=r0.jury_id',array('j0.jury_date'));
			
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			$query = $req->query();
			//Zend_Debug::dump($req->assemble());
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
	    
	}