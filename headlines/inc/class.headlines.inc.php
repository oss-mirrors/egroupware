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
		var $cache_table = 'egw_headlines_cached';
		var $site_table = 'egw_headlines_sites';
		var $xmlrpc = False;
		var $debug  = False;

		var $con       = 0;
		var $display   = '';
		var $base_url  = '';
		var $newsfile  = '';
		var $lastread  = 0;
		var $newstype  = '';
		var $cachetime = 0;
		var $listings  = 0;
		var $error_timeout = False;

		function headlines()
		{
			$this->db = clone($GLOBALS['egw']->db);
			$this->db->set_app('headlines');
			$this->current_time = time();
			$this->xmlrpc = $GLOBALS['egw_info']['server']['xmlrpc'] ? True : False;
		}

		/* try to get the links for the site */
		function getLinks($site)
		{
			$links = array();

			$this->readtable($site);
			if($this->needrefresh())
			{
				$links = $this->getLinksSite();
				if(@is_array($links))
				{
					$this->saveToDb($links);
				}
			}
			else
			{
				$links = $this->getLinksDB();
			}

			return $links;
		}

		/* do a quick read of the table */
		function readtable($site)
		{
			$this->db->select(
				$this->site_table,
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
			$this->db->select($this->cache_table,'title,link',array('site' => (int)$site),__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$cache[$this->db->f('title')] = $this->db->f('link');
			}
			return $cache;
		}

		/* Determines if the headlines were cached less than $cachetime minutes ago.
		 *  Renamed from isCached()
		 */
		function needrefresh()
		{
			if($this->debug)
			{
				/* Refresh faster for debug purposes, e.g. don't wait an hour for cachetime of 60 */
				$cachetime = $this->cachetime;
			}
			else
			{
				$cachetime = $this->cachetime * 60;
			}

			if(($this->current_time - $this->lastread) > $cachetime)
			{
				if($this->debug)
				{
					echo '<br>Need to refresh site ' . $this->con;
				}
				return True;
			}
			if($this->debug)
			{
				echo '<br>Site ' . $this->con . ' is new enough';
			}
			return False;
		}

		// get the links from the database
		function getLinksDB()
		{
			$this->db->select($this->cache_table,'title,link',array('site' => (int)$this->con),__LINE__,__FILE__);

			if(!$this->db->num_rows())
			{
				$links = $this->getLinksSite();  // try from site again
				if(!@is_array($links))
				{
					$this->saveToDb();
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
			@set_time_limit(30);
			/* get the file that contains the links as one string */
			$data = $GLOBALS['egw']->network->gethttpsocketfile($this->base_url . $this->newsfile,NULL,NULL,True);
			if(!$data)
			{
				return False;
			}
			/* if the xml-file specifes an encoding, convert it to our own encoding */
			if (preg_match('/\<\?xml.*encoding="([^"]+)"/i',$data,$matches) && $matches[1])
			{
				if($this->debug)
				{
					echo "<br>Converting from charset '$matches[1]'\n";
				}
				$data = $GLOBALS['egw']->translation->convert($data,$matches[1]);
			}
			else
			{
				$data = $GLOBALS['egw']->translation->convert($data,'iso-8859-1');
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

			$rss =& CreateObject('headlines.rss',$data,$simple);
			$allItems = $rss->getAllItems();
			unset($rss);

			$i = 1;
			$links = array();
			while(list($title,$link) = @each($allItems))
			{
				if($title)
				{
					/* Above checks that the title is not empty, which happens with some sites. */
					if($i == $this->listings)
					{
						break;
					}
					$i++;
					/* Some sites (Wired) return a CR in the middle of the title - maybe in the rss class... */
					$title = str_replace("\n",'',$title);
					$links[$title] = $link;
				}
			}

			return $links;
		}

		/* get a list of the sites */
		function getList()
		{
			@set_time_limit(0);

			// determine the options to properly extract the links
			$startat = '</image>';
			$linkstr = 'link';
			$exclude = '';

			// get the file that contains the links
			$lines = $GLOBALS['egw']->network->gethttpsocketfile('http://demo.egroupware.org/headlines.rdf');
			if(!$lines)
			{
				return False;
			}

			$startnum = 0;

			/* determine which line to begin grabbing the links */
			for($i=0;$i<count($lines);$i++)
			{
				if(ereg($startat,$lines[$i],$regs))
				{
					$startnum = $i;
					break;
				}
			}

			/* extract the links and assemble into array $links */
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
					$links[$j]['display'] = $regs[1];
					$links[$j]['display'] = ereg_replace("&amp;apos;","'",$links[$j]['display']);
				}
				elseif(ereg("<$linkstr>(.*)</$linkstr>",$lines[$i],$regs))
				{
					$links[$j]['server'] = $regs[1];
				}
				elseif(ereg("<description>(.*)</description>",$lines[$i],$regs))
				{
					$links[$j]['type'] = $regs[1];
					$j++;
				}
			}

			$this->db->transaction_begin();
			for($i=0;$i<count($links);$i++)
			{
				$server = str_replace('http://','',$links[$i]['server']);
				$file   = strstr($server,'/');
				$server = 'http://' . str_replace($file,'',$server);

				$this->db->select(
					$this->site_table,
					'con,display,base_url,newsfile,newstype',
					array(
						'display'  => $links[$i]['display'],
						'base_url' => $server,
						'newsfile' => $file
					),
					__LINE__,__FILE__
				);
				if($this->db->num_rows() == 0)
				{
					$this->db->insert(
						$this->site_table,
						array(
							'display'   => $links[$i]['display'],
							'base_url'  => $server,
							'newsfile'  => $file,
							'newstype'  => $links[$i]['type'],
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

				if($this->db->f('newstype') <> $links[$i]['type'])
				{
					$this->db->update(
						$this->site_table,
						array('newstype' => $links[$i]['type']),
						array('con' => (int)$this->db->f('con')),
						__LINE__,__FILE__
					);
				}
			}
			$this->db->transaction_commit();
		}

		/* Export entire site list for creation of a proper site xml file */
		function exportList()
		{
			$out = array();

			$this->db->select(
				$this->site_table,
				'display,base_url,newsfile,newstype',
				'',
				__LINE__,__FILE__
			);
			if(!$this->db->num_rows())
			{
				return False;
			}

			while($this->db->next_record())
			{
				$out[] = array(
					'title' => $this->db->f(0),
					'link'  => $this->db->f(1) . $this->db->f(2),
					'description'  => $this->db->f(3)
				);
			}
			return $out;
		}

		/* Save the new set of links and update the cache time */
		function saveToDB($links)
		{
			if($this->debug)
			{
				echo '<br>Saving to cache...';
			}
			$this->db->delete($this->cache_table,array('site' => (int)$this->con),__LINE__,__FILE__);

			// save links
			foreach($links as $title => $link)
			{
				$this->db->insert(
					$this->cache_table,
					array(
						'site'  => $this->con,
						'title' => $title,
						'link'  => $link
					),
					False,
					__LINE__,__FILE__
				);
			}

			// save cache time
			$this->db->update(
				$this->site_table,
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
						$this->site_table,
						array(
							'display'   => $sitedata['display'],
							'base_url'  => $sitedata['base_url'],
							'newsfile'  => $sitedata['newsfile'],
							'lastread'  => 0,
							'newstype'  => $sitedata['newstype'],
							'cachetime' => $sitedata['cachetime'],
							'listings'  => $sitedata['listings']
						),
						array('con' => (int)$sitedata['con']),
						__LINE__,__FILE__
					);
					$rtrn = array('con' => (int)$sitedata['con']);
					break;
				case 'add':
					$this->db->select(
						$this->site_table,
						'display',
						array(
							'base_url' => strtolower($sitedata['base_url']),
							'newsfile' => strtolower($sitedata['newsfile'])
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
						$this->site_table,
						array(
							'display'   => $sitedata['display'],
							'base_url'  => strtolower($sitedata['base_url']),
							'newsfile'  => strtolower($sitedata['newsfile']),
							'lastread'  => 0,
							'newstype'  => $sitedata['newstype'],
							'cachetime' => (int)$sitedata['cachetime'],
							'listings'  => (int)$sitedata['listings']
						),
						False,
						__LINE__,__FILE__
					);
					$rtrn = array('con' => $this->db->get_last_insert_id($this->site_table,'con'));
			}
			return $rtrn;
		}

		function delete($id=False)
		{
			$this->db->transaction_begin();

			$con = (int)$id;
			$this->db->delete($this->site_table, array('con'  => $con),__LINE__,__FILE__);
			$this->db->delete($this->cache_table,array('site' => $con),__LINE__,__FILE__);

			// not sure what this function should do, but it was fiddeling direct with the prefs table and
			// calling not existing methods of the preferences class -- RalfBecker 2005/11/13
			if (isset($GLOBALS['egw_info']['user']['preferences']['headlines'][$con]))
			{
				$GLOBALS['egw']->preferences->delete('headlines',$con);
				$GLOBALS['egw']->preferences->save_repository(false,'user');
			}

			$this->db->transaction_commit();
			return array('con' => (int)$id);
		}

		function sites()
		{
			$sites = false;
			$this->db->select($this->site_table,'con,display',false,__LINE__,__FILE__,false,'ORDER BY display asc');
			while ($this->db->next_record())
			{
				$sites[$this->db->f('con')] = $this->db->f('display');
			}
			return $sites;
		}
	}
?>
