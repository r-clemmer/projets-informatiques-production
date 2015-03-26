var dateFirstDay;
var dateLastDay;
var authId;	

function setEntityId(id){
	authId = id;
}


function getMois(i){
	var mois = new Array("01","02","03","04","05","06","07","08","09","10","11","12");
	
	return mois[i];
}
function getJour(i){
	
	if(i == 1){
		return '01';
	}else {
		return formatJour(i-1);
	}
}
function formatJour(i){
	var jour = new Array("00","01","02","03","04","05","06","07","08","09");
	if(i<10){
		return jour[i];
	}else {
		return i;
	}
}
function activateTooltip(x, y){
	var pos = $('#calendar').position();
	var side = 'gauche';
	var imgSource = '';
	
	if(x > 375){
		side = 'droite';
	}
	
	if(side == 'gauche'){
		$("#tips_haut").css('background-image','url('+baseUrl+'/img/tooltip_hautGauche.png)');
		if($.browser.msie){
			var X = pos.left+x-23;
			var Y = pos.top+y+60;
		}else {
			var X = pos.left+x-23;
			var Y = pos.top+y+45;
		}
	}else {

		$("#tips_haut").css('background-image','url('+baseUrl+'/img/tooltip_hautDroite.png)');

		if($.browser.msie){
			var X = pos.left+x-300;
			var Y = pos.top+y+60;
		}else {
			var X = pos.left+x-285;
			var Y = pos.top+y+45;
		}
	}
	
	$('#tooltips').css('left', X);
	$('#tooltips').css('position', 'absolute');
	$('#tooltips').css('top', Y);
	$('#tooltips').fadeIn('slow');
}
function desactiveTooltip(){
	$('#tooltips').fadeOut('fast');
}

function updateCalendar(tabEvents){
	$('#calendar').fullCalendar( 'removeEvents');
	//$('#calendar').fullCalendar( 'removeEventSource', tabEvents);
	$('#calendar').fullCalendar( 'addEventSource', tabEvents);
	$('#refresh').css('display','none');
}


function getDatesOperations(dateDeb,dateFin){
	$('#refresh').css('display','block');
	var listeEvents= new Array();
	var reqFinish = false;
	$.ajax({
 	   type: "POST",
 	   url: baseUrl+"/calendrier/getdatesoperations",
 	   data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
 	   async: false,
 	   success: function(msg){
	    	var liste = msg.split('*');
	    	
	    	for(op in liste){
		    	var champs = liste[op].split('/');
	    		listeEvents.push({title : champs[1]+' opération(s)',start  : champs[0],className : 'op',description:champs[0]});
	    	}	   
 	   },
 	  complete: function() {
 		  reqFinish = true;
 		  
 		}
 	 });
	while(reqFinish != true){};
	return listeEvents;
}
function getDatesDossier(dateDeb,dateFin){
	$('#refresh').css('display','block');
	var listeEvents= new Array();
	var reqFinish = false;
	$.ajax({
 	   type: "POST",
 	   url: baseUrl+"/calendrier/getdatesenvoidossier",
 	  data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
 	   async: false,
 	   success: function(msg){

	    	var liste = msg.split('*');
	    	
	    	for(op in liste){
		    	var champs = liste[op].split('/');
	    		listeEvents.push({id:champs[0],title : champs[2]+' dossier(s)',start  : champs[1],className : 'dossier',description:champs[1]});
	    	}

		},
		  complete: function() {
			  reqFinish = true;
			}
		 });
	while(reqFinish != true){};
	return listeEvents;
}
function getDatesJury(dateDeb,dateFin){

	$('#refresh').css('display','block');
	var listeEvents= new Array();
	var reqFinish = false;
	
	$.ajax({
 	   type: "POST",
 	   url: baseUrl+"/calendrier/getdatesjury",
 	  data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
 	   async: false,
 	   success: function(msg){
			
	    	var liste = msg.split('*');
	    	
	    	for(op in liste){
		    	var champs = liste[op].split('/');
	    		listeEvents.push({id:champs[0],title : champs[0]+" jury(s)",start  : champs[1],className : 'jury',description:champs[1]});
	    	}
	},
	  complete: function() {
		  reqFinish = true;
		}
	 });
	
while(reqFinish != true){};
return listeEvents;
}
function getDatesOutils(type,dateDeb,dateFin){

	$('#refresh').css('display','block');
	var listeEvents= new Array();
	var reqFinish = false;
	
	 $.ajax({
  	   type: "POST",
  	   async: false,
  	   url: baseUrl+"/calendrier/getdatesoutil",
  	   data: "type="+type+"&debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
  	   success: function(msg){
	    	

	    	var liste = msg.split('*');
	    	
	    	for(op in liste){
		    	var champs = liste[op].split('/');
	    		listeEvents.push({id:champs[0],title : champs[2]+" "+champs[0]+"(s)",start  : champs[1],className : 'outils',description:champs[1]});
	    	}
  	   },
 	  complete: function() {
 		  reqFinish = true;
 		}
 	 });
	
while(reqFinish != true){};
return listeEvents;
}

