function desactiveContextMenu(){
	menuContextActive = false;
}
var menuContextActive = true;

function loadFile(dir){
	//$('#refresh').css('display','block');
	
	$.ajax({
	   type: "POST",
	   url: baseUrl+'/outils/getfile/',
	   data: "dir="+dir,
	   success: function(msg){
			var files = msg.split('*');

			var html = "";
			for(i=0;i<(files.length)-1;i++){
				var fileName = files[i].substring(files[i].lastIndexOf('/')+1,files[i].length);
				ext = fileName.split('.');
				var listeExt = "bmp avi bat jar doc pdf txt css docx gif html ini iso java jpeg jpg mov movie mp3 mp4 mpeg pdd php png ppt pptx psd rar rtf tif tiff wave wma wmv xls xlsx xsl zip xml";
				var extension = ext[1];
				if(listeExt.indexOf(ext[1]) == -1){
					extension = ext[1];
					ext[1] = "Default";
					
				} 
				
				html += "<div class='fileBloc "+ext[1]+"' title='"+fileName+"'><a href='"+baseUrl+"/outils/download?dir="+files[i]+"' rel='"+files[i]+"' class='linkFileOnContent'><img src='"+baseUrl+"/img/icones/"+ext[1].toUpperCase()+".png' /><br class='clear' /><span>"+ext[0].substr(0, 15)+'.'+extension+"</span></a></div>";
				
			}
			$('#contentFolder').html(html);
			//alert(html);
			
			var hauteurContent = $('#contentFolder').height();
			$('#tree').css('min-height',hauteurContent);
			
	   },
	   complete : function(){
		   $('#refresh').css('display','none');
	   }
	});
}

$(document).ready( function() {
	
	$('#refresh').css('display','block');
	loadFile('./documents/ressources/');

	$("#action").css('display','block');
	
    $('#tree_content').fileTree({
        root: './documents/ressources/',
        script: baseUrl+'/outils/filetree/',
        expandSpeed: 100,
        collapseSpeed: 100,
        multiFolder: false
    }, function(file) {
    });
    

   $('#deleteCancel').click(function(){
	   $(".actionFile").hide();
   });
   
   $('.directory').livequery('mousedown', function(event) {
	   $(".actionFile").hide();
		var dir = $(this).children('a').attr('rel');
		loadFile(dir);		
   });
   
   $('#rootDir').livequery('mousedown', function(event) {
	   $('.expanded').removeClass('expanded').addClass('collapsed').children('ul').css('display','none');
	   $(".actionFile").hide();
	   loadFile('./documents/ressources/');
   });
   
   $('#rootDir').livequery('mouseover', function(event) {
	   if(menuContextActive== true){
	       var obj = $(this);
	       $(this).contextMenu({
	   	        menu: "myMenuDirRoot"
	   	    },
	   	    function(action, el, pos) {
	   	    	var dossierNom = obj.children('a').text();
	   	    	var dossierPath = obj.children('a').attr('rel');
	   	    	
	       	    switch(action){
		        	    case "upload":
			        	    $(".actionFile").hide(); 
			        	    $('#uploadFile').slideDown("slow");
			        	    $('#dir_pathUp').attr('value',dossierPath);
			        	    
		            	break;
		        	    case "add":
		        	    	$(".actionFile").hide();
		        	  	    $("#newDir").slideDown('slow');
		        	  	  $('#dir_newDir').attr('value',dossierPath);
			        	break;
	       	    }
	   	        
	   	    });
	       return false; 
	   }
   });
	// MENU CONTEXTUEL POUR LES DOSSIERS PRESENT DANS L'ARBRE
   $('.directory').livequery('mouseover', function(event) {
	   if(menuContextActive== true){
        var obj = $(this);
    	 $(this).contextMenu({
    	        menu: "myMenuDir"
    	    },
    	    function(action, el, pos) {
    	    	var dossierNom = obj.children('a').text();
    	    	var dossierPath = obj.children('a').attr('rel');
    	    	
        	    switch(action){
	        	    case "upload":
		        	    $(".actionFile").hide(); 
		        	    $('#uploadFile').slideDown("slow");
		        	    $('#dir_pathUp').attr('value',dossierPath);
		        	    
	            	break;
	        	    case "rename":
	        	    	$(".actionFile").hide();
	        	    	$('#renameDir').slideDown("slow");
	        	    	$('#dir_pathRename').attr('value',dossierPath);
		            break;
	        	    case "delete":
	        	    	$(".actionFile").hide();
	        	    	$('#deleteDir').slideDown("slow");
	        	    	$('#dir_pathDelete').attr('value',dossierPath);
		            break;
	        	    case "add":
	        	    	$(".actionFile").hide();
	        	  	    $("#newDir").slideDown('slow');
	        	  	  $('#dir_newDir').attr('value',dossierPath);
		        	break;
        	    }
    	        
    	    });
        return false; 
	   }
    });
	// MENU CONTEXTUEL POUR LES FICHIERS PRESENT DANS L'ARBRE
	$('.file').livequery('mouseover', function(event) { 
		
		if(menuContextActive== true){
		   	var obj = $(this);
		   	$(this).contextMenu({
		   		menu: "myMenuFile"
		   	},
		   	function(action, el, pos) {
		   		var dossierFile = obj.children('a').attr('rel');
		   		switch(action){
		   			case "down":
						window.location = baseUrl+"/outils/download?dir="+dossierFile;
		            break;
	        	    case "rename":
	        	    	$(".actionFile").hide();
	        	    	$('#renameFile').slideDown("slow");
	        	    	$('#fileDirRename').attr('value',dossierFile);
		            break;
	        	    case "delete":
	        	    	$(".actionFile").hide();
	        	    	$('#deleteFile').slideDown("slow");
	        	    	$('#fileDelete').attr('value',dossierFile);
		            break;
	        	}
		   	});
		   return false; 
		}
	});
	// MENU CONTEXTUEL POUR LES FICHIERS
 	$('.fileBloc').livequery('mouseover', function(event) { 
 		if(menuContextActive== true){
	    	var obj = $(this);
			   	$(this).contextMenu({
			   		menu: "myMenuFile"
			   	},
			   	function(action, el, pos) {
			   		var dossierFile = obj.children('a').attr('rel');
			   		switch(action){
			   			case "down":
							 window.location = baseUrl+"/outils/download?dir="+dossierFile;
			            break;
		        	    case "rename":
		        	    	$(".actionFile").hide();
		        	    	$('#renameFile').slideDown("slow");
		        	    	$('#fileDirRename').attr('value',dossierFile);
			            break;
		        	    case "delete":
		        	    	
		        	    	$(".actionFile").hide();
		        	    	$('#deleteFile').slideDown("slow");
		        	    	//$('#fileDelete').attr('value',dossierFile);
			            break;
	    	    	}
			   	});
		   return false; 
 		}
	});


});