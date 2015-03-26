<?php

	class Model_Contact extends Zend_Db_Table_Abstract{
		
		protected $_name = 'contact';
		protected $_primary = 'contact_id';
		protected $_dependentTables = array(
			'Model_Binome',
			'Model_Expertise',
			'Model_ContactsFiche',
			'Model_FonctionContact',
			'Model_MembreJury'
		);
		protected $_referenceMap = array(
	        'personne'	=> array(
	            'columns'		=> 'personne_id',
	            'refTableClass' => 'Model_Personne',
				'refColumns'	=> 'personne_id'
	        )
	    );
	    
	  	public function get($contact_id){
	  		
	  		return $this->find($contact_id)->current();
	  	}
	  	
	  	public function getPersonne( $contact_id = 0 ){

			if( $contact_id > 0 ){
				$sql = "
					SELECT
						*
					FROM
						personne AS p,
						contact AS c
					WHERE
						c.personne_id = p.personne_id
						AND c.contact_id = $contact_id
				";
				$stmt = $this->_db->query($sql);
				$personne = $stmt->fetch();
				return $personne;
			}else return false;
	  	}
	  	
	  	public function getByPersonne($personne_id){
	  		
	  		$sql = "
	  			SELECT *
	  			FROM contact
	  			WHERE personne_id = $personne_id
	  		";
	  		$stmt = $this->_db->query($sql);
	  		$contact = $stmt->fetchObject();
	  		return $contact;
	  	}
	  	
	  	public function getFonctions($contact_id){
	  		
	  		$sql = "
	  			SELECT f.*
	  			FROM fonction AS f, fonction_contact AS fc
	  			WHERE fc.fonction_id = f.fonction_id
	  			AND fc.contact_id = $contact_id
	  		";
	  		$stmt = $this->_db->query($sql);
	  		$fonctions = $stmt->fetchAll();
	  		return $fonctions;
	  	}
	  	
	  	public function deleteFonctions($contact_id){
	  		
	  		$sql = "
	  			DELETE FROM fonction_contact
	  			WHERE contact_id = $contact_id
	  		";
	  		$this->_db->query($sql);
	  	}

		public function getListeRechercheNom( $string, $type, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

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
				SELECT
				  p.*, c.*
				FROM
				  `personne` AS p,
				  `contact` AS c,
				  `fonction_contact` AS fc,
				  `entite` AS e
				WHERE (
				  c.personne_id = p.personne_id
				  AND fc.contact_id = c.contact_id
				  AND p.entite_id = e.entite_id
				  $where
				  AND fc.fonction_id = $type
				)
			";
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			$stmt = $this->_db->query( $sql );
			return $contacts = $stmt->fetchAll();

		}

		public function getFicheIds( $contact_id = 0 ){

			if( $contact_id > 0 ){
				$sql = "
					SELECT
						fiche_id
					FROM
						`contacts_fiche`
					WHERE
						contact_id = $contact_id
				";
				$stmt = $this->_db->query( $sql );
				return $fiche_ids = $stmt->fetchColumn( 0 );
			}
		}

		public function getFiches( $contact_id = 0 ){

			if( $contact_id > 0 ){

				$sql = "
					SELECT
						f.*
					FROM
						`contacts_fiche` AS cf,
						`fiche` AS f
					WHERE
						cf.fiche_id = f.fiche_id
						AND cf.contact_id = $contact_id
				";
				$stmt = $this->_db->query( $sql );
				return $fiches = $stmt->fetchAll();
			}

		}

		public function exist( $contact_id = 0 ){

			if( $contact_id > 0 ){
				$contact = $this->get($contact_id);
				if( $contact ){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}

		}

		public function existByString( $contact_nom = '' ){

			if( $contact_nom != '' ){

				$sql = "
					SELECT
						*
					FROM
						`personne` AS p,
						`contact` AS c
					WHERE
						c.personne_id = p.personne_id
						AND p.personne_nom = '$contact_nom'
				";
				$contact = $this->_db->query( $sql )->fetch();

				if( $contact ){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}

		}

		public function getByNomStrict( $nom = '' ){

			if( $nom != '' ){

				$sql = "
					SELECT
						*
					FROM
						`personne` AS p,
						`contact` AS c
					WHERE
						c.personne_id = p.personne_id
						AND p.personne_nom = '$nom'
				";
				return $this->_db->query( $sql )->fetch();
			}

		}

		public function getByTypeEntite( $type_entite_libelle ){

			if( is_array($type_entite_libelle) ){

				$where = ' AND (';
				$i = 0;
				foreach( $type_entite_libelle as $type ){
					if( $i > 0 ) $where .= ' OR ';
					$where .= " te.type_entite_libelle = '$type' ";
					$i++;
				}
				$where .= ' ) ';

			}elseif( is_string($type_entite_libelle) ){

				$where = " AND te.type_entite_libelle = '$type_entite_libelle' ";
				
			}

			$sql = "
				SELECT
				  c.*, p.*, e.*
				FROM
				  `contact` AS c,
				  `personne` AS p,
				  `entite` AS e,
				  `entite_type_entite` AS ete,
				  `type_entite` AS te
				WHERE
				  c.personne_id = p.personne_id
				  AND p.entite_id = e.entite_id
				  AND ete.entite_id = e.entite_id
				  AND ete.type_entite_id = te.type_entite_id
				  $where
				ORDER BY
					p.personne_nom
			";
			return $contacts = $this->_db->query( $sql )->fetchAll();

		}

		public function getExpertises( $contact_id = 0 ){

			if( $contact_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`expertise`
					WHERE
						contact_id = $contact_id
				";
				return $expertises = $this->_db->query( $sql )->fetchAll();

			}

		}

		public function getFormateurs( $entite_id = 0 ){

			if( $entite_id > 0 ){
				$where = " AND p.entite_id = $entite_id ";
			}else{
				$where = "";
			}

			$sql = "
				SELECT
				  *
				FROM
				  `contact` AS c,
				  `personne` AS p,
				  `fonction_contact` AS fc,
				  `fonction` AS f
				WHERE
				  p.personne_id = c.personne_id
				  AND c.contact_id = fc.contact_id
				  AND f.fonction_id = fc.fonction_id
				 $where
				  AND f.fonction_libelle = 'formateur'
			";
			return $formateurs = $this->_db->query( $sql )->fetchAll();
		}
		

	    
	}