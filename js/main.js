glimmer = {
	
	init: function() {
		glimmer.backgroundProcess.init();
	},
	
	
	
	backgroundProcess: {
		init: function(){
			$('a.background').live('click', function(){
				var link = $(this);
				var loader = $('<img/>');
				
				loader.attr('src', '/wp-content/plugins/glimmer/img/dots-white.gif');
				
				$.get(link.attr('href'), function(){
					window.location.reload();
				});
				
				loader.insertAfter(link);
				link.remove();
				
				return false;
			});
		},
		
	},
	
};


$ = jQuery;
$(document).ready(function(){
	glimmer.init();
});