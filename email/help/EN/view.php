<?php
	/**************************************************************************\
	* phpGroupWare - User manual                                               *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = Array
	(
		'headonly'		=> True,
		'currentapp'	=> 'email'
	);

	include('../../../header.inc.php');

	$GLOBALS['phpgw']->help = CreateObject('phpgwapi.help_helper');
	$GLOBALS['phpgw']->help->set_params(array('app_name'	=> 'email',
												'title'		=> lang('email') . ' - ' . lang('view'),
												'controls'	=> array('up'	=> 'list.php')));
	$values['view']	= array
	(
		'view_img'	=> $GLOBALS['phpgw']->common->image('email','help_view'),
		'item_1'	=> 'This shows what directory you are currently in. The example shows that this is the INBOX.',
		'item_2'	=> 'See the image subset labeled 2 for a description of these symbols.',
		'item_3'	=> 'Move the the previous/next message in the list.',
		'item_4'	=> 'Clicking on the little envelope image will bring up an entry to add this name to your address book. If you click on the name in blue, in this case Joe Consumer; it brings up a reply message.',
		'item_5'	=> 'Refer to #4 for information.',
		'item_6'	=> 'These are the names of the files that are attachments.',
		'item_7'	=> 'Section 1: This is the message of the e-mail.',
		'item_8'	=> 'Section 2: Attachment #1. This is a file that the sender has attached to this e-mail. To download it, click on the blue link (post-nuke4SME.html). If the e-mail does not contain an attachment, then it would only list Section 1.',
		'item_9'	=> 'Section 3: Attachment #2. Refer to #8 for information.'
	);

	$GLOBALS['phpgw']->help->xdraw($values);
	$GLOBALS['phpgw']->xslttpl->set_var('phpgw',$GLOBALS['phpgw']->help->output);
?>
