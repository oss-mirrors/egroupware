<?php
  /**************************************************************************\
  * eGroupWare - User manual                                                 *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$phpgw_flags = Array(
		'currentapp'	=> 'manual'
	);
	$phpgw_info['flags'] = $phpgw_flags;
	include('../../../header.inc.php');
	$font = $phpgw_info['theme']['font'];
?>
<img src="<?php echo $phpgw->common->image('preferences','navbar.gif'); ?>" border="0">
<font face="<?php echo $font ?>" size="2"><p/>
The system functionality can be used to allocate, track and audit tasks of 
groups or members of a specific group.
<ul><p/>At the top of the screen there are two clickable areas<br/>
<font color="blue">[New ticket | View all tickets]</font>
Clicking on the View all tickets, will change to read<br/>
<font color="blue">[New ticket | View only open tickets]</font>and only the open tickets
in the system will be displayed.<p/>
To view or make changes to a ticket, click on the ticket number.<p/>
<li><b>View:</b><br/>
When you enter the trouble ticket system, all tickets that have been created by you
or members of your group (and assigned Group readable) will be displayed. This includes
closed tickets.<p/></li>
<li><b>Create:</b><br/>
To create a ticket, click on "New Ticket", you will then be presented with the form to add
the details as below..<p/></li>
<li><b>Edit:</b><br/>
To edit a ticket, click on the number of the ticket, make changes to the relevant files,
enter additional text, then click ok.<p/></li>
<li><b>Close:</b><br/>
To close a ticket, click on the number of the ticket, then click on the close button at the bottom
of the page.. <b>NB:</b>It is a good idea to always add a comment when you close ticket, so that other
members of the groups, can determine the outcome and result.<p/></li>
The ticket will be opened, you can see the following information: 
<table width="80%">
<td bgcolor="#ccddeb" width="50%" valign="top">
<font face="<?php echo $font; ?>" size="2">
Last name:<br/>
ID:<br/>
Assigned from:<br/>
Open date:<br/>
Closed date: (if applicable)<br/>
Priority:<br/>
Group:<br/>
Assigned to:<br/>
Subject:<br/>
Details:<br/>
Additional notes:<br/>
Update:OK:Close buttons:</td></table></ul></font>
<?php $phpgw->common->phpgw_footer(); ?>
