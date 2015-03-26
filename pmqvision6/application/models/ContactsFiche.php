<?php

	class Model_ContactsFiche extends Zend_Db_Table_Abstract{
		
		protected $_name = 'contacts_fiche';
		protected $_referenceMap = array(
	        'fiche'	=> array(
	            'columns'		=> 'fiche_id',
	            'refTableClass' => 'Model_Fiche',
				'refColumns'	=> 'fiche_id'
	        ),
	        'entite'	=> array(
	            'columns'		=> 'entite_id',
	            'refTableClass' => 'Model_Entite',
				'refColumns'	=> 'entite_id'
	        ),
	        'contact'	=> array(
	            'columns'		=> 'contact_id',
	            'refTableClass' => 'Model_Contact',
				'refColumns'	=> 'contact_id'
	        )
	    );

		public function get( $fiche_id = 0, $entite_id = 0, $contact_id = 0 ){

			$i = 0;
			if( $fiche_id > 0 ){
				$where = " WHERE fiche_id = $fiche_id ";
				$i++;
			}
			if( $entite_id > 0 ){
				if( $i>0 ) $where .= " AND entite_id = $entite_id ";
				else $where = " WHERE entite_id = $entite_id ";
				$i++;
			}
			if( $contact_id > 0 ){
				if( $i>0 ) $where .= " AND contact_id = $contact_id ";
				else $where = " WHERE contact_id = $contact_id ";
				$i++;
			}

			$sql = "
				SELECT
					*
				FROM
					contacts_fiche
				$where
			";
			return $this->_db->query($sql)->fetchAll();

		}
	    
	    public function deleteEntite($entite_id){
	    	
	    	$sql = "
	    		UPDATE contacts_fiche
	    		SET entite_id = null
	    		WHERE entite_id = $entite_id
	    	";
	    	$this->_db->query($sql);
	    	
	    }
	    
	}