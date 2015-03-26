<?php

	class Model_Etat extends Zend_Db_Table_Abstract{
		
		protected $_name = 'etat';
		protected $_primary = 'etat_id';
		protected $_dependentTables = array('Model_CandidatMetier');

		public function get( $etat_id = 0 ){

			if( $etat_id > 0 ){
				return $etat = $this->find( $etat_id )->current();
			}

		}

		public function getByLibelle( $etat_libelle = "" ){

			if( $etat_libelle != "" ){
				$where = " WHERE e.etat_libelle = '$etat_libelle' ";
			}else{
				$where = "";
			}
			$sql = "
				SELECT
					*
				FROM
					`etat` AS e
				$where
			";
			return $etats = $this->_db->query( $sql )->fetchAll();
		}
	    
	}