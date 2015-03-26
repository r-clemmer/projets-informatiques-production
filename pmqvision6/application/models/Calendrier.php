<?php
class Model_Calendrier extends Zend_Db_Table_Abstract{
	
	protected $_name = 'fiche';
	
	public function getDatesOperations($debut,$fin,$id){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('fiche AS f0',array('fiche_date_meo','COUNT(*) AS nb'));
		$req->where("fiche_date_meo BETWEEN '$debut' AND '$fin'");
		$req->group('fiche_date_meo');
		
		if($id != 'forthac'){
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$id);
		}
		
		$query = $req->query();
	
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
		
	}
	public function getInfosOperation($date,$auth){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('fiche AS f0');
		$req->where('f0.fiche_date_meo=?',$date);
		
		if($auth != 'forthac'){
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$auth);
		}
		
		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
	}
	
	
	public function getDatesEnvoiDossier($debut,$fin,$id){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('metier AS m0',array('m0.metier_id','m0.metier_date_envoi_dossiers','COUNT(*) AS nb'));
		$req->where("metier_date_envoi_dossiers BETWEEN '$debut' AND '$fin'");
		$req->group('metier_date_envoi_dossiers');
		
		if($id != 'forthac'){
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$id);
		}
		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
	}
	public function getInfosDossier($date,$auth){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('metier AS m0');
		$req->where('metier_date_envoi_dossiers=?',$date);
		
		if($auth != 'forthac'){
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$auth);
		}
		
		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
	}
	
	
	public function getDatesJury($debut,$fin,$id){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('jury AS j0',array('j0.jury_date','COUNT(DISTINCT j0.jury_id) AS nb'));
		$req->group('j0.jury_date');
		$req->where("j0.jury_date BETWEEN '$debut' AND '$fin'");
		
		if($id != 'forthac'){
			
			$req->joinLeft('resultat AS r1','r1.jury_id=j0.jury_id',array());
			$req->joinLeft('candidat_metier AS cm0','cm0.candidat_metier_id=r1.candidat_metier_id',array());
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());

			$req->where("cf0.entite_id =? ",$id);
		}

		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
		
	}
	public function getInfosJury($date,$auth){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('jury AS j0',array('j0.jury_id','j0.jury_ville'));
		$req->where('j0.jury_date =?',$date);
		
		if($auth != 'forthac'){
			$req->group('j0.jury_id');
			$req->joinLeft('resultat AS r1','r1.jury_id=j0.jury_id',array());
			$req->joinLeft('candidat_metier AS cm0','cm0.candidat_metier_id=r1.candidat_metier_id',array());
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$auth);
		}
		
		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
	}

	
	public function getDatesOutils($type,$debut,$fin,$id){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('resultat_outil AS r0',array('r0.resultat_date','COUNT(*) AS nb'));
		$req->joinLeft('outil AS o1','o1.outil_id=r0.outil_id',array('outil_libelle'));
		$req->where('o1.outil_id =?',$type);
		$req->where('o1.outil_libelle !=?','capacite');
		$req->where("r0.resultat_date BETWEEN '$debut' AND '$fin'");
		$req->group('r0.resultat_date');
		
		if($id != 'forthac'){			
			$req->joinLeft('resultat AS r1','r1.resultat_id=r0.resultat_id',array());
			$req->joinLeft('candidat_metier AS cm0','cm0.candidat_metier_id=r1.candidat_metier_id',array());
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$id);
		}
		
		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
		
	}
	public function getInfosOutils($date,$type,$auth){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('resultat_outil AS r0',array('resultat_date'));
		
		$req->joinLeft('outil AS o1','o1.outil_id=r0.outil_id',array());
		$req->joinLeft('resultat AS r1','r1.resultat_id=r0.resultat_id',array());
		$req->joinLeft('candidat_metier AS cm0','cm0.candidat_metier_id=r1.candidat_metier_id',array());
		$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
		$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array('p0.personne_nom','p0.personne_prenom'));

		$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
		$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array('m0.fiche_id'));
		
		$req->group('r0.resultat_outil_id');
		$req->where('r0.resultat_date =?',$date);
		$req->where('o1.outil_libelle =?',$type);
		$req->order('p0.personne_nom ASC');
		
		if($auth != 'forthac'){
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$auth);
		}

		$query = $req->query();

		return $query->fetchAll(Zend_Db::FETCH_OBJ);
	}
	public function getAllDatesOutils($type,$debut,$fin,$id){
		$req = $this->select()->setIntegrityCheck(false);
		$req->from('resultat_outil AS r0',array('r0.resultat_date','COUNT(*) AS nb'));
		$req->joinLeft('outil AS o1','o1.outil_id=r0.outil_id',array('outil_libelle AS type'));
		$req->joinLeft('demarche AS d0','d0.demarche_id=o1.demarche_id',array());
		$req->where('d0.demarche_abrege=?',$type);
		$req->where("r0.resultat_date BETWEEN '$debut' AND '$fin'");
		$req->where('o1.outil_libelle !=?','capacite');
		$req->group('r0.resultat_date');
		$req->group('o1.outil_id');
		
		if($id != 'forthac'){
			$req->joinLeft('resultat AS r1','r1.resultat_id=r0.resultat_id',array());
			$req->joinLeft('candidat_metier AS cm0','cm0.candidat_metier_id=r1.candidat_metier_id',array());
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->where("cf0.entite_id =? ",$id);
		}
		
		$query = $req->query();
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
	}
    
}