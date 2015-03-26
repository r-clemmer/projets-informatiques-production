<?php

	class Model_Candidat extends Zend_Db_Table_Abstract{
		
		protected $_name = 'candidat';
		protected $_primary = 'candidat_id';
		protected $_dependentTables = array('Model_Candidat');
		protected $_referenceMap = array(
	        'personne'	=> array(
	            'columns'		=> 'personne_id',
	            'refTableClass' => 'Model_Personne',
				'refColumns'	=> 'personne_id'
	        )
	    );

		public function get( $candidat_id = 0 ){

			if( $candidat_id > 0 ){

				$sql = "
					SELECT
					  *
					FROM
					  `candidat` AS c,
					  `personne` AS p,
					  `civilite` AS civ,
					  `entite` AS e
					WHERE
					  c.personne_id = p.personne_id
					  AND p.civilite_id = civ.civilite_id
					  AND p.entite_id = e.entite_id
					  AND c.candidat_id = $candidat_id
				";
				return $candidat = $this->_db->query( $sql )->fetch();
			}

		}

		public function getByPersonneID( $personne_id = 0 ){

			if( $personne_id > 0 ){

				$sql = "
					SELECT
					  *
					FROM
					  `candidat` AS c,
					  `personne` AS p,
					  `civilite` AS civ,
					  `entite` AS e
					WHERE
					  c.personne_id = p.personne_id
					  AND p.civilite_id = civ.civilite_id
					  AND p.entite_id = e.entite_id
					  AND p.personne_id = $personne_id
				";
				return $candidat = $this->_db->query( $sql )->fetch();

			}

		}

		public function getCandidatMetiers( $candidat_id = 0 ){

			if( $candidat_id > 0 ){

				$sql = "
					SELECT
					  *,
					  (
						SELECT
						  r.raison_libelle
						FROM
						  raison AS r
						WHERE
						  r.raison_id = cm.raison_id
					  ) AS raison_libelle
					FROM
					  `candidat_metier` AS cm,
					  `etat` AS e
					WHERE
					  cm.etat_id = e.etat_id
					  AND cm.candidat_id = $candidat_id
				";
				return $candidat_metiers = $this->_db->query( $sql )->fetchAll();
			}

		}

		public function getListeRechercheNom( $string, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

			$strings = explode(' ', $string);
			$where = "";
			foreach( $strings as $string ){
				$where .= " AND (p.personne_prenom LIKE '$string%' ";
				$where .= " OR p.personne_nom LIKE '$string%') ";
			}

			$role = Zend_Auth::getInstance()->getIdentity()->role;
			$entite_id = Zend_Auth::getInstance()->getIdentity()->entite_id;

			if( $role == 'branche' ) $where .= ' AND e.parent_id = '.$entite_id;

			$sql = "
				SELECT p.*, c.*
				FROM
				  `personne` AS p,
				  `candidat` AS c,
				  `candidat_metier` AS cm,
				  `metier` AS m,
				  `entite` AS e
				WHERE (
				  c.personne_id = p.personne_id
				  AND cm.candidat_id = c.candidat_id
				  AND cm.metier_id = m.metier_id
				  AND p.entite_id = e.entite_id
				  $where
				)
			";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			$stmt = $this->_db->query( $sql );
			return $candidats = $stmt->fetchAll();

		}

		public function getFiches( $candidat_id = 0 ){

			if( $candidat_id > 0 ){

				$sql = "
					SELECT
						f.*
					FROM
						`candidat_metier` AS cm,
						`metier` AS m,
						`fiche` AS f
					WHERE
						cm.metier_id = m.metier_id
						AND m.fiche_id = f.fiche_id
						AND cm.candidat_id = $candidat_id
				";
				$stmt = $this->_db->query( $sql );
				return $fiches = $stmt->fetchAll();
			}

		}

		public function getMetiers( $candidat_id = 0 ){

			if( $candidat_id > 0 ){

				$sql = "
					SELECT
						m.*
					FROM
						`candidat_metier` AS cm,
						`metier` AS m
					WHERE
						cm.metier_id = m.metier_id
						AND cm.candidat_id = $candidat_id
				";
				$stmt = $this->_db->query($sql);
				return $metiers = $stmt->fetchAll();
			}

		}

		public function getInfoCandidat($candidat_id){

			$cand = $this->select()->setIntegrityCheck(false);
			$cand->from('candidat AS c0');
			$cand->joinLeft('personne AS p0', 'c0.personne_id = p0.personne_id');
			$cand->joinLeft('civilite AS c1', 'p0.civilite_id = c1.civilite_id');
			$cand->joinLeft('entite AS e0', 'p0.entite_id = e0.entite_id', 'entite_nom');
			$cand->where('c0.candidat_id = ?', $candidat_id);


			return $cand->query()->fetch(Zend_Db::FETCH_OBJ);
		}

		public function getListeCandidats( $string = '', $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ,$entite_id = 0){

			$where = "";
			if( isset( $string ) ){
				$strings = explode(' ', $string);
				foreach( $strings as $string ){
					$where .= " AND (p.personne_prenom LIKE '$string%' ";
					$where .= " OR e.entite_nom LIKE '$string%' ";
					$where .= " OR p.personne_nom LIKE '$string%') ";
				}
			}
			
			if($entite_id > 0)
			{
				$where .= " AND (e.entite_id = '$entite_id') ";
			}

			/*$sql = "
				SELECT p.*, c.*, e.*, cm.candidat_metier_id, m.metier_id, m.fiche_id
				FROM
				  `personne` AS p,
				  `candidat` AS c,
				  `entite` AS e,
					`candidat_metier` AS cm,
					`metier` AS m
				WHERE (
				  c.personne_id = p.personne_id
				  AND p.entite_id = e.entite_id
				  AND c.candidat_id = cm.candidat_id
				  AND cm.metier_id = m.metier_id
				  $where
				)
			";*/
			$sql = "
				SELECT p.*, c.*, e.*, m.metier_id, m.fiche_id
				FROM
				  `personne` AS p,
				  `candidat` AS c,
				  `entite` AS e,
					`candidat_metier` AS cm,
					`metier` AS m
				WHERE (
				  c.personne_id = p.personne_id
				  AND p.entite_id = e.entite_id
				  AND c.candidat_id = cm.candidat_id
				  AND cm.metier_id = m.metier_id
				  $where
				)
				GROUP BY c.candidat_id
			";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
		//	echo $sql;
			$stmt = $this->_db->query( $sql );
			return $candidats = $stmt->fetchAll();

		}
	    
		public function getInfosForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_formation_duree_estimee AS form_est','cm0.candidat_metier_formation_duree_realisee AS form_real','cm0.candidat_metier_formation_remarque AS form_rem'));
		
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array('c0.candidat_anciennete AS ancien','c0.candidat_contrat AS contrat'));
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array('p0.personne_poste AS poste','p0.personne_date_naissance AS naissance'));

			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array('m0.demarche_id AS demarche','m0.bloc1_id','m0.bloc2_id'));
			
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array('f0.fiche_date_creation AS position'));
			$req->joinLeft('objectif as o1','o1.objectif_id=f0.objectif_id',array('o1.objectif_libelle AS obj'));
			
			$req->joinLeft('binome AS b1','b1.binome_id=cm0.tuteur_id',array());
			$req->joinLeft('contact AS c3','c3.contact_id=b1.contact_id',array('c3.contact_date_formation AS formation'));
			$req->joinLeft('personne AS p2','p2.personne_id=c3.personne_id',array('p2.personne_nom AS tuteur_nom','p2.personne_prenom AS tuteur_prenom','p2.civilite_id AS tuteur_civilite'));
		
			//etapes positionnement
			$req->joinLeft('resultat AS r0','r0.candidat_metier_id=cm0.candidat_metier_id',array('MAX(r0.resultat_num_passage) AS num_passage','r0.resultat_commentaire_jury AS com'));

			$req->joinLeft('jury AS j0','j0.jury_id=r0.jury_id',array());
			
			$req->joinLeft('resultat_outil AS ro0','ro0.resultat_id=r0.resultat_id AND ro0.outil_id=1',array('ro0.resultat_date AS livret'));
			$req->joinLeft('resultat_outil AS ro1','ro1.resultat_id=r0.resultat_id AND ro1.outil_id=3',array('ro1.resultat_date AS obs'));
			$req->joinLeft('resultat_outil AS ro2','ro2.resultat_id=r0.resultat_id AND ro2.outil_id=2',array('ro2.resultat_date AS quest'));
			$req->joinLeft('resultat_outil AS ro3','ro3.resultat_id=r0.resultat_id AND ro3.outil_id=4',array('ro3.resultat_date AS entretien'));
		
			$req->joinLeft('resultat_outil AS ro4','ro4.resultat_id=r0.resultat_id AND ro4.outil_id=7',array('ro4.resultat_date AS questCcsp'));
			$req->joinLeft('resultat_outil AS ro5','ro5.resultat_id=r0.resultat_id AND ro5.outil_id=8',array('ro5.resultat_date AS obsCcsp'));
			$req->joinLeft('resultat_outil AS ro6','ro6.resultat_id=r0.resultat_id AND ro6.outil_id=9',array('ro6.resultat_date AS entretienCcsp'));
		
			$req->where('r0.resultat_num_passage= (SELECT MAX(resultat_num_passage) FROM resultat WHERE candidat_metier_id=cm0.candidat_metier_id)');
			
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			$query = $req->query();
			//Zend_Debug::dump($req->assemble());
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
	}