<title>File attachment</title>
<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * This file written by Joseph Engo <jengo@phpgroupware.org>                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_flags = array("noheader" => True, "nonavbar" => True);
  $phpgw_flags["currentapp"] = "email";
  include("../header.inc.php");

  // Some on the methods where borrowed from
  // Squirrelmail <Luke Ehresman> http://www.squirrelmail.org

  $uploaddir = $phpgw_info["server"]["temp_dir"]
	     . $phpgw_info["server"]["dir_separator"]
	     . $phpgw->session->id
	     . $phpgw_info["server"]["dir_separator"];

  if ($action == "Delete") {
     for ($i=0; $i<count($delete); $i++) {


        unlink($uploaddir . $delete[$i]);
        unlink($uploaddir . $delete[$i] . ".info");
     }
  }

  if ($action == "Attach File") {
     srand((double)microtime()*1000000);
     $random_number = rand(100000000,999999999);
     $newfilename = md5("$uploadedfile, $uploadedfile_name, " . $phpgw->session->id
		      . time() . getenv("REMOTE_ADDR") . $random_number );

     copy($uploadedfile, $uploaddir . $newfilename);
     $ftp = fopen($uploaddir . $newfilename . ".info","w");
      fputs($ftp,"$uploadedfile_type\n$uploadedfile_name\n");
     fclose($ftp);
  }

  if (! file_exists($phpgw_info["server"]["temp_dir"]
	     . $phpgw_info["server"]["dir_separator"]
	     . $phpgw->session->id))
     mkdir($phpgw_info["server"]["temp_dir"]
	 . $phpgw_info["server"]["dir_separator"]
	 . $phpgw->session->id,0700);

  ?>
    <form ENCTYPE="multipart/form-data" method="POST" action="attach_file.php">
      <?php echo $phpgw->session->hidden_var(); ?>

      <table border=0>
      <tr> <td>Attach file:</td> </tr>
      <tr> <td>Current attachments:</td> </tr>
      <?php
        $dh = opendir($phpgw_info["server"]["temp_dir"]
		    . $phpgw_info["server"]["dir_separator"]
	 	    . $phpgw->session->id);
        while ($file = readdir($dh)) {
          if ($file != "." && $file != ".." && ereg("\.info",$file)) {
             $file_info = file($uploaddir . $file);
             echo "<tr><td><input type=checkbox name=\"delete[]\" "
		. "value=\"" . substr($file,0,-5) . "\">$file_info[1]</tr></td>\n";
             $totalfiles++;
          }
        }
        closedir($dh);
        if ($totalfiles == 0)
           echo "<tr></td>None</td></tr>\n";
        else
           echo "<tr><td><input type=\"submit\" name=\"action\" value=\"Delete\"></td></tr>\n";
      ?>
      <tr>
       <td>File: <input type="file" name="uploadedfile"></td>
       <td><input type="submit" name="action" value="Attach File"></td>
      </tr>
      <tr>
        <td align="center" colspan="2"><input type="submit" value="Done" onClick="window.close()"></td>
      </tr>
      </table>
     </form>

