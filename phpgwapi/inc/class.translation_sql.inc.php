<?php
  /**************************************************************************\
  * phpGroupWare API - Translation class for SQL                             *
  * This file written by Joseph Engo <jengo@phpgroupware.org>                *
  * and Dan Kuykendall <seek3r@phpgroupware.org>                             *
  * Handles multi-language support use SQL tables                            *
  * Copyright (C) 2000, 2001 Joseph Engo                                     *
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

	// define the maximal length of a message_id, all message_ids have to be unique
	// in this length, our column is varchar 255, but addslashes might add some length
	if (!defined('MAX_MESSAGE_ID_LENGTH'))
	{
		define('MAX_MESSAGE_ID_LENGTH',230);
	}

	class translation
	{
		var $userlang = 'en';
		var $loaded_apps = array();

		function translation()
		{
			$this->db = is_object($GLOBALS['phpgw']->db) ? $GLOBALS['phpgw']->db : $GLOBALS['phpgw_setup']->db;
		}

		function init()
		{
			// post-nuke and php-nuke are using $GLOBALS['lang'] too
			// but not as array!
			// this produces very strange results
			if (!is_array($GLOBALS['lang']))
			{
				$GLOBALS['lang'] = array();
			}

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['lang'])
			{
				$this->userlang = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
			}
			$this->add_app('common');
			if (!count($GLOBALS['lang']))
			{
				$this->userlang = 'en';
				$this->add_app('common');
			}
			$this->add_app($GLOBALS['phpgw_info']['flags']['currentapp']);
		}

		/*!
		@function translate
		@abstract translates a phrase and evtl. substitute some variables
		@returns the translation
		*/
		function translate($key, $vars=false )
		{
			if (!$vars)
			{
				$vars = array();
			}
			if (!is_array($GLOBALS['lang']) || !count($GLOBALS['lang']))
			{
				$this->init();
			}
			$ret = $key.'*';	// save key if we dont find a translation

			$key = strtolower(trim(substr($key,0,MAX_MESSAGE_ID_LENGTH)));

			if (isset($GLOBALS['lang'][$key]))
			{
				$ret = $GLOBALS['lang'][$key];
			}
			$ndx = 1;
			foreach($vars as $val)
			{
				$ret = preg_replace( "/%$ndx/", $val, $ret );
				++$ndx;
			}
			return $ret;
		}

		/*!
		@function add_app
		@abstract adds translations for an application from the database to the lang-array
		@syntax add_app($app,$lang=False)
		@param $app name of the application to add (or 'common' for the general translations)
		@param $lang 2-char code of the language to use or False if the users language should be used
		*/
		function add_app($app,$lang=False)
		{
			$lang = $lang ? $lang : $this->userlang;

			if (!isset($this->loaded_apps[$app]) || $this->loaded_apps[$app] != $lang)
			{
				$sql = "select message_id,content from phpgw_lang where lang='".$lang."' and app_name='".$app."'";
				$this->db->query($sql,__LINE__,__FILE__);
				while ($this->db->next_record())
				{
					$GLOBALS['lang'][strtolower ($this->db->f('message_id'))] = $this->db->f('content');
				}
				$this->loaded_apps[$app] = $lang;
			}
		}

		/*!
		@function get_installed_langs
		@abstract returns a list of installed langs
		@returns array with 2-character lang-code as key and descriptiv lang-name as data
		*/
		function get_installed_langs()
		{
			if (!is_array($this->langs))
			{
				$this->db->query("SELECT DISTINCT l.lang,ln.lang_name FROM phpgw_lang l,phpgw_languages ln WHERE l.lang = ln.lang_id",__LINE__,__FILE__);
				if (!$this->db->num_rows())
				{
					return False;
				}
				while ($this->db->next_record())
				{
					$this->langs[$this->db->f('lang')] = $this->db->f('lang_name');
				}
			}
			return $this->langs;
		}

		/*!
		@function get_installed_charsets
		@abstract returns a list of installed charsets
		@returns array with charset as key and comma-separated list of langs useing the charset as data
		*/
		function get_installed_charsets()
		{
			if (!is_array($this->charsets))
			{
				$this->db->query("SELECT DISTINCT l.lang,ln.lang_name,l.content AS charset FROM phpgw_lang l,phpgw_languages ln WHERE l.lang = ln.lang_id AND l.message_id='charset'",__LINE__,__FILE__);
				if (!$this->db->num_rows())
				{
					return False;
				}
				while ($this->db->next_record())
				{
					$data = &$this->charsets[$charset = $this->db->f('charset')];
					$data .= ($data ? ', ' : $charset.': ').
						$this->db->f('lang_name').' ('.$this->db->f('lang').')';
				}
			}
			return $this->charsets;
		}

		/*!
		@function install_langs
		@abstract installs translations for the selected langs into the database
		@syntax install_langs($langs,$upgrademethod='dumpold')
		@param $langs array of langs to install (as data NOT keys (!))
		@param $upgrademethod 'dumpold' (recommended & fastest), 'addonlynew' languages, 'addmissing' phrases
		*/
		function install_langs($langs,$upgrademethod='dumpold')
		{
			@set_time_limit(0);	// we might need some time

			if (!isset($GLOBALS['phpgw_info']['server']) && $upgrademethod != 'dumpold')
			{
				$this->db->query("select * from phpgw_config WHERE config_app='phpgwapi' AND config_name='lang_ctimes'",__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$GLOBALS['phpgw_info']['server']['lang_ctimes'] = unserialize(stripslashes($this->db->f('config_value')));
				}
			}

			if (!is_array($langs) || !count($langs))
			{
				return;
			}
			$this->db->transaction_begin();

			if ($upgrademethod == 'dumpold')
			{
				// dont delete the custom main- & loginscreen messages every time
				$this->db->query("DELETE FROM phpgw_lang where app_name != 'mainscreen' AND app_name != 'loginscreen'",__LINE__,__FILE__);
				//echo '<br>Test: dumpold';
				$GLOBALS['phpgw_info']['server']['lang_ctimes'] = array();
			}
			foreach($langs as $lang)
			{
				//echo '<br>Working on: ' . $lang;
				$addlang = False;
				if ($upgrademethod == 'addonlynew')
				{
					//echo "<br>Test: addonlynew - select count(*) from phpgw_lang where lang='".$lang."'";
					$this->db->query("SELECT COUNT(*) FROM phpgw_lang WHERE lang='".$lang."'",__LINE__,__FILE__);
					$this->db->next_record();

					if ($this->db->f(0) == 0)
					{
						//echo '<br>Test: addonlynew - True';
						$addlang = True;
					}
				}
				if (($addlang && $upgrademethod == 'addonlynew') || ($upgrademethod != 'addonlynew'))
				{
					//echo '<br>Test: loop above file()';
					if (!is_object($GLOBALS['phpgw_setup']))
					{
						$GLOBALS['phpgw_setup'] = CreateObject('phpgwapi.setup');
						$GLOBALS['phpgw_setup']->db = $this->db;
					}
					$setup_info = $GLOBALS['phpgw_setup']->detection->get_versions();
					$setup_info = $GLOBALS['phpgw_setup']->detection->get_db_versions($setup_info);
					$raw = array();
					// Visit each app/setup dir, look for a phpgw_lang file
					foreach($setup_info as $key => $app)
					{
						$appfile = PHPGW_SERVER_ROOT . SEP . @$app['name'] . SEP . 'setup' . SEP . 'phpgw_' . strtolower($lang) . '.lang';
						//echo '<br>Checking in: ' . $app['name'];
						if($GLOBALS['phpgw_setup']->app_registered(@$app['name']) && file_exists($appfile))
						{
							//echo '<br>Including: ' . $appfile;
							$lines = file($appfile);
							foreach($lines as $line)
							{
								list($message_id,$app_name,,$content) = explode("\t",$line);
								$message_id = $this->db->db_addslashes(substr(strtolower(chop($message_id)),0,MAX_MESSAGE_ID_LENGTH));
								$app_name = $this->db->db_addslashes(chop($app_name));
								$content = $this->db->db_addslashes(chop($content));

								$raw[$app_name][$message_id] = $content;
							}
							$GLOBALS['phpgw_info']['server']['lang_ctimes'][$lang][$app['name']] = filectime($appfile);
						}
					}
					foreach($raw as $app_name => $ids)
					{
						foreach($ids as $message_id => $content)
						{
							$addit = False;
							//echo '<br>APPNAME:' . $app_name . ' PHRASE:' . $message_id;
							if ($upgrademethod == 'addmissing')
							{
								//echo '<br>Test: addmissing';
								$this->db->query("SELECT COUNT(*) FROM phpgw_lang WHERE message_id='$message_id' AND lang='$lang' AND (app_name='$app_name' OR app_name='common') AND content='$content'",__LINE__,__FILE__);
								$this->db->next_record();

								if ($this->db->f(0) == 0)
								{
									//echo '<br>Test: addmissing - True - Total: ' . $this->db->f(0);
									$addit = True;
								}
							}

							if ($addit || $upgrademethod == 'addonlynew' || $upgrademethod == 'dumpold')
							{
								if($message_id && $content)
								{
									//echo "<br>adding - insert into phpgw_lang values ('$message_id','$app_name','$lang','$content')";
									$result = $this->db->query("INSERT INTO phpgw_lang (message_id,app_name,lang,content) VALUES('$message_id','$app_name','$lang','$content')",__LINE__,__FILE__);
									if (intval($result) <= 0)
									{
										echo "<br>Error inserting record: phpgw_lang values ('$message_id','$app_name','$lang','$content')";
									}
								}
							}
						}
					}
				}
			}
			$this->db->transaction_commit();

			// update the ctimes of the installed langsfiles for the autoloading of the lang-files
			//
			$this->db->query("DELETE from phpgw_config WHERE config_app='phpgwapi' AND config_name='lang_ctimes'",__LINE__,__FILE__);
			$this->db->query($query="INSERT INTO phpgw_config(config_app,config_name,config_value) VALUES ('phpgwapi','lang_ctimes','".
				addslashes(serialize($GLOBALS['phpgw_info']['server']['lang_ctimes']))."')",__LINE__,__FILE__);
		}

		/*!
		@function autolaod_changed_langfiles
		@abstract re-loads all (!) langfiles if one langfile for the an app and the language of the user has changed
		*/
		function autoload_changed_langfiles()
		{
			//echo "<h1>check_langs()</h1>\n";
			if ($GLOBALS['phpgw_info']['server']['lang_ctimes'] && !is_array($GLOBALS['phpgw_info']['server']['lang_ctimes']))
			{
				$GLOBALS['phpgw_info']['server']['lang_ctimes'] = unserialize($GLOBALS['phpgw_info']['server']['lang_ctimes']);
			}
			//_debug_array($GLOBALS['phpgw_info']['server']['lang_ctimes']);

			$lang = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
			$apps = $GLOBALS['phpgw_info']['user']['apps'];
			$apps['phpgwapi'] = True;	// check the api too
			foreach($apps as $app => $data)
			{
				$fname = PHPGW_SERVER_ROOT . "/$app/setup/phpgw_$lang.lang";

				if (file_exists($fname))
				{
					$ctime = filectime($fname);
					$ltime = intval($GLOBALS['phpgw_info']['server']['lang_ctimes'][$lang][$app]);
					//echo "checking lang='$lang', app='$app', ctime='$ctime', ltime='$ltime'<br>\n";

					if ($ctime != $ltime)
					{
						// update all langs
						$this->install_langs(array_keys($this->get_installed_langs()));
						break;
					}
				}
			}
		}
	}
