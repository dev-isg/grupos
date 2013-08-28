
jQuery.noConflict()(function($){
	$(window).resize(function() {

		var $container = $('#content');

		$container.imagesLoaded(function(){
			$container.masonry();
		});
	});
});