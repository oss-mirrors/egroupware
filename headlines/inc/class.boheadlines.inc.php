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

	class boheadlines
	{
		var $so;
		var $sites = array();

		function boheadlines()
		{
			$this->so = CreateObject('headlines.headlines');
		}

		function getsites()
		{
			if(!$GLOBALS['egw_info']['user']['preferences']['headlines']['headlines_layout'])
			{
				$GLOBALS['egw']->preferences->add('headlines','headlines_layout','basic');
				$GLOBALS['egw']->preferences->save_repository();
				$GLOBALS['egw_info']['user']['preferences']['headlines']['headlines_layout'] = 'gray';
			}

			foreach($GLOBALS['egw_info']['user']['preferences']['headlines'] as $n => $name)
			{
				if(is_int($n))
				{
					$this->sites[] = $n;
				}
			}
			return $this->sites;
		}

		function read($id=False)
		{
			$this->so->readtable($id);
			$sitedata = array(
				'display'   => $this->so->display,
				'base_url'  => $this->so->base_url,
				'newsfile'  => $this->so->newsfile,
				'cachetime' => $this->so->cachetime,
				'newstype'  => $this->so->newstype,
				'listings'  => $this->so->listings
			);
			return $sitedata;
		}

		function readcache($site=False)
		{
			return $this->so->readcache($site);
		}

		function readtable($site=False)
		{
			$this->so->readtable($site);

			$this->con       = $this->so->con;
			$this->display   = $this->so->display;
			$this->base_url  = $this->so->base_url;
			$this->newsfile  = $this->so->newsfile;
			$this->lastread  = $this->so->lastread;
			$this->newstype  = $this->so->newstype;
			$this->cachetime = $this->so->cachetime;
			$this->listings  = $this->so->listings;
			return True;
		}

		function edit($sitedata)
		{
			$mode = @isset($sitedata['con']) ? 'change' : 'add';

			if(!$sitedata['display'])
			{
				$errors[] = lang('You must enter a display');
			}

			if(!$sitedata['base_url'])
			{
				$errors[] = lang('You must enter a base url');
			}

			if(!$sitedata['newsfile'])
			{
				$errors[] = lang('You must enter a news url');
			}

			if(!$sitedata['cachetime'])
			{
				$errors[] = lang('You must enter the number of minutes between reload');
			}

			if(!$sitedata['listings'])
			{
				$errors[] = lang('You must enter the number of listings display');
			}

			if($sitedata['listings'] && !ereg('^[0-9]+$',$sitedata['listings']))
			{
				$errors[] = lang('You can only enter numbers for listings display');
			}

			if($sitedata['cachetime'] && !ereg('^[0-9]+$',$sitedata['cachetime']))
			{
				$errors[] = lang('You can only enter numbers minutes between refresh');
			}

			if(@is_array($errors))
			{
				return $errors;
			}

			return $this->so->edit($sitedata);
		}

		function delete($id=False)
		{
			return $this->so->delete($id);
		}

		function getList()
		{
			return $this->so->getList();
		}

		function getlinks($site)
		{
			return $this->so->getlinks($site);
		}

		function list_methods($_type='xmlrpc')
		{
			/*
			  This handles introspection or discovery by the logged in client,
			  in which case the input might be an array.  The server always calls
			  this function to fill the server dispatch map using a string.
			*/
			if(is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}
			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						/*
						'read' => array(
							'function'  => 'read',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Read site data for a single site by passing the id.')
						),
						*/
						'read' => array(
							'function'  => 'readtable',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Read site data for a single site by passing the id.')
						),
						'readcache' => array(
							'function'  => 'readcache',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Read cached site data for a single site by passing the id.')
						),
						'edit' => array(
							'function'  => 'edit',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Add a single entry by passing the fields.')
						),
						'delete' => array(
							'function'  => 'delete',
							'signature' => array(array(xmlrpcString,xmlrpcInt)),
							'docstring' => lang('Delete a single entry by passing the id.')
						),
						'get_list' => array(
							'function'  => 'get_list',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Read a list / search for entries.')
						),
						'get_links' => array(	// alias for consitent nameing
							'function'  => 'get_links',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Read a list / search for entries.')
						),
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}
	}
