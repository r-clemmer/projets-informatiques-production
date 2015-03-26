<?php

include 'PHPExcel.php';
include 'PHPExcel/Writer/Excel2007.php';

class ExportExcel {
	private $_candidats;
	private $_out_data;
	private $_in_data;
	private $_tab_titre;
	
	private $_excelObject;
	private $_sheetStats;
	
	private $_array_col = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
	'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
	'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
	'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK','CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
	'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK','DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ');
	
	
	public function __construct($candidat,$array_out,$tab_dem){
		$this->_candidats = $candidat;
		$this->_out_data = $array_out;
		$this->_in_data = $tab_dem;
		$this->prepare();

	}
	
	private function prepare(){
		$this->_excelObject = new PHPExcel();
		$this->_sheetStats = $this->_excelObject->getActiveSheet();
		$this->_sheetStats->setTitle('Statistiques');
		$this->_sheetStats->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$this->_sheetStats->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$mTitre = new Model_Titre();
		$mDemarches = new Model_Demarche();

		$tab_dem = array();		
		if($this->_in_data != NULL){
			$dem = $mDemarches->get($this->_in_data[0]['demarche']);
			$demarche = $mTitre->getListeAndSpec($this->_in_data[0]['demarche']);
			$tab_dem[$dem["demarche_id"]] = $demarche;
							

		}else {
			$listeDem = $mDemarches->getAll();
			foreach($listeDem AS $dem){
				$demarche = $mTitre->getListeAndSpec($dem["demarche_id"]);
				$tab_dem[$dem["demarche_id"]] = $demarche;
				
				
			}
			
		}

		$this->_tab_titre = $tab_dem;
		


		$this->printCandidats();
	}
	
	private function printCandidats(){
		$this->_sheetStats->mergeCells('A1:A2');
		$this->_sheetStats->getStyle('A1:A2')->applyFromArray($this->setCellStyle(0));
		$this->_sheetStats->setCellValue('A1', 'Nom/prénom candidat');

		$this->_sheetStats->mergeCells('B1:B2');
		$this->_sheetStats->getStyle('B1:B2')->applyFromArray($this->setCellStyle(0));
		$this->_sheetStats->mergeCells('C1:C2');
		$this->_sheetStats->getStyle('C1:C2')->applyFromArray($this->setCellStyle(0));
		$this->_sheetStats->setCellValue('B1', 'Homme');
		$this->_sheetStats->setCellValue('C1', 'Femme');
		$this->_sheetStats->getColumnDimension('A')->setWidth(40);

		$this->printStats();
	}


	private function printStats(){
		
		$fDates = new Fonctions_Dates();

		$i = 3;

		foreach($this->_candidats as $candidat){

			if($i%2 == 0){
				$this->_sheetStats->getStyle($i)->applyFromArray($this->setCellStyle(2));
			}else {
				$this->_sheetStats->getStyle($i)->applyFromArray($this->setCellStyle(3));
			}
			$this->_sheetStats->setCellValue('A'.$i, Fonctions_Candidat::getCivilite(($candidat->civilite_id)-1).' '.ucwords($candidat->nom.' '.$candidat->prenom));
			
			if($candidat->civilite_id == 2){
				$valH = 1;
				$valF = '';
			}else {
				$valH = '';
				$valF = 1;
			}
			$this->_sheetStats->setCellValue('B'.$i, $valH);
			$this->_sheetStats->setCellValue('C'.$i, $valF);
			if($i==3){
				
				
			}

			
			$column = new PHPExcel_Worksheet_ColumnDimension('D');
			$j = 3;
			if(in_array('op',$this->_out_data)){

				$colonneFiche = $this->_array_col[$j];
				$j++;
				$colonneDate1 = $this->_array_col[$j];
				$j++;
				$colonneDate2 = $this->_array_col[$j];
				$j++;
				if($i==3){
					$this->_sheetStats->mergeCells($colonneFiche.'1:'.$colonneDate2.'1');
					$enTete = $this->_sheetStats->getStyle($colonneFiche.'1:'.$colonneDate2.'1');
					$enTete->applyFromArray($this->setCellStyle(0));	
					$this->_sheetStats->setCellValue($colonneFiche.'1',"Liste opérations")->getColumnDimension($colonneFiche)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneFiche.'2',"Fiche liaison")->getColumnDimension($colonneFiche)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneDate1.'2',"Date de création")->getColumnDimension($colonneDate1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneDate2.'2',"Date de mise en oeuvre")->getColumnDimension($colonneDate2)->setAutoSize(true);
				}
				

				$this->_sheetStats->setCellValue($colonneFiche.$i,$candidat->fiche);
				$this->_sheetStats->setCellValue($colonneDate1.$i,$fDates->formatDate($candidat->creation));
				$this->_sheetStats->setCellValue($colonneDate2.$i,$fDates->formatDate($candidat->meo));
				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),3));
				
			}
			if(in_array('org',$this->_out_data)){
						
				$colonne1 = $this->_array_col[$j];
				$j++;
				$colonne2 = $this->_array_col[$j];
				$j++;
				$colonne3 = $this->_array_col[$j];
				$j++;
				$colonne4 = $this->_array_col[$j];
				$j++;
				if($i==3){
					$this->_sheetStats->mergeCells($colonne1.'1:'.$colonne4.'1');
					$enTete = $this->_sheetStats->getStyle($colonne1.'1:'.$colonne4.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonne1.'1',"Organisme")->getColumnDimension($colonne1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne1.'2',"Organisme référent")->getColumnDimension($colonne1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne2.'2',"Expert métier")->getColumnDimension($colonne2)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne3.'2'," Entité Opcalia ")->getColumnDimension($colonne3)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne4.'2',"Conseiller")->getColumnDimension($colonne4)->setAutoSize(true);
					
				}

				$this->_sheetStats->setCellValue($colonne1.$i,ucwords($candidat->org));
				$this->_sheetStats->setCellValue($colonne2.$i,Fonctions_Candidat::getCivilite(($candidat->expert_civilite)-1).' '.ucwords($candidat->expert_nom.' '.$candidat->expert_prenom));
				$this->_sheetStats->setCellValue($colonne3.$i,ucwords($candidat->delegation));
				$this->_sheetStats->setCellValue($colonne4.$i,Fonctions_Candidat::getCivilite(($candidat->con_civilite)-1).' '.ucwords($candidat->con_nom.' '.$candidat->con_prenom));
				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),4));
			}
			if(in_array('ent',$this->_out_data)){
				$colonne1 = $this->_array_col[$j];
				$j++;
				$colonne2 = $this->_array_col[$j];
				$j++;
				$colonne3 = $this->_array_col[$j];
				$j++;
				
				if($i==3){
					$this->_sheetStats->mergeCells($colonne1.'1:'.$colonne3.'1');
					$enTete = $this->_sheetStats->getStyle($colonne1.'1:'.$colonne3.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonne1.'1',"Entreprise")->getColumnDimension($colonne1)->setAutoSize(true);
			
					$this->_sheetStats->setCellValue($colonne1.'2',"Entreprise")->getColumnDimension($colonne1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne2.'2',"Branche")->getColumnDimension($colonne2)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne3.'2',"Ville")->getColumnDimension($colonne3)->setAutoSize(true);
				}
		
				$this->_sheetStats->setCellValue($colonne1.$i,ucwords($candidat->entreprise));
				$this->_sheetStats->setCellValue($colonne2.$i,ucwords($candidat->branche));
				$this->_sheetStats->setCellValue($colonne3.$i,ucwords($candidat->ville));

				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),3));
			}
			if(in_array('dossier',$this->_out_data)){
				$colonne1 = $this->_array_col[$j];
				$j++;
				$colonne2 = $this->_array_col[$j];
				$j++;
				$colonne3 = $this->_array_col[$j];
				$j++;
				
				if($i==3){
					$this->_sheetStats->mergeCells($colonne1.'1:'.$colonne3.'1');
					$enTete = $this->_sheetStats->getStyle($colonne1.'1:'.$colonne3.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonne1.'1',"Dossier")->getColumnDimension($colonne1)->setAutoSize(true);
	
					$this->_sheetStats->setCellValue($colonne1.'2',"Candidat")->getColumnDimension($colonne1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne2.'2',"Référent")->getColumnDimension($colonne2)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne3.'2',"Date d'envoi")->getColumnDimension($colonne3)->setAutoSize(true);
				}
				$this->_sheetStats->setCellValue($colonne1.$i,$candidat->nbCandidats);
				$this->_sheetStats->setCellValue($colonne2.$i,$candidat->referent);
				$this->_sheetStats->setCellValue($colonne3.$i,$fDates->formatDate($candidat->envoi));

				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),3));
			}
			if(in_array('cand',$this->_out_data)){
				$colonneCand1 = $this->_array_col[$j];
				$j++;
				$colonneCand2 = $this->_array_col[$j];
				$j++;
				$colonneCand3 = $this->_array_col[$j];
				$j++;
				$colonneCand4 = $this->_array_col[$j];
				$j++;
				$colonneCand5 = $this->_array_col[$j];
				$j++;
				$colonneCand6 = $this->_array_col[$j];
				$j++;
				$colonne7 = $this->_array_col[$j];
				$j++;
				$colonne8 = $this->_array_col[$j];
				$j++;
				$colonne9 = $this->_array_col[$j];
				$j++;
				$colonne10 = $this->_array_col[$j];
				$j++;
				$colonne11 = $this->_array_col[$j];
				$j++;
				$colonne12 = $this->_array_col[$j];
				$j++;
				$colonne13 = $this->_array_col[$j];
				$j++;
				$colonne14 = $this->_array_col[$j];
				$j++;
				$colonne15 = $this->_array_col[$j];
				$j++;
				$colonne16 = $this->_array_col[$j];
				$j++;
				$colonne17 = $this->_array_col[$j];
				$j++;
				
				$colonne18 = $this->_array_col[$j];
				$j++;
				$colonne19 = $this->_array_col[$j];
				$j++;
				$colonne20 = $this->_array_col[$j];
				$j++;
				
				
				if($i==3){
					$indexF = $j;
					$index = $j;
					foreach($this->_tab_titre AS $titre){

						foreach($titre AS $val){
							
							$this->_sheetStats->setCellValue($this->_array_col[$index].'2',$val['nom'])->getColumnDimension($this->_array_col[$index])->setWidth(7);
							$index++;
						}
						
					}
					

					$this->_sheetStats->mergeCells($this->_array_col[$j].'1:'.$this->_array_col[$index-1].'1');
					$enTete = $this->_sheetStats->getStyle($this->_array_col[$j].'1:'.$this->_array_col[$index-1].'1');
					$enTete->applyFromArray($this->setCellStyle(0));		
					$this->_sheetStats->setCellValue($this->_array_col[$j].'1',"Titre visé")->getColumnDimension($this->_array_col[$j])->setAutoSize(true);					
					
					$this->_sheetStats->mergeCells($colonneCand1.'1:'.$colonneCand3.'1');
					$enTete = $this->_sheetStats->getStyle($colonneCand1.'1:'.$colonneCand3.'1');
					$enTete->applyFromArray($this->setCellStyle(0));			
					
					$this->_sheetStats->setCellValue($colonneCand1.'2',"< 26 ans")->getColumnDimension($colonneCand1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneCand2.'2',"26 ans < age < 46 ans")->getColumnDimension($colonneCand2)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneCand3.'2',"> 46 ans")->getColumnDimension($colonneCand3)->setAutoSize(true);
					$this->_sheetStats->mergeCells($colonneCand1.'1:'.$colonne13.'1');
					$this->_sheetStats->setCellValue($colonneCand1.'1',"Candidat")->getColumnDimension($colonneCand1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneCand4.'2',"< 5 ans")->getColumnDimension($colonneCand4)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneCand5.'2',"5 ans < ancienneté < 15 ans")->getColumnDimension($colonneCand5)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneCand6.'2',"> 15 ans")->getColumnDimension($colonneCand6)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne7.'2',"Type de de contrat")->getColumnDimension($colonne7)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne8.'2',"Titre du métier")->getColumnDimension($colonne8)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne9.'2',"Première certification ?")->getColumnDimension($colonne9)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne10.'2',"Objectif de l'opération")->getColumnDimension($colonne10)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne11.'2',"Année de positionnement")->getColumnDimension($colonne11)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne12.'2',"Référent Evaluateur CQP")->getColumnDimension($colonne12)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne13.'2',"Formation Référent Evaluateur Entreprise")->getColumnDimension($colonne13)->setAutoSize(true);
	
					$enTete = $this->_sheetStats->getStyle($colonneCand1.'1:'.$colonne13.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->mergeCells($colonne14.'1:'.$colonne17.'1');
					$this->_sheetStats->setCellValue($colonne14.'1',"Etapes du CQP")->getColumnDimension($colonne14)->setAutoSize(true);
					$enTete = $this->_sheetStats->getStyle($colonne14.'1:'.$colonne17.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonne14.'2',"Livret")->getColumnDimension($colonne14)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne15.'2',"Obs au poste")->getColumnDimension($colonne15)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne16.'2',"Questionnaire")->getColumnDimension($colonne16)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne17.'2',"Entretien")->getColumnDimension($colonne17)->setAutoSize(true);
	
					$this->_sheetStats->mergeCells($colonne18.'1:'.$colonne20.'1');
					$this->_sheetStats->setCellValue($colonne18.'1',"Etapes du CCSP")->getColumnDimension($colonne18)->setAutoSize(true);
					$enTete = $this->_sheetStats->getStyle($colonne18.'1:'.$colonne20.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonne18.'2',"Questionnaire")->getColumnDimension($colonne18)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne19.'2',"Obs au poste")->getColumnDimension($colonne19)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne20.'2',"Entretien")->getColumnDimension($colonne20)->setAutoSize(true);
				}
				


				$age = Fonctions_Candidat::getAge($candidat->naissance);
				if($age <= 26) $this->_sheetStats->setCellValue($colonneCand1.$i,'1');
				if($age > 26 && $age < 46) $this->_sheetStats->setCellValue($colonneCand2.$i,'1');
				if($age >= 46) $this->_sheetStats->setCellValue($colonneCand3.$i,'1');
				
				$anciennete =  Fonctions_Candidat::getAnciennete($candidat->ancien);
				if($anciennete <= 5) $this->_sheetStats->setCellValue($colonneCand4.$i,'1');
				if($anciennete > 5 && $anciennete < 15) $this->_sheetStats->setCellValue($colonneCand5.$i,'1');
				if($anciennete >= 15) $this->_sheetStats->setCellValue($colonneCand6.$i,'1');
						
				$this->_sheetStats->setCellValue($colonne7.$i,$candidat->contrat);
				$this->_sheetStats->setCellValue($colonne8.$i,$candidat->poste);
				$this->_sheetStats->setCellValue($colonne9.$i,$candidat->cursus);

				$index = $j;
				foreach($this->_tab_titre AS $key=>$titre){

					foreach($titre AS $val){
						if($val['id']==$candidat->bloc1_id && $candidat->demarche==$key){
							$this->_sheetStats->setCellValue($this->_array_col[$index].$i,'1');
						}
						$index++;
					}
					
				}
				

				$this->_sheetStats->setCellValue($colonne10.$i,ucfirst($candidat->obj));
				$this->_sheetStats->setCellValue($colonne11.$i,$fDates->formatDate($candidat->position));
				
				$this->_sheetStats->setCellValue($colonne12.$i,Fonctions_Candidat::getCivilite(($candidat->tuteur_civilite)-1).' '.ucfirst($candidat->tuteur_nom).' '.ucfirst($candidat->tuteur_prenom));
				$this->_sheetStats->setCellValue($colonne13.$i,$fDates->formatDate($candidat->formation));
				
				$this->_sheetStats->setCellValue($colonne14.$i,$fDates->formatDate($candidat->livret));
				$this->_sheetStats->setCellValue($colonne15.$i,$fDates->formatDate($candidat->obs));
				$this->_sheetStats->setCellValue($colonne16.$i,$fDates->formatDate($candidat->quest));
				$this->_sheetStats->setCellValue($colonne17.$i,$fDates->formatDate($candidat->entretien));
				
				$this->_sheetStats->setCellValue($colonne18.$i,$fDates->formatDate($candidat->questCcsp));
				$this->_sheetStats->setCellValue($colonne19.$i,$fDates->formatDate($candidat->obsCcsp));
				$this->_sheetStats->setCellValue($colonne20.$i,$fDates->formatDate($candidat->entCcsp));
				
				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),$index));
				$j = $index;
			}
			

			if(in_array('form',$this->_out_data)){
				$colonneForm1 = $this->_array_col[$j];
				$j++;
				$colonneForm2 = $this->_array_col[$j];
				$j++;
				$colonne3 = $this->_array_col[$j];
				$j++;
				
				if($i==3){
					$this->_sheetStats->mergeCells($colonneForm1.'1:'.$colonne3.'1');
					$enTete = $this->_sheetStats->getStyle($colonneForm1.'1:'.$colonne3.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonneForm1.'1',"Formation")->getColumnDimension($colonneForm1)->setAutoSize(true);
	
					$this->_sheetStats->setCellValue($colonneForm1.'2',"Nbre heures formation préconisées")->getColumnDimension($colonneForm1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneForm2.'2',"Nbre heures formation réalisées")->getColumnDimension($colonneForm2)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne3.'2',"Commentaires")->getColumnDimension($colonne3)->setWidth(100);
				}

				$this->_sheetStats->setCellValue($colonneForm1.$i,$candidat->form_est);
				$this->_sheetStats->setCellValue($colonneForm2.$i,$candidat->form_real);
				$this->_sheetStats->setCellValue($colonne3.$i,$candidat->form_rem);

				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),3));
			}
			
			
			
			if(in_array('jury',$this->_out_data)){
				$colonne1 = $this->_array_col[$j];
				$j++;
				$colonne2 = $this->_array_col[$j];
				$j++;
				
				if($i==3){
					$this->_sheetStats->mergeCells($colonne1.'1:'.$colonne2.'1');
					$enTete = $this->_sheetStats->getStyle($colonne1.'1:'.$colonne2.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonne1.'1',"Jury")->getColumnDimension($colonne1)->setAutoSize(true);
	
					$this->_sheetStats->setCellValue($colonne1.'2',"Date jury")->getColumnDimension($colonne1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonne2.'2',"Commentaires")->getColumnDimension($colonne2)->setAutoSize(true);
				}
				
				$this->_sheetStats->setCellValue($colonne1.$i,$fDates->formatDate($candidat->jury_date));
				$this->_sheetStats->setCellValue($colonne2.$i,$candidat->com);
				
				
				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),2));
			}
			


			if(in_array('result',$this->_out_data)){
				$colonneRes1 = $this->_array_col[$j];
				$j++;
				$colonneRes2 = $this->_array_col[$j];
				$j++;
				$colonneRes3 = $this->_array_col[$j];
				$j++;
				$colonneRes4 = $this->_array_col[$j];
				$j++;
				$colonneRes5 = $this->_array_col[$j];
				$j++;
				$colonneRes6 = $this->_array_col[$j];
				$j++;
				$colonne7 = $this->_array_col[$j];
				$j++;
								
				
				if($i==3){
					$index = $j;

							
					foreach($this->_tab_titre AS $titre){

						foreach($titre AS $val){
							$this->_sheetStats->setCellValue($this->_array_col[$index].'2',$val['nom'])->getColumnDimension($this->_array_col[$index])->setWidth(15);
							$index++;
							
						}
						
					}
					

	
			$this->_sheetStats->mergeCells($this->_array_col[$j].'1:'.$this->_array_col[$index-1].'1');

					$enTete = $this->_sheetStats->getStyle($this->_array_col[$j].'1:'.$this->_array_col[$index-1].'1');
					$enTete->applyFromArray($this->setCellStyle(0));		
					$this->_sheetStats->setCellValue($this->_array_col[$j].'1',"Titre obtenu")->getColumnDimension($this->_array_col[$j])->setAutoSize(true);					
					
					
					
					$this->_sheetStats->mergeCells($colonneRes1.'1:'.$colonne7.'1');
					$enTete = $this->_sheetStats->getStyle($colonneRes1.'1:'.$colonne7.'1');
					$enTete->applyFromArray($this->setCellStyle(0));
					
					$this->_sheetStats->setCellValue($colonneRes1.'1',"Résultat")->getColumnDimension($colonneRes1)->setAutoSize(true);
	
					$this->_sheetStats->setCellValue($colonneRes1.'2',"Certifié")->getColumnDimension($colonneRes1)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneRes2.'2',"Admissible")->getColumnDimension($colonneRes2)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneRes3.'2',"En formation")->getColumnDimension($colonneRes3)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneRes4.'2',"En évaluation")->getColumnDimension($colonneRes4)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneRes5.'2',"En positionnement")->getColumnDimension($colonneRes5)->setAutoSize(true);
					$this->_sheetStats->setCellValue($colonneRes6.'2',"Abandon")->getColumnDimension($colonneRes6)->setAutoSize(true);
	
					$this->_sheetStats->setCellValue($colonne7.'2',"Motif d'abandon")->getColumnDimension($colonne7)->setAutoSize(true);
				}
				
													
						

				switch($candidat->etat_id){
					case 1:
						$this->_sheetStats->setCellValue($colonneRes5.$i,'1');
						break;
					case 2:
						$this->_sheetStats->setCellValue($colonneRes3.$i,'1');
						break;
					case 3:
						$this->_sheetStats->setCellValue($colonneRes4.$i,'1');
						break;
					case 4:
						$this->_sheetStats->setCellValue($colonneRes2.$i,'1');
						break;
					case 5:
						$this->_sheetStats->setCellValue($colonneRes1.$i,'1');
						break;
					case 6:
						$this->_sheetStats->setCellValue($colonneRes6.$i,'1');
						break;							
				}							
				$this->_sheetStats->setCellValue($colonne7.$i,ucfirst($candidat->raison_libelle));

				$index = $j;
				$indexFR = $j;
				foreach($this->_tab_titre AS $key=>$titre){
					foreach($titre AS $val){
						if($val['id']==$candidat->bloc1_id && $candidat->etat_id==5 && $candidat->demarche==$key){
							$this->_sheetStats->setCellValue($this->_array_col[$index].$i,'1');
						}
						$index++;
					}
					
				}


				$j = $index;
				//$column->setColumnIndex($this->getNextLetter($column->getColumnIndex(),7));
			}
			
			
			$i++;
		}
		
		

		
		$column->setColumnIndex($this->_array_col[$j-1]);
		$this->_sheetStats->getStyle('B2:'.$column->getColumnIndex().'2')->applyFromArray($this->setCellStyle(1));
		
		//totaux
		$this->_sheetStats->getStyle($i)->applyFromArray($this->setCellStyle(4));
		//candidats
		$this->_sheetStats->setCellValue('A'.$i, count($this->_candidats).' candidat(s)' );
		//hommes
		$this->_sheetStats->setCellValue('B'.$i, '=SUM(B3:B'.($i-1).')' );
		$this->_sheetStats->getCell('B'.$i)->getCalculatedValue();
		//femmes
		$this->_sheetStats->setCellValue('C'.$i, '=SUM(C3:C'.($i-1).')' );
		$this->_sheetStats->getCell('C'.$i)->getCalculatedValue();
		if(in_array('cand',$this->_out_data)){
			//age
			$this->_sheetStats->setCellValue($colonneCand1.$i, '=SUM('.$colonneCand1.'3:'.$colonneCand1.($i-1).')' );
			$this->_sheetStats->getCell($colonneCand1.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneCand2.$i, '=SUM('.$colonneCand2.'3:'.$colonneCand2.($i-1).')' );
			$this->_sheetStats->getCell($colonneCand2.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneCand3.$i, '=SUM('.$colonneCand3.'3:'.$colonneCand3.($i-1).')' );
			$this->_sheetStats->getCell($colonneCand3.$i)->getCalculatedValue();
			//anciennete
			$this->_sheetStats->setCellValue($colonneCand4.$i, '=SUM('.$colonneCand4.'3:'.$colonneCand4.($i-1).')' );
			$this->_sheetStats->getCell($colonneCand4.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneCand5.$i, '=SUM('.$colonneCand5.'3:'.$colonneCand5.($i-1).')' );
			$this->_sheetStats->getCell($colonneCand5.$i)->getCalculatedValue();

			$this->_sheetStats->setCellValue($colonneCand6.$i, '=SUM('.$colonneCand6.'3:'.$colonneCand6.($i-1).')' );
			$this->_sheetStats->getCell($colonneCand6.$i)->getCalculatedValue();
		}
		//Results
		if(in_array('result',$this->_out_data)){
			$this->_sheetStats->setCellValue($colonneRes1.$i, '=SUM('.$colonneRes1.'3:'.$colonneRes1.($i-1).')' );
			$this->_sheetStats->getCell($colonneRes1.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneRes2.$i, '=SUM('.$colonneRes2.'3:'.$colonneRes2.($i-1).')' );
			$this->_sheetStats->getCell($colonneRes2.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneRes3.$i, '=SUM('.$colonneRes3.'3:'.$colonneRes3.($i-1).')' );
			$this->_sheetStats->getCell($colonneRes3.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneRes4.$i, '=SUM('.$colonneRes4.'3:'.$colonneRes4.($i-1).')' );
			$this->_sheetStats->getCell($colonneRes4.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneRes5.$i, '=SUM('.$colonneRes5.'3:'.$colonneRes5.($i-1).')' );
			$this->_sheetStats->getCell($colonneRes5.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneRes6.$i, '=SUM('.$colonneRes6.'3:'.$colonneRes6.($i-1).')' );
			$this->_sheetStats->getCell($colonneRes6.$i)->getCalculatedValue();
			
			foreach($this->_tab_titre AS $titre){
				foreach($titre AS $val){
					$this->_sheetStats->setCellValue($this->_array_col[$indexFR].$i, '=SUM('.$this->_array_col[$indexFR].'3:'.$this->_array_col[$indexFR].($i-1).')' );
					$indexFR++;
				}
				
			}
		}
		//Formation
		if(in_array('form',$this->_out_data)){
			$this->_sheetStats->setCellValue($colonneForm1.$i, '=SUM('.$colonneForm1.'3:'.$colonneForm1.($i-1).')' );
			$this->_sheetStats->getCell($colonneForm1.$i)->getCalculatedValue();
			$this->_sheetStats->setCellValue($colonneForm2.$i, '=SUM('.$colonneForm2.'3:'.$colonneForm2.($i-1).')' );
			$this->_sheetStats->getCell($colonneForm2.$i)->getCalculatedValue();
		}
		//cqp
		if(in_array('cand',$this->_out_data)){
			foreach($this->_tab_titre AS $titre){
				foreach($titre AS $val){
					$this->_sheetStats->setCellValue($this->_array_col[$indexF].$i, '=SUM('.$this->_array_col[$indexF].'3:'.$this->_array_col[$indexF].($i-1).')' );
					$indexF++;
				}
				
			}
		}
	}
	
	public function getFile(){
		$this->_excelObject->setActiveSheetIndex(0);
		$writer = new PHPExcel_Writer_Excel5($this->_excelObject);
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	//excel 2007
		header('Content-type: application/vnd.ms-excel');	//excel 5
		header('Content-Disposition:inline;filename=export.xls');
		$writer->save('php://output');
	}
	
	
	/********************************/
	private function getNextLetter($val,$i){

		if($val == 'Z'){
			$val = 'A'.chr(65+$i-1);
		}else if(strlen($val)>1){
			$f = substr($val,0,1);
			
			if($f=='A'){
				$val = 'A'.$val;
				$val = substr($val,1,1);
				$val = ord($val);
				$val = chr($val+$i);
			}else {
				$val = 'B'.$val;
			}
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
	private function getNextLetter2($val,$i){
		
		if($val == 'Z'){
			$val = 'A'.chr(65+$i-1);
		}else if(strlen($val)>1){
			$f = substr($val,0,1);
			
			if($f=='A'){
				$val = 'A'.$val;
				$val = substr($val,1,1);
				$val = ord($val);
				$val = chr($val+$i);
			}else {
				$val = 'B'.$val;
			}
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
	private function getPrevLetter($val){

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
	private function setCellStyle($numStyle){
		switch($numStyle){
			case 0:
				$style = array(	
					'fill'=> array(
								'type'=>PHPExcel_Style_Fill::FILL_SOLID
					),
					'alignment'	=> array(
								'vertical'		=> 'center',
								'horizontal'	=> 'center'
					),
					'font'		=> array(
								'name'			=> 'Arial',
								'size'			=> 12,
								'color'			=> array(	'rgb'	=> '000000' ),
								'bold'			=> true
					),
					'borders'	=> array(
								'bottom'	=> array(	'style'	=> 'medium',
												'color'	=> array(	'rgb'	=> '000000' ),
											),
								'left'		=> array(	'style'	=> 'medium',
												'color'	=> array(	'rgb'	=> '000000' ),
											),
								'right'		=> array(	'style'	=> 'medium',
												'color'	=> array(	'rgb'	=> '000000' ),
											),
								'top'		=> array(	'style'	=> 'medium',
												'color'	=> array(	'rgb'	=> '000000' )
											)
					)
					
 				);
				break;
			case 1:
				$style = array(	
					'fill'=> array(
								'type'=>PHPExcel_Style_Fill::FILL_SOLID
					),
					'alignment'	=> array(
								'vertical'		=> 'center',
								'horizontal'	=> 'center'
					),
					'font'		=> array(
								'name'			=> 'Arial',
								'size'			=> 9,
								'color'			=> array(	'rgb'	=> '000000' ),
								'bold'			=> true
					),
					'borders'	=> array(
								'allborders' => array(
				                 'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
				                 'color' => array(
				                     'rgb' => '808080'
				                 )
             					)
					)
					
 				);
				break;
			case 2:
				$style = array(	
					'fill'=> array(
								'type'=>PHPExcel_Style_Fill::FILL_SOLID,
								'color'=>array('rgb'=>'acd5ff')
					),
					'alignment'	=> array(
								'vertical'		=> 'center',
								'horizontal'	=> 'center',
								'wrap'=>true
					),
					'font'		=> array(
								'name'			=> 'Arial',
								'size'			=> 8,
								'color'			=> array(	'rgb'	=> '000000' )
					)	
 				);
				break;
			case 3:
				$style = array(	
					'alignment'	=> array(
								'vertical'		=> 'center',
								'horizontal'	=> 'center',
 								'wrap'=>true
					),
					'font'		=> array(
								'name'			=> 'Arial',
								'size'			=> 8,
								'color'			=> array(	'rgb'	=> '000000' )
					)
							
 				);
				break;
			case 4:
				$style = array(
					'fill'=> array(
							'type'=>PHPExcel_Style_Fill::FILL_SOLID,
							'color'=>array('rgb'=>'ffff00')
					),
					'alignment'	=> array(
								'vertical'		=> 'center',
								'horizontal'	=> 'center',
 								'wrap'=>true
					),
					'font'		=> array(
								'name'			=> 'Arial',
								'size'			=> 8,
								'color'			=> array(	'rgb'	=> '000000' )
					)

 				);
				break;
		}
		
		return $style;
		
	}
}