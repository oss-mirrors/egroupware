<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_flags = array("noheader" => True, "nonavbar" => True, "messageclass" => True);
  $phpgw_flags["currentapp"] = "email";
  include("../header.inc.php");

  if ($phpgw_info["user"]["permissions"]["anonymous"]) {
       $phpgw->common->navbar();
     echo "<p><center>Sorry, sending mail is disabled</center>";
     exit;
  }

  $sep = $phpgw->common->filesystem_separator();

  if (file_exists($phpgw_info["server"]["temp_dir"].$sep.$phpgw_info["user"]["sessionid"])) {
     $dh = opendir($phpgw_info["server"]["temp_dir"] . $sep . $phpgw_info["user"]["sessionid"]);
     while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
           if (! ereg("\.info",$file)) {
              $total_files++;
              $size = filesize($phpgw_info["server"]["temp_dir"] . $sep
					 . $phpgw_info["user"]["sessionid"] . $sep . $file);

              $file_info = file($phpgw_info["server"]["temp_dir"] . $sep
					  . $phpgw_info["user"]["sessionid"] . $sep . $file . ".info");

              $file_info[0] = chop($file_info[0]);
              $file_info[1] = chop($file_info[1]);

              $fh = fopen($phpgw_info["server"]["temp_dir"] . $sep
				. $phpgw_info["user"]["sessionid"]
				. $sep . $file,"r");

              $rawfile = fread($fh,$size);
              $encoded_attach = chunk_split(base64_encode($rawfile));

              $body .= "\n\n--Message-Boundary\n"
                         . "Content-type: $file_info[0]; name=\"$file_info[1]\"\n"
                         . "Content-Transfer-Encoding: BASE64\n"
                         . "Content-disposition: attachment; filename=\"$file_info[1]\"\n\n"
                         . $encoded_attach . "\n"; 
              unlink($phpgw_info["server"]["temp_dir"] . $sep
		   . $phpgw_info["user"]["sessionid"] . $sep . $file);

              unlink($phpgw_info["server"]["temp_dir"] . $sep
		   . $phpgw_info["user"]["sessionid"] . $sep . $file . ".info");
           }	// if ! .info
        }	// if ! . or ..
     } 		// while dirread
     rmdir($phpgw_info["server"]["temp_dir"] . $sep . $phpgw_info["user"]["sessionid"]);
  }		// if dir

//  }
//  if (strlen($cc)>1) $to .= ",".$cc;
/*
  if (!empty($cc) && empty($bcc)) {
    $to = "$to\n";
    $to .= "Cc: $cc";
  } elseif (!empty($cc) && !empty($bcc)) {
    $to = "$to\n";
    $to .= "Cc: $cc\n";
    $to .= "Bcc: $bcc";
  }
*/
  $rc = $phpgw->send->msg("email", $to, $subject, stripslashes($body), "", $cc, $bcc);
  if ($rc) {
    header("Location: " . $phpgw->link("index.php","cd=13&folder=" . urlencode($return)) );
  } else {
    echo "Your message could <B>not</B> be sent!<BR>\n";
    echo "The mail server returned:<BR>".
         "err_code: '".$phpgw->send->err["code"]."';<BR>".
         "err_msg: '".htmlspecialchars($phpgw->send->err[msg])."';<BR>\n".
         "err_desc: '".$phpgw->err[desc]."'.<P>\n";
    echo "To go back to the msg list, click <A HRef=\"".$phpgw->link("index.php","cd=13&folder=" . urlencode($return))."\">here</a>";
  }
