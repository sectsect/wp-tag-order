jQuery(document).ready(function() {
	var removeElements = function(text, selector) {
		var wrapped = jQuery("<div>" + text + "</div>");
		wrapped.find(selector).remove();
		return wrapped.html();
	}
	/*==================================================
		Sync Tags to "#tagsdiv-post_tag" box
	================================================== */
	var flag = "";
	setTimeout(function(){
	    jQuery("[id^='tagsdiv-']").find('.tagchecklist').on('DOMSubtreeModified propertychange', function() {
			setTimeout(jQuery.proxy(function(){
				var cont = jQuery(this).html();
				if(cont == flag) {
			        return;		//prevent multiple simultaneous triggers
			    }
				flag = cont;

				var postboxid = jQuery(this).closest('.postbox').attr('id');
				var taxonomy = postboxid.replace('tagsdiv-', '');
				var removedBtnString = removeElements(cont, "button");
				var ary = new Array();
				jQuery(removedBtnString).filter('span').each(function(){
					ary.push(jQuery(this).text().substr(1));
				});
				var string = ary.join();
				jQuery.ajax({
					url          : wto_data.plugin_dir_url + "includes/sync-tags.php",
					dataType     : "json",
					data         : {id : wto_data.post_id, nonce : wto_data.nonce, taxonomy : taxonomy, tags : string},
					type         : "post",
					beforeSend   : function(){
						jQuery("#tagsdiv-" + taxonomy + " h2, #wpto_meta_box-" + taxonomy + " h2").addClass("ready");
					}
				}).done(function(data){
					jQuery("#wpto_meta_box-" + taxonomy + " .inside .inner ul").html(data);
				}).fail(function(XMLHttpRequest, textStatus, errorThrown){
					alert("Load Error. Please Reload...");
				}).always(function(data){
					setTimeout(function(){
						jQuery("#tagsdiv-" + taxonomy + " h2, #wpto_meta_box-" + taxonomy + " h2").removeClass("ready");
					}, 300);
				});
			}, this), 20);
	    });
	}, 400);

	/*==================================================
		jQuery UI Sortable
	================================================== */
	jQuery(".wpto_meta_box_panel .inside .inner ul").sortable({
		update:function(event, ui){
			var postboxid = jQuery(this).closest('.postbox').attr('id');
			var taxonomy = postboxid.replace('wpto_meta_box-', '');

			var ary = new Array();
			jQuery(this).find('input[type="hidden"]').each(function(){
				var tag = jQuery(this).val();
				ary.push(tag);
		    });
			var string = ary.join();
			jQuery.ajax({
				url          : wto_data.plugin_dir_url + "includes/ajax-update.php",
				dataType     : "json",
				data         : {id : wto_data.post_id, nonce : wto_data.nonce, taxonomy : taxonomy, tags : string},
				type         : "post",
				beforeSend   : function(){
					jQuery("#wpto_meta_box-" + taxonomy + " h2").addClass("ready");
				}
			}).done(function(data){

			}).fail(function(XMLHttpRequest, textStatus, errorThrown){
				alert("Load Error. Please Reload...");
			}).always(function(data){
				setTimeout(function(){
				    jQuery("#wpto_meta_box-" + taxonomy + " h2").removeClass("ready");
				}, 300);
			});
	    }
	});
});
