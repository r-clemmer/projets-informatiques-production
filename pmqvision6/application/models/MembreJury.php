<?php

	class Model_MembreJury extends Zend_Db_Table_Abstract{
		
		protected $_name = 'membre_jury';
		protected $_referenceMap = array(
	        'contact'	=> array(
	            'columns'		=> 'contact_id',
	            'refTableClass' => 'Model_Contact',
				'refColumns'	=> 'contact_id'
	        ),
	        'jury'	=> array(
	            'columns'		=> 'jury_id',
	            'refTableClass' => 'Model_Jury',
				'refColumns'	=> 'jury_id'
	        ),
	        'type_membre_jury'	=> array(
	            'columns'		=> 'type_membre_jury_id',
	            'refTableClass' => 'Model_TypeMembreJury',
				'refColumns'	=> 'type_membre_jury_id'
	        )
	    );

		public function add( $contact_id = 0, $jury_id = 0, $type_membre_id = 0 ){

			if( $contact_id > 0 && $jury_id > 0 && $type_membre_id > 0 ){

				$sql = "
					INSERT INTO `membre_jury`
					( contact_id, jury_id, type_membre_jury_id )
					VALUES( $contact_id, $jury_id, $type_membre_id );
				";
				$this->_db->query($sql);
			}

		}
	    
	}