function checkQuestionnaire(){
	var nbQuestOk = 0;
	var nbQuestion = $('input[name=nbQuestion]').val();
	var stringQuestion;
	
	$("input[name^=quest_]").each(function() {
		if($(this).val() != ""){
			nbQuestOk++;
		}
		stringQuestion += $(this).val() + '_' + $(this).attr('id') + '@';
	});
	
	if(nbQuestOk > 0 && nbQuestOk < nbQuestion){
		//$('#alert').children('ul').append('<li>Veuillez remplir tous les champs "Questionnaire"</li>')
		//alert('Veuillez remplir tous les champs "Questionnaire"');
		return false;
	}else{
		
		if($("#date_questionnaire").val() != "" || nbQuestOk == 0){
			
			return true;
		}else{
			//$('#alert').children('ul').append('<li>Veuillez saisir la date du questionnaire</li>');
			return false;
		}
	}
}

function checkObservation(){
	var nbObsOk = 0;
	var nbObsTotOk = 0;
	var nbObservation = $('input[name=nbObservation]').val();
	
	$("input[name^=capac_]").each(function() {
		if($(this).val() != ""){
			nbObsOk++;
		}
	});
	
	$("input[name^=capacTotal_]").each(function() {
		if($(this).val() != ""){
			nbObsTotOk++;
		}
	});
	if(nbObsOk > 0 && nbObsOk < nbObservation){
		//$('#alert').children('ul').append('<li>Veuillez remplir tous les champs "Observation"</li>');
		return false;
	}else{
		if(nbObsTotOk > 0 && nbObsTotOk < nbObservation){
			$('#alert').children('ul').append('<li>Veuillez remplir tous les champs "Observation"</li>');
			return false;
		}else{
			if($("#date_observation").val() != "" || nbObsOk == 0 || nbObsTotOk == 0){
				return true;
			}else{
				$('#alert').children('ul').append('<li>Veuillez saisir la date d\'observation</li>');
				return false;
			}
		}
	}
}

function checkEntretien(){
	var nbEntretienOk = 0;
	var nbEntretien = $('input[name=nbEntretien]').val();
	$("select[name^=entretien_]").each(function() {
		
		if($(this).val() != ""){
			nbEntretienOk++;
		}
	});
	
	if(nbEntretienOk > 0 && nbEntretienOk < nbEntretien){
		$('#alert').children('ul').append('<li>Veuillez remplir tous les champs "Entretien"</li>');
		return false;
	}else{
		
		if($("#date_entretien").val() != "" || nbEntretienOk == 0){
			return true;
		}else{
			$('#alert').children('ul').append('<li>Veuillez saisir la date de l\'entretien</li>');
			return false;
		}
	}
}

function saveQuestionnaire(next, arg, redirect){
	var stringQuestion = new String();
	$("input[name^=quest_]").each(function() {
		stringQuestion += $(this).val() + '_' + $(this).attr('id') + '@';
	});
	
	var resultat_id = $('input[name=resultat_id]').val();
	var date_questionnaire = $('#date_questionnaire').val();
	//alert(stringQuestion);
	$.ajax({
		url:baseUrl+'/saisie/insert/',
		type:'post',
		
		cache:false,
		data:'resultat_id='+resultat_id+'&outil_id='+'&value='+stringQuestion+'&date='+date_questionnaire+'&outil=2',
		success: function(response){
			$("#questLoader").attr('src', baseUrl+'/img/complete.gif');
			
			if(next != undefined){
				if(arg != undefined){
					if(redirect != undefined){
						next(arg, redirect);
					}else{
						next(arg);
					}
				}else{
					if(redirect != undefined){
						next(undefined, redirect);
					}else{
						next();
					}
				}
			}else{
				setTimeout("$(\"#overlaySave\").fadeOut('normal')", 1000);
				if(redirect != undefined){
					setTimeout("document.location.href = '"+redirect+"'", 1000);
				}
			}
			return true;
			
			//$("#overlaySave").fadeOut('normal');
		}
	});
}

function saveObservation(next, redirect){
	var stringObservation = new String();
	
	
	$("input[name^=capac_]").each(function() {
		stringObservation += $(this).val() + '_' + $("input[name=capacTotal_"+$(this).attr('id')+"]").val() + '@';
	});
	
	var candidat_metier_id = $('input[name=candidat_metier_id]').val();
	var date_observation = $('#date_observation').val();
	//alert(stringQuestion);
	$.ajax({
		url:baseUrl+'/saisie/insert/',
		type:'post',
		
		cache:false,
		data:'candidat_metier_id='+candidat_metier_id+'&value='+stringObservation+'&date='+date_observation+'&outil=3',
		success: function(response){
			$("#obsLoader").attr('src', baseUrl+'/img/complete.gif');
			if(next != undefined){
				if(redirect != undefined){
					next(redirect);
				}else{
					next();
				}
			}else{
				setTimeout("$(\"#overlaySave\").fadeOut('normal')", 1000);
				if(redirect != undefined){
					setTimeout("document.location.href = '"+redirect+"'", 1000);
				}
			}
			return true;
		}
	});
}

