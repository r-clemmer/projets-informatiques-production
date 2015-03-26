<?php
class Model_OrgaSpe extends Zend_Db_Table_Abstract{
	
	protected $_name = 'orga_spe';
	protected $_primary = 'orga_spe_id';
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

	public function get( $orga_spe_id = 0, $metier_id = 0, $contact_id = 0 ){

		if( $binome_id > 0 ) $where = " WHERE orga_spe_id = $orga_spe_id ";
		elseif( $metier_id > 0 && $contact_id > 0 ) $where = " WHERE metier_id = $metier_id AND contact_id = $contact_id ";

		$sql = "
			SELECT
				*
			FROM
				orga_spe
			$where
		";
		return $orga_spe = $this->_db->query( $sql )->fetch();
	}

	public function add( $metier_id = 0, $contact_id = 0, $default = false ){

		if( $metier_id > 0 && $contact_id > 0 ){

			$data = array(
				'metier_id'		=> $metier_id,
				'contact_id'	=>	$contact_id,
				'orga_spe_defaut'	=>	$default
			);

			return $this->insert($data);
		}

	}

	public function setDefault( $metier_id = 0, $contact_id = 0, $default = 0 ){

		if( $metier_id > 0 && $contact_id > 0 ){

			$this->update( array( 'orga_spe_defaut' => $default ), " metier_id = $metier_id AND contact_id = $contact_id ");

		}

	}
    
}