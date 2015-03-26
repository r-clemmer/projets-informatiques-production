<?php

	class Model_TypeMembreJury extends Zend_Db_Table_Abstract{
		
		protected $_name = 'type_membre_jury';
		protected $_primary = 'type_membre_jury_id';
		protected $_dependentTables = array('Model_MembreJury');

		public function get( $type_membre_jury_id = 0 ){

			return $this->find($type_membre_jury_id)->current();
		}

		public function getAll(){

			return $types = $this->fetchAll();

		}

		public function getByLibelle( $libelle = "" ){

			if( $libelle != '' ){
				$sql = "
					SELECT
						*
					FROM
						`type_membre_jury`
					WHERE
						type_membre_jury_libelle = \"$libelle\"
				";
				return $type = $this->_db->query($sql)->fetch();
			}
		}
		
	}