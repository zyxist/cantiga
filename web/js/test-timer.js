(function($) {
	$('#test-timer').backward_timer({'seconds': $('#test-timer').data('time-min') * 60, format: 'm%:s%'});
	$('#test-timer').backward_timer('start');	
}(jQuery));