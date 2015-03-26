<?php
class Fonctions_Candidat{
	
	function getCivilite($id_civ){
		switch($id_civ){
			case 0 : $civ='Madame';
				break;
			case 1 : $civ='Monsieur';
				break;
			case 2 : $civ='Mademoiselle';
				break;
			default : $civ='';
				break;
		}
		return $civ;
	}
	
	function getAge($date_de_naissance){
		
		$annee = substr($date_de_naissance,0,4);
		
		$age = date('Y') - $annee; 
		
		return $age;
	}
	
	function getAnciennete($anciennete){
		
		$anciennete = date('Y-m-d') - $anciennete;
		
		return $anciennete;
	}
	
}