<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class module_html extends Module
	{
		function module_html()
		{
			$this->i18n = true;
			$this->arguments = array(
				'htmlcontent' => array(
					'type' => 'htmlarea',
					'label' => lang('Enter the block content here'),
					'large' => True,	// show label above content
					'i18n' => True,
					'params' => Array(
						'style' => 'width:100%; min-width:500px; height:300px',
						'plugins' => 'UploadImage,ContextMenu,TableOperations,SpellChecker'
					)
				)
			);
			$this->properties = array('striphtml' => array('type' => 'checkbox', 'label' => lang('Strip HTML from block content?')));
			$this->title = lang('HTML module');
			$this->description = lang('This module is a simple HTML editor');
		}

		function get_content(&$arguments,$properties)
		{
			if ($properties['striphtml'])
			{
				return $GLOBALS['egw']->strip_html($arguments['htmlcontent']);
			}
			// spamsaver emailaddress and activating the links
			if (!is_object($GLOBALS['egw']->html))
			{
				$GLOBALS['egw']->html =& CreateObject('phpgwapi.html');
			}
			return $GLOBALS['egw']->html->activate_links($arguments['htmlcontent']);
		}
	}
