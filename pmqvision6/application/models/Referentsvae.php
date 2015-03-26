<?php
class Model_Referentsvae extends Zend_Db_Table_Abstract{
	
	protected $_name = 'referentsvae';
	protected $_primary = 'id_referentvae';
	

	public function get( $metier_id = 0, $contact_id = 0){

		
		if( $metier_id > 0  ) $where = " WHERE metier_id = $metier_id AND contact_id = $contact_id ";

		$sql = "
			SELECT
				*
			FROM
				referentsvae
			$where
		";
		return $binome = $this->_db->query( $sql )->fetch();
	}

	public function add( $metier_id = 0, $contact_id = 0){
		if( $metier_id > 0 && $contact_id > 0 ){
			$data = array(
				'metier_id'		=> $metier_id,
				'contact_id'	=>	$contact_id
			);

			return $this->insert($data);
		}
	}


    
}