function getAllDates(type,dateDeb,dateFin){
	var listeEvents= new Array();
	
	switch (type){
		case 'cles':
			//OPERATIONS
			$.ajax({
		 	   type: "POST",
		 	   url: baseUrl+"/calendrier/getdatesoperations",
		 	   data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
		 	   success: function(msg){
			    	var liste = msg.split('*');
			    	
			    	for(op in liste){
				    	var champs = liste[op].split('/');
			    		listeEvents.push({title : champs[1]+' opération(s)',start  : champs[0],className : 'op',description:champs[0]});
			    	}	   
			    	//ENVOI DOSSIER
			    	$.ajax({
			    	 	  type: "POST",
			    	 	  url: baseUrl+"/calendrier/getdatesenvoidossier",
			    	 	  data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
			    	 	  success: function(msg){

		    		    	var liste = msg.split('*');
		    		    	
		    		    	for(op in liste){
		    			    	var champs = liste[op].split('/');
		    		    		listeEvents.push({id:champs[0],title : champs[2]+' dossier(s)',start  : champs[1],className : 'dossier',description:champs[1]});
		    		    	}
		    		    	
		    		    	$.ajax({
			    		    	 type: "POST",
			    		    	 url: baseUrl+"/calendrier/getdatesjury",
			    		    	 data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId,
					    	 	 success: function(msg){
		    		    			
				    		    	var liste = msg.split('*');
				    		    	
				    		    	for(op in liste){
				    			    	var champs = liste[op].split('/');
				    		    		listeEvents.push({id:champs[0],title : champs[0]+" jury(s)",start  : champs[1],className : 'jury',description:champs[1]});
				    		    	}
				    		    	updateCalendar(listeEvents);					    		    	
					    		}
		    		    	});

			    			}
			    	});
		 	   }
			});
		break;
		
		case 'cqp':
			listeEvents = getAllDatesOutils(type,dateFirstDay,dateLastDay);
			updateCalendar(listeEvents);
		break;
			
		case 'diplome':
			listeEvents = getAllDatesOutils(type,dateFirstDay,dateLastDay);
			updateCalendar(listeEvents);
		break;
			
		case 'ccsp':
			listeEvents = getAllDatesOutils(type,dateFirstDay,dateLastDay);
			updateCalendar(listeEvents);
		break;
	
	}
}

function getAllDatesOutils(type,dateDeb,dateFin){
	$('#refresh').css('display','block');
	var listeEvents= new Array();
	var reqFinish = false;
	
	 $.ajax({
  	   type: "POST",
  	   async: false,
  	   url: baseUrl+"/calendrier/getalldatesoutil",
  	   data: "debut="+dateDeb+"&fin="+dateFin+"&id="+authId+"&type="+type,
  	   success: function(msg){
	    	var liste = msg.split('*');
	    	
	    	for(op in liste){
		    	var champs = liste[op].split('/');
	    		listeEvents.push({id:champs[0],title : champs[2]+" "+champs[3]+"(s)",start  : champs[1],className : 'outils',description:champs[1]});
	    	}
  	   },
 	  complete: function() {
 		  reqFinish = true;
 		}
 	 });
	
while(reqFinish != true){};
return listeEvents;
}

