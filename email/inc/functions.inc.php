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

  $d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
  if($d1 == "htt" || $d1 == "ftp" ) {
    echo "Failed attempt to break in via an old Security Hole!<br>\n";
    $phpgw->common->phpgw_exit();
  } unset($d1);

  /**************************************************************************\
  * Some constants we need to define                                         *
  \**************************************************************************/

  if (! defined("TYPEVIDEO")) { // without imap compiled in some constants
    define ("TYPETEXT",0);      // are missing
    define ("TYPEMULTIPART",1);
    define ("TYPEMESSAGE",2);
    define ("TYPEAPPLICATION",3);
    define ("TYPEAUDIO",4);
    define ("TYPEIMAGE",5);
    define ("TYPEVIDEO",6);
    define ("TYPEOTHER",7);
//  define ("TYPEMODEL",
    define ("ENC7BIT",0);
    define ("ENC8BIT",1);
    define ("ENCBINARY",2);
    define ("ENCBASE64",3);
    define ("ENCQUOTEDPRINTABLE",4);
    define ("ENCOTHER",5);
    define ("ENCUU",6);
  }

  /**************************************************************************\
  * SubClasses needed by msg funcs.                                          *
  \**************************************************************************/

  class msg_struct {
     var $type = 0;
     var $encoding = 5;
     var $ifsubtype = false, $subtype = "plain";
     var $ifdescription = false, $description;
     var $ifid = false, $id;
     var $lines = "0";
     var $bytes = "0";
     var $ifdisposition = false, $disposition;
     var $ifdparameters = false, $dparameters;
     var $ifparameters = false, $parameters;
     var $parts;
  }

  class msg_params {
     var $attribute;
     var $value;
     function msg_params($attrib,$val) {
       $this->attribute = $attrib;
       $this->value     = $val;
     }
  }

  class msg_headinfo {
    var $remail, $date, $Date, $subject, $Subject,
        $in_reply_to, $message_id, $newsgroups, $followup_to, $references,
        $Recent, $Unseen, $Answered, $Deleted, $Draft, $Flagged,
        $toaddress, $to = Array(),
        $fromaddress, $from = Array(),
        $ccaddress, $cc = Array(),
        $bccaddress, $bcc = Array(),
        $reply_toaddress, $reply_to = Array(),
        $senderaddress, $sender = Array(),
        $return_path, $return_path = Array(),
        $udate, $fetchfrom, $fetchsubject, $Size;
  }

  class msg_aka {
    var $personal, $adl, $mailbox, $host;
  }

  class msg_mb_info {
    var $Date = "", $Driver ="", $Mailbox = "", $Nmsgs = "",
        $Recent = "", $Unread = "", $Size;
  }

  /**************************************************************************\
  \**************************************************************************/

  class msg_common 
  { 
    var $msg_struct;
    var $err = array("code","msg","desc");
    var $msg_info = Array(Array());
    var $tempfile, $force_check;
    var $boundary, $got_structure;

    function msg_common_() {
      global $phpgw_info;
      $this->err["code"] = " ";
      $this->err["msg"]  = " ";
      $this->err["desc"] = " ";
      $this->tempfile = $phpgw_info["server"]["temp_dir"].$phpgw_info["server"]["dir_separator"].$phpgw_info["user"]["userid"].".mhd";
      $this->force_check = false;
      $this->got_structure = false;
    }

    /**************************************************************************\
    * phpGW functions for developers.                                          *
    \**************************************************************************/

    function get_flag($stream,$msg_num,$flag) {
      $header = $this->fetchheader($stream,$msg_num);
      $flag = strtolower($flag);
      for ($i=0;$i<count($header);$i++) {
        $pos = strpos($header[$i],":");
        if (is_int($pos) && $pos) {
          $keyword = trim(substr($header[$i],0,$pos));
          $content = trim(substr($header[$i],$pos+1));
          if (strtolower($keyword) == $flag) return $content;
        }
      }
      return false;
    }

    /**************************************************************************\
    * Common functions used by several pieces of this class                    *
    \**************************************************************************/
  
    function base64($string) {
      return base64_decode($string);
    }
  
    function construct_folder_str( $folder ) {
      /* This is only used by the login() function */
      // Cyrus style: INBOX.Junque
      // UWash style: ./aeromail/Junque
      global $phpgw_info;

      if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus") {
        $folder_str = "INBOX.".$folder;
      } else {
        $folder_str = "mail/".$folder;
      }
      return $folder_str;
    }

    function createmailbox($stream,$mailbox) {
      return false;
    }
     
    function deconstruct_folder_str( $folder )
    {
      /* This is only used by the login() function */
      // Cyrus style: INBOX.Junque
      // UWash style: ./aeromail/Junque
      global $phpgw_info;

      if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus") {
        $srch_str = "INBOX.";
      } else {
        $srch_str = "mail/";
      }
      $folder_str = substr($folder, strlen($srch_str), strlen($folder));

      return $folder_str;
    }

    function deletemailbox($stream,$mailbox) {
      return false;
    } 
     
    function fetchheader($stream,$msg_num) {
      $header = $this->get_header($stream,$msg_num);
      return implode("\n",$header);
    } 
     
    function fetchstructure($stream,$msg_num,$flags="") {
      $header = $this->get_header($stream,$msg_num);
      if (!$header): return false; endif;
      $info = $this->get_structure($header,1);
      if (!$info->bytes):
        $rc = ($this->msg2socket($stream,"LIST $msg_num\n"));
        if (!($this->pop_socket2msg($stream))):
          $pos = strpos($this->err[msg]," ");
          $info->bytes = substr($this->err[msg],$pos+1);
        endif;
      endif;
      if ($info->type == 1) { // multipart
        $body = $this->get_body($stream,$msg_num);
        $boundary = $this->get_boundary(&$info);
        $boundary = str_replace("\"","",$boundary);
        $this->boundary = $boundary;
        for ($i=1;$i<=$body[0];$i++) {
          $pos1 = strpos($body[$i],"--$boundary");
          $pos2 = strpos($body[$i],"--$boundary--");
          if (is_int($pos2) && !$pos2) {
            break;
          }
          if (is_int($pos1) && !$pos1) {
            $info->parts[] = $this->get_structure($body,&$i,true);
          }
        }
      }
      $this->got_structure = true;
      return $info;
    } 
     
    function header($stream,$msg_nr,$fromlength="",$tolength="",$defaulthost="") {
      $info = new msg_headinfo;
      $info->Size = $this->size_msg($stream,$msg_nr);
      $header = $this->get_header($stream,$msg_nr);
      if (!$header): return false; endif;
      for ($i=1;$i<=$header[0];$i++) {
        $pos = strpos($header[$i]," ");
        if (is_int($pos) && !$pos): continue; endif;
        $keyword = strtolower(substr($header[$i],0,$pos));
        $content = trim(substr($header[$i],$pos+1));
        switch ($keyword) {
          case "from"    :
          case "from:"   :
            $info->from = $this->get_addr_details("from",$content,&$header,&$i);
            break;
          case "to"      :
          case "to:"     :  // following two lines need to be put into a loop!
            $info->to   = $this->get_addr_details("to",$content,&$header,&$i);
            break;
          case "cc"      :
          case "cc:"     :
            $info->cc   = $this->get_addr_details("cc",$content,&$header,&$i);
            break;
          case "bcc"     :
          case "bcc:"    :
            $info->bcc  = $this->get_addr_details("bcc",$content,&$header,&$i);
            break;
          case "reply-to"  :
          case "reply-to:" :
            $info->reply_to = $this->get_addr_details("reply_to",$content,&$header,&$i);
            break;
          case "sender"  :
          case "sender:" :
            $info->sender = $this->get_addr_details("sender",$content,&$header,&$i);
            break;
          case "return-path"  :
          case "return-path:" :
            $info->return_path = $this->get_addr_details("return_path",$content,&$header,&$i);
            break;
          case "subject"  :
          case "subject:" :
          case "Subject:" :
            $pos = strpos($header[$i+1]," "); if (is_int($pos) && !$pos) {
              $i++; $content .= chop($header[$i]); }
            $info->subject = htmlspecialchars($content);
            $info->Subject = htmlspecialchars($content);
            break;

          // only temp
          case "message-id"  :
          case "message-id:" : $info->message_id = htmlspecialchars($content); break;
          case "newsgroups:" : $info->newsgroups = htmlspecialchars($content); break;
          case "references:" : $info->references = htmlspecialchars($content); break;
          case "in-reply-to:" : $info->in_reply_to = htmlspecialchars($content); break;
          case "followup-to:" : $info->follow_up_to = htmlspecialchars($content); break;
          case "date:"   :
            $info->date  = $content;
            $info->udate = $this->make_udate($content);
            break;
          default        : break;
        }
      }
      return $info;
    } 

    function listmailbox($stream,$ref,$pattern) {
      return false;
    }

    function qprint($string) {
      $string = str_replace("_", " ", $string);
      $string = str_replace("=\r\n","",$string);
      $string = quoted_printable_decode($string);
      return $string;
    } 
     
  } // end of class msg_common


	$phpgw_info['user']['preferences'] = $phpgw->common->create_emailpreferences($phpgw_info['user']['preferences']);

  $phpgw->msg = CreateObject("email.msg");
  $phpgw->msg->msg_common_();

  /*Set some defults*/
  if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UWash" &&
      $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" && !$folder) {
