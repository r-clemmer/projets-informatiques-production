<?php
class Model_Binome extends Zend_Db_Table_Abstract{
	
	protected $_name = 'binome';
	protected $_primary = 'binome_id';
	protected $_dependentTables = array('Model_CandidatMetier');
	protected $_referenceMap = array(
        'metier'	=> array(
            'columns'		=> 'metier_id',
            'refTableClass' => 'Model_Metier',
			'refColumns'	=> 'metier_id'
        ),
        'contact'	=> array(
            'columns'		=> 'contact_id',
            'refTableClass' => 'Model_Contact',
			'refColumns'	=> 'contact_id'
        )
    );

	public function get( $binome_id = 0, $metier_id = 0, $contact_id = 0 ){

		if( $binome_id > 0 ) $where = " WHERE binome_id = $binome_id ";
		elseif( $metier_id > 0 && $contact_id > 0 ) $where = " WHERE metier_id = $metier_id AND contact_id = $contact_id ";

		$sql = "
			SELECT
				*
			FROM
				binome
			$where
		";
		return $binome = $this->_db->query( $sql )->fetch();
	}

	public function add( $metier_id = 0, $contact_id = 0, $default = false ,$type=''){
		if( $metier_id > 0 && $contact_id > 0 ){
			$data = array(
				'metier_id'		=> $metier_id,
				'contact_id'	=>	$contact_id,
				'binome_defaut'	=>	$default,
				'type'	=>	$type
			
			);

			return $this->insert($data);
		}
	}

	public function setDefault( $metier_id = 0, $contact_id = 0, $default = 0 ){

		if( $metier_id > 0 && $contact_id > 0 ){

			$this->update( array( 'binome_defaut' => $default ), " metier_id = $metier_id AND contact_id = $contact_id ");

		}

	}
    
}