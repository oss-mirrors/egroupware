<?php
  /**************************************************************************\
  * phpGroupWare Email - NNTP Mail emulator                                  *
  * http://www.phpgroupware.org/api                                          *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Mail function abstraction for NNTP servers                               *
  * Copyright (C) 2000, 2001 Mark Peters                                     *
  * -------------------------------------------------------------------------*
  * This library is part of phpGroupWare (http://www.phpgroupware.org)       * 
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */

  class msg extends msg_common
  {

//    var $net;
    var $folder;
    var $number_msgs;
    var $first_msg;
    var $last_msg;
    var $msg_numbers = array();
    var $tried_once = 0;

    function msg() {
      global $phpgw;

      $this->msg_common_();
//      $this->net = new network(True);
//      $phpgw->network->errorset = 0;
//      $phpgw->network->set_addcrlf(True);
    }

    function append($stream, $folder = "Sent", $header, $body, $flags = "") {
      return false;
    }

    function close($stream,$flags="") {
      global $phpgw;
      if (!$phpgw->network->msg2socket("QUIT","^205",&$response)) return 0;
      return $phpgw->network->close_port();
    }

    function fetchbody($stream,$msgnr,$partnr=0,$flags="") {
      global $phpgw;
      if (!$this->got_structure) $struct = $this->fetchstructure($stream,$msgnr);
      $phpgw->db->query("SELECT body FROM news_msg WHERE con=".$this->folder." AND msg=".$msgnr);
      $phpgw->db->next_record();
      $body = unserialize(stripslashes($phpgw->db->f("body")));
      if(!$body[0]) {
	$body = $this->get_body($stream,$msg_num);
	$phpgw->db->query("UPDATE news_msg SET body='".addslashes(serialize($body))."' WHERE con=".$this->folder." AND msg=".$msgnr);
      }
//      for($i=1;$i<=$body[0];$i++) echo "body[$i] = ".$body[$i]."<br>\n";
      $bodystart = false;
      $i=0;
      $partstart = true;
      $partstop = true;
      $multipart = false;
      if ($this->boundary) {
        $thispart  = 0; $partstart = false; $partstop = false;
        $multipart = true; $boundary = "--".$this->boundary;
      }
      $message = "";
      for($i=1;$i<=$body[0];$i++) {
	$line=$body[$i];
	if(trim($line) == "") {
	  if ($multipart && ($thispart == $partnr)) {
	    $partstart = true;
	  } else {
	    $bodystart = true;
	  }
	}
	if($multipart && is_int(strpos($line,$boundary)) && !strpos($line,$boundary)) {
//	if($multipart && ereg("^".$boundary,$line)) {
          if ($thispart == $partnr) $partstop = true;
          $thispart++;
        }
        if (chop($line) == ".") {
          $line = "";
        } else {
          $pos = strpos($line,".");
          if (is_int($pos) && !$pos)
            $line = substr($line,1);
        }
        if (!$multipart) {
          if (is_string($line) && $line && $bodystart)
	    if(!ereg("^Content-",$line) && chop($line) <> "" && !ereg("^--".$boundary,$line)) $message .= $line;
        } elseif (is_string($line) && $line && $partstart && !$partstop) {
	    if(!ereg("^Content-",$line) && chop($line) <> "" && !ereg("^--".$boundary,$line)) $message .= $line;
        }
      }
//echo $message;
      return $message;
    } 
     
    function fetchheader($stream,$msg_num) {
      $header = array();
      $header = $this->get_header($stream,$msg_num);
      return implode("\n",$header);
    } 
 
    function fetchstructure($stream,$msg_num,$flags="") {
      global $phpgw;

      $mime_msg = False;
      $phpgw->db->query("SELECT mime_version, body FROM news_msg WHERE con=".$this->folder." AND msg=".$msg_num);
      if (!$phpgw->db->num_rows()) {
	$header = array();
	$header = $this->get_header($stream,$msg_num);
	if (!$header) return false;
	for ($i=1;$i<=$header[0];$i++)
	  if (ereg("^mime-version",strtolower($header[$i]))) $mime_msg = True;
	$phpgw->db->query("SELECT mime_version, body FROM news_msg WHERE con=".$this->folder." AND msg=".$msg_num);
      }
      $phpgw->db->next_record();
      if ($phpgw->db->f("mime_version")) $mime_msg = True;
      $body = unserialize(stripslashes($phpgw->db->f("body")));
//      if(!$mime_msg) {
//	$boundary = uniqid("");
//	$phpgw->db->query("UPDATE news_msg SET content_type='".addslashes("multipart/mixed; boundary=\"".$boundary."\"")."' WHERE con=".$this->folder." AND msg=".$msg_num);
//	$phpgw->db->query("UPDATE news_msg SET content_transfer_encoding='".addslashes("7bit")."' WHERE con=".$this->folder." AND msg=".$msg_num);
//	$phpgw->db->query("UPDATE news_msg SET mime_version='".addslashes("1.0")."' WHERE con=".$this->folder." AND msg=".$msg_num);
//      }
      $info = $this->get_structure($msg_num,"header");
      $boundary = $this->get_boundary(&$info);
      $boundary = str_replace("\"","",$boundary);
      $this->boundary = $boundary;
//      echo "Boundary = ".$boundary."<br>\n";
      if (!$mime_msg) $info->type = 1;
      if ($info->type == 1) { // multipart
	if(!$body[0]) {
	  $body = $this->get_body($stream,$msg_num);
	  $phpgw->db->query("UPDATE news_msg SET body='".addslashes(serialize($body))."' WHERE con=".$this->folder." AND msg=".$msg_num);
	}
        for ($i=1;$i<=$body[0];$i++) {
//	  echo "body[$i] = ".$body[$i]."<br>\n";
	  if(ereg("----".$boundary,$body[$i])) break;
	  if(ereg("--".$boundary,$body[$i]))
	    $info->parts[] = $this->get_structure($msg_num,"body",($i + 1),true);
//          $pos1 = strpos($body[$i],"--$boundary");
//          $pos2 = strpos($body[$i],"--$boundary--");
//	  echo "pos1 = ".$pos1."<br>\n";
//	  echo "pos2 = ".$pos2."<br>\n";
//	  echo "Boundary = ".$boundary."<br>\n";
//          if (is_int($pos2) && !$pos2) {
//            break;
//          }
//          if (is_int($pos1) && !$pos1) {
//            $info->parts[] = $this->get_structure($msg_num,"body",($i + 1),true);
//          }
        }
      }
      $this->got_structure = true;
      return $info;
    } 

//    function get_addr_details($people,$address,$header,$count) {
    function get_addr_details($people,$address) {
      global $phpgw_info;
      if (!trim($address)) return false;
      // check wether this header info is split to multiple lines
//      $done = false;
//      do {
//	$pos = strpos($header[$count+1]," ");
//	if (is_int($pos) && !$pos) {
//	  $count++;
//	  $address .= chop($header[$count]);
//	} else {
//	  $done = true;
//	}
//      } while (!$done);
      $temp = $people . "address";
      if ($people == "return_path") {
	$this->$people = htmlspecialchars($address);
//      } elseif ($people == "newsgroups") {
//	$this->newsgroups = htmlspecialchars($address);
      } else {
	$this->$temp = htmlspecialchars($address);
      }

      For ($i=0,$pos=1;$pos;$i++) {
	$addr_details = new msg_aka;
	$pos = strpos($address,"<");
	$pos3 = strpos($address,"(");
	if (is_int($pos)) {
	  $pos2 = strpos($address,">");
	  if ($pos2 == $pos + 1) {
	    $addr_details->adl = "nobody@nowhere";
	  } else {
	    $addr_details->adl = substr($address,$pos+1,$pos2 - $pos -1);
	  }
	  if ($pos) {
	    $addr_details->personal = substr($address,0,$pos - 1);
	  }
	} elseif (is_int($pos3)) {
	  $pos2 = strpos($address,")");
	  if ($pos2 == $pos3+1) {
	    $addr_details->personal = "nobody";
	  } else {
	    $addr_details->personal = substr($address,$pos3 + 1,$pos2 - $pos3 - 1);
	  }
	  if ($pos3) {
	    $addr_details->adl = substr($address,0,$pos3 - 1);
	  }
	} else {
	  $addr_details->adl = $address;
	  $addr_details->personal = $address;
	}
	$pos3 = strpos($addr_details->adl,"@");
	if (!$pos3) {
	  if (!$pos) $addr_details->mailbox = $addr_details->adl;
	  $addr_details->host = $phpgw_info["server"]["mail_suffix"];
	  $details[$i] = $addr_details;
	  return $details;
	}
	$addr_details->mailbox = substr($addr_details->adl,0,$pos3);
	$addr_details->host    = substr($addr_details->adl,$pos3+1);
	$pos = ereg("\"",$addr_details->personal);
        if ($pos) $addr_details->personal = substr($addr_details->personal,1,strlen($addr_details->personal)-2);
        $pos = strpos($address,",");
	if ($pos) $address = trim(substr($address,$pos+1));
	$details[$i] = $addr_details;
      }
      return $details;
    }

    function get_body($stream,$msg_num) {
      global $phpgw;
      if(!$phpgw->network->msg2socket("BODY $msg_num","^222",&$response)) return false;
      $body = array();
      $i = 0;
      while ($line = $phpgw->network->read_port()) {
	if (chop($line) == ".") {
	  if(count($body) == 0) $body[$i++] = "Body Not Found!\n";
	  break;
	}
	$body[$i++] = $line;
      }
      $body[0] = $i - 1;
      if($this->is_uu_encoded($body)) {
	$boundary = uniqid("");
	$body = $this->split_uuencoded_into_parts($body,$boundary);
	$phpgw->db->query("UPDATE news_msg SET content_type='".addslashes("multipart/mixed; boundary=\"".$boundary."\"")."' WHERE con=".$this->folder." AND msg=".$msg_num);
//	$phpgw->db->query("UPDATE news_msg SET content_transfer_encoding='".addslashes("7bit")."' WHERE con=".$this->folder." AND msg=".$msg_num);
	$phpgw->db->query("UPDATE news_msg SET mime_version='".addslashes("1.0")."' WHERE con=".$this->folder." AND msg=".$msg_num);
      }
      if (!$this->is_mime_encoded($msg_num)) {
	$boundary=uniqid("----=_NextPart");
	$k = 1;
	$tempbody[$k++] = "--".$boundary;
	$tempbody[$k++] = "Content-Type: text/plain; charset=us-ascii";
	$tempbody[$k++] = "Content-Transfer-Encoding: 7bit";
	for($i=1;$i<=$body[0];$i++) {
	  $tempbody[$k++] = $body[$i];
	}
	$tempbody[$k] = "----".$boundary;
	$tempbody[0] = $k;
	$body = $tempbody;
	$phpgw->db->query("UPDATE news_msg SET content_type='".addslashes("multipart/mixed; boundary=\"".$boundary."\"")."' WHERE con=".$this->folder." AND msg=".$msg_num);
//	$phpgw->db->query("UPDATE news_msg SET content_transfer_encoding='".addslashes("7bit")."' WHERE con=".$this->folder." AND msg=".$msg_num);
	$phpgw->db->query("UPDATE news_msg SET mime_version='".addslashes("1.0")."' WHERE con=".$this->folder." AND msg=".$msg_num);
      }
      return $body;
    }

    function get_body_1($stream,$msg_num) {
      global $phpgw;
      if(!$phpgw->network->msg2socket("BODY $msg_num","^222",&$response)) return false;
      $i = 0;
      $j = 0;
      $uuencoded = False;
      $begin_found = False;
      while($line = $phpgw->network->read_port()) {
	if (chop($line) == ".") break;
	if (substr($line,0,1) == "." && strlen($line) == 4) $line = substr($line,1);
//	echo "Line = ".$line."<br>\n";
	if (ereg("^BEGIN --- CUT HERE --- Cut Here --- cut here --- ",$line)) continue;
	if (ereg("^END --- CUT HERE --- Cut Here --- cut here --- ",$line)) continue;
	if (strpos($line,"x-uuencoded")) {
	  $line = str_replace("x-uuencoded","base64",$line);
	  $uuencoded = True;
	  $begin_found = True;
	  continue;
	}
	if (ereg("^begin",$line) && !$uuencoded) {
	  $lines = ereg_replace("\n","",$line);
	  $temparray = explode(" ",$lines);
	  if (is_int((int)$temparray[1])) {
	    $newpart = 0;
	    for($k=2,$filename="";$k<count($temparray);$k++)
	      $filename .= $temparray[$k];
	    $filename = substr($filename,0,strlen($filename)-1);
	  }
	  $i++;
	  $body[$i++] = " --".$this->boundary;
	  $body[$i++] = "Content-Type: ".$this->getMimeType(strtoupper($filename))."; name=\"".$filename."\"";
//	  $body[$i++] = "Content-Type: ".$this->getMimeType(strtoupper($filename));
	  $body[$i++] = "Content-Transfer-Encoding: base64";
	  $body[$i] = "Content-Disposition: inline; filename=\"".$filename."\"";
	  $begin_found = True;
	  continue;
	}
        if ($begin_found) {
	  if(!ereg("^end",$line)) $binary[$j++] = $line;
	} else {
	  $i++;
	  $body[$i] = $line;
	}
	if (ereg("^end",$line) && $begin_found) {
	  $i++;
	  $body[$i] = base64_encode($this->uudecode($binary));
	  if (!$uuencoded) {
	    $i++;
	    $body[$i] = " --".$this->boundary."--";
	  }
	  $uuencoded = False;
	  $begin_found = False;
	  $binary = Array();
	  $j=0;
	}
      }
      $body[0] = $i;
      return $body;
    }

    function get_boundary($info) {
      for ($i=0;$i<count($info->parameters);$i++) {
	$temp = $info->parameters[$i];
	if ($temp->attribute == "boundary")
	  $boundary = $temp->value;
      }
      return trim($boundary);
    }

    function get_ctype($header,$info,$i,$content) { // used by pop_fetchstructure only
      $pos = strpos($content,"/");
      if (is_int($pos) && $pos) {
	$prim_type = strtolower(substr($content,0,$pos));
      } else {
	$prim_type = strtolower($content);
      }
      $pos = strpos($prim_type,";");
      if (is_int($pos) && $pos) $prim_type = substr($prim_type,0,$pos);
      switch ($prim_type) {
	case "text"        : $info->type = 0; break;
	case "multipart"   : $info->type = 1; break;
	case "message"     : $info->type = 2; break;
	case "application" : $info->type = 3; break;
	case "audio"       : $info->type = 4; break;
	case "image"       : $info->type = 5; break;
	case "video"       : $info->type = 6; break;
	default            : $info->type = 7; break;
      }
      $pos = strpos($content,"/");
      if (is_int($pos)) {
	$pos_para = strpos($content,";");
	if (is_int($pos_para) && $pos_para) {
	  $info->subtype = strtoupper(substr($content,($pos+1),($pos_para - $pos -1)));
	} else {
	  $info->subtype = strtoupper(substr($content,($pos+1)));
	}
	$info->ifsubtype = true;
      }
      if (is_int($pos_para)) $i = $this->get_mime_param($header,&$info,$i);
    }

    function get_header($stream,$msg_num) {
      global $phpgw;
      $header = array();
      if(!$phpgw->network->msg2socket("HEAD ".$msg_num,"^221",&$response)) return 0;
      $i = 1;
      while($line = $phpgw->network->read_port()) {
	if (chop($line) == ".") break;
	$header[$i++] = $line;
      }
      $header[0] = $i - 1;

      return $header;
    }

    function get_mime_param($header,$info,$i) { // used by pop_fetchstructure only
      $pos = strpos($header[$i],";");
      $content = trim(substr($header[$i],$pos+1));
      $done = false;
      do {
	$more = strpos($header[$i+1]," ");
	if (strlen($content)==0 && (is_int($more) && !$more)) {
	  $i++;
	  $content = trim($header[$i]);
	}
	if (strlen($content)==0) break;
	$pos = strpos($content,"=");
	if (!(is_int($pos) && $pos)): return $i; endif;
	$val = str_replace("\"","",substr($content,$pos+1));
	$info->parameters[] = new msg_params(substr($content,0,$pos),$val);
	$info->ifparameters = true;
	$content="";
	if (!is_int($more) || $more) $done = true;
      } while (!$done);
      return $i;
    }

    function get_msg_info($stream,$msg_nr,$msg_id) {
      $h_info = new msg_headinfo;
      $t_info = array();

      $h_info = $this->header($stream,$msg_nr);

      if(!$h_info) $h_info = $this->header($stream,$msg_nr);

      $t_info[0] = $msg_nr;
      $t_info[1] = $msg_id;
      $t_info[2] = $h_info->udate;
      $t_info[3] = $h_info->udate;
      $t_info[4] = $h_info->fromaddress;
      $t_info[5] = $h_info->toaddress;
      $t_info[6] = $h_info->ccaddress;
      $t_info[7] = $h_info->subject;
      $t_info[8] = 0;
      $t_info[9] = $h_info->Size;

      return $t_info;
    }

    function get_structure($msg_num,$part,$part_nr=0,$is_multi=false) {
      global $phpgw;

      $info = new msg_struct;
      if ($is_multi) {
	$info->type = 0;
	$info->encoding = 0;
      }
      $phpgw->db->query("SELECT content_type, content_transfer_encoding, content_description, mime_version, msglines, body FROM news_msg WHERE con=".$this->folder." AND msg=".$msg_num);
      $phpgw->db->next_record();
      switch($part) {
	case "header" :
	  $msg_part[0] = 5;
	  $msg_part[1] = "Content-Type: ".stripslashes($phpgw->db->f("content_type"));
	  $msg_part[2] = "Content-Transfer-Encoding: ".stripslashes($phpgw->db->f("content_transfer_encoding"));
	  $msg_part[3] = "Content-Description: ".stripslashes($phpgw->db->f("content_description"));
	  $msg_part[4] = "MIME-Version: ".stripslashes($phpgw->db->f("mime_version"));
	  $msg_part[5] = "Lines: ".$phpgw->db->f("msglines");
	  $start = 1;
	  break;
	case "body" :
//	  echo "Inside get_structure(body)<br>\n";
	  $msg_part = unserialize(stripslashes($phpgw->db->f("body")));
	  $start = $part_nr;
//	  if(!$msg_part[0]) {
//	    $msg_part = $this->get_body($msg_num);
//	    $phpgw->db->query("UPDATE news_msg SET body='".addslashes(serialize($msg_part))."' WHERE con=".$this->folder." AND msg=".$msg_num);
//	  }
	  break;
	default: break;
      }
//      echo "msg_structure.boundary = ".$this->boundary."<br>\n";
      for ($i=$start;$i<=$msg_part[0];$i++) {
	$msg_part[$i] = trim($msg_part[$i]);
//	echo "msg_part = ".$msg_part[$i]."<br>\n";
	$pos = strpos($msg_part[$i]," ");
	if (is_int($pos) && ($pos==0)) continue;
	if ($part == "body" && ereg("^--".$this->boundary."--",$msg_part[$i])) break;
	$keyword = strtolower(substr($msg_part[$i],0,$pos));
	$content = trim(substr($msg_part[$i],$pos+1));
	switch ($keyword) {
	  case "content-type:" :
	    $this->get_ctype($msg_part,&$info,&$i,$content);
	    break;
	  case "content-transfer-encoding:" :
	    switch (strtolower($content)) {
	      case "7bit"             : $info->encoding = 0; break;
	      case "8bit"             : $info->encoding = 1; break;
	      case "binary"           : $info->encoding = 2; break;
	      case "base64"           : $info->encoding = 3; break;
	      case "quoted-printable" : $info->encoding = 4; break;
	      case "x-uuencoded"      : $info->encoding = 6; break;
	      default                 : $info->encoding = 5; break;
	    }
	    break;
	  case "content-description:" :
	    $info->description   = $content;
	    $i = $this->more_info($msg_part,$i,&$info,"description");
	    $info->ifdescription = true;
	    break;
	  case "content-identifier:" :
	    $info->id   = $content;
	    $i = $this->more_info($msg_part,$i,&$info,"id");
	    $info->ifid = true;
	    break;
	  case "lines:" : $info->lines = $content; break;
	  case "content-length:" : $info->bytes = $content; break;
	  case "content-disposition:" :
	    $info->disposition   = $content;
	    $i = $this->more_info($msg_part,$i,&$info,"disposition");
	    $info->ifdisposition = true;
	    break;
	  case "mime-version:" :
	    $pos = strpos($content,"=");
	    $info->parameters[] = new msg_params("MIME-Version",substr($content,$pos+1));
	    $info->ifparameters = true;
	    break;
	  default : break;
	}
      }
      return $info;
    }

    function set_stat_info($response) {
      $tarray = explode(" ",$response);
      $tarray[2] = str_replace("\r","",$tarray[2]);
      $tarray[2] = str_replace("\n","",$tarray[2]);
      return array("id"=>(int)$tarray[1],"uid"=>$tarray[2]);
    }
 
    function get_uid($stream) {
      global $phpgw;
      if (!$phpgw->network->msg2socket("stat ".$this->first_msg,"^223",&$response)) return 0;
      $uid_list = array(array("id","uid"));
      $i = 1;
      $uid_list[$i] = $this->set_stat_info($response);
      while ($phpgw->network->msg2socket("next","^223",&$response)) {
	$i++;
	$uid_list[$i] = $this->set_stat_info($response);
      }
      return $uid_list;
    }

    function getMimeType($file) {
      global $phpgw_info;

      $file=basename($file);
      $mimefile=$phpgw_info["server"]["api_inc"]."/phpgw_mime.types";
      $fp=fopen($mimefile,"r");
      $contents = explode("\n",fread ($fp, filesize($mimefile)));
      fclose($fp);

      $parts=explode(".",$file);
      $ext=strtolower($parts[(sizeof($parts)-1)]);
      for($i=0;$i<sizeof($contents);$i++) {
	if (! ereg("^#",$contents[$i])) {
	  $line=split("[[:space:]]+", $contents[$i]);
	  if (sizeof($line) >= 2) {
	    for($j=1;$j<sizeof($line);$j++) {
	      if (strtolower($line[$j]) == $ext) {
		return strtolower($line[0]);
	      }
	    }
	  }
	}
      }
      return "text/plain";
    }

    function parse_header() {
      global $phpgw;
      $info = new msg_headinfo;
      $phpgw->db->next_record();
      $info->from = $this->get_addr_details("from",stripslashes($phpgw->db->f("fromadd")));
      $info->to   = $this->get_addr_details("to",stripslashes($phpgw->db->f("toadd")));
      $info->cc   = $this->get_addr_details("cc",stripslashes($phpgw->db->f("ccadd")));
      $info->bcc  = $this->get_addr_details("bcc",stripslashes($phpgw->db->f("bccadd")));
      $info->reply_to = $this->get_addr_details("reply_to",stripslashes($phpgw->db->f("reply_to")));
      $info->sender = $this->get_addr_details("sender",stripslashes($phpgw->db->f("sender")));
      $info->return_path = $this->get_addr_details("return_path",stripslashes($phpgw->db->f("return_path")));
      $info->subject = stripslashes($phpgw->db->f("subject"));
      $info->Subject = stripslashes($phpgw->db->f("subject"));
      $info->Size = $phpgw->db->f("msgsize");
      $info->message_id = stripslashes($phpgw->db->f("message_id"));
      $info->references = stripslashes($phpgw->db->f("reference"));
      $info->in_reply_to = stripslashes($phpgw->db->f("in_reply_to"));
      $info->follow_up_to = stripslashes($phpgw->db->f("follow_up_to"));
      $info->udate = $phpgw->db->f("udate");
      return $info;
    }

    function header($stream,$msg_nr,$fromlength="",$tolength="",$defaulthost="") {
      global $phpgw;
      $phpgw->db->query("SELECT * FROM news_msg WHERE con=".$this->folder." AND msg=".$msg_nr);
      if($phpgw->db->num_rows()) { return $this->parse_header(); }
      $header = array();
//      $info->Size = $this->size_msg($stream,$msg_nr);
      $header = $this->get_header($stream,$msg_nr);
      if (!$header) { return false; }
      $phpgw->db->query("INSERT INTO news_msg(con,msg,body) VALUES(".$this->folder.",".$msg_nr.",' ')");
      for ($i=1;$i<=$header[0];$i++) {
        $pos = strpos($header[$i]," ");
        if (is_int($pos) && !$pos) continue;
        $keyword = strtolower(substr($header[$i],0,$pos));
        $content = trim(substr($header[$i],$pos+1));
        switch ($keyword) {
	  case "path:"   :
	    $phpgw->db->query("UPDATE news_msg SET path='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
          case "from"    :
          case "from:"   :
	    $phpgw->db->query("UPDATE news_msg SET fromadd='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "newsgroups:" :
          case "to"      :
          case "to:"     :  // following two lines need to be put into a loop!
	    $phpgw->db->query("UPDATE news_msg SET toadd='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "cc"      :
          case "cc:"     :
	    $phpgw->db->query("UPDATE news_msg SET ccadd='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "bcc"     :
          case "bcc:"    :
	    $phpgw->db->query("UPDATE news_msg SET bccadd='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "reply-to"  :
          case "reply-to:" :
	    $phpgw->db->query("UPDATE news_msg SET reply_to='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "sender"  :
          case "sender:" :
	    $phpgw->db->query("UPDATE news_msg SET sender='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "return-path"  :
          case "return-path:" :
	    $phpgw->db->query("UPDATE news_msg SET return_path='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
          case "subject"  :
          case "subject:" :
          case "Subject:" :
	    $phpgw->db->query("UPDATE news_msg SET subject='".addslashes(htmlspecialchars($content))."' WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;

          // only temp
	  case "lines:"      :
	    $phpgw->db->query("UPDATE news_msg SET msglines=".$content.", msgsize=".$content." WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
          case "message-id"  :
          case "message-id:" :
	    $phpgw->db->query("UPDATE news_msg SET uid='".addslashes(htmlspecialchars($content))."', message_id='".addslashes(htmlspecialchars($content))."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "x-ref:"	     :
          case "references:" :
	    $phpgw->db->query("UPDATE news_msg SET reference='".addslashes(htmlspecialchars($content))."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
          case "in-reply-to:" :
	    $phpgw->db->query("UPDATE news_msg SET in_reply_to='".addslashes(htmlspecialchars($content))."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
          case "followup-to:" :
	    $phpgw->db->query("UPDATE news_msg SET follow_up_to='".addslashes(htmlspecialchars($content))."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
          case "date:"   :
	    $phpgw->db->query("UPDATE news_msg SET udate=".$this->make_udate($content)." WHERE con=".$this->folder." AND msg=".$msg_nr);
            break;
	  case "nntp-posting-host:"	:
	    $phpgw->db->query("UPDATE news_msg SET nntp_posting_host='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "nntp-posting-date:"	:
	    $phpgw->db->query("UPDATE news_msg SET nntp_posting_date='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "x-complaints-to:"	:
	    $phpgw->db->query("UPDATE news_msg SET x_complaints_to='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "x-trace:"	:
	    $phpgw->db->query("UPDATE news_msg SET x_trace='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "organization:"	:
	    $phpgw->db->query("UPDATE news_msg SET organization='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "content-type:"	:
	    $phpgw->db->query("UPDATE news_msg SET content_type='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "x-abuse-info:"	:
	    $pos = strpos($header[($i + 1)]," ");
	    if (is_int($pos) && !$pos) {
	    } elseif (strtolower(substr($header[($i + 1)],0,$pos)) == "x-abuse-info:") {
	      $content .= "\n";
	      $content .= trim(substr($header[($i + 1)],$pos+1));
	      $i++;
	    }
	    $phpgw->db->query("UPDATE news_msg SET x_abuse_info='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "content-transfer-encoding:"	:
	    $phpgw->db->query("UPDATE news_msg SET content_transfer_encoding='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "content-description:"	:
	    $phpgw->db->query("UPDATE news_msg SET content_description='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "x-mailer:"	:
	    $phpgw->db->query("UPDATE news_msg SET x_mailer='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
	  case "mime-version:"	:
	    $phpgw->db->query("UPDATE news_msg SET mime_version='".addslashes($content)."' WHERE con=".$this->folder." AND msg=".$msg_nr);
	    break;
          default        : break;
        }
      }
      $phpgw->db->query("SELECT * FROM news_msg WHERE con=".$this->folder." AND msg=".$msg_nr);
      return $this->parse_header();
    } 

    function is_mime_encoded($msg_nr) {
      global $phpgw;
      $phpgw->db->query("SELECT content_type FROM news_msg WHERE con=".$this->folder." AND msg=".$msg_nr);
      $phpgw->db->next_record();
      $is = False;
      if($phpgw->db->f("content_type")) $is = True;
      return $is;
    }

    function is_uu_encoded($body) {
      $found_begin = false;
      $found_end = false;
      for($i=1;$i<=$body[0];$i++) {
	if (ereg("^begin",$body[$i])) {
	  $tempvar = explode(" ",$body[$i]);
	  if (count($tempvar) > 2 && is_long((int)$tempvar[1])) $found_begin = true;
	} elseif ($found_begin)
	  if (ereg("^end",$body[$i])) $found_end = true;
      }
      return $found_end;
    }

    function listmailbox($stream,$ref,$pattern) { // no other folders on pop
      return false;
    }

    function logout() {
      global $phpgw;
      unlink($this->tempfile);
      if($phpgw->network->socket)
	$phpgw->network->close_port();
    }
     
    function login($folder = "") {
      global $phpgw;
      global $phpgw_info;

      error_reporting(error_reporting() - 2);

      if(!$folder || $folder == "INBOX") {
//	ksort($phpgw_info["user"]["preferences"]["nntp"]);
	$pref = each($phpgw_info["user"]["preferences"]["nntp"]);
	$folder=(int)$pref[0];
	$phpgw->db->query("SELECT name FROM newsgroups WHERE con=$folder");
      } elseif(is_long($folder)) {
	$phpgw->db->query("SELECT name FROM newsgroups WHERE con=$folder");
      }

      if(!is_string($folder)) {
	$this->folder = $folder;
        $phpgw->db->next_record();
        $folder = $phpgw->db->f("name");
      } else {
	$phpgw->db->query("SELECT con FROM newsgroups WHERE name='$folder'");
	$phpgw->db->next_record();
	$this->folder = $phpgw->db->f("con");
      }

//      $this->folder = $folder;

//      echo "Folder = $folder<br>\n";

      $mbox = $this->open($folder,
			  $phpgw_info["server"]["nntp_login_username"],
			  $phpgw_info["server"]["nntp_login_password"]);

      error_reporting(error_reporting() + 2);
      return $mbox;
    }

    function mailboxmsginfo($stream) {
      $info = new msg_mb_info;
      $info->Nmsgs = $this->number_msgs;
      $info->Size  = 0;

      if ($info->Nmsgs) {
         return $info;
      } else {
         return False;
      }
    } 

    function mailcopy($stream,$msg_list,$mailbox,$flags) { // no other mbox on pop
      return false;
    } 
     
    function mail_move($stream,$msg_list,$mailbox) { // no other mbox on pop
      return false;
    } 
     
    function make_udate($msg_date) {
      $monthname = array("jan"=>1,"feb"=>2,"mar"=>3,"apr"=>4,"may"=>5,"jun"=>6,
			 "jul"=>7,"aug"=>8,"sep"=>9,"oct"=>10,"nov"=>11,"dec"=>12);
      $pos = strpos($msg_date,",");
      if ($pos) $msg_date = trim(substr($msg_date,$pos+1));
      $pos = strpos($msg_date," ");
      $day = substr($msg_date,0,$pos);
      $msg_date = trim(substr($msg_date,$pos));
      $month = strtolower(substr($msg_date,0,3));
      $msg_date = trim(substr($msg_date,3));
      $pos  = strpos($msg_date," ");
      $year = trim(substr($msg_date,0,$pos));
      $msg_date = trim(substr($msg_date,$pos));
      $hour = substr($msg_date,0,2);
      $minute = substr($msg_date,3,2);
      $second = substr($msg_date,6,2);
      $pos = strrpos($msg_date," ");
      $tzoff = trim(substr($msg_date,$pos));
      if (strlen($tzoff)==5) {
	$diffh = substr($tzoff,1,2); $diffm = substr($tzoff,3);
	if ((substr($tzoff,0,1)=="+") && is_int($diffh)) {
	  $hour -= $diffh; $minute -= $diffm;
	} else {
	  $hour += $diffh; $minute += $diffm;
	}
	if ($hour > 23) { $day++; $hour -= 24; }
      }
      $utime = mktime($hour,$minute,$second,$monthname[$month],$day,$year);
//      echo "<!-- hour = $hour, minute = $minute, second = $second, month = $month, monthname = ".$monthname[$month].", day = $day, year = $year -->\n";
      return $utime;
    }

    function mode_reader() {
      global $phpgw;
      return $phpgw->network->msg2socket("mode reader","^20[01]",&$response);
    }

    function more_info($header,$i,$info,$infokey) {
      do {
	$pos = strpos($header[$i+1]," ");
	if (is_int($pos) && !$pos) {
	  $i++;
	  $info->$infokey .= ltrim($header[$i]);
	}
      } while (is_int($pos) && !$pos);
      return $i;
    }

    function msg_sort($sorted,$criteria) {
// Debug
//echo "<!-- BEGIN MSG_SORT() -->\n";
// End Debug
//      echo "-- count(msg_info) = ".count($this->msg_info)." --<br>\n";
      for ($i=1;$i<=count($sorted);$i++) {
	$temp[$i] = strtolower($this->msg_info[$i][$criteria]);
	switch ($criteria) {
	  case 8 :              // size is a string here so we have to add
	    do {                // some leading zeros for sorting
	      $temp[$i] = "0".$temp[$i];
	    } while (strlen($temp[$i]) < 12);
	    break;
	  case 4 :
	    $temp[$i] = str_replace("&quot;","",$temp[$i]);
	    break;
	  default     : break;
	}
// Debug
//echo "<!-- temp[$i] = ".$temp[$i]." -->\n";
//echo "<!-- this->msg_info[$i][$criteria] = ".$this->msg_info[$i][$criteria]." -->\n";
// End Debug
      }
      asort($temp);
      for (reset($temp),$i=1; $key = key($temp); next($temp), $i++) {
	$sorted[$i] = $this->msg_info[$key][0];
// Debug
//echo "-- sorted[$i] = ".$this->msg_info[$key][0]." --<br>\n";
// End Debug
      }
// Debug
//echo "<!-- END MSG_SORT() -->\n";
// End Debug
      return $sorted;
    }

    function next_msg() {
      global $phpgw;
      if(!$phpgw->network->msg2socket("next","^223",&$response)) return ((int)$this->last_msg + 1);
      $tarray = explode(" ",$response);
      return $tarray[1];
    }

    function num_msg($stream) { // returns number of messages in the mailbox
      return $this->number_msgs;
    }

    function open($mailbox,$username="",$password="",$flags="") {
      global $phpgw;
      global $phpgw_info;
      global $folder;
      if(!$phpgw->network->open_port($phpgw_info["server"]["nntp_server"],
				$phpgw_info["server"]["nntp_port"],
				15)) return 0;

      $this->postable = ereg("^200",$phpgw->network->read_port());

      if ($username <> "" && $password <> "") {
        if (!$phpgw->network->msg2socket("authinfo user $username","^381",&$response)) return 0;
        if (!$phpgw->network->msg2socket("authinfo pass $password","^281",&$response)) return 0;
      }

      if(!$this->mode_reader()) return 0;
      if(isset($mailbox) && $mailbox) {
	if(!$phpgw->network->msg2socket("group ".$mailbox,"^211",&$response)) return 0;
        $temp_array = explode(" ",$response);
        $this->number_msgs = (int)$temp_array[1];
        $this->first_msg = (int)$temp_array[2];
        $this->last_msg = (int)$temp_array[3];
	$folder = $mailbox;
	return 1;
      } else return 1;
    }    

    function reopen($stream,$mailbox,$flags) {
      return false;
    }
     
    function size_msg($stream,$msg_nr,$lines) {
      global $phpgw;
      if (!$phpgw->network->msg2socket("BODY $msg_nr","^222",&$response)) return 0;
      $size = 0;
      while($line = $phpgw->network->read_port()) {
	if(chop($line) == ".") break;
	$size += strlen($line);
      }
      return $size;
    }

    function sort($stream,$criteria,$reverse="",$options="") {
      global $phpgw;

      if (!$this->num_msg($stream)) return false;     // no msgs - no sort.

      $uid_list = $this->get_uid($stream);

      $phpgw->db->query("DELETE FROM news_msg WHERE con=".$this->folder." AND msg < ".$this->first_msg);

      for ($i=1;$i<count($uid_list);$i++) {
	$sorted[$i] = $uid_list[$i]["id"];
      }

      $j=0;
      $phpgw->db->query("SELECT * FROM news_msg WHERE con=".$this->folder." ORDER BY msg");
      if($phpgw->db->num_rows()) {
	while($phpgw->db->next_record()) {
	  $i = count($this->msg_info);
	  $this->msg_info[$i][0] = $phpgw->db->f("msg");
	  $this->msg_info[$i][1] = stripslashes($phpgw->db->f("uid"));
	  $this->msg_info[$i][2] = $phpgw->db->f("udate");
	  $this->msg_info[$i][3] = $phpgw->db->f("udate");
	  $this->msg_info[$i][4] = stripslashes($phpgw->db->f("fromadd"));
	  $this->msg_info[$i][5] = stripslashes($phpgw->db->f("toadd"));
	  $this->msg_info[$i][6] = stripslashes($phpgw->db->f("ccadd"));
	  $this->msg_info[$i][7] = stripslashes($phpgw->db->f("subject"));
	  $this->msg_info[$i][8] = $phpgw->db->f("msgsize");
	  $this->msg_info[$i][9] = $phpgw->db->f("msglines");
	  if($this->msg_info[$i][0] == $uid_list[$i+1]["id"]) $j++;
	}
      }

      reset($this->msg_info);

//      echo "-- count(uid_list) = ".count($uid_list)." --<br>\n";
//      echo "-- count(msg_info) = ".count($this->msg_info)." --<br>\n";
     
      if ( count($sorted) != $j ) {
	if (count($this->msg_info)>1) {
	  $sorted = $this->msg_sort(&$sorted,0);
	  $this->update_msg_info($stream,$uid_list);
	} else {
	  for ($i=1;$i<count($uid_list);$i++) {
	    $this->msg_info[$i] = $this->get_msg_info($stream,$uid_list[$i]["id"],$uid_list[$i]["uid"]);
	  }
	}
      }
      $criteria = strtolower($criteria);
      switch ($criteria) {
	case 0    : $this->msg_sort(&$sorted,2); break;
	case 2    : $this->msg_sort(&$sorted,4); break;
	case 3    : $this->msg_sort(&$sorted,7); break;
	case 6    : $this->msg_sort(&$sorted,8); break;
	case "sortdate"    : $this->msg_sort(&$sorted,2); break;
	case "sortarrival" : $this->msg_sort(&$sorted,3); break;
	case "sortfrom"    : $this->msg_sort(&$sorted,4); break;
	case "sortto"      : $this->msg_sort(&$sorted,5); break;
	case "sortcc"      : $this->msg_sort(&$sorted,6); break;
	case "sortsubject" : $this->msg_sort(&$sorted,7); break;
	case "sortsize"    : $this->msg_sort(&$sorted,8); break;
	default            : break;
      }

      for ($i=0;$i<count($sorted);$i++) {
	$tsorted[$i] = $sorted[($i+1)];
      }

//      $phpgw->common->appsession($this->msg_info);
//      reset($this->msg_info);

//      echo "-- count(msg_info) = ".count($this->msg_info)." --<br>\n";

      return $tsorted;
    }
     
    function split_uuencoded_into_parts($body,$boundary) {
      global $phpgw;
      global $phpgw_info;

      $binary = Array();
      $tempbody = Array();
      $parts=0;
      $mime_text_header[0] = "--".$boundary;
      $mime_text_header[1] = "Content-Type: text/plain; charset=us-ascii";
      $mime_text_header[2] = "Content-Transfer-Encoding: 7bit";
      $newpart = 1;
      $found_begin = 0;
      $j = 0;
      $k = 1;
      $binary = "";
      for($i=1;$i<=$body[0];$i++) {
	if($newpart && !ereg("^begin",strtolower($body[$i]))) {
	  $tempbody[$k++] = $mime_text_header[0];
	  $tempbody[$k++] = $mime_text_header[1];
	  $tempbody[$k++] = $mime_text_header[2];
	  $tempbody[$k++] = $body[$i];
	  $newpart = 0;
	  $parts++;
        } elseif ($found_begin) {
	  if (!ereg("^end",$body[$i])) {
	    if($body[$i]<>"" || $body[$i]<>"\n") {
	      $binary[$j] = $body[$i];
	      $j++;
	    }
	  } else {
	    $attach = base64_encode($this->uudecode($binary));
	    $content_type=$this->getMimeType(strtolower($filename));
	    if(!$newpart && !$tempbody) $tempbody[$k++] = "--".$mime_text_header[0];
	    $tempbody[$k++] = $mime_text_header[0];
	    $tempbody[$k++] = "Content-Type: ".$content_type."; name=\"".$filename."\"";
	    $tempbody[$k++] = "Content-Transfer-Encoding: base64";
	    $tempbody[$k++] = "Content-Disposition: inline; filename=\"".$filename."\"";
	    $tempbody[$k++] = $attach;
	    $tempbody[$k++] = "--".$mime_text_header[0];
	    $binary = Array();
	    $filename = "";
	    $found_begin = 0;
	    if (chop($body[$i + 1]) <> "" && chop($body[$i + 1]) <> ".") {
	      $newpart = 1;
	      $i += 2;
	    }
	  }
	} elseif (ereg("^begin",$body[$i])) {
	  $body[$i] = ereg_replace("\n","",$body[$i]);
	  $temparray = explode(" ",$body[$i]);
	  if (is_int((int)$temparray[1])) {
	    $newpart = 0;
	    for($k=2,$filename="";$k<count($temparray);$k++)
	      $filename .= $temparray[$k];
	    $filename = substr($filename,0,strlen($filename)-1);
	    $found_begin = 1;
	    $j=0;
	    $parts++;
	  }
	} else {
	  $tempbody[$k++] = $body[$i];
	}
      }
      $tempbody[0] = $k - 1;
      return $tempbody;
    }

    function status($stream,$mailbox,$options) {
      $status = (object) "0";
      return $status;
    }

    function update_msg_info($stream,$uid_list) {
      global $phpgw;

      $t_list = Array(Array());
      $h_info = new msg_headinfo;

      for ($i=1;$i<=count($uid_list);$i++) {
	$found = false;
	for ($k=1;$k<=count($this->msg_info);$k++) {
	  if ($this->msg_info[$k][0] == $uid_list[$i]["id"]) {
	    $t_list[$i] = $this->msg_info[$k];
	    $found = true;
	  }
	  if ($found) continue 2;
	}
	if ($found) break; // else rebuild with new info from server
	$h_info = $this->header($stream,$uid_list[$i]["id"]);
	$t_list[$i][0] = $uid_list[$i]["id"];
	$t_list[$i][1] = $uid_list[$i]["uid"];
	$t_list[$i][2] = $h_info->udate;
	$t_list[$i][3] = $h_info->udate;
	$t_list[$i][4] = $h_info->fromaddress;
	$t_list[$i][5] = $h_info->toaddress;
	$t_list[$i][6] = $h_info->ccaddress;
	$t_list[$i][7] = $h_info->subject;
	$t_list[$i][8] = 0;
	$t_list[$i][9] = $h_info->lines;
      }
      $this->msg_info = $t_list;
      return true;
    }

    function uudecode($str) {
      $file="";
//      echo "-- inside uudecode - ".count($str)." --<br>\n";
      for($i=0;$i<count($str);$i++) {
	if (ereg("^begin",$str[$i]) || ereg("^end",$str[$i])) continue;
	if ($i==count($str)-1 && $str[$i] == "`") $phpgw->common->phpgw_exit();
	$pos=1;
	$d=0;
	$len=(int)(((ord(substr($str[$i],0,1)) ^ 0x20) - ' ') & 077);
	while (($d+3<=$len) && ($pos+4<=strlen($str[$i]))) {
	  $c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
	  $c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
	  $c2=(ord(substr($str[$i],$pos+2,1)) ^ 0x20);
	  $c3=(ord(substr($str[$i],$pos+3,1)) ^ 0x20);
	  $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
	  $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
	  $file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077)      );
	  $pos+=4;
	  $d+=3;
	}
	if (($d+2<=$len) && ($pos+3<=strlen($str[$i]))) {
	  $c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
	  $c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
	  $c2=(ord(substr($str[$i],$pos+2,1)) ^ 0x20);
	  $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
	  $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
	  $pos+=3;
	  $d+=2;
	}
	if (($d+1<=$len) && ($pos+2<=strlen($str[$i]))) {
	  $c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
	  $c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
	  $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
	}
      }
      return $file;
    }
  }
?>
