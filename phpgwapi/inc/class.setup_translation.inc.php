<?php
  /**************************************************************************\
  * phpGroupWare API - Translation class for phpgw lang files                *
  * This file written by Miles Lott <milosch@phpgroupware.org>               *
  * and Dan Kuykendall <seek3r@phpgroupware.org>                             *
  * Handles multi-language support using flat files                          *
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

	if (!defined('MAX_MESSAGE_ID_LENGTH'))
	{
		define('MAX_MESSAGE_ID_LENGTH',230);
	}

	class setup_translation
	{
		var $langarray;

		/*!
		@function setup_lang
		@abstract constructor for the class, loads all phrases into langarray
		@param $lang	user lang variable (defaults to en)
		*/
		function setup_translation()
		{
			$ConfigLang = get_var('ConfigLang',Array('POST','COOKIE'));

			if(!$ConfigLang)
			{
				$lang = 'en';
			}
			else
			{
				$lang = $ConfigLang;
			}

			$fn = '.' . SEP . 'lang' . SEP . 'phpgw_' . $lang . '.lang';
			if (!file_exists($fn))
			{
				$fn = '.' . SEP . 'lang' . SEP . 'phpgw_en.lang';
			}

			if (file_exists($fn))
			{
				$fp = fopen($fn,'r');
				while ($data = fgets($fp,8000))
				{
					list($message_id,$app_name,$null,$content) = explode("\t",$data);
					if ($app_name == 'setup' || $app_name == 'common' || $app_name == 'all')
					{
						$this->langarray[] = array(
							'message_id' => $message_id,
							'content'    => trim($content)
						);
					}
				}
				fclose($fp);
			}
		}

		/*!
		@function translate
		@abstract Translate phrase to user selected lang
		@param $key  phrase to translate
		@param $vars vars sent to lang function, passed to us
		*/
		function translate($key, $vars=False) 
		{
			if (!$vars)
			{
				$vars = array();
			}

			$ret = $key;

			@reset($this->langarray);
			while(list($null,$data) = @each($this->langarray))
			{
				$lang[strtolower($data['message_id'])] = $data['content'];
			}

			if (isset($lang[strtolower ($key)]) && $lang[strtolower ($key)])
			{
				$ret = $lang[strtolower ($key)];
			}
			else
			{
				$ret = $key.'*';
			}
			$ndx = 1;
			while( list($key,$val) = each( $vars ) )
			{
				$ret = preg_replace( "/%$ndx/", $val, $ret );
				++$ndx;
			}
			return $ret;
		}

		// the following functions have been moved to phpgwapi/tanslation_sql

		function setup_translation_sql()
		{
			if (!is_object($this->sql))
			{
				include_once(PHPGW_API_INC.'/class.translation_sql.inc.php');
				$this->sql = new translation;
			}
		}

		function get_langs($DEBUG=False)
		{
			$this->setup_translation_sql();
			return $this->sql->get_langs($DEBUG);
		}

		function drop_langs($appname,$DEBUG=False)
		{
			$this->setup_translation_sql();
			return $this->sql->drop_langs($appname,$DEBUG);
		}

		function add_langs($appname,$DEBUG=False,$force_langs=False)
		{
			$this->setup_translation_sql();
			return $this->sql->add_langs($appname,$DEBUG,$force_langs);
		}
	}
?>
