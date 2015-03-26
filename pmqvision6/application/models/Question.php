<?php

	class Model_Question extends Zend_Db_Table_Abstract{
		
		protected $_name = 'question';
		protected $_primary = 'question_id';
		protected $_dependentTables = array('Model_Reponse');
		
		public function get($question_id){
			
			$sql = "
				SELECT *
				FROM question
				WHERE question_id = $question_id
			";
			$stmt = $this->_db->query($sql);
			return $question = $stmt->fetchObject();
		}
		
		public function getListe($sidx, $sord, $start, $limit, $valide = false){
			
			if($valide != true){
				$where = " WHERE question_valide = 1 ";
			}else{
				$where = "";
			}
			
			$sql = "
				SELECT q.*, (
					SELECT count(r.reponse_id)
					FROM reponse AS r
					WHERE r.question_id = q.question_id
				) AS count
				FROM question AS q
				$where
				GROUP BY q.question_id
				ORDER BY ".$sidx." ".$sord."
				LIMIT ".$start.", ".$limit;
			$stmt = $this->_db->query($sql);
			$questions = $stmt->fetchAll();
			return $questions;
		}
		
		public function count($valide = 0){
			
			if($valide != 1){
				$where = " WHERE question_valide = 1 ";
			}else{
				$where = "";
			}
			
			$sql = "
				SELECT count(*) AS count
				FROM question
				$where
			";
			$stmt = $this->_db->query($sql);
			$res = $stmt->fetchAll();
			$count = $res[0]['count'];
			return $count;
		}
		
		public function set($question_auteur, $question_objet, $question_message, $question_severite){
			
			$date = date("Y-m-d G:i:s");
			
			$question_auteur = addslashes($question_auteur);
			$question_objet = addslashes($question_objet);
			$question_message = addslashes($question_message);
			$entite_id = $_SESSION['Zend_Auth']['storage']->entite_id;
			
			$sql = "
				INSERT INTO question
				(question_auteur, question_objet, question_message, question_severite, question_date, entite_id)
				VALUES('$question_auteur', '$question_objet', '$question_message', $question_severite, '$date', $entite_id)
			";
			$this->_db->query($sql);
		}
		
		public function valider($question_id, $validation){
			
			$sql = "
				UPDATE question
				SET question_valide = $validation
				WHERE question_id = $question_id
			";
			$this->_db->query($sql);
		}
	    
	}