<?php
class Model_Statistique extends Zend_Db_Table_Abstract{
	
	protected $_name = 'personne';
	
	public function getCandidatsMetier($out_tab,$in_id_op,$in_date_op_deb,$in_date_op_fin,$in_demarche,$in_titre,$in_org_referent,$in_delegation,$in_branche,$in_entreprise,$in_region,$in_etat){
		$auth = Zend_Auth::getInstance();
		$role = $auth->getStorage()->read()->role;

		$req = $this->select()->setIntegrityCheck(false);
		
		// Formation
		if(in_array('form',$out_tab)){
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_id AS id_cand','cm0.candidat_metier_formation_duree_estimee AS form_est','cm0.candidat_metier_formation_duree_realisee AS form_real','cm0.candidat_metier_formation_remarque AS form_rem'));
		}else {
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_id AS id_cand'));
		}
		
		// Jury
		if(in_array('jury',$out_tab)){
			if(in_array('cand',$out_tab)){
				$req->joinLeft('resultat AS r6','r6.candidat_metier_id=cm0.candidat_metier_id',array('MAX(r6.resultat_num_passage) AS num_passage','r6.resultat_commentaire_jury AS com'));
			}else {
				$req->joinLeft('resultat AS r6','r6.candidat_metier_id=cm0.candidat_metier_id',array('r6.resultat_commentaire_jury AS com'));
			}
			
			$req->where('r6.resultat_num_passage= (SELECT MAX(resultat_num_passage) FROM resultat WHERE candidat_metier_id=cm0.candidat_metier_id)');
			$req->joinLeft('jury AS j0','j0.jury_id=r6.jury_id',array('j0.jury_date'));
			
		}elseif(in_array('cand',$out_tab)){
			$req->joinLeft('resultat AS r6','r6.candidat_metier_id=cm0.candidat_metier_id',array('r6.resultat_commentaire_jury AS com'));
			$req->where('r6.resultat_num_passage= (SELECT MAX(resultat_num_passage) FROM resultat WHERE candidat_metier_id=cm0.candidat_metier_id)');
		}
		
		// Dossier
		if(in_array('dossier',$out_tab)){
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array('m0.metier_nb_dossiers_candidats AS nbCandidats','m0.metier_nb_dossiers_tuteurs AS referent','m0.metier_date_envoi_dossiers AS envoi'));
		}else {
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array());
		}
		
		// Opérations
		if(in_array('op',$out_tab)){
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array('f0.fiche_id AS fiche','f0.fiche_date_creation AS creation','f0.fiche_date_meo AS meo'));
		}else {
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array());
		}
			
		// Candidat
		if(in_array('cand',$out_tab)){
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array('c0.candidat_anciennete AS ancien','c0.candidat_contrat AS contrat','c0.candidat_cursus AS cursus'));
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array('p0.personne_nom AS nom','p0.personne_prenom AS prenom','p0.civilite_id','p0.personne_poste AS poste','p0.personne_date_naissance AS naissance','p0.visible AS personne_visible'));
			$req->joinLeft('metier AS m6','m6.metier_id=cm0.metier_id',array('m6.demarche_id AS demarche','m6.bloc1_id','m6.bloc2_id'));
			
			$req->joinLeft('fiche AS f6','f6.fiche_id=m6.fiche_id',array('f6.fiche_date_creation AS position'));
			$req->joinLeft('objectif as o1','o1.objectif_id=f6.objectif_id',array('o1.objectif_libelle AS obj'));
			
			$req->joinLeft('binome AS b1','b1.binome_id=cm0.tuteur_id',array());
			$req->joinLeft('contact AS c3','c3.contact_id=b1.contact_id',array('c3.contact_date_formation AS formation'));
			$req->joinLeft('personne AS p6','p6.personne_id=c3.personne_id',array('p6.personne_nom AS tuteur_nom','p6.personne_prenom AS tuteur_prenom','p6.civilite_id AS tuteur_civilite'));
		
			//etapes positionnement
			$req->joinLeft('resultat_outil AS ro0','ro0.resultat_id=r6.resultat_id AND ro0.outil_id=1',array('ro0.resultat_date AS livret'));
			$req->joinLeft('resultat_outil AS ro1','ro1.resultat_id=r6.resultat_id AND ro1.outil_id=3',array('ro1.resultat_date AS obs'));
			$req->joinLeft('resultat_outil AS ro2','ro2.resultat_id=r6.resultat_id AND ro2.outil_id=2',array('ro2.resultat_date AS quest'));
			$req->joinLeft('resultat_outil AS ro3','ro3.resultat_id=r6.resultat_id AND ro3.outil_id=4',array('ro3.resultat_date AS entretien'));
		
			$req->joinLeft('resultat_outil AS ro4','ro4.resultat_id=r6.resultat_id AND ro4.outil_id=7',array('ro4.resultat_date AS questCcsp'));
			$req->joinLeft('resultat_outil AS ro5','ro5.resultat_id=r6.resultat_id AND ro5.outil_id=8',array('ro5.resultat_date AS obsCcsp'));
			$req->joinLeft('resultat_outil AS ro6','ro6.resultat_id=r6.resultat_id AND ro6.outil_id=9',array('ro6.resultat_date AS entCcsp'));
			
			
			
		}else {
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array('p0.personne_nom AS nom','p0.personne_prenom AS prenom','p0.civilite_id'));
		}
		
		// Entreprise
		if(in_array('ent',$out_tab)){
			$req->joinLeft('entite AS e0','e0.entite_id=p0.entite_id',array('e0.entite_nom AS entreprise','e0.entite_ville AS ville'));
			$req->joinLeft('entite AS ep','ep.entite_id=e0.parent_id',array('ep.entite_nom AS branche'));
		}else {
			$req->joinLeft('entite AS e0','e0.entite_id=p0.entite_id',array());
		}
		
		// Resultat
		if(in_array('result',$out_tab)){
			$req->joinLeft('etat AS e6','e6.etat_id=cm0.etat_id',array('e6.etat_libelle','e6.etat_id'));
			//raison abandon
			$req->joinLeft('raison AS r7','r7.raison_id=cm0.raison_id',array('r7.raison_libelle'));
		}
		
		// Organisme
		if(in_array('org',$out_tab)){
			//Expert metier
			$req->joinLeft('binome AS b0','b0.binome_id=cm0.expert_id',array());
			$req->joinLeft('contact as c1','c1.contact_id=b0.contact_id',array());
			$req->joinLeft('personne as p1','p1.personne_id=c1.personne_id',array('p1.personne_nom AS expert_nom','p1.personne_prenom AS expert_prenom','p1.civilite_id AS expert_civilite'));
			
			//referent
			$req->joinLeft('contacts_fiche AS cf1','cf1.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e2','e2.entite_id=cf1.entite_id',array('e2.entite_nom AS org'));
			$req->joinLeft('entite_type_entite AS ete2','ete2.entite_id= e2.entite_id',array());
			$req->joinLeft('type_entite AS te2','te2.type_entite_id= ete2.type_entite_id',array());
			$req->where('te2.type_entite_id=2');
			
			//Delegation et conseiller
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e1','e1.entite_id=cf0.entite_id',array('e1.entite_nom AS delegation'));
			$req->joinLeft('contact AS c2','c2.contact_id=cf0.contact_id',array());
			$req->joinLeft('personne AS p2','p2.personne_id=c2.personne_id',array('p2.personne_nom AS con_nom','p2.personne_prenom AS con_prenom','p2.civilite_id AS con_civilite'));		
			$req->joinLeft('entite_type_entite AS ete1','ete1.entite_id= e1.entite_id',array());
			$req->joinLeft('type_entite AS te1','te1.type_entite_id= ete1.type_entite_id',array());
			$req->where('te1.type_entite_id=4');
		}else {
			$req->joinLeft('contacts_fiche AS cf1','cf1.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e2','e2.entite_id=cf1.entite_id',array());
			$req->joinLeft('entite_type_entite AS ete2','ete2.entite_id= e2.entite_id',array());
			$req->joinLeft('type_entite AS te2','te2.type_entite_id= ete2.type_entite_id',array());
			$req->where('te2.type_entite_id=2');
			
			$req->joinLeft('contacts_fiche AS cf0','cf0.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e1','e1.entite_id=cf0.entite_id',array());
			$req->joinLeft('entite_type_entite AS ete1','ete1.entite_id= e1.entite_id',array());
			$req->joinLeft('type_entite AS te1','te1.type_entite_id= ete1.type_entite_id',array());
			$req->where('te1.type_entite_id=4');
		}

		$req->group('cm0.candidat_metier_id')->group('f0.fiche_id');
		$req->order('p0.personne_nom ASC');

		$test=0;
		
		if($role == 'délégation'){
			$entite_id = $auth->getIdentity()->entite_id;
			$req->where('e1.entite_id = ?',$entite_id);
		}else{
			if($role != 'forthac')
			{
				$entite_id = $auth->getIdentity()->entite_id;
				$req->where('e2.entite_id = ?',$entite_id);
			}
		}
		if($in_id_op!=''){
			$req->where('m0.fiche_id = ?',$in_id_op);
		}
		if($in_titre!=''){
			$req->where('m0.bloc1_id = ?',$in_titre);
		}
		if($in_demarche!=''){
			$req->where('m0.demarche_id = ?',$in_demarche);
		}
		if($in_date_op_deb!=''){
			$req->where('f0.fiche_date_creation >= ?',Fonctions_Dates::formatDate2($in_date_op_deb));
			$test++;
		}
		if($in_date_op_fin!=''){
			$req->where('f0.fiche_date_creation <= ?',Fonctions_Dates::formatDate2($in_date_op_fin));
			$test++;
		}
		if($in_etat!='' && $in_etat!='99'){
			$req->where('cm0.etat_id = ?',$in_etat);
		}elseif( $in_etat == 99 ){
			$req->where('cm0.etat_id = ?',1);
			$req->orWhere('cm0.etat_id = ?',3);
		}
		if($in_entreprise!=''){
			$req->where('e0.entite_id = ?',$in_entreprise);
		}
		if($in_branche!=''){
			$req->where('e0.parent_id = ?',$in_branche);
		}
		if($in_delegation!=''){
			$req->where('e1.entite_id = ?',$in_delegation);
		}
		if($in_org_referent!=''){
			$req->where('e2.entite_id = ?',$in_org_referent);
		}
		if($in_region != NULL ){
			$req->where("(SUBSTR(e0.entite_cp, 1, 2) IN (?))",$in_region);
		}

		//Zend_Debug::dump($req->assemble());

		$query = $req->query();
		
		
		
		return $query->fetchAll(Zend_Db::FETCH_OBJ);
			
	}
	
	public function getInfosForStats($in_id_op,$in_date_op_deb,$in_date_op_fin,$in_demarche,$in_titre,$in_org_referent,$in_delegation,$in_branche,$in_entreprise,$in_etat){
			$auth = Zend_Auth::getInstance();
			$role = $auth->getStorage()->read()->role;

			$req = $this->select()->setIntegrityCheck(false);

			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_formation_duree_estimee','cm0.candidat_metier_formation_duree_realisee','cm0.candidat_metier_formation_remarque'));
			
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array('m0.metier_nb_dossiers_candidats AS nbCandidats','m0.metier_nb_dossiers_tuteurs AS referent','m0.metier_date_envoi_dossiers AS envoi','m0.demarche_id AS demarche','m0.bloc1_id','m0.bloc2_id'));
			//$req->joinLeft('fiche AS f1','f1.fiche_id=m0.fiche_id',array());
			
			//Etat
			$req->joinLeft('etat AS etat0','etat0.etat_id=cm0.etat_id',array('etat0.etat_libelle'));
			//raison abandon
			$req->joinLeft('raison AS raison0','raison0.raison_id=cm0.raison_id',array('raison0.raison_libelle'));
			//Info candidat
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array('c0.candidat_anciennete','c0.candidat_contrat','c0.candidat_cursus'));
			//Info personne
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array('p0.personne_poste AS poste','p0.personne_nom','p0.personne_prenom','p0.civilite_id','p0.personne_date_naissance'));
			//Info entreprise
			$req->joinLeft('entite AS e0','e0.entite_id=p0.entite_id',array('e0.entite_nom AS entreprise','e0.entite_ville AS ville'));
			//Info branche
			$req->joinLeft('entite AS e2','e2.entite_id=e0.parent_id',array('e2.entite_nom AS branche'));
			
			//Info opération
			$req->joinLeft('contacts_fiche AS cf0','cf0.entite_id=e0.entite_id',array());
			$req->joinLeft('fiche AS f0','f0.fiche_id=m0.fiche_id',array('f0.fiche_id AS id_op','f0.fiche_date_creation','f0.fiche_date_meo'));
			
			
				
			//Info metier
			$req->joinLeft('contact as c1','c1.contact_id=cf0.contact_id',array());
			
			//Objectif
			$req->joinLeft('objectif as o1','o1.objectif_id=f0.objectif_id',array('o1.objectif_libelle AS obj'));
		
			//Expert metier
			$req->joinLeft('binome AS b0','b0.binome_id=cm0.expert_id',array());
			$req->joinLeft('contact as c2','c2.contact_id=b0.contact_id',array());
			$req->joinLeft('personne as p1','p1.personne_id=c2.personne_id',array('p1.personne_nom AS expert_nom','p1.personne_prenom AS expert_prenom'));
			
			$req->joinLeft('contacts_fiche AS cf5','cf5.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e5','e5.entite_id=cf5.entite_id',array('e5.entite_nom AS org'));
			$req->joinLeft('entite_type_entite AS ete5','ete5.entite_id= e5.entite_id',array());
			$req->joinLeft('type_entite AS te5','te5.type_entite_id= ete5.type_entite_id',array());
			$req->where('te5.type_entite_libelle="organisme référent"');

			//Delegation et conseiller
			$req->joinLeft('contacts_fiche AS cf2','cf2.fiche_id=f0.fiche_id',array());
			$req->joinLeft('entite AS e4','e4.entite_id=cf2.entite_id',array('e4.entite_nom AS delegation'));
			$req->joinLeft('contact AS c4','c4.contact_id=cf2.contact_id',array());
			$req->joinLeft('personne AS p4','p4.personne_id=c4.personne_id',array('p4.personne_nom AS con_nom','p4.personne_prenom AS con_prenom'));		
			$req->joinLeft('entite_type_entite AS ete4','ete4.entite_id= e4.entite_id',array());
			$req->joinLeft('type_entite AS te2','te2.type_entite_id= ete4.type_entite_id',array());
			$req->where('te2.type_entite_libelle="délégation"');
			
			//Evaluateur
			$req->joinLeft('binome AS b1','b1.binome_id=cm0.tuteur_id',array());
			$req->joinLeft('contact AS c3','c3.contact_id=b1.contact_id',array('c3.contact_date_formation'));
			$req->joinLeft('personne AS p2','p2.personne_id=c3.personne_id',array('p2.personne_nom AS tuteur_nom','p2.personne_prenom AS tuteur_prenom'));
			
			//Jury
			$req->joinLeft('resultat AS r0','r0.candidat_metier_id=cm0.candidat_metier_id',array('MAX(r0.resultat_num_passage) AS num_passage','r0.resultat_commentaire_jury AS com'));
			$req->where('r0.resultat_num_passage= (SELECT MAX(resultat_num_passage) FROM resultat WHERE candidat_metier_id=cm0.candidat_metier_id)');
			
			$req->joinLeft('jury AS j0','j0.jury_id=r0.jury_id',array('j0.jury_date'));

			//etapes positionnement

			$req->joinLeft('resultat_outil AS ro0','ro0.resultat_id=r0.resultat_id AND ro0.outil_id=1',array('ro0.resultat_date AS livret'));
			$req->joinLeft('resultat_outil AS ro1','ro1.resultat_id=r0.resultat_id AND ro1.outil_id=3',array('ro1.resultat_date AS obs'));
			
			$req->joinLeft('resultat_outil AS ro2','ro2.resultat_id=r0.resultat_id AND ro2.outil_id=2',array('ro2.resultat_date AS quest'));
			$req->joinLeft('resultat_outil AS ro3','ro3.resultat_id=r0.resultat_id AND ro3.outil_id=4',array('ro3.resultat_date AS entretien'));
			
			
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			
			
			if($role != 'forthac'){
				$entite_id = $auth->getIdentity()->entite_id;
				$req->where('p0.entite_id = ?',$entite_id);
			}
			
			if($in_titre != -1 && $in_titre!=''){
				$req->where('m0.bloc1_id = ?',$in_titre);
			}
			if($in_demarche != -1 && $in_demarche!=''){
				$req->where('m0.demarche_id = ?',$in_demarche);
			}
			if($in_delegation != -1 && $in_delegation!=''){
				$req->where('e4.entite_id = ?',$in_delegation);
			}
			
			if($in_date_op_deb != -1 && $in_date_op_deb!=''){
				$req->where('f0.fiche_date_creation >= ?',Fonctions_Dates::formatDate2($in_date_op_deb));
			}
			if($in_date_op_fin != -1 && $in_date_op_fin!=''){
				$req->where('f0.fiche_date_creation <= ?',Fonctions_Dates::formatDate2($in_date_op_fin));
			}
			if($in_org_referent != -1 && $in_org_referent!=''){
				$req->where('e5.entite_id = ?',$in_org_referent);
			}
			if($in_branche != -1 && $in_branche!=''){
				$req->where('e2.entite_id = ?',$in_branche);
			}
			if($in_entreprise != -1 && $in_entreprise!=''){
				$req->where('e0.entite_id = ?',$in_entreprise);
			}
			if($in_etat != -1 && $in_etat!=''){
				$req->where('etat0.etat_id = ?',$in_etat);
			}
			if($in_id_op != -1 && $in_id_op!=''){
				$req->where('f0.fiche_id = ?',$in_id_op);
			}
			
//			Zend_Debug::dump($req->assemble());

			$query = $req->query();

			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}		
}