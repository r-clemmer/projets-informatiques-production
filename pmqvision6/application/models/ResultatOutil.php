<?php

	class Model_ResultatOutil extends Zend_Db_Table_Abstract{
		
		protected $_name = 'resultat_outil';
		protected $_primary = 'resultat_outil_id';
		protected $_referenceMap = array(
	        'outil'	=> array(
	            'columns'		=> 'outil_id',
	            'refTableClass' => 'Model_Outil',
				'refColumns'	=> 'outil_id'
	        ),
	        'resultat'	=> array(
	            'columns'		=> 'resultat_id',
	            'refTableClass' => 'Model_Resultat',
				'refColumns'	=> 'resultat_id'
	        )
	    );

		public function get( $resultat_outil_id ){
			return $this->find($resultat_outil_id)->current();
		}

		public function add( $outil_id, $resultat_id, $resultat_valeur, $resultat_date ){

		
			
			
			$data = array(
				'outil_id'			=>	$outil_id,
				'resultat_id'		=>	$resultat_id,
				'resultat_valeur'	=>	$resultat_valeur,
				'resultat_date'		=>	$resultat_date
			);
			return $this->insert($data);
		}

		public function modif( $resultat_outil_id, $resultat_valeur, $resultat_date ){

			$data = array(
				'resultat_valeur'	=>	$resultat_valeur,
				'resultat_date'		=>	$resultat_date
			);
			$where = " resultat_outil_id = $resultat_outil_id ";
			$this->update($data, $where);
			
			
			
			
			
		}
	    

		public function suppr( $resultat_id ){
			$where = " resultat_id = $resultat_id ";
			
			$this->delete( $where);
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
				  resultat_outil AS ro,
				  outil AS o,
				  resultat AS r,
				  candidat_metier AS cm,
				  candidat AS c,
				  personne AS p,
				  metier AS m,
				  entite AS e,
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE resultat_date >= '$date1'
				AND resultat_date <= '$date2'
				$where
				AND ro.outil_id = o.outil_id
				AND ro.resultat_id = r.resultat_id
				AND cm.candidat_metier_id = r.candidat_metier_id
				AND cm.candidat_id = c.candidat_id
				AND c.personne_id = p.personne_id
				AND m.metier_id = cm.metier_id
				AND p.entite_id = e.entite_id
				AND cf.fiche_id = f.fiche_id
				AND m.fiche_id = f.fiche_id
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
		    	  ro.resultat_outil_id,
				  ro.resultat_date,
				  o.outil_id,
				  o.outil_libelle,
				  c.candidat_id,
				  p.civilite_id,
				  p.personne_nom,
				  p.personne_prenom,
				  m.fiche_id,
				  e.entite_id,
				  e.entite_nom
				FROM
				  resultat_outil AS ro,
				  outil AS o,
				  resultat AS r,
				  candidat_metier AS cm,
				  candidat AS c,
				  personne AS p,
				  metier AS m,
				  entite AS e,
				  fiche AS f,
				  contacts_fiche AS cf
				WHERE resultat_date >= '$date1'
				AND resultat_date <= '$date2'
				$where
				AND ro.outil_id = o.outil_id
				AND ro.resultat_id = r.resultat_id
				AND cm.candidat_metier_id = r.candidat_metier_id
				AND cm.candidat_id = c.candidat_id
				AND c.personne_id = p.personne_id
				AND m.metier_id = cm.metier_id
				AND p.entite_id = e.entite_id
				AND cf.fiche_id = f.fiche_id
				AND m.fiche_id = f.fiche_id
				ORDER BY $sidx $sord
				LIMIT $start, $limit;
	    	";
	    	$stmt = $this->_db->query($sql);
	    	$resultats = $stmt->fetchAll();
	    	return $resultats;
	    }
	    
	}