<?php
   function createLink($string) {
      global $phpgw;
      return $phpgw->link($string);
   }

   function getConnectionInfo() {
      global $phpgw;
      $unencrypted=$phpgw->common->appsession();
      return $unencrypted;
   }

   function phpftp_connect($host,$user,$pass) {
      // echo "connecting to $host with $user and $pass\n";
      $ftp = ftp_connect($host);
      if ( $ftp ) {
         if ( ftp_login($ftp,$user,$pass) ) {
            return $ftp;
         }
      }
   }


   function renameForm($template,$session,$filename,$directory) {
      global $bgcolor, $em_bg, $target;
      $rename_form_begin= "<form action=\"" . createLink($target) . 
         "\" method=\"post\">\n" .
         "<input type=\"hidden\" name=\"action\" value=\"rename\">\n" .
         "<input type=\"hidden\" name=\"olddir\" value=\"$directory\">\n" . 
         "<input type=\"hidden\" name=\"newdir\" value=\"$directory\">\n" . 
         "<input type=\"hidden\" name=\"filename\" value=\"$filename\">\n";
      $rename_form_end = "</form>\n";
      $rename_form_from=  $filename;
      $rename_form_to="<input type=text name=\"newfilename\" size=20" .
         "value=\"\">";
      $rename_form_submit="<input type=\"submit\" name=\"confirm\" " .
         "value=\"" . lang_ftp("rename") . "\">\n";
      $rename_form_cancel="<input type=\"submit\" name=\"cancel\" " .
         "value=\"" . lang_ftp("cancel") . "\">\n";

      $template->set_var(array(
         "rename_form_begin" => $rename_form_begin,
         "rename_form_end"  => $rename_form_end,
         "rename_form_from" => $rename_form_from,
         "rename_form_to" => $rename_form_to,
         "rename_form_submit" => $rename_form_submit,
         "rename_form_cancel" => $rename_form_cancel,
         "lang_rename_from" => lang_ftp("rename from"), 
         "lang_rename_to" => lang_ftp("rename to")
         ));

      $template->parse("out","rename",true);
      // $template->p("renameform");
      $template->set_var("return",$template->get("out"));
      return $template->get("return");
   }

   function confirmDeleteForm($template,$session,$filename,$directory,$type) {
      global $bgcolor, $em_bg, $target;
      $delete_form_begin= "<form action=\"" . createLink($target) . 
         "\" method=\"post\">\n" .
         "<input type=\"hidden\" name=\"action\" value=\"delete\">\n" .
         "<input type=\"hidden\" name=\"olddir\" value=\"$directory\">\n" .
         "<input type=\"hidden\" name=\"newdir\" value=\"$directory\">\n" .
         "<input type=\"hidden\" name=\"file\" value=\"$filename\">\n";
      $delete_form_end = "</form>\n";
      $delete_form_question = lang_ftp("confirm delete",$directory . "/" . 
         $filename);
      $delete_form_from= $directory . "/" . $filename;
      $delete_form_to="<input type=text name=\"newname\" size=20" .
         "value=\"\">";
      $delete_form_confirm="<input type=\"submit\" name=\"confirm\" " .
         "value=\"" . lang_ftp("delete") . "\">\n";
      $delete_form_cancel="<input type=\"submit\" name=\"cancel\" " .
         "value=\"" . lang_ftp("cancel") . "\">\n";

      $template->set_var(array(
         "delete_form_begin" => $delete_form_begin,
         "delete_form_end"  => $delete_form_end,
         "delete_form_question" => $delete_form_question,
         "delete_form_confirm" => $delete_form_confirm,
         "delete_form_cancel" => $delete_form_cancel
         ));

      $template->parse("out","confirm_delete",true);
      $template->set_var("return",$template->get("out"));
      return $template->get("return");
   }

   function newLogin($template,$dfhost,$dfuser,$dfpass) {
      global $bgcolor, $em_bg, $target;
      $login_form_begin= "<form action=\"" . createLink($target) . 
         "\" method=\"post\">\n" .
         "<input type=\"hidden\" name=\"action\" value=\"login\">\n";
      $login_form_end="</form>\n";
      $login_form_username="<input type=text size=20 name=\"username\" " .
         "value=\"$dfuser\">";
      $login_form_password="<input type=password name=\"password\" size=20 " .
         "value=\"$dfpass\">";
      $login_form_ftpserver="<input type=text name=\"ftpserver\" size=20 " .
         "value=\"$dfhost\">";
      $login_form_submit="<input type=\"submit\" name=\"submit\" value=\"" .
         lang_ftp("connect") . "\">\n";
      $login_form_end="</form>";

      $template->set_var(array(
         "login_form_begin" => $login_form_begin,
         "login_form_end" => $login_form_end,
         "login_form_username" => $login_form_username,
         "login_form_password" => $login_form_password,
         "login_form_ftpserver" => $login_form_ftpserver,
         "login_form_submit" => $login_form_submit,
         "lang_username" => lang_ftp("username"),
         "lang_password" => lang_ftp("password"),
         "lang_ftpserver" => lang_ftp("ftpserver")
         ));

      $template->parse("loginform","login",false);
      $template->p("loginform");
      return;
   }

   function phpftp_get( $ftp, $tempdir, $dir, $file ) {
		srand((double)microtime()*1000000);
		$randval = rand();
		$tmpfile="$tempdir/" . $file . "." . $randval;
      ftp_chdir($ftp,$dir);
      $remotefile=$dir . "/" . $file;
		if ( ! ftp_get( $ftp, $tmpfile, $remotefile, FTP_BINARY ) ) {
         echo "tmpfile=\"$tmpfile\",file=\"$remotefile\"<BR>\n";
		   ftp_quit( $ftp );
         echo macro_get_Link("newlogin","Start over?");
         $retval=0;
		} else {
			ftp_quit( $ftp );
			header( "Content-Type: application/octet-stream" );
			header( "Content-Disposition: attachment; filename=" . $file );
			readfile( $tmpfile );
         $retval=1;
		}
		@unlink( $tmpfile );
      return $retval;
   }

   function getMimeType($file) {
      global $phpgw_info;
      $file=basename($file);
      $mimefile=$phpgw_info["server"]["server_root"]."/ftp/mime.types";
      $fp=fopen($mimefile,"r");
      $contents = explode("\n",fread ($fp, filesize($mimefile)));
      fclose($fp);

      $parts=explode(".",$file);
      $ext=$parts[(sizeof($parts)-1)];

      for($i=0;$i<sizeof($contents);$i++) {
         if (! ereg("^#",$contents[$i])) {
            $line=split("[[:space:]]+", $contents[$i]);
            if (sizeof($line) >= 2) {
               for($j=1;$j<sizeof($line);$j++) {
                  if ($line[$j] == $ext) {
                     $mimetype=$line[0];
                     return $mimetype;
                  }
               }
            }
         }
      }
      return "text/plain";
   }

   function phpftp_view( $ftp, $tempdir, $dir, $file ) {
		srand((double)microtime()*1000000);
		$randval = rand();
		$tmpfile="$tempdir/" . $file . "." . $randval;
      ftp_chdir($ftp,$dir);
      $remotefile=$dir . "/" . $file;
		if ( ! ftp_get( $ftp, $tmpfile, $remotefile, FTP_BINARY ) ) {
         echo "tmpfile=\"$tmpfile\",file=\"$remotefile\"<BR>\n";
         macro_get_Link("newlogin","Start over?");
         $retval=0;
		} else {
         $content_type=getMimeType($remotefile);
         header("Content-Type: $content_type");
			readfile( $tmpfile );
         $retval=1;
		}
		@unlink( $tmpfile );
      return $retval;
   }

   function updateSession($string="") {
     global $phpgw;
      $phpgw->common->appsession($string);
      return;
   }

   function phpftp_getList($ftp,$dir) {
      // this function should return a list of the files (including
      // directories in the directory given
      // since the ftp_nlist command cant be relied on, we do an nlist,
      // if there are no directories listed (with a size -1) then we
      // have to do a rawlist and get the directories by the values
      // with a : after their name
      $list=ftp_nlist($ftp,$dir);
      $dirsfound=0;
      for($i=0;$i<sizeof($list);$i++) {
         if (ftp_size($ftp,$list[$i]) == -1) {
            // this was a directory
            $dirsfound=1;
            // echo "Found a dir in $list[$i]<BR>\n";
            continue;
         }
      }

      if (!$dirsfound) {
         // echo "Had to do a rawlist\n";
         $rawlist=ftp_rawlist($ftp,$dir);
         for($i=0;$i<sizeof($rawlist);$i++) {
            if (ereg(":$",$rawlist[$i])) {
               // print "found directory \"$rawlist[$i]\"<BR>\n";
               array_push($list,substr($rawlist[$i],0,-1));
            }
         }
      }

      sort($list);
      // for($i=0;$i<sizeof($list);$i++) {
      //    echo $list[$i] . "<BR>\n";
      // }
      for ($i=count($list);$i>0;$i--) {
        $list[$i] = $list[$i-1];
      }
      $list[0] = "..";
//      array_unshift($list,".."); // this would be php4 only
      return $list;
   }


   function macro_get_Link($action,$string) {
      // globals everything it needs but the string to link
      global $olddir, $newdir, $file, $target;
      $retval = "<a href=\"" . createLink($target) . 
         "&olddir=" . urlencode($olddir) . "&action=" .
         urlencode($action) . "&file=" . urlencode($file) . "&newdir=" .
         urlencode($newdir) . "\">";
      $retval .= $string;
      $retval .= "</a>";
      return $retval;
   }

   function phpftp_delete($file,$confirm){
   }

   function phpftp_rename($origfile,$newfile,$confirm) {
   }

?>
