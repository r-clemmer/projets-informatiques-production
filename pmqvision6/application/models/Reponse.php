<?php

	class Model_Reponse extends Zend_Db_Table_Abstract{
		
		protected $_name = 'reponse';
		protected $_primary = 'reponse_id';
		protected $_referenceMap = array(
	        'question'	=> array(
	            'columns'		=> 'question_id',
	            'refTableClass' => 'Model_Question',
				'refColumns'	=> 'question_id'
	        )
	    );
	    
	    public function getListeByQuestion($question_id){
	    	
	    	$sql = "
	    		SELECT *
	    		FROM reponse
	    		WHERE question_id = $question_id
	    		ORDER BY reponse_date DESC
	    	";
	    	$stmt = $this->_db->query($sql);
	    	return $reponses = $stmt->fetchAll();
	    }
	    
		public function set($auteur, $message, $question_id){
			
			$date = date("Y-m-d G:i:s");
			$entite_id = $_SESSION['Zend_Auth']['storage']->entite_id;
			
			$sql = "
				INSERT INTO reponse
				(reponse_auteur, reponse_message, reponse_date, question_id, entite_id)
				VALUES('$auteur', '$message', '$date', $question_id, $entite_id)
			";
			$this->_db->query($sql);
		}
	    
	}