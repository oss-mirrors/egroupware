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
												'title'		=> lang('email') . ' - ' . lang('list'),
												'controls'	=> array('down'	=> 'view.php')));
	$values['list']	= array
	(
		'list_img'	=> $GLOBALS['phpgw']->common->image('email','help_list'),
		'item_1'	=> '',
		'item_2'	=> '',
		'item_3'	=> '',
		'item_4'	=> '',
		'item_5'	=> 'The single arrow moves backward one page, while the double arrows move to the first page.',
		'item_6'	=> 'The single arrow moves forward one page, while the double arrows move to the last page.',
		'item_7'	=> 'Shows what directory you are in (here it shows the INBOX) and other information such as the size of the folder and how many new and saved messages you have.',
		'item_8'	=> 'This pull down menu allows you to change what directory you want to view. i.e inbox, sent-mail or trash. ',
		'item_9'	=> 'The Folder button brings you a listing of all the directories you have in your e-mail account. It also lists how many new/saved messages you have in that folder.',
		'item_10'	=> 'Clicking inside this box brings a check mark inside. This "selects" the message. More than one message may be selected if desired.',
		'item_11'	=> 'The red asterisk in this column denotes that it is a new, unread message.Once it has been read, it will no longer show. If there is a paper clip at the bottom of the column,it means that this e-mail contains an attachment.',
		'item_12'	=> 'The subject of the e-mail. By clicking on the text "subject" you are telling the e-mail to be listed by in order by the subject. In this example, the subject of the e-mail is called "test". By clicking on "test", it will open up that message.',
		'item_13'	=> 'The "From" column: Address of the person who sent you the e-mail. The example shows this came from someone@somewhere.com. If you were to click on the "from" link, it would sort all the messages in this folder by the sender. If you click on the name of the person,in this case someone, it will bring up a link to add this person to your addressbook.',
		'item_14'	=> 'This column lists the date when you received this e-mail. Clicking on "Date" sorts the list in order by date.',
		'item_15'	=> 'This lists the size of each message in kilobytes. If you click on "Size", it will sort the list based on size.',
		'item_16'	=> 'Clicking on the check mark selects all messages.',
		'item_17'	=> 'By pressing this link, it will send all selected files to the trash folder.',
		'item_18'	=> 'Press this link to create a new e-mail message.',
		'item_19'	=> 'Once a message(s)has been selected, use this pull down menu to move it to another directory.'
	);

	$GLOBALS['phpgw']->help->xdraw($values);
	$GLOBALS['phpgw']->xslttpl->set_var('phpgw',$GLOBALS['phpgw']->help->output);
?>
