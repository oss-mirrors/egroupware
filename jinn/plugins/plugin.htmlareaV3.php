<?php
	/*************************************************************************\
	* phpGroupWare - HTMLAREA-form-plugin for phpGW-jinn                      *
	* The original script is written by interactivetools.com, inc.            *
	* Ported to phpGW by Pim Snel info@lingewoud.nl                           *
	* --------------------------------------------                            *
	* http://www.phpgroupware.org                                             *
	* http://www.interactivetools.com/                                        *
	* http://www.lingewoud.nl                                                 *
	* --------------------------------------------                            *
	* The original script HTMLAREA is distributed under a Open Source-licence *
	* See the readme.txt in the htmlarea-directory for the complete licence   *
	* text.                                                                   *
	* phpGroupWare and the jinn are free software; you can                    *
	* redistribute it and/or modify it under the terms of the GNU General     *
	* Public License as published by the Free Software Foundation; either     *
	* version 2 of the License, or (at your option) any later version.        *
	\*************************************************************************/

	$description = '
	the htmlAreaV3 plugin is based on htmlArea v3 from interactivetools.com
	licenced under the BSD licence.<P>
	htmlArea is a WYSIWYG editor replacement for any textarea field. Instead
	of teaching your software users how to code basic HTML to format their
	content.<P>
	Known issues: Only works with one rich textbox at a time in IE, bugfixes
	are very welcome';

	$this->plugins['htmlAreaV3']['name']			= 'htmlAreaV3';
	$this->plugins['htmlAreaV3']['title']			= 'htmlArea v3';
	$this->plugins['htmlAreaV3']['version']			= '0.8.2';
	$this->plugins['htmlAreaV3']['enable']			= 1;
	$this->plugins['htmlAreaV3']['description']		= $description;
	$this->plugins['htmlAreaV3']['db_field_hooks']	= array
	(
		'blob',
		'text'
	);

	$this->plugins['htmlAreaV3']['config']		= array
	(
		'enable_font_buttons'=>array(array('Yes','No'),'select',''),
		'enable_alignment_buttons'=>array(array('Yes','No'),'select',''),
		'enable_list_buttons'=>array(array('Yes','No'),'select',''),
		'enable_html_source_button'=>array(array('Yes','No'),'select',''),
		'enable_tables_button'=>array(array('Yes','No'),'select',''),
		'enable_image_button'=>array(array('Yes','No'),'select',''),
		'enable_color_buttons'=>array(array('Yes','No'),'select',''),
		'enable_horizontal_ruler_button'=>array(array('Yes','No'),'select',''),
		'enable_fullscreen_editor_button'=>array(array('Yes','No'),'select',''),
		'enable_link_button'=>array(array('Yes','No'),'select','')
	);

	function plg_fi_htmlAreaV3($field_name, $value, $config)
	{
		$editor_url=$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/htmlareaV3/';

		if($config[enable_image_button]!='No') $bar_image = '"insertimage",';
		if($config[enable_html_source_button]!='No') $bar_html = '"htmlmode",';
		if($config[enable_alignment_buttons]!='No') $bar_align = '[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator" ],';
		if($config[enable_font_buttons]!='No') $bar_font = '[ "fontname", "space" ], [ "fontsize", "space" ], [ "formatblock", "space"], 	[ "bold", "italic", "underline", "separator" ], [ "strikethrough", "subscript", "superscript", "linebreak" ],';
		if($config[enable_list_buttons]!='No') $bar_list = '[ "orderedlist", "unorderedlist", "outdent", "indent", "separator" ],';
		if($config[enable_tables_button]!='No') $bar_table = '"inserttable",';
		if($config[enable_color_buttons]!='No') $bar_colors = '[ "forecolor", "backcolor", "textindicator", "separator" ],';
		if($config[enable_horizontal_ruler_button]!='No') $bar_ruler = '"horizontalrule",';
		if($config[enable_fullscreen_editor_button]!='No') $bar_fullscreen = '"popupeditor",';
		if($config[enable_link_button]!='No') $bar_link = '"createlink",';
		
		/*********************************************************************\
		* $input['field'] will be rendered in the form                        *
		\*********************************************************************/

		$input='
		<script type="text/javascript" src="jinn/plugins/htmlareaV3/htmlarea.js"></script>
		<script type="text/javascript" src="jinn/plugins/htmlareaV3/htmlarea-lang-en.js"></script>
		<script type="text/javascript" src="jinn/plugins/htmlareaV3/dialog.js"></script>
		<style type="text/css">
		@import url(jinn/plugins/htmlareaV3/htmlarea.css);
		textarea { background-color: #fff; border: 1px solid 00f; }
		</style>
		<br><textarea id="'.$field_name.'" name="'.$field_name.'" style="width:100%" rows="20">'.$value.'</textarea><br>
		
		<script language="javascript1.2" type="text/javascript">
		<!--
		/*
		 INTERNET EXPLORER BUG (tell something news)
		 right now the plugin is only able to create one (the last) 
		 rich textbox in IE. Mozilla doesn\'t encounter problems.

		 possible solution is to create a array of field that have to 
		 be generated and when onLoad starts all elements of the array
		 are generated.
		*/
		
		function initEditor()
		{
			var editor = null;
			var cfg = new HTMLArea.Config(); 
			cfg.editorURL = "'.$editor_url.'"; 
			cfg.toolbar = [ '.$bar_font.' '.$bar_align.' '.$bar_list.' '.$bar_colors.'
			[ '.$bar_ruler.' '.$bar_link.' '.$bar_image.' '.$bar_table.' '.$bar_html.' "separator" ],
			[ '.$bar_fullscreen.' "about" ] ];

			editor = new HTMLArea("'.$field_name.'", cfg); 
			editor.generate();
		}
		
		if (HTMLArea.is_ie) 
		{
			document.body.onload=initEditor;
		}
		else
		{
			initEditor();
		}
		//-->
		</script>
		';

		return $input;
	}
	?>
