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

	$GLOBALS['phpgw_info']['flags'] = array
	(
		'currentapp' => 'manual',
		'enable_utilities_class' => True
	);

	include('../header.inc.php');

echo '<img src="' . $GLOBALS['phpgw']->common->image($GLOBALS['phpgw_info']['flags']['currentapp'],'title_overview') . '" border="0">';
?>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
The following pages are an to be used as a guideline/ready reference
for how to navigate the pages found here, and the functionality of
each application.
<p>
Please visit <a href="http://www.phpgroupware.org" target=_new><b> phpgroupware </b></a> the 
home page of this open source project, by way of trying to say to  the young
people who wrote (or at least put together much of this) ,<b> thankyou</b>.
Without such keen and enthusiastic joint, co-operation, many projects on the
internet as we know it, would not be available, or would at the very least
be similar to many other well known <b> costly </b> alternatives.
<br>
For those of you who are not familar with open source, please visit 
<a href="http://www.opensource.org" target=_new> www.opensource.org</a> 
<p>
Now on with the overview: (NB: please be aware this is an evolving project, so some
functionality may not be available to you yet, or perhaps there will be be extra
things, not yet included here.)If you have any problems with these pages or understanding
what is written, please <a href=mailto:"kim@vuurwerk.nl"> mail </a> and we will do our
best to fix or assist.
<p>
The names are pretty self evident, however for completion :)
<ul>
<li><b>Address Book:</b>
<br>A quick and detailed address book, to keep various levels of contact information and a
search function to find people you need quickly.
<li><b>Administration:</b> 
<br>Where the administrator of the system can create users/ groups, set up levels of access for users/groups, view active sessions (see who is connected to the system), view access logs, set headline stats, set Network News, and see other intersting things about what their system is doing.
<li><b>Bookmarks:</b>
<br>As yet still under development, may be incorporated into other features.
<li><b>Calendar:</b>
<br>Day, week and monthly viewing, with hourly breakdown of each day, appointment
scheduling, with the ability to add specific people or groups to alert others to your
availabilty or not. Also a search function, very helpful for tracking down those
forgotten birthdays or appointments ;)
<li><b>Chat:</b>
<br>Chat rooms, to talk realtime with other users in the system.
<li><b>Email:</b>
<br>Pop3 and IMAP functionalty for webased mail.
<li><b>File Manager:</b>
<br>Application to help manage documents within a group, or privately. Upload,edit,copy.
<li><b>Headlines:</b>
<br>Latest snippets of news as set by the Systems Admin, and selected by your preferences.
<li><b>Home:</b> 
<br> The first page you reach after you log in to the system
<li><b>Human Resources:</b>
<br>View where all the people on the network fit in.. which group
they belong to and what groups have what users.
<li><b>Preferences</b>
<br> Here you can change your passwd, select differement themes, change your settings and 
choose which news groups you would like to monitor... fun with color :)
<li><b>Inventory:</b>
<br>Build and maintain an inventory.
<li><b>Todo list:</b>
<br>Check your own tasks or those of your group members, and a search function.
<li><b>NNTP:</b>
<br>All the latest news.
<br>
<li><b>Trouble Ticket System:</b>
<br>Tracking system, for trouble tickets and problem resolution.
</ul>
That is about it.. for a more detailed howto, please look into each section for clear indepth
explainations.. and remember to click on the little square boxes next to the icons for drop
downs of what is in each folder.
<p>
<?php
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
