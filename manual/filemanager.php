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
<table cellpadding="10">
<?php include("side.php"); ?>
<td valign="top">
<img src="images/title_filemanager.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
The file manager function is to allow users to upload files to the system, allowing both
private and shared access to the files.
<p>
<b>Important</b> Private files should be kept in your private group. Files shared with
other members of the group, can be viewed,changed or deleted by them.
<p>
<ul>
<li><b>Upload:</b>
<br>Choose the group you wish to use, click the browse button in that group block.
A popup window will appear, browse through your computer to find the file you
want, then click ok.
<br>The pop box will disappear, and the name of the file should now be on the 
screen, click on upload. 
<p>
<li><b>Download:</b>
<br>Choose the group you wish to use, click on the button below files to select the file
you want download.  A popup window will appear, click ok to save the file on your computer
,then click ok.
<p>
<li><b>Create:</b>
<br>Choose the group you wish to use, type in the name of the file you want to create
,click on the create button.
<br>The file will be opened in your browser, where you can make changes to the file,
then click on save. When you have finished creating click the exit button.
<p>

<li><b>Edit:</b>
<br>Choose the group you wish to use, click on the button below files to select the 
file you want to edit, a drop down box will then display all the files currently in that group.
<br>Choose one, click on the edit button.
<br>The file will be opened in your browser, where you can make changes to the file,
then click on save. When you have finished editting click the exit button.
<p>
<li><b>Copy:</b>
<br>Choose the group you wish to use, click on the button below files to select the
file you want to copy, a new box will appear at the top of the screen. Change the name of the
file to the name you want to call the copy, then click 
<p>
<li><b>Delete:</b>
<br>Choose the group you wish to use, click on the button below files to select the file
you want delete, a new box will appear at the top of the screen.  With an option Yes/No.
Click on yes, and the file will be deleted.
<p>
<li><b>Rename to:</b>
<br>Choose the group you wish to use, click on the button below files to select the file
you want rename, a new box will appear at the top of the screen.  
Click on yes, and the file will be deleted.
<p>

</td>
</table>
</body>
</html>
