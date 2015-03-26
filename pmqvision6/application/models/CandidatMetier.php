<?php

	class Model_CandidatMetier extends Zend_Db_Table_Abstract{
		
		protected $_name = 'candidat_metier';
		protected $_primary = 'candidat_metier_id';
		protected $_dependentTables = array('Model_Resultat');
		protected $_referenceMap = array(
	        'candidat'	=> array(
	            'columns'		=> 'candidat_id',
	            'refTableClass' => 'Model_Candidat',
				'refColumns'	=> 'candidat_id'
	        ),
	        'metier'	=> array(
	            'columns'		=> 'metier_id',
	            'refTableClass' => 'Model_Metier',
				'refColumns'	=> 'metier_id'
	        ),
	        'binome'	=> array(
	            'columns'		=> 'tuteur_id',
	            'refTableClass' => 'Model_Binome',
				'refColumns'	=> 'binome_id'
	        ),
	        'binome'	=> array(
	            'columns'		=> 'expert_id',
	            'refTableClass' => 'Model_Binome',
				'refColumns'	=> 'binome_id'
	        ),
	        'etat'	=> array(
	            'columns'		=> 'etat_id',
	            'refTableClass' => 'Model_Etat',
				'refColumns'	=> 'etat_id'
	        ),
	        'raison'	=> array(
	            'columns'		=> 'raison_id',
	            'refTableClass' => 'Model_Raison',
				'refColumns'	=> 'raison_id'
	        )
	    );

		public function get( $candidat_metier_id ){

			return $candidat_metier = $this->find( $candidat_metier_id )->current();
		}

		public function add( $candidat_id = 0, $metier_id = 0 ){

			if( $candidat_id > 0 && $metier_id > 0 ){

				$data = array(
					'candidat_id'	=>	$candidat_id,
					'metier_id'		=>	$metier_id,
					'etat_id'		=>	1
				);
				$this->insert($data);

			}

		}

		public function getMetier( $candidat_metier_id = 0 ){

			if( $candidat_metier_id > 0 ){

				$sql = "
					SELECT
						m.*,
						cm.candidat_id
					FROM
						`metier` AS m,
						`candidat_metier` AS cm
					WHERE
						cm.metier_id = m.metier_id
						AND cm.candidat_metier_id = '$candidat_metier_id'
				";
				$stmt = $this->_db->query( $sql );
				return $metier = $stmt->fetch();
			}

		}
		
		public function getOtherMetier($candidat_id, $metier_id){
			$reqMetier = $this->select()->setIntegrityCheck(false);
			$reqMetier->from('candidat_metier AS cm');
			$reqMetier->where('cm.candidat_id = ?', $candidat_id);
			$reqMetier->where('cm.metier_id <> ?', $metier_id);
			
			return $reqMetier->query()->fetchAll();
		}
		
		public function getFiche( $candidat_metier_id = 0 ){

			if( $candidat_metier_id > 0 ){

				$sql = "
					SELECT
						m.*
					FROM
						`metier` AS m,
						`candidat_metier` AS cm,
						`fiche` AS f
					WHERE
						cm.metier_id = m.metier_id
						AND m.fiche_id = f.fiche_id
						AND cm.candidat_metier_id = $candidat_metier_id
				";
				$stmt = $this->_db->query( $sql );
				return $fiche = $stmt->fetch();
			}

		}

		public function getExpert( $candidat_metier_id = 0 ){

			if( $candidat_metier_id > 0 ){

				$sql = "
					SELECT
						b.*
					FROM
						`candidat_metier` AS cm,
						`binome` AS b
					WHERE
						cm.expert_id = b.binome_id
						AND cm.candidat_metier_id = $candidat_metier_id
				";
				$binome = $this->_db->query( $sql )->fetch();

				if( $binome ){
					$mContacts = new Model_Contact();
					return $expert = $mContacts->getPersonne($binome['contact_id']);
				}

			}

		}

		public function getEvaluateur( $candidat_metier_id = 0 ){

			if( $candidat_metier_id > 0 ){

				$sql = "
					SELECT
						b.*
					FROM
						`candidat_metier` AS cm,
						`binome` AS b
					WHERE
						cm.tuteur_id = b.binome_id
						AND cm.candidat_metier_id = $candidat_metier_id
				";
				$binome = $this->_db->query( $sql )->fetch();

				if( $binome ){
					$mContacts = new Model_Contact();
					return $evaluateur = $mContacts->getPersonne($binome['contact_id']);
				}

			}

		}
		
		public function getOrganisme( $candidat_metier_id = 0 ){

			if( $candidat_metier_id > 0 ){

				$sql = "
					SELECT
						b.*
					FROM
						`candidat_metier` AS cm,
						`orga_spe` AS b
					WHERE
						cm.metier_id  = b.metier_id
						AND cm.candidat_metier_id = $candidat_metier_id
				";
				$binome = $this->_db->query( $sql )->fetch();

				if( $binome ){
					$mContacts = new Model_Contact();
					return $evaluateur = $mContacts->getPersonne($binome['contact_id']);
				}

			}

		}

		public function getEtat( $candidat_metier_id = 0 ){

			if( $candidat_metier_id > 0 ){

				$candidat_metier = $this->find( $candidat_metier_id )->current();

				$mEtats = new Model_Etat();
				return $etat = $mEtats->get( $candidat_metier['etat_id'] );

			}

		}

		public function getDemarche( $candidat_metier_id ){

			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();

			$candidat_metier = $mCandidatMetiers->fetchRow( " candidat_metier_id = $candidat_metier_id " );
			return $demarche = $mMetiers->getDemarche($candidat_metier['metier_id']);

		}

		public function updateEtat( $candidat_metier_id = 0, $etat_id = 0 ){

			if( $candidat_metier_id > 0 && $etat_id > 0 ){

				$data = array(
					'etat_id'	=>	$etat_id
				);
				$where = " candidat_metier_id = $candidat_metier_id ";
				$this->update($data, $where);

			}

		}
		
		public function getInfosForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_formation_duree_estimee AS form_est','cm0.candidat_metier_formation_duree_realisee AS form_real','cm0.candidat_metier_formation_remarque AS form_rem'));
		
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());

			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			$query = $req->query();
			//Zend_Debug::dump($req->assemble());
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
	    
	}