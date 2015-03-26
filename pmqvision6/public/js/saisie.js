$(document).ready(function(){
	
	$("#contentForm").tabs();

	$("#listCand").change(function(){
		document.location.href = baseUrl + '/saisie/index/metier/'+$("#listCand").val()+'/';
	});
	
});