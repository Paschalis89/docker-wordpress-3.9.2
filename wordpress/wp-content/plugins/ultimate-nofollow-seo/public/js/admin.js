
jQuery ( function () {
	var displayValue = sessionStorage.getItem('heygoto-packages-ad-display');
	if ( displayValue == 'none' )
		jQuery('.heygoto-packages-wrapper').hide();	
	else
		jQuery('.heygoto-packages-wrapper').removeClass('no-show');
	var ajaxUrl = my_ajax_object.ajax_url;

	jQuery('#hgt-seo-all-records').on('click', '#edit-form-value', function() {
		event.preventDefault();
		var id = jQuery(this).attr('href');
		jQuery('#update-form').slideDown(200);
		jQuery.ajax({
			url: ajaxUrl,
			data: {
				action: 'uns_get_element_by_id',
				value: id
			},
			success: function ( value ) {
				jQuery('#update-form')[0].reset();
				value = value.split (',')
				var selector =  value[0].trim();
				var selectorValue =  value[1].trim();
				var selectorUrl =  value[2].trim();
				var reverseValue = value[3].trim();
				jQuery('#uns-selector').val( selector);
				jQuery('#uns-element').val(selectorValue);
				jQuery('#uns-baseurl').val(selectorUrl);
				if ( reverseValue == 'on' )
					jQuery('#uns-reserve').attr('checked','checked');
				jQuery('#uns-id').val(id);
			}
		});
	});

	jQuery('#hgt-seo-all-records').on('click', '#delete-form-value', function() {
		event.preventDefault();
		var userInput = confirm("Are you sure you want to delete?");
		if ( userInput == true ) {
			var id = jQuery(this).attr('href');
			jQuery.ajax({
				url: ajaxUrl,
				data: {
					action: 'delete_element_by_id',
					id : id
				},
				success: function(response){
					if ( response == 'success')
						window.location.reload();
				}
			});
		}
		
	});

	jQuery('#uns-form-update-btn').on('click', function(){
		event.preventDefault();
		var formData = jQuery('#update-form').serialize();
		jQuery.ajax({
			url: ajaxUrl,
			data: {
				action: 'uns_update_form_values',
				values: formData
			},
			success: function () {
				window.location.reload();
			}
		});
	});


	jQuery('#uns-form-cancel-btn').on('click', function(){
		event.preventDefault();
		jQuery('#update-form').slideUp(200);
	});
	
	jQuery('#close-heygoto-packages-wrapper').on('click', function() {
		sessionStorage.setItem('heygoto-packages-ad-display','none');
		jQuery('.heygoto-packages-wrapper').hide();	
	});
});