function saveEntretien(redirect){
	var stringEntretien = new String();
	
	
	$("select[name^=entretien_]").each(function() {
		stringEntretien += $(this).val() + '@';
	});
	
	var candidat_metier_id = $('input[name=candidat_metier_id]').val();
	var date_entretien = $('#date_entretien').val();
	//alert(stringQuestion);
	$.ajax({
		url:baseUrl+'/saisie/insert/',
		type:'post',
		
		cache:false,
		data:'candidat_metier_id='+candidat_metier_id+'&value='+stringEntretien+'&date='+date_entretien+'&outil=4',
		success: function(response){
			$("#entLoader").attr('src', baseUrl+'/img/complete.gif');
			setTimeout("$(\"#overlaySave\").fadeOut('normal')", 1000);
			if(redirect != undefined){
				setTimeout("document.location.href = '"+redirect+"'", 1000);
			}
			return true;
		}
	});
}

function setValues(validQuest, validObs, validEnt, listeCand){
	
	if($("#date_livret").val() != ""){
		var candidat_metier_id = $('input[name=candidat_metier_id]').val();
		var date_livret = $('#date_livret').val();
		$.ajax({
			url:baseUrl+'/saisie/insert/',
			type:'post',
			cache:false,
			data:'candidat_metier_id='+candidat_metier_id+'&date='+date_livret+'&outil=1',
			success: function(response){
				return true;
			}
		});
	}
	
	$("#overlaySave").fadeIn('fast');
	if(validQuest){
		if(validObs){
			validObs = false;
			if(validEnt){
				//validEnt = false;
				saveQuestionnaire(saveObservation, saveEntretien, listeCand);
				return true;
			}else{
				saveQuestionnaire(saveObservation, undefined, listeCand);
				return true;
			}
		}else{
			if(validEnt){
				//validEnt = false;
				saveQuestionnaire(saveEntretien, undefined, listeCand);
				return true;
			}else{
				saveQuestionnaire(undefined, undefined, listeCand);
				return true;
			}
		}
	}
	
	if(validObs){
		if(validEnt){
			//validEnt = false;
			saveObservation(saveEntretien, listeCand);
			return true;
		}else{
			saveObservation(undefined, listeCand);
			return true;
		}
	}
	
	if(validEnt){
		saveEntretien(listeCand);
		return true;
	}
	
	
}

$(document).ready(function(){
	var detectedModif = new Array();
	
	detectedModif['quest'] = false;
	detectedModif['obs'] = false;
	detectedModif['ent'] = false;
	detectedModif['livret'] = false;
	
	$('#alert').bind( "dialogclose", function(event, ui) {
		$('#alert').children('ul').html('');
	});
	
	$("#contentForm").tabs();
	$("#goFiche").button({ icons: {primary:'ui-icon-arrowrefresh-1-w'} });
	$("#saveNotes").button({ icons: {primary:'ui-icon-disk'} });
	$("input[name^=quest_]").keyup(function(){
		var total = new Number($(this).attr('id'));
        var currentVal = new Number($(this).val());
		//alert($(this).val());
		if(currentVal < 0){
			$(this).val('');
		}else if(currentVal > total){
			$(this).val('');
		}
		
	});
	
	$("#date_questionnaire").change(function(){
		detectedModif['quest'] = true;
	});
	
	$("#date_observation").change(function(){
		detectedModif['obs'] = true;
	});
	
	$("#date_entretien").change(function(){
		detectedModif['ent'] = true;
	});
	
	$("input[name^=quest_]").change(function(){
		detectedModif['quest'] = true;
	});
	
	$("input[name^=capac_]").change(function(){
		detectedModif['obs'] = true;
	});
	
	$("select[name^=entretien_]").change(function(){
		detectedModif['ent'] = true;
	});
	
	$("#date_livret").change(function(){
		detectedModif['livret'] = true;
	});
	
	$("#listCand").change(function(){
		var validQuest;
		var validObs;
		var validEnt;
		
		$("#overlaySave").children('div').children('#elements').html(" ");
		
		if(detectedModif['quest']){
			if(checkQuestionnaire()){
				validQuest = true;
				$("#overlaySave").children('div').children('#elements').append('<p>Questionnaire <img id="questLoader" src="'+baseUrl+'/img/ajax-loader_s.gif" /></p>');
			}else{
				validQuest = false;
			}
		}
		
		if(detectedModif['obs']){
			if(checkObservation()){
				validObs = true;
				$("#overlaySave").children('div').children('#elements').append('<p>Observation <img id="obsLoader" src="'+baseUrl+'/img/ajax-loader_s.gif" /></p>');
			}else{
				validObs = false;
			}
		}
		
		if(detectedModif['ent']){
			if(checkEntretien()){
				validEnt = true;
				$("#overlaySave").children('div').children('#elements').append('<p>Entretien <img id="entLoader" src="'+baseUrl+'/img/ajax-loader_s.gif" /></p>');
			}else{
				validEnt = false;
			}
		}
			
		if(validQuest == false || validObs == false || validEnt == false){
			$("#listCand").val($("input[name=candidat_id]").val());
			$('#alert').html('Les notes saisie sont incorrectes et/ou incomplètes.');
			$('#alert').dialog( {
				resizable:false,
				modal:true,
				title:"Saisie incorrect",
				buttons:{
					"Ok": function() {
						$(this).dialog("close");
						}
				}
			});
		}else{
			if(detectedModif['quest'] == true || detectedModif['obs'] == true || detectedModif['ent'] == true){
				detectedModif['quest'] = false;
				detectedModif['obs'] = false;
				detectedModif['ent'] = false;
				$('#alert').html('Des modifications ont été apporté.<br />Que voulez-vous faire ?');
				$('#alert').dialog( {
					width:"350px",
					resizable:false,
					modal:true,
					title:"Modification détecté",
					buttons:{
						"Sauvegarder": function() {
							$(this).dialog("close");
							setValues(validQuest, validObs, validEnt, baseUrl + '/saisie/index/metier/'+$("#listCand").val()+'/');
						},
						"Continuer": function() {
							$(this).dialog("close");
							document.location.href = baseUrl + '/saisie/index/metier/'+$("#listCand").val()+'/';
							},
						
						"Annuler": function() {
							$(this).dialog("close");
							$("#listCand").val($("input[name=candidat_id]").val());
							}
					}
				});
			}else if(detectedModif['livret']){
				var candidat_metier_id = $('input[name=candidat_metier_id]').val();
				var date_livret = $('#date_livret').val();
				$.ajax({
					url:baseUrl+'/saisie/insert/',
					type:'post',
					cache:false,
					data:'candidat_metier_id='+candidat_metier_id+'&date='+date_livret+'&outil=1',
					success: function(response){
						document.location.href = baseUrl + '/saisie/index/metier/'+$("#listCand").val()+'/';
						return true;
					}
				});
			}else{
				document.location.href = baseUrl + '/saisie/index/metier/'+$("#listCand").val()+'/';
			}
			
		}
		
	});
	
	$("#goFiche").click(function(){
		
		document.location.href = baseUrl + '/operations/visu/num/'+$('input[name=fiche_id]').val()+'/#ui-tabs-5';
	});
	
	$("#saveNotes").click(function(){
		var validQuest;
		var validObs;
		var validEnt;
		
		$("#overlaySave").children('div').children('#elements').html(" ");
		
		if(detectedModif['quest']){
			if(checkQuestionnaire()){
				validQuest = true;
				$("#overlaySave").children('div').children('#elements').append('<p>Questionnaire <img id="questLoader" src="'+baseUrl+'/img/ajax-loader_s.gif" /></p>');
			}else{
				validQuest = false;
			}
		}
		
		if(detectedModif['obs']){
			if(checkObservation()){
				validObs = true;
				$("#overlaySave").children('div').children('#elements').append('<p>Observation <img id="obsLoader" src="'+baseUrl+'/img/ajax-loader_s.gif" /></p>');
			}else{
				validObs = false;
			}
		}
		
		if(detectedModif['ent']){
			if(checkEntretien()){
				validEnt = true;
				$("#overlaySave").children('div').children('#elements').append('<p>Entretien <img id="entLoader" src="'+baseUrl+'/img/ajax-loader_s.gif" /></p>');
			}else{
				validEnt = false;
			}
		}
			
		if(validQuest == false || validObs == false || validEnt == false){
			$("#listCand").val($("input[name=candidat_id]").val());
			$('#alert').html('Les notes saisie sont incorrectes et/ou incomplètes.');
			$('#alert').dialog( {
				resizable:false,
				modal:true,
				title:"Saisie incorrect",
				buttons:{
					"Ok": function() {
						$(this).dialog("close");
						}
				}
			});
		}else{
			if(detectedModif['quest'] == true || detectedModif['obs'] == true || detectedModif['ent'] == true){
				detectedModif['quest'] = false;
				detectedModif['obs'] = false;
				detectedModif['ent'] = false;
				setValues(validQuest, validObs, validEnt);
						
			}else if(detectedModif['livret']){
				var candidat_metier_id = $('input[name=candidat_metier_id]').val();
				var date_livret = $('#date_livret').val();
				$.ajax({
					url:baseUrl+'/saisie/insert/',
					type:'post',
					cache:false,
					data:'candidat_metier_id='+candidat_metier_id+'&date='+date_livret+'&outil=1',
					success: function(response){
						return true;
					}
				});
			}else{
				$('#alert').html('Aucune modification détectée.');
				$('#alert').dialog( {
					resizable:false,
					modal:true,
					title:"Sauvegarde",
					buttons:{
						"Ok": function() {
							$(this).dialog("close");
							}
					}
				});
			}
			
		}
	});
	
});