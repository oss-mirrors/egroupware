<?
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

	/*********************************************************************\
	* $setup_info['jinn'] tells the site object administrator   *
	* for which databasefieldtypes the plugin can be used for and more    *
	\*********************************************************************/
	
	//$this->plugins['form']['list'][]='htmlarea';
	$this->plugins['htmlarea']['name']		= 'htmlarea';
	$this->plugins['htmlarea']['title']		= 'HTMLarea plugin';
	$this->plugins['htmlarea']['version']		= '0.9';
	$this->plugins['htmlarea']['enable']		= 1;
	$this->plugins['htmlarea']['db_field_hooks']	= array
	(
		'text',
		'varchar',
		'blob'
	);
	$this->plugins['htmlarea']['config']		= array
	(
		'Enable_Tables'=>'False',
		'Enable_Image_Handling'=>'False',
		'Enable_Sub_and_Superscript'=>'False'
	);

	// funcion must be called like this: 'plugin_[plugin_name]'

	function plugin_htmlarea($field_name,$value, $config, $local_bo)
	{

	$editor_url=$GLOBALS['phpgw_info']['server']['webserver_url'].'/';

	/*********************************************************************\
	 * $input['field'] will be rendered in the form                        *
	 \*********************************************************************/

	$input='
		<script language="Javascript1.2" src="jinn/plugins/fip/htmlarea/editor.js"></script>
		<script>
		// set this to the URL of editor direcory (with trailing forward slash)
		// NOTE: _editor_url MUST be on the same domain as this page or the popups
		// won\'t work (due to IE cross frame/cross window security restrictions).
		// example: http://www.hostname.com/editor/

		_editor_url = "'.$editor_url.'";
	</script>
		<style type="text/css">
		<!--
		.btn   { BORDER-WIDTH: 1; width: 26px; height: 24px; }
	.btnDN { BORDER-WIDTH: 1; width: 26px; height: 24px; BORDER-STYLE: inset; BACKGROUND-COLOR: buttonhighlight; }
	.btnNA { BORDER-WIDTH: 1; width: 26px; height: 24px; filter: alpha(opacity=25); }
	-->
		</style>
		<!-- END : EDITOR HEADER -->
		<!----------------------------------------------------------------->

		<style type="text/css">
		<!--
		//body, td { font-family: arial; font-size: 12px; }
		.headline { font-family: arial black, arial; font-size: 28px; letter-spacing: -2px; }
	.subhead  { font-family: arial, verdana; font-size: 12px; let!ter-spacing: -1px; }
	-->
		</style>

		<textarea name="'.$field_name.'" id="'.$field_name.'" style="width:100%; height:200">'.$value.'</textarea>

		<script language="javascript1.2">
		editor_generate(\''.$field_name.'\'); // field, width, height
	</script>

		<!--
		example links to put in extra HTML
		<a href="javascript:editor_insertHTML(\''.$field_name.'\',\'<font style=\'background-color: yellow\'>\',\'</font>\');">Highlight selected text</a> -
		<a href="javascript:editor_insertHTML(\''.$field_name.'\',\':)\');">Insert Smiley</a>
		-->
		';

	return $input;
}

?>
