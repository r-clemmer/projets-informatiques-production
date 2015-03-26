$(document).ready(function(){
	
	$("a.button").button();
	
	$("a.button.add").button({
	    icons: {
	        primary: 'ui-icon-circle-plus'
	    },
	    text: true
	});
	
	$("a.button.details").button({
	    icons: {
	        primary: 'ui-icon-zoomin'
	    },
	    text: false
	});
	
	$("a.button.modif").button({
	    icons: {
	        primary: 'ui-icon-pencil'
	    },
	    text: true
	});
	
	$("a.button.delete").button({
	    icons: {
	        primary: 'ui-icon-trash'
	    },
	    text: true
	});
	
	$("a.button.record").button({
	    icons: {
	        primary: 'ui-icon-disk'
	    },
	    text: true
	});
	
	$("a.button.return").button({
	    icons: {
	        primary: 'ui-icon-arrowrefresh-1-w'
	    },
	    text: true
	});
	
	$("a.button.logout").button({
	    icons: {
	        primary: 'ui-icon-circle-close'
	    },
	    text: true
	});
	
	$("a.button.attribute").button({
	    icons: {
	        primary: 'ui-icon-plusthick'
	    },
	    text: true
	});
	
	$("a.button.home").button({
	    icons: {
	        primary: 'ui-icon-home'
	    },
	    text: true
	});	
	$("a.button.wrench").button({
	    icons: {
	        primary: 'ui-icon-wrench'
	    },
	    text: false
	});
	$("a.button.document").button({
	    icons: {
	        primary: 'ui-icon-document'
	    },
	    text: true
	});
	$("a.button.search").button({
	    icons: {
	        primary: 'ui-icon-zoomin'
	    },
	    text: true
	});
	$("a.button.script").button({
	    icons: {
	        primary: 'ui-icon-script'
	    },
	    text: true
	});
	$("a.button.print").button({
	    icons: {
	        primary: 'ui-icon-print'
	    },
	    text: true
	});
	$("a.button.mail").button({
	    icons: {
	        primary: 'ui-icon-mail-closed'
	    },
	    text: true
	});
	$("a.button.refresh").button({
		icons:{
			primary: 'ui-icon-refresh'
		},
		text: false
	});
});