<?php
	/*************************************************************************\
	* phpGroupWare - HTMLAREA-form-plugin for phpGW-jinn            *
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
	* phpGroupWare and the jinn are free software; you can          *
	* redistribute it and/or modify it under the terms of the GNU General     *
	* Public License as published by the Free Software Foundation; either     *
	* version 2 of the License, or (at your option) any later version.        *
	\*************************************************************************/

	$description='htmlArea is a plugin implementation of htmlArea rich text box 
	editor by interactivetools.com, inc. It\'s requires Internet Explorer 5.5+ 
	for WYSIWYG functionality, if you\'re looking for a cross platform rich 
	textbox editor check the htmlArea v3, also available as JiNN input plugin';

	$this->plugins['htmlarea']['name']		= 'htmlarea';
	$this->plugins['htmlarea']['title']		= 'HTMLarea plugin';
	$this->plugins['htmlarea']['version']	= '0.9.2';
	$this->plugins['htmlarea']['enable']	= 1;
	$this->plugins['htmlarea']['description']	= $description;
	$this->plugins['htmlarea']['db_field_hooks']= array
	(
		'text',
		'varchar',
		'blob'
	);
	$this->plugins['htmlarea']['config']	= array
	(
		'Allow_table_insert'=>array('3','text','maxlength=2 size=2'),
	);

	// funcion must be called like this: 'plugin_[plugin_name]'

	function plg_fi_htmlarea($field_name,$value, $config)
	{

		$editor_url=$GLOBALS['phpgw_info']['server']['webserver_url'].'/';

		/*********************************************************************\
		* $input['field'] will be rendered in the form                        *
		\*********************************************************************/

		$input='
		<script language="Javascript1.2" src="jinn/plugins/htmlarea/editor.js"></script>
		<script>
		_editor_url = "'.$editor_url.'";
		</script>
		<style type="text/css">
		<!--
		.btn   { BORDER-WIDTH: 1; width: 26px; height: 24px; }
		.btnDN { BORDER-WIDTH: 1; width: 26px; height: 24px; BORDER-STYLE: inset; BACKGROUND-COLOR: buttonhighlight; }
		.btnNA { BORDER-WIDTH: 1; width: 26px; height: 24px; filter: alpha(opacity=25); }
		-->
		</style>

		<textarea name="'.$field_name.'" id="'.$field_name.'" style="width:100%; height:200">'.$value.'</textarea>
		<script language="javascript1.2">
		editor_generate(\''.$field_name.'\'); // field, width, height
		</script>
		';

		return $input;
	}

	?>
