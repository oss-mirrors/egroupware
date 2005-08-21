<?php
  /**************************************************************************\
  * eGroupWare - news headlines                                              *
  * http://www.egroupware.org                                                *
  * Written by Mark Peters <mpeters@satx.rr.com>                             *
  * Based on pheadlines 0.1 19991104 by Dan Steinman <dan@dansteinman.com>   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class headlines
	{
		// socket timeout in seconds
		var $current_time;
		var $db;

		var $con       = 0;
		var $display   = '';
		var $base_url  = '';
		var $newsfile  = '';
		var $lastread  = 0;
		var $newstype  = '';
		var $cachetime = 0;
		var $listings  = 0;
		var $error_timeout = False;

		// wired news was messing up, I dunno
		// "wired" => array("Wired&nbsp;News","http://www.wired.com","/news_drop/netcenter/netcenter.rdf/","rdf"),

		function headlines()
		{
			$GLOBALS['egw']->network = CreateObject('phpgwapi.network',False);
			$this->db     = clone($GLOBALS['egw']->db);
			$this->db->set_app('headlines');
			$this->current_time = time();
		}

		// try to get the links for the site
		function getLinks($site)
		{
			$links = array();
			if(!$this->readtable($site))
			{
				return $links;
			}

			if($this->isCached())
			{
				$links = $this->getLinksDB();
			}
			else
			{
				$links = $this->getLinksSite();

				if(@is_array($links))
				{
					$this->saveToDB($links);
				}
				else
				{
					$links = $this->getLinksDB();
					$this->error_timeout = True;
				}
			}
			return $links;
		}

		// do a quick read of the table
		function readtable($site)
		{
			$this->db->select(
				'phpgw_headlines_sites',
				'con,display,base_url,newsfile,lastread,newstype,cachetime,listings',
				array('con' => (int)$site),
				__LINE__,__FILE__
			);
			if(!$this->db->num_rows())
			{
				return False;
			}
			$this->db->next_record();

			$this->con       = $this->db->f(0);
			$this->display   = $this->db->f(1);
			$this->base_url  = $this->db->f(2);
			$this->newsfile  = $this->db->f(3);
			$this->lastread  = $this->db->f(4);
			$this->newstype  = $this->db->f(5);
			$this->cachetime = $this->db->f(6);
			$this->listings  = $this->db->f(7);

			return True;
		}

		function readcache($site)
		{
			$cache = array();
			$this->db->select('phpgw_headlines_cached','title,link',array('site' => (int)$id),__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$cache['link'][]  = $this->db->f('link');
				$cache['title'][] = $this->db->f('title');
			}
			return $cache;
		}

		// determines if the headlines were cached less than $cachetime minutes ago
		function isCached()
		{
			return (($this->current_time - $this->lastread) < ($this->cachetime * 60));
		}

		// get the links from the database
		function getLinksDB()
		{
//			return $this->getLinksSite();
			$this->db->select('phpgw_headlines_cached','title,link',array('site' => (int)$this->con),__LINE__,__FILE__);

			if(!$this->db->num_rows())
			{
				$links = $this->getLinksSite();  // try from site again
				if(!@is_array($links))
				{
					$display = htmlspecialchars($this->display);
//					die("</table><b>error</b>: unable to get links for <br><a href=\""
//						. "$this->base_url\">$this->display</a>");
					return False;
				}
			}
			else
			{
				while($this->db->next_record())
				{
					$links[$this->db->f('title')] = $this->db->f('link');
				}
			}
			return $links;
		}

		// get a new set of links from the site
		function getLinksSite()
		{
			/* get the file that contains the links as one string */
			$data = $GLOBALS['egw']->network->gethttpsocketfile($this->base_url . $this->newsfile,NULL,NULL,True);
			if(!$data)
			{
				return False;
			}
			// if the xml-file specifys an encoding, convert it to our own encoding
			if (preg_match('/\<\?xml.*encoding="([^"]+)"/i',$data,$matches) && $matches[1])
			{
				//echo "<p>converting from charset '$matches[1]'</p>\n";
				$data = $GLOBALS['egw']->translation->convert($data,$matches[1]);
			}

			switch($this->newstype)
			{
				case 'rdf':
				case 'fm':
					$simple = True;
					break;
				case 'lt':
					$data = @str_replace('<story>','<item>',$data);
					$data = @str_replace('</story>','</item>',$data);
					$data = @str_replace('<url>','<link>',$data);
					$data = @str_replace('</url>','</link>',$data);
					$simple = True;
					break;
				default: 
					$simple = False;
			}

			$rss = CreateObject('headlines.rss',$data,$simple);
			$allItems = $rss->getAllItems();
			unset($rss);

			$i = 1;
			$links = array();
			while(list($key,$val) = @each($allItems))
			{
				if($i == $this->listings)
				{
					break;
				}
				$i++;
				$links[$key] = $val;
			}

			return $links;
		}

		// get a list of the sites
		function getList()
		{
			@set_time_limit(0);

			// determine the options to properly extract the links
			$startat = '</image>';
			$linkstr = 'link';
			$exclude = '';

			// get the file that contains the links
//			$lines = $GLOBALS['egw']->network->gethttpsocketfile("http://blinkylight.com/headlines.rdf");
			$lines = $GLOBALS['egw']->network->gethttpsocketfile('http://egroupware.org/egroupware/headlines.rdf');
			if(!$lines)
			{
				return False;
			}

			$startnum = 0;

			// determine which line to begin grabbing the links
			for($i=0;$i<count($lines);$i++)
			{
				if(ereg($startat,$lines[$i],$regs))
				{
					$startnum = $i;
					break;
				}
			}

			// extract the links and assemble into array $links
			$links = array();
			for($i=$startnum,$j=0;$i<count($lines);$i++)
			{
				if(ereg("<title>(.*)</title>",$lines[$i],$regs))
				{
					if($regs[1] == $exclude)
					{
						$i+=1;
						break;
					}
					$title[$j] = $regs[1];
					$title[$j] = ereg_replace("&amp;apos;","'",$title[$j]);
				}
				elseif(ereg("<$linkstr>(.*)</$linkstr>",$lines[$i],$regs))
				{
					$links[$j] = $regs[1];
				}
				elseif(ereg("<description>(.*)</description>",$lines[$i],$regs))
				{
					$type[$j] = $regs[1];
					$j++;
				}
			}

			$this->db->transaction_begin();
			for($i=0;$i<count($title);$i++)
			{
				$server = str_replace('http://','',$links[$i]);
				$file   = strstr($server,'/');
				$server = 'http://' . str_replace($file,'',$server);

				$this->db->select(
					'phpgw_headlines_sites',
					'con,display,base_url,newsfile,newstype',
					array(
						'display'  => $title[$i],
						'base_url' => $server,
						'newsfile' => $file
					),
					__LINE__,__FILE__
				);
				if($this->db->num_rows() == 0)
				{
					$this->db->insert(
						'phpgw_headlines_sites',
						array(
							'display'   => $title[$i],
							'base_url'  => $server,
							'newsfile'  => $file,
							'newstype'  => $type[$i],
							'lastread'  => 0,
							'cachetime' => 60,
							'listings'  => 20
						),
						False,
						__LINE__,__FILE__
					);
					continue;
				}
				$this->db->next_record();

				if($this->db->f('newstype') <> $type[$i])
				{
					$this->db->update(
						'phpgw_headlines_sites',
						array('newstype' => $type[$i]),
						array('con' => (int)$this->db->f('con')),
						__LINE__,__FILE__
					);
				}
			}
			$this->db->transaction_commit();
		}

		// save the new set of links and update the cache time
		function saveToDB($links)
		{
			$this->db->delete('phpgw_headlines_cached',array('site' => (int)$this->con),__LINE__,__FILE__);

			// save links
			foreach($links as $title => $link)
			{
				$title = $this->db->db_addslashes($title);
				$link  = $this->db->db_addslashes($link);

				$this->db->insert(
					'phpgw_headlines_cached',
					array(
						'con'   => $this->con,
						'title' => $title,
						'link'  => $link
					),
					False,
					__LINE__,__FILE__
				);
			}

			// save cache time
			$this->db->update(
				'phpgw_headlines_sites',
				array('lastread' => $this->current_time),
				array('con' => (int)$this->con),
				__LINE__,__FILE__
			);
		}

		function edit($sitedata)
		{
			$mode = @isset($sitedata['con']) ? 'change' : 'add';
			switch($mode)
			{
				case 'change':
					$this->db->update(
						'phpgw_headlines_sites',
						array(
							'display'   => $this->db->db_addslashes($sitedata['display']) ,
							'base_url'  => $this->db->db_addslashes($sitedata['base_url']),
							'newsfile'  => $this->db->db_addslashes($sitedata['newsfile']),
							'lastread'  => 0,
							'cachetime' => (int)$sitedata['cachetime'],
							'listings'  => (int)$sitedata['listings']
						),
						array('con' => (int)$sitedata['con']),
						__LINE__,__FILE__
					);
					break;
				case 'add':
					$this->db->select(
						'phpgw_headlines_sites',
						'display',
						array(
							'base_url' => $this->db->db_addslashes(strtolower($sitedata['base_url'])),
							'newsfile' => $this->db->db_addslashes(strtolower($sitedata['newsfile']))
						),
						__LINE__,__FILE__
					);

					$this->db->next_record();
					if($this->db->f('display'))
					{
						$errors[] = lang('That site has already been entered');
					}

					if(is_array($errors))
					{
						return $errors;
					}

					$this->db->insert(
						'phpgw_headlines_sites',
						array(
							'display'   => $GLOBALS['egw']->db->db_addslashes($sitedata['display']),
							'base_url'  => $GLOBALS['egw']->db->db_addslashes(strtolower($sitedata['base_url'])),
							'newsfile'  => $GLOBALS['egw']->db->db_addslashes(strtolower($sitedata['newsfile'])),
							'lastread'  => 0,
							'newstype'  => $GLOBALS['egw']->db->db_addslashes($sitedata['newstype']),
							'cachetime' => (int)$sitedata['cachetime'],
							'listings'  => (int)$sitedata['listings']
						),
						False,
						__LINE__,__FILE__
					);
			}
			return True;
		}

		function delete($id=False)
		{
			$this->db->transaction_begin();

			$con = (int)$id;
			$this->db->delete('phpgw_headlines_sites', array('con'  => $con),__LINE__,__FILE__);
			$this->db->delete('phpgw_headlines_cached',array('site' => $con),__LINE__,__FILE__);

			$this->db->select('phpgw_preferences','*',False,__LINE__,__FILE__);
			while($this->db->next_record())
			{
				if($this->db->f('preference_owner') == $GLOBALS['egw_info']['user']['account_id'])
				{
					if($GLOBALS['egw_info']['user']['preferences']['headlines'][$con])
					{
						$GLOBALS['egw']->preferences->delete('headlines',$con);
						$GLOBALS['egw']->preferences->commit();
					}
				}
				else
				{
					$phpgw_newuser['user']['preferences'] = $this->db->f('preference_value');
					if($phpgw_newuser['user']['preferences']['headlines'][$con])
					{
						$GLOBALS['egw']->preferences->delete_newuser('headlines',$con);
						$GLOBALS['egw']->preferences->commit_user($this->db->f('preference_owner'));
					}
				}
			}

			$this->db->transaction_commit();
			return True;
		}
	}
?>
