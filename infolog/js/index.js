/**
 * Javascript used on the infolog index page
 */


/**
 * Javascript handling for multiple entry actions
 */
function do_infolog_action(selbox) {
	if(selbox.value == "") return;
	var prefix = selbox.id.substring(0,selbox.id.indexOf('['));
	switch(selbox.value) {
		case "link":
			var popup = document.getElementById(prefix+'[link_popup]');
			if(popup) {
				popup.style.display = 'block';
			}
			return;
		case "cat":
			var popup = document.getElementById(prefix+'[cat_popup]');
			if(popup) {
				popup.style.display = 'block';
			}
			return;
	}
	selbox.form.submit();
	selbox.value = "";
}

/**
 * Hide popup and clear values
 */
function hide_popup(element, div_id) {
	var prefix = element.id.substring(0,element.id.indexOf('['));
	var popup = document.getElementById(prefix+'['+div_id+']');
	if(popup) {
		popup.style.display = 'none';
	}
}
