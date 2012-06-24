// jquery functions for admin panel
 
jQuery(document).ready(function($) {    		    
	//Toggle sub menu options
	$('.wpbook_hidden').hide();
	$(':checkbox').change(function() {
		changeCheck(this);
		});
	$(':checkbox:checked').each(function(){
		changeCheck(this);
		});
		
	function changeCheck(x) {
		var option = 'wpbook_option_' + $(x).attr('id');
		if ($('.' + option).is(':visible')) {
			$('.' + option).fadeOut();
		}
		else {
			$('.' + option).fadeIn();
		}
	}
});