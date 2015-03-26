<?php
class Fonctions_Strings{

	public function reduce( $string = "", $nb_caracteres = 0 ){

		if( $nb_caracteres > 0 ){

			$string = substr($string, 0, $nb_caracteres);
			return $string;

		}else{
			return false;
		}

	}
	
	public function getNextLetter($val,$i){
		//$car = ord($val);
		
		
		
		if($val == 'Z'){
			$val = 'A'.chr(65+$i-1);
			
		}else if(strlen($val)>1){
			$val = substr($val,1,1);
			$val = ord($val);
			$val = chr($val+$i);
			$val = 'A'.$val;
		}else {
			$val = ord($val);
			if($val+$i <= 90){
				$val = chr($val+$i);
			}else {
				$temp = $val+$i;
				$temp = $temp - 90;
				$val = 'A'.chr(65+$temp-1);
			}
		}
		
		
		
		
		return $val;
	}
	public function getPrevLetter($val){

		if($val == 'AA'){
			$val = 'Z';
			
		}else if(strlen($val)>1){
			$val = substr($val,1,1);
			$val = ord($val);
			$val = chr($val-1);
			$val = 'A'.$val;
		}else {
			$val = ord($val);
			$val = chr($val-1);
		}
		return $val;
	}

}