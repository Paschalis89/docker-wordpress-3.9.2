jQuery ( function () {

	var ajaxurl = my_ajax_object.ajax_url;
	jQuery.ajax({
		type: 'GET',
		url: ajaxurl,
		data: {
			action : 'uns_get_db_results',
			keyData: 'value'
		},
		success: function( response ) {
			var response =  JSON.parse ( response );
			jQuery.each ( response, function ( index, value ) {
				value = value.split (',')
				var selector =  value[0].trim();
				var selectorValue =  value[1].trim();
				var selectorUrl =  value[2].trim();
				var reverseValue = value[3].trim();
				build_jquery_selector( selector, selectorValue, selectorUrl, reverseValue );
			});
		},
		error: function(errorThrown){
		    console.log(errorThrown);
		}
	});

	function build_jquery_selector ( selector, selectorValue, selectorUrl, reverseValue ) {
		if ( reverseValue != 'on' ) {
			if ( selector == 'tag' ) {
				var val = jQuery(selectorValue).find ( 'a[href*="'+selectorUrl+'"]' );
				add_follow_nofollow( val );
			} else if ( selector == 'id' ) {
				var val = jQuery('#'+selectorValue).find ( 'a[href*="'+selectorUrl+'"]' );
				add_follow_nofollow( val );
			} else if ( selector == 'class' ) {
				var val = jQuery('.'+selectorValue).find ( 'a[href*="'+selectorUrl+'"]' );
				add_follow_nofollow( val );
			}
		} else if ( reverseValue == 'on' ) {
			if ( selector == 'tag' ) {
				var val = jQuery(selectorValue).find ('a').not('[href*="'+selectorUrl+'"]');
				add_follow_nofollow( val );
			} else if ( selector == 'id' ) {
				var val = jQuery('#'+selectorValue).find ('a').not('[href*="'+selectorUrl+'"]');
				add_follow_nofollow( val );
			} else if ( selector == 'class' ) {
				var val = jQuery('.'+selectorValue).find ('a').not('[href*="'+selectorUrl+'"]');
				add_follow_nofollow( val );
			}
		}
	}

	function add_follow_nofollow ( val ) {
		if ( val.length > 0 ) {
			if ( window.location.pathname !== "/" )
				val.attr('rel', 'nofollow');
		}
	}
});