function afficheEvents(typeDate){
	switch(typeDate){
	
	case 'all':
		
		var typeOutil = $('#selectType option:selected').attr('class');
		
		displayAllEvents(typeOutil);
		
	break;
	case '2':
		var listeEvents = new Array();

		listeEvents = getDatesOperations(dateFirstDay,dateLastDay);
		updateCalendar(listeEvents);
	    
	break;
	case '3':
		var listeEvents =  new Array();

		listeEvents = getDatesDossier(dateFirstDay,dateLastDay);
		updateCalendar(listeEvents);
	break;
	
	case '4':
		var listeEvents =  new Array();
		
		listeEvents = getDatesJury(dateFirstDay,dateLastDay);
		updateCalendar(listeEvents);
	break;
	
	
	case 'outils':
		var idOutil = $('#selectType option:selected').attr('class');
		var listeEvents =  new Array();

		listeEvents = getDatesOutils(idOutil,dateFirstDay,dateLastDay);
		updateCalendar(listeEvents);
	break;

}	
}
function concatTab(tab1,tab2){
	var tailleTab1 = tab1.length;
	var tailleTab2 = tab2.length;

	var j = 0;
	
	for(i=tailleTab1;i<tailleTab1+tailleTab2;i++){
		tab1[i] = tab2[j];
		j++;
	}
	
	return tab1;
}
function displayAllEvents(type){
	$('#refresh').css('display','block');
	var listeEvents= new Array();

	getAllDates(type,dateFirstDay,dateLastDay);
}

$(document).ready(function() {
	var req;
	
	$('#closeToolTip').click(function(){
		desactiveTooltip();
	});
	$('#calendar').fullCalendar({
		theme: true,
		firstDay: 1,
		defaultView: 'month',
		weekMode: 'variable',
		header: {
			left: 'prev,next today',
			center: 'title',
			right: ''
		},
		disableDragging: true,
		disableResizing: true,
		editable: true,
		
		eventClick: function(calEvent, jsEvent, view) {
			
			var pos= $(this).position();
			$('.clickedEvent').removeClass('clickedEvent');
			$(this).children('a').addClass("clickedEvent");
			activateTooltip(pos.left, pos.top);
			
			$('#tooltips').children('#tips_content').html("<img style='margin-left:50px;' src='"+baseUrl+"/img/ajax-loader.gif' />");
			
			if(req)	req.abort();
			
			req = $.ajax({
	    	   type: "POST",
	    	   url: baseUrl+"/calendrier/getinfosdate",
	    	   data: "type="+calEvent.className+"&id="+calEvent.id+"&date="+calEvent.description+"&auth="+authId,
	    	   success: function(msg){
					$('#tooltips').children('#tips_content').html(msg);
	    	 	}
			});		
	    },
	    dayClick: function(date, allDay, jsEvent, view) {
	    	
	    	desactiveTooltip();
	    },
	    viewDisplay: function(view) {
	    	
	    	var numMois = view.start.getMonth()+1;
	    	var annee = view.start.getFullYear();
	    	var anneeMin = annee - 5;
	    	var anneeMax = annee + 5;
	    	
	    	var htmlAnnee = "";
	    	for(var i=anneeMin;i<=anneeMax;i++){
	    		htmlAnnee += "<option value="+i+">"+i+"</option>";
	    	}
	    	$('#selectAnnee').html(htmlAnnee);
	    	
	    	
	    	
	    	$('#selectMois option[value='+numMois+']').attr("selected", true);
	    	$('#selectAnnee option[value='+annee+']').attr("selected", true);
	    	
	    	
	    	
	    	dateFirstDay = view.visStart.getFullYear()+"-"+getMois(view.visStart.getMonth())+"-"+getJour(view.visStart.getDate());//YYYY-MM-JJ
	    	dateLastDay = view.visEnd.getFullYear()+"-"+getMois(view.visEnd.getMonth())+"-"+formatJour(view.visEnd.getDate());//YYYY-MM-JJ
	    	
	    	var typeDate = $('#selectType').val();
	    	afficheEvents(typeDate);	
	    	desactiveTooltip();
	    }

	});
	
	$('#selectType').change(function(){
		desactiveTooltip();
		var typeDate = $(this).val();
		$('#refresh').css('display','block');
		afficheEvents(typeDate);			
	});
	$('#selectMois').change(function(){
		desactiveTooltip();
		var moisDate = $(this).val();
		if(moisDate != 0){
			moisDate = moisDate - 1;
			var anneeDate = $('#selectAnnee').val();
			$('#calendar').fullCalendar( 'gotoDate', anneeDate,moisDate);
		}
	});
	$('#selectAnnee').change(function(){
		desactiveTooltip();
		var anneeDate = $(this).val();
		var moisDate = $('#selectMois').val();
		if(moisDate != 0){
			moisDate = moisDate-1;
			$('#calendar').fullCalendar( 'gotoDate', anneeDate,moisDate);
		}	
	});
	
});