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

  $phpgw_info["flags"] = array("currentapp" => "manual", "enable_utilities_class" => True);
  include("../header.inc.php");
?>
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_administration.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
This function is usually only available to the systems administrator of the system.
Administration of all applications, user and group accounts, session logging and control.
<ul>
<li><b>Headline sites:</b>
<br>Administer headline sites as seen by users in the headlines application.
<p><i>Edit:</i> Options for the headline sites:
<br> Display,BaseURL, NewsFile,Minutes between reloads,Listing Displayed,News Type.
<p> <i>Delete:</i>Remove an existing headling site, clicking on delete will give
you a checking page to be sure you do want to delete.
<p><i>View:</i>Displays set options as in edit.
<p><i>Add:</i>Form for adding new headline site, options as in edit.
<p>
</ul>
<?php $phpgw->common->phpgw_footer(); ?>
