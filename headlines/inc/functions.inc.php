<?php
  /**************************************************************************\
  * phpGroupWare - news headlines                                            *
  * http://www.phpgroupware.org                                              *
  * Written by Mark Peters <mpeters@satx.rr.com>                             *
  * Based on pheadlines 0.1 19991104 by Dan Steinman <dan@dansteinman.com>   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  function headlines_update($lid,$headlines)
  {
     global $phpgw;
     $phpgw->db->query("DELETE FROM users_headlines WHERE owner='$lid'");
     if (count($headlines) != 0) {
   	for ($i=0;$i<count($headlines);$i++) {
	   $phpgw->db->query("INSERT INTO users_headlines (owner,site) "
			   . "values ('$lid',".$headlines[$i].")");
 	}
     }
  }


class phpHeadlines {
  // socket timeout in seconds
  var $socket_timeout = 20;

  var $current_time;

  var $con = 0;
  var $display = "";
  var $base_url = "";
  var $newsfile = "";
  var $lastread = 0;
  var $newstype = "";
  var $cachetime = 0;
  var $listings = 0;

  // wired news was messing up, I dunno
  // "wired" => array("Wired&nbsp;News","http://www.wired.com","/news_drop/netcenter/netcenter.rdf/","rdf"),	

  // try to get the links for the site
  function getLinks($site) {
    if (! $this->readtable($site))
       return $links;

    if ($this->isCached()) {
       $links = $this->getLinksDB();
    } else {
       $links = $this->getLinksSite();

      if ($links) {
        $this->saveToDB($links);
      } else {
        $links = $this->getLinksDB();
        $this->error_timeout = true;
      }
    }
    return $links;
  }

  // do a quick read of the table
  function readtable($site) {
    global $phpgw;

    $phpgw->db->query("SELECT con,display,base_url,newsfile,lastread,newstype,"
                    . "cachetime,listings FROM news_site WHERE con = $site");
    if ($phpgw->db->num_rows() == 0)
       return False;
    $phpgw->db->next_record();

    $this->con = $phpgw->db->f(0);
    $this->display = $phpgw->db->f(1);
    $this->base_url = $phpgw->db->f(2);
    $this->newsfile = $phpgw->db->f(3);
    $this->lastread = $phpgw->db->f(4);
    $this->newstype = $phpgw->db->f(5);
    $this->cachetime = $phpgw->db->f(6);
    $this->listings = $phpgw->db->f(7);

    return True;
  }

  // determines if the headlines were cached less than $cachetime minutes ago
  function isCached() {
    $this->current_time = time();
    return (($this->current_time - $this->lastread)<$this->cachetime * 60);
  }

  // get the links from the database
  function getLinksDB() {
    global $phpgw;

    $sql = "SELECT title, link FROM news_headlines WHERE site = " . $this->con;
    $phpgw->db->query($sql);
		
    if (! $phpgw->db->num_rows()) {
      $links = $this->getLinksSite();  // try from site again
      if (!$links) {
        $display = htmlspecialchars($this->display);
        die("</table><b>error</b>: unable to get links for <br><a href=\""
	  . "$this->base_url\">$this->display</a>");
      }
    }
		
    while($phpgw->db->next_record()) {
      $links[$phpgw->db->f('title')] = $phpgw->db->f('link');
    }
    return $links;
  }

  // get a new set of links from the site
  function getLinksSite() {
    global $phpgw;

    // determine the options to properly extract the links
    $startat = "</image>";
    $linkstr = "link";
    $exclude = "";
		
    if ($this->newstype=="rdf-chan") $startat = "</channel>";
    else if ($this->newstype=="lt") $linkstr = "url";
    else if ($this->newstype=="fm") $exclude = "quick finder";
    else if ($this->newstype=="sf") $startat = "</textinput>";
		
    // get the file that contains the links
    $lines = $this->getSocketFile();
    if (!$lines) return false;
	
    $startnum = 0;

    // determine which line to begin grabbing the links
    for ($i=0;$i<count($lines);$i++) {
      if (ereg($startat,$lines[$i],$regs)) {
        $startnum = $i;
        break;
      }
    }

    // extract the links and assemble into array $links
    $links = array();
    for ($i=$startnum;$i<count($lines);$i++) {
      if (count($links)>=$this->listings) break;
      if (ereg("<title>(.*)</title>",$lines[$i],$regs)) {
        if ($regs[1] == $exclude) {$i+=1; break;}
        $title = $regs[1];
        $title = ereg_replace("&amp;apos;","'",$title);
      }
      else if (ereg("<$linkstr>(.*)</$linkstr>",$lines[$i],$regs)) {
        $links[$title] = $regs[1];
      }
    }
		
    return $links;
  }

  // return contents of a web url as an array or false if timeout
  function getSocketFile() {
    $server = str_replace("http://","",$this->base_url.$this->newsfile);
    $file = strstr($server,"/");
    $server = str_replace("$file","",$server);
    $fp = fsockopen($server, 80, &$errno, &$errstr);
    if ($fp) {
      fputs($fp,"GET $file HTTP/1.0\nHost: $server\n\n");
      while ($line = fgets($fp, 4096)) {
        $lines[] = $line;
      }
      fclose($fp);
      return $lines;
    }
    else {
      return false;
    }
  }
	
// This will need some work still
	// get a list of the sites
  function getList()
  {
    while (list($name,$value) = each($this->sites)) {
      $links[$value[0]] = $name;
    }
    return $links;
  }

  // save the new set of links and update the cache time
  function saveToDB($links) {
    global $phpgw;

    $sql = "DELETE FROM news_headlines WHERE site = " . $this->con;
    $phpgw->db->query($sql);
		
    // save links
    while (list($title,$link) = each($links)) {
      $link = addslashes($link);
      $title = addslashes($title);
      $phpgw->db->query("INSERT INTO news_headlines VALUES("
                     . $this->con.",'$title','$link')");
    }		
		
    // save cache time
    $phpgw->db->query("UPDATE news_site SET lastread = '" 
                   . $this->current_time ."' WHERE con = " . $this->con);
  }
}
?>
