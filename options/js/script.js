jQuery(function() {
	var btn = 'jQuery("#wpbody-content form input[name=apply]")';
	jQuery("#wpbody-content form input[name=apply]").on("click",function(){
		swal.queue([{
			title              : 'Are you sure?',
			text               : "You won't be able to revert this!",
			type               : 'warning',
			showCancelButton   : true,
			confirmButtonColor : '#3085d6',
			cancelButtonColor  : '#d33',
			confirmButtonText  : 'Yes, apply!',
			showLoaderOnConfirm: true,
			preConfirm: function() {
				return new Promise(function(resolve) {
					jQuery.ajax({
						url       : wto_options_data.ajax_url,
						dataType  : "json",
						data      : {
							nonce  : wto_options_data.nonce,
							action : wto_options_data.action,
						},
						type      : "post",
						beforeSend: function() {
							jQuery("#wpbody-content form input[name=apply]").prop('disabled', true);
							$html = '<p><strong class="processing">Processing.</strong></p>';
							jQuery('#setting-apply-settings_updated').html($html).fadeIn();
						}
					}).done(function(data) {

					}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
						alert("Load Error. Please Reload...");
					}).always(function(data) {
						$html = '<p><strong>Applied to the ' + data + ' posts.</strong></p>';
						jQuery('#setting-apply-settings_updated').html($html);
						// For sweetalert2.js
						var str = 'Applied to ' + data + ' posts.';
						swal.insertQueueStep(str);
						resolve();
					});
				});
			}
		}]);

		return false;
	});
});
