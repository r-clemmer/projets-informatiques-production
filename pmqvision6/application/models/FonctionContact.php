<?php

	class Model_FonctionContact extends Zend_Db_Table_Abstract{
		
		protected $_name = 'fonction_contact';
		protected $_referenceMap = array(
	        'fonction'	=> array(
	            'columns'		=> 'fonction_id',
	            'refTableClass' => 'Model_Fonction',
				'refColumns'	=> 'fonction_id'
	        ),
	        'contact'	=> array(
	            'columns'		=> 'contact_id',
	            'refTableClass' => 'Model_Contact',
				'refColumns'	=> 'contact_id'
	        )
	    );
	    
	    public function set($contact_id = 0, $fonction_id = 0){

			if( $contact_id > 0 && $fonction_id > 0 ){

				$sql = "
					INSERT INTO fonction_contact (contact_id, fonction_id)
					VALUES($contact_id, $fonction_id);
				";

				$this->_db->query($sql);
			}
	    	
	    }
	    
	}