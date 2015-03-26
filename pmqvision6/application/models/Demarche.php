<?php

	class Model_Demarche extends Zend_Db_Table_Abstract{
		
		protected $_name = 'demarche';
		protected $_primary = 'demarche_id';
		protected $_dependentTables = array(
			'Model_Metier',
			'Model_Expertise',
			'Model_Outil'
		);

		public function get( $demarche_id = 0 ){

			if( $demarche_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						demarche
					WHERE
						demarche_id = $demarche_id
				";
				return $demarche = $this->_db->query( $sql )->fetch();
			}

		}

		public function getAll(){

			$sql = "
					SELECT
						*
					FROM
						demarche
				";
			
				return $demarches = $this->_db->query( $sql )->fetchAll();

		}
	    
	}