// Tiny MCE Paste Plugin
// Updated by speednet 25 May 2005  - IE converts and pastes without opening popup window

/* Import plugin specific language pack */ 
tinyMCE.importPluginLanguagePack('paste', 'en,sv'); 

function TinyMCE_paste_getControlHTML(control_name) { 
	switch (control_name) { 
		case "pastetext": 
			return '<img id="{$editor_id}pastetext" src="{$pluginurl}/images/pastetext.gif" title="{$lang_paste_text_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" onclick="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mcePasteText\');" />'; 

		case "pasteword": 
			return '<img id="{$editor_id}pasteword" src="{$pluginurl}/images/pasteword.gif" title="{$lang_paste_word_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" onclick="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mcePasteWord\');" />'; 

		case "selectall": 
			return '<img id="{$editor_id}selectall" src="{$pluginurl}/images/selectall.gif" title="{$lang_selectall_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" onclick="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceSelectAll\');" />';
	} 

	return ''; 
} 

function TinyMCE_paste_execCommand(editor_id, element, command, user_interface, value) { 
	switch (command) { 
		case "mcePasteText": 
			if (tinyMCE.isMSIE && tinyMCE.getParam('paste_use_dialog', false))
				TinyMCE_paste__insertText(clipboardData.getData("Text"), true); 
			else { 
				var template = new Array(); 
				template['file']	= '../../plugins/paste/pastetext.htm'; // Relative to theme 
				template['width']  = 450; 
				template['height'] = 400; 
				var plain_text = ""; 
				tinyMCE.openWindow(template, {editor_id : editor_id, plain_text: plain_text, resizable : "yes", scrollbars : "no", mceDo : 'insert'}); 
			}

			return true;

		case "mcePasteWord": 
			if (tinyMCE.isMSIE && tinyMCE.getParam('paste_use_dialog', false)) {
				var html = TinyMCE_paste__clipboardHTML();

				if (html && html.length > 0)
					TinyMCE_paste__insertWordContent(html);
			} else { 
				var template = new Array(); 
				template['file']	= '../../plugins/paste/pasteword.htm'; // Relative to theme 
				template['width']  = 450; 
				template['height'] = 400; 
				var plain_text = ""; 
				tinyMCE.openWindow(template, {editor_id : editor_id, plain_text: plain_text, resizable : "yes", scrollbars : "no", mceDo : 'insert'});
			}

		 	return true;

		case "mceSelectAll":
			tinyMCE.execInstanceCommand(editor_id, 'selectall'); 
			return true; 

	} 

	// Pass to next handler in chain 
	return false; 
} 

function TinyMCE_paste__insertText(content, bLinebreaks) { 

	if (content && content.length > 0) {
		if (bLinebreaks) { 
			// Special paragraph treatment 
			if (tinyMCE.getParam("plaintext_create_paragraphs", true)) { 
				content = tinyMCE.regexpReplace(content, "\r\n\r\n", "</p><p>", "gi"); 
				content = tinyMCE.regexpReplace(content, "\r\r", "</p><p>", "gi"); 
				content = tinyMCE.regexpReplace(content, "\n\n", "</p><p>", "gi"); 
	
				// Has paragraphs 
				if ((pos = content.indexOf('</p><p>')) != -1) { 
					tinyMCE.execCommand("Delete"); 
	
					var node = tinyMCE.selectedInstance.getFocusElement(); 
	
					// Get list of elements to break 
					var breakElms = new Array(); 

					do { 
						if (node.nodeType == 1) { 
							// Don't break tables and break at body 
							if (node.nodeName == "TD" || node.nodeName == "BODY") 
								break; 
	
							breakElms[breakElms.length] = node; 
						} 
					} while(node = node.parentNode); 
	
					var before = "", after = "</p>"; 
					before += content.substring(0, pos); 
	
					for (var i=0; i<breakElms.length; i++) { 
						before += "</" + breakElms[i].nodeName + ">"; 
						after += "<" + breakElms[(breakElms.length-1)-i].nodeName + ">"; 
					} 
	
					before += "<p>"; 
					content = before + content.substring(pos+7) + after; 
				} 
			} 
	
			content = tinyMCE.regexpReplace(content, "\r\n", "<br />", "gi"); 
			content = tinyMCE.regexpReplace(content, "\r", "<br />", "gi"); 
			content = tinyMCE.regexpReplace(content, "\n", "<br />", "gi"); 
		} 
	
		tinyMCE.execCommand("mceInsertRawHTML", false, content); 
	}
}

function TinyMCE_paste__insertWordContent(content) { 

	if (content && content.length > 0) {
		// Cleanup Word content
		content = content.replace(new RegExp('<(!--)([^>]*)(--)>', 'g'), "");  // Word comments
		content = content.replace(/<\/?span[^>]*>/gi, "");
		content = content.replace(/<(\w[^>]*) style="([^"]*)"([^>]*)/gi, "<$1$3");
		content = content.replace(/<\/?font[^>]*>/gi, "");
		content = content.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");
		content = content.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");
		content = content.replace(/<\\?\?xml[^>]*>/gi, "");
		content = content.replace(/<\/?\w+:[^>]*>/gi, "");
		content = content.replace(/\/?&nbsp;*/gi, "");
		content = content.replace('<p>&nbsp;</p>', '' ,'g');

		if (!tinyMCE.settings['force_p_newlines']) {
			content = content.replace('', '' ,'gi');
			content = content.replace('</p>', '<br /><br />' ,'gi');
		}
	
		if (!tinyMCE.isMSIE && !tinyMCE.settings['force_p_newlines']) {
			content = content.replace(/<\/?p[^>]*>/gi, "");
		}
	
		content = content.replace(/<\/?div[^>]*>/gi, "");
	
		// Insert cleaned content
		tinyMCE.execCommand("mceAddUndoLevel");
		tinyMCE.execCommand("mceInsertContent", false, content);
	}
}

function TinyMCE_paste__clipboardHTML() {
	var div = document.getElementById('_TinyMCE_clipboardHTML');

	if (!div) {
		var div = document.createElement('DIV');
		div.id = '_TinyMCE_clipboardHTML';

		with (div.style) {
			visibility = 'hidden';
			overflow = 'hidden';
			position = 'absolute';
			width = 1;
			height = 1;
		}

		document.body.appendChild(div);
	}

	div.innerHTML = '';
	var rng = document.body.createTextRange();
	rng.moveToElementText(div);
	rng.execCommand('Paste');
	var html = div.innerHTML;
	div.innerHTML = '';
	return html;
}

