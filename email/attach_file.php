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

	$phpgw_flags = Array(
		'currentapp'	=>	'email',
		'enable_network_class'	=> True,
		'noheader'	=> True,
		'nonavbar'	=> True
	);
	
	$phpgw_info['flags'] = $phpgw_flags;
  
	include('../header.inc.php');
  
	echo '<body bgcolor="' . $phpgw_info['theme']['bg_color'] . '">';

	// Some on the methods where borrowed from
	// Squirrelmail <Luke Ehresman> http://www.squirrelmail.org

	$uploaddir = $phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid'] . SEP;

	if ($action == 'Delete')
	{
		for ($i=0; $i<count($delete); $i++)
		{
			unlink($uploaddir . $delete[$i]);
			unlink($uploaddir . $delete[$i] . '.info');
		}
	}

	if ($action == 'Attach File')
	{
		srand((double)microtime()*1000000);
		$random_number = rand(100000000,999999999);
		$newfilename = md5($uploadedfile.', '.$uploadedfile_name.', '.$phpgw_info['user']['sessionid'].time().getenv('REMOTE_ADDR').$random_number);

		// Check for uploaded file of 0-length, or no file (patch from Zone added by Milosch)
		//if ($uploadedfile == "none" && $uploadedfile_size == 0) This could work also
		if ($uploadedfile_size == 0)
		{
			touch ($uploaddir . $newfilename);
		}
		else
		{
			copy($uploadedfile, $uploaddir . $newfilename);
		}

		$ftp = fopen($uploaddir . $newfilename . '.info','w');
		fputs($ftp,$uploadedfile_type."\n".$uploadedfile_name."\n");
		fclose($ftp);
	}

	if (!file_exists($phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid']))
	{
		mkdir($phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid'],0700);
	}
  ?>
    <form ENCTYPE="multipart/form-data" method="POST" action="<?php echo $phpgw->link('/email/attach_file.php')?>">
      <table border=0>
      <tr> <td>Attach file:</td> </tr>
      <tr> <td>Current attachments:</td> </tr>
      <?php
        $dh = opendir($phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid']);
        while ($file = readdir($dh)) {
          if ($file != '.' && $file != '..' && ereg("\.info",$file)) {
             $file_info = file($uploaddir . $file);
             echo '<tr><td><input type="checkbox" name="delete[]" value="'.substr($file,0,-5).'">'.$file_info[1].'</tr></td>'."\n";
             $totalfiles++;
          }
        }
        closedir($dh);
        if ($totalfiles == 0)
           echo '<tr></td>None</td></tr>'."\n";
        else
           echo '<tr><td><input type="submit" name="action" value="Delete"></td></tr>'."\n";
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
<?php
	$phpgw->common->phpgw_exit();
?>
