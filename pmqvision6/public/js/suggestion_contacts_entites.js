$(document).ready( function () { // lorsque la page est entierement chargée
	$("input#inputString").attr('autocomplete', 'off');
	$("input#inputString").keyup( function() { // si on presse une touche du clavier en étant dans le champ texte qui a pour id inputString
		lookup($(this).val());
	});
});

function lookup(inputString) {
	
	if(inputString.length == 0) { // si le champs txte est vide
		$('#suggestions').hide(); // on cache les suggestions
	} else { // sinon
		$.post(baseUrl+"/ajax/autocompletioncontactsentites", {queryString: ""+inputString+""}, function(data){ // on envoit la valeur du champ texte dans la variable post queryString au fichier ajax.php
			if(data.length >0) {
				$('#suggestions').show(); // si il y a un retour, on affiche la liste
				$('#autoSuggestionsList').html(data); // et on remplit la liste des données
			}
		});
	}
	
}

function fill(thisValue, string) { // remplir le champ texte si une suggestion est cliquée
	
	$('#entite_id').val(thisValue);
	$('#inputString').val(string);
	$("span.error#entite_id").hide();
	
	$.post(
		baseUrl+"/ajax/getfonctionsbyentite",
		{entite_id : $("input#entite_id").val()},
		function(data){ // on envoit la valeur du champ texte dans la variable post queryString au fichier ajax.php
			if(data.length >0) {
				$('#fonctions').html(data); // et on remplit la liste des données
				$('#fonctions').removeAttr("disabled");
				$("span.error#fonctions").show();
			}
		}
	);
	
	setTimeout("$('#suggestions').hide();", 200);
	
}