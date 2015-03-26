$(document).ready(function(){
	$("a#link-1").parent().attr("id","actif");
	$("div#content-2").css("display","none");
	$("div#content-3").css("display","none");
	$("div#content-4").css("display","none");
});

function afficher(etape){
	for(i=1;i<=4;i++){
		if(etape==i){
			$("a#link-"+i).parent().attr("id","actif");
			$("div#content-"+i).css("display","block");
		}else{
			$("a#link-"+i).parent().removeAttr("id");
			$("div#content-"+i).css("display","none");
		}
	}
}