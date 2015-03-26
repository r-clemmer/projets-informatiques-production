<?php
class Fonctions_Dates{
	
	//entree : string AAAA-MM-JJ
	//sortie : string JJ/MM/AAAA
	public function formatDate($date1){
		if( $date1 != '0000-00-00' ){
			if($date1!=''){
				$annee = substr($date1,0,4);
				$mois = substr($date1,5,2);
				$jour = substr($date1,8,2);
				$date2 = $jour.'/'.$mois.'/'.$annee;
			}else{
				$date2 = '';
			}
		}else{
			$date2 = '';
		}
		return $date2;
	}
	
	//sortue : string AAAA-MM-JJ
	//entree : string JJ/MM/AAAA
	public function formatDate2($date1){
		if( $date1 != '00/00/0000' ){
			if($date1!=''){
				$jour = substr($date1,0,2);
				$mois = substr($date1,3,2);
				$annee = substr($date1,6,4);
				$date2 = $annee.'-'.$mois.'-'.$jour;
			}else{
				$date2 = '';
			}
		}else{
			$date2 = '';
		}
		return $date2;
	}
	
	//entree : string AAAA-MM-JJ
	//sortie : string JJ mois AAAA
	public function formatDateMoisLettres($date1){
		if( $date1 != '' && $date1 != '0000-00-00' ){
			$annee = substr($date1,0,4);
			$mois = substr($date1,5,2);
			$jour = substr($date1,8,2);
			$mois=$this->getMoisLettre($mois);
			$date2 = $jour.' '.$mois.' '.$annee;
		}else{
			$date2 = '';
		}
		return $date2;
	}
	
	//entree : string 01 (ex)
	//sortie : string janvier (ex)
	public function getMoisLettre($mois){
		switch($mois){
			case '01' : return $moisLettres='janvier';
				break;
			case '02' : return $moisLettres='février';
				break;
			case '03' : return $moisLettres='mars';
				break;
			case '04' : return $moisLettres='avril';
				break;
			case '05' : return $moisLettres='mai';
				break;
			case '06' : return $moisLettres='juin';
				break;
			case '07' : return $moisLettres='juillet';
				break;
			case '08' : return $moisLettres='août';
				break;
			case '09' : return $moisLettres='septembre';
				break;
			case '10' : return $moisLettres='octobre';
				break;
			case '11' : return $moisLettres='novembre';
				break;
			case '12' : return $moisLettres='décembre';
				break;
		}
	}
	
	/**
     * 
     * @param string $date
     * sous forme DD/MM/YYYY
     * @return bool
     */
    public function checkDate($date = ''){

		if( strlen( $date ) == 10 ){
    	
			$year = substr($date, 6, 4);
			$month = substr($date, 3, 2);
			$day = substr($date, 0, 2);

			if(checkdate($month, $day, $year) && strlen($date)==10){
				return true;
			}else{
				return false;
			}
		}
    	
    }
    
    /**
     * 
     * @param string $date1
     * @param string $date2
     * entrees sous forme DD/MM/YYYY
     * @return <, >, = ou false
     */
	public function checkOrdreDate($date1 = '', $date2 = ''){
		
		if($this->checkDate($date1) == false || $this->checkDate($date2) == false){
			return false;
		}else{
			$year1 = substr($date1, 6, 4);
    		$month1 = substr($date1, 3, 2);
    		$day1 = substr($date1, 0, 2);
    		$year2 = substr($date2, 6, 4);
    		$month2 = substr($date2, 3, 2);
    		$day2 = substr($date2, 0, 2);
    		if($year1 < $year2){
    			return '<';
    		}elseif($year1 > $year2){
    			return '>';
    		}else{
    			if($month1 < $month2){
    				return '<';
    			}elseif($month1 > $month2){
    				return '>';
    			}else{
    				if($day1 < $day2){
    					return '<';
    				}elseif($day1 > $day2){
    					return '>';
    				}else{
    					return '=';
    				}
    			}
    		}
		}
    	
    }
    
    /**
     * 
     * @param string $date
     * sous forme DD/MM/YYYY
     * @return string
     * sous forme YYYY-MM-DD
     */
	public function unformatDate($date = ''){
		
		if($date!=''){
			
			$annee = substr($date,6,4);
			$mois = substr($date,3,2);
			$jour = substr($date,0,2);
			$date = $annee.'-'.$mois.'-'.$jour;
			return $date;
			
		}else{
			
			return false;
			
		}
		
	}
	
	/**
	 * 
	 * @param string $DH
	 * sous forme YYYY-MM-DD HH:MM:SS
	 * @return array 
	 * date => DD/MM/YYY
	 * heure => HH:MM:SS
	 */
	public function formatDateHeure( $DH = '0000-00-00 00:00:00' ){
		
		if( $DH != '0000-00-00 00:00:00' ){
			
			$date = substr($DH, 0, 10);
			$date = $this->formatDate($date);
			
			$heure = substr($DH, 11, 8);
			
			return array( 'date' => $date, 'heure' => $heure );
			
		}else{
			
			return false;
		}
		
	}

	public function getNbYears( $date = '0000-00-00' ){

		$list = explode('-', $date);
		$annee = '';
		$mois = '';
		$jour = '';
		if(isset($list[0]))
		{
			$annee = $list[0];
		}
	if(isset($list[1]))
		{
			$mois = $list[1];
		}
	if(isset($list[2]))
		{
			$jour = $list[2];
		}
		
		
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$annees = $today['annee'] - $annee;
		
		if ($today['mois'] <= $mois) {
			if ($mois == $today['mois']) {
				if ($jour > $today['jour']) $annees--;
			}else{
				$annees--;
			}
		}
		return $annees;
	}

	public function getNbYearsTwoDates( $date1 = '0000-00-00', $date2 = '0000-00-00' ){

		if( $date1 != '0000-00-00' && $date2 != '0000-00-00' && ( $this->checkOrdreDate( $this->formatDate( $date1 ), $this->formatDate( $date2 ) ) == '<' ) ){
			$list1 = explode('-', $date1);
			$annee1 = $list1[0];
			$mois1 = $list1[1];
			$jour1 = $list1[2];
			$list2 = explode('-', $date2);
			$annee2 = $list2[0];
			$mois2 = $list2[1];
			$jour2 = $list2[2];
			$annees = $annee2 - $annee1;
			if ($mois2 <= $mois1) {
				if ($mois1 == $mois2) {
					if ($jour1 > $jour2) $annees--;
				}else{
					$annees--;
				}
			}
			return $annees;

		}else return false;
	}
	
}