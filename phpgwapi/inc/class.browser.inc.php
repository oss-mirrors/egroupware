<?php
  /**************************************************************************\
  * phpGroupWare API - Browser detect functions                              *
  * This file written by Miles Lott <milosch@phpgroupware.org>               *
  * Majority of code borrowed from Sourceforge 2.5                           *
  * Copyright 1999-2000 (c) The SourceForge Crew - http://sourceforge.net    *
  * Browser detection functions for phpGroupWare developers                  *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
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

	class browser
	{
		var $BROWSER_AGENT;
		var $BROWSER_VER;
		var $BROWSER_PLATFORM;
		var $data;

		function browser ()
		{
			global $HTTP_USER_AGENT;
			/*
				Determine browser and version
			*/
			if (ereg( 'MSIE ([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER = $log_version[1];
				$this->BROWSER_AGENT = 'IE';
			}
			elseif (ereg( 'Opera ([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version) ||
				ereg( 'Opera/([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER   = $log_version[1];
				$this->BROWSER_AGENT = 'OPERA';
			}
			elseif ( eregi( 'iCab ([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version) ||
				eregi( 'iCab/([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER   = $log_version[1];
				$this->BROWSER_AGENT = 'iCab';
			} 
			elseif ( eregi( 'Netscape6/([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version)) 
			{
				$this->BROWSER_VER   = $log_version[1];
				$this->BROWSER_AGENT = 'Netscape';
			}

			elseif (ereg( 'Mozilla/([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER=$log_version[1];
				$this->BROWSER_AGENT='MOZILLA';
			}
			else
			{
				$this->BROWSER_VER=0;
				$this->BROWSER_AGENT='OTHER';
			}

			/*
				Determine platform
			*/
			if (strstr($HTTP_USER_AGENT,'Win'))
			{
				$this->BROWSER_PLATFORM='Win';
			}
			else if (strstr($HTTP_USER_AGENT,'Mac'))
			{
				$this->BROWSER_PLATFORM='Mac';
			}
			else if (strstr($HTTP_USER_AGENT,'Linux'))
			{
				$this->BROWSER_PLATFORM='Linux';
			}
			else if (strstr($HTTP_USER_AGENT,'Unix'))
			{
				$this->BROWSER_PLATFORM='Unix';
			}
			else if (strstr($HTTP_USER_AGENT,'Beos'))
			{
				$this->BROWSER_PLATFORM='Beos';
			}
			else
			{
				$this->BROWSER_PLATFORM='Other';
			}

			/*
			echo "\n\nAgent: $HTTP_USER_AGENT";
			echo "\nIE: ".browser_is_ie();
			echo "\nMac: ".browser_is_mac();
			echo "\nWindows: ".browser_is_windows();
			echo "\nPlatform: ".browser_get_platform();
			echo "\nVersion: ".browser_get_version();
			echo "\nAgent: ".browser_get_agent();
			*/
		}

		function return_array()
		{
			$this->data = array(
				'agent'    => $this->get_agent(),
				'version'  => $this->get_version(),
				'platform' => $this->get_platform()
			);

			return $this->data;
		}

		function get_agent()
		{
			return $this->BROWSER_AGENT;
		}

		function get_version()
		{
			return $this->BROWSER_VER;
		}

		function get_platform()
		{
			return $this->BROWSER_PLATFORM;
		}

		function is_linux()
		{
			if ($this->get_platform()=='Linux')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_unix()
		{
			if ($this->get_platform()=='Unix')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_beos()
		{
			if ($this->get_platform()=='Beos')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_mac()
		{
			if ($this->get_platform()=='Mac')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_windows()
		{
			if ($this->get_platform()=='Win')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_ie()
		{
			if ($this->get_agent()=='IE')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_netscape()
		{
			if ($this->get_agent()=='MOZILLA')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		// Echo content headers for file downloads
		function content_header($fn="")
		{
			if ($fn)
			{
				if ($this->get_agent() == "IE") // && browser_get_version() == "5.5")
				{
					$attachment = "";
				}
					else
				{
					$attachment = " attachment;";
				}
				header('Content-disposition:'.$attachment.' filename="'.$fn.'"');
				header('Content-type: application/octetstream');
				header('Pragma: no-cache');
				header('Expires: 0');
			}
		}
	}
?>