// Changed by skeeter 04 Jan 01
// This was changed to give me access back to my folders.
// Not sure what it would break if the user has a default folder preference set,
// but will allow access to other folders now.
//      $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
      $phpgw_info["user"]["preferences"]["email"]["folder"] = (!$phpgw_info["user"]["preferences"]["email"]["folder"] ? "INBOX" : $phpgw_info["user"]["preferences"]["email"]["folder"]);
//backward compatibility
      $folder = $phpgw_info["user"]["preferences"]["email"]["folder"];
  }

  if(!$folder) $folder="INBOX";
  
      //echo "<b>TEST:</b> ".$phpgw_info["user"]["preferences"]["email"]["folder"];

  // Its better then them using a ton of PHP errors.
  // Changed by Milosch on 3-26-2001 - This check was not working, and the code progressed to giving stream pointer errors
  // From the msg_imap class.  I tried to clean it up here so I could see what was happening.
  if (!$PHP_SELF) global $PHP_SELF;  // This was a problem for me.
  $attop   = ereg($phpgw_info['server']['webserver_url'] . '/index.php',$PHP_SELF);
  $inprefs = ereg("preferences",$PHP_SELF);

  if (!$inprefs) $mailbox = $phpgw->msg->login($folder); // Changed this to not try connection in prefs

  if (!$mailbox && !$attop) {
     echo "<p><center><b>" . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")
        . "</b></center>";
     $phpgw->common->phpgw_exit(True);
  }

  function decode_header_string($string) {
    global $phpgw;

    if($string) {
      $pos = strpos($string,"=?");
      if(!is_int($pos)) { return $string; }
      $preceding = substr($string,0,$pos); // save any preceding text
      $end = strlen($string);
      $search = substr($string,$pos+2,$end - $pos - 2 ); // the mime header spec says this is the longest a single encoded word can be
      $d1 = strpos($search,"?");
      if(!is_int($d1)) { return $string; }
      $charset = strtolower(substr($string,$pos+2,$d1));
      $search = substr($search,$d1+1);
      $d2 = strpos($search,"?");
      if(!is_int($d2)) { return $string; }
      $encoding = substr($search,0,$d2);
      $search = substr($search,$d2+1);
      $end = strpos($search,"?=");
      if(!is_int($end)) { return $string; }
      $encoded_text = substr($search,0,$end);
      $rest = substr($string,(strlen($preceding.$charset.$encoding.$encoded_text)+6));
      if(strtoupper($encoding) == "Q") {
	      $decoded = $phpgw->msg->qprint(str_replace("_"," ",$encoded_text));
      }
      if (strtoupper($encoding) == "B") {
        $decoded = urldecode(base64_decode($encoded_text));
      }
      return $preceding . $decoded . decode_header_string($rest);
    } else return $string;
  }

  function list_folders($mailbox)
  {
    global $phpgw, $phpgw_info;
    // UWash patched for Maildir style: $Maildir.Junque
    // Cyrus style: INBOX.Junque
    // UWash style: ./aeromail/Junque

    if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"]) {
      while($pref = each($phpgw_info["user"]["preferences"]["nntp"])) {
	      $phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
	      while($phpgw->db->next_record()) {
	        echo '<option value="' . urlencode($phpgw->db->f("name")) . '">' . $phpgw->db->f("name")
	           . '</option>';
        }
      }
    } else {
      if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UW-Maildir") {
        $stdoffset = 1;  // Used below to setup $nm
	      if ( isset($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) ) {
          if ( empty($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) ) {
            $filter = "";
          } else {
	          $filter = $phpgw_info["user"]["preferences"]["email"]["mail_folder"];
          }
	      }
      } elseif ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus") {
	      $filter = "INBOX";
	      $stdoffset = 1;
      } else {
	      $filter = "mail/";
        $stdoffset = 1;
      }

      $mailboxes = $phpgw->msg->listmailbox($mailbox,"{".$phpgw_info["user"]["preferences"]["email"]["mail_server"].":".$phpgw_info["user"]["preferences"]["email"]["mail_port"]."}",$filter."*");  
      if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] != "pop3")
        if (gettype($mailboxes) == "array") {
 	        sort($mailboxes); // added sort for folder names 
        }
        if($mailboxes) {
	        $num_boxes = count($mailboxes);
	        if ($filter != "INBOX") { 
	          echo '<option value="INBOX">INBOX</option>'; 
	        }
	        for ($index = 0; $index < $num_boxes; $index++) {
	          $nm = substr($mailboxes[$index], strrpos($mailboxes[$index], "}") + $stdoffset, strlen($mailboxes[$index]));
	          echo '<option value="';
	          if ($nm != "INBOX") {
	             $foldername = $phpgw->msg->deconstruct_folder_str($nm);
	          } else {
	             $foldername = "INBOX";
	          }
	          echo urlencode($foldername) . '">' . $foldername . '</option>';
	          echo "\n";
	        }
        } else {
	      echo '<option value="INBOX">INBOX</option>';
      }
    }
  }

  function get_mime_type($de_part) {
    $mime_type = "unknown";
    if (isset($de_part->type) && $de_part->type) {
      switch ($de_part->type) {
	      case TYPETEXT:		$mime_type = "text"; break;
	      case TYPEMESSAGE:	$mime_type = "message"; break;
      	case TYPEAPPLICATION:	$mime_type = "application"; break;
      	case TYPEAUDIO:		$mime_type = "audio"; break;
      	case TYPEIMAGE:		$mime_type = "image"; break;
      	case TYPEVIDEO:		$mime_type = "video"; break;
      	case TYPEMODEL:		$mime_type = "model"; break;
      	default:		$mime_type = "unknown";
      }
    }
    return $mime_type;
  }

  function get_mime_encoding($de_part) {
    $mime_encoding = "other";
    if (isset($de_part->encoding) && $de_part->encoding) {
      switch ($de_part->encoding) {
      	case ENCBASE64:			$mime_encoding = "base64"; break;
      	case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
      	case ENCOTHER:			$mime_encoding = "other";  break;
      	default:			$mime_encoding = "other";
      }
    }
    return $mime_encoding;
  }

  function get_att_name($de_part)
  {
    $att_name = "Unknown";
    if ($de_part->ifparameters) {
      for ($i = 0; $i < count($de_part->parameters); $i++) 
      {
        $param = $de_part->parameters[$i];
        if (strtoupper($param->attribute) == "NAME") {
          $att_name = $param->value;
        }
      }
    }
    return $att_name;
  }

  function attach_display($de_part, $part_no)
  {
    global $msgnum, $phpgw, $phpgw_info, $folder;
    $mime_type = get_mime_type($de_part);  
    $mime_encoding = get_mime_encoding($de_part);

    $att_name = "unknown";

    for ($i = 0; $i < count($de_part->parameters); $i++)
    {
      $param = $de_part->parameters[$i];
      if (strtoupper($param->attribute) == "NAME")
      {
        $att_name = $param->value;
	      $url_att_name = urlencode($att_name);
	      $att_name = decode_header_string($att_name);
      }
    }

//    $jnk = "<a href=\"".$phpgw->link("get_attach.php","folder=".$phpgw_info["user"]["preferences"]["email"]["folder"]
    $jnk = "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/get_attach.php","folder=".$folder
		       ."&msgnum=$msgnum&part_no=$part_no&type=$mime_type"
		       ."&subtype=".$de_part->subtype."&name=$url_att_name"
		       ."&encoding=$mime_encoding")."\">$att_name</a>";
    return $jnk;
  }

  function inline_display($de_part, $part_no)
  {
    global $mailbox, $msgnum, $phpgw, $phpgw_info;
    $mime_type = get_mime_type($de_part);
    $mime_encoding = get_mime_encoding($de_part);

    $dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_no);

    $tag = "pre";
    $jnk = $de_part->ifdisposition ? $de_part->disposition : "unknown";
    if ($mime_encoding == "qprint")
    {
      $dsp = $phpgw->msg->qprint($dsp);
      $tag = "tt";
    }

    // Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
    // a better way to do message wrapping

    if (strtoupper($de_part->subtype) == "PLAIN")
    {
      // nlbr and htmlentities functions are strip latin5 characters
      if (strtoupper(lang("charset")) <> "BIG5")
         $dsp = $phpgw->strip_html($dsp);
      $dsp = ereg_replace( "^","<p>",$dsp);
      $dsp = ereg_replace( "\n","<br>",$dsp);
      $dsp = ereg_replace( "$","</p>", $dsp);
      $dsp = make_clickable($dsp);
      echo "<table border=\"0\" align=\"left\" cellpadding=\"10\" width=\"80%\">"
           ."<tr><td>$dsp</td></tr></table>";
    } else if (strtoupper($de_part->subtype) == "HTML") {
      output_bound(lang("section").":" , "$mime_type/".strtolower($de_part->subtype));
      echo $dsp;
    } else {
      output_bound(lang("section").":" , "$mime_type/".strtolower($de_part->subtype));
      echo "<$tag>$dsp</$tag>\n";
    }
  }

  function output_bound($title, $str)
  {
    global $phpgw_info;
    echo "</td></tr></table>\n"
      . "<table border=\"0\" cellpadding=\"4\" cellspacing=\"3\" "
      . "width=\"700\">\n<tr><td bgcolor\"" . $phpgw_info["theme"]["th_bg"] . "\" " 
      . "valign=\"top\"><font size=\"2\" face=\"" . $phpgw_info["theme"]["font"] . "\">"
      . "<b>$title</b></td>\n<td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" "
      . "width=\"570\"><font size=\"2\" face=\"" . $phpgw_info["theme"]["font"] . "\">"
      . "$str</td></tr></table>\n<p>\n<table border=\"0\" cellpadding=\"2\" "
      . "cellspacing=\"0\" width=\"100%\"><tr><td>";
  }

  function image_display($folder, $msgnum, $de_part, $part_no, $att_name)  {
    global $phpgw;
    global $phpgw_info;

    output_bound(lang("image").":" , $att_name);
    $extra_parms = "folder=".urlencode($folder)."&m=".$msgnum
		 . "&p=".$part_no."&s=".strtolower($de_part->subtype)."&n=".$att_name;
    if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"]) 
      $extra_parms .= "&newsmode=on";
    $view_link = $phpgw->link("/".$phpgw_info['flags']['currentapp']."/view_image.php",$extra_parms);
    echo "\n<img src=\"".$view_link."\">\n<p>\n";
  }

  // function make_clickable taken from text_to_links() in the SourceForge Snipplet Library
  // http://sourceforge.net/snippet/detail.php?type=snippet&id=100004
  // modified to make mailto: addresses compose in phpGW
  function make_clickable($data)
  {
    global $phpgw;

    if(empty($data))
    {
      return $data;
    }

    $lines = split("\n",$data);

    while ( list ($key,$line) = each ($lines))
    {

      $line = eregi_replace("([ \t]|^)www\."," http://www.",$line);
      $line = eregi_replace("([ \t]|^)ftp\."," ftp://ftp.",$line);
      $line = eregi_replace("(http://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
      $line = eregi_replace("(https://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
      $line = eregi_replace("(ftp://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
      $line = eregi_replace("([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))",
               "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/compose.php","folder=".urlencode($phpgw_info["user"]["preferences"]["email"]["folder"]))
               ."&to=\\1\">\\1</a>", $line);

      $newText .= $line . "\n";

    }

    return $newText;
  }
?>
