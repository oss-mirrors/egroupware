<?php
	/**************************************************************************\
	* eGroupWare API - Translation class for SQL                               *
	* This file written by Joseph Engo <jengo@phpgroupware.org>                *
	* and Dan Kuykendall <seek3r@phpgroupware.org>                             *
	* Handles multi-language support use SQL tables                            *
	* Copyright (C) 2000, 2001 Joseph Engo                                     *
	* ------------------------------------------------------------------------ *
	* This library is part of the eGroupWare API                               *
	* http://www.egroupware.org/api                                            *
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
	// some constanst for pre php4.3
	if (!defined('PHP_SHLIB_SUFFIX'))
	{
		define('PHP_SHLIB_SUFFIX',strtoupper(substr(PHP_OS, 0,3)) == 'WIN' ? 'dll' : 'so');
	}
	if (!defined('PHP_SHLIB_PREFIX'))
	{
		define('PHP_SHLIB_PREFIX',PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '');
	}

	class translation
	{
		var $userlang = 'en';
		var $loaded_apps = array();
		var $line_rejected = array();
		var $lang_array = array();

		/**
		 * Constructor, sets up a copy of the db-object, gets the system-charset and tries to load the mbstring extension
		 */
		function translation($warnings = False)
		{
			for ($i = 1; $i <= 9; $i++)
			{
				$this->placeholders[] = '%'.$i;
			}
			$this->db = is_object($GLOBALS['egw']->db) ? $GLOBALS['egw']->db : $GLOBALS['egw_setup']->db;
			$this->db->set_app('phpgwapi');
			$this->lang_table = 'phpgw_lang';
			$this->languages_table = 'phpgw_languages';
			$this->config_table = 'phpgw_config';

			if (!isset($GLOBALS['egw_setup']))
			{
				$this->system_charset = @$GLOBALS['egw_info']['server']['system_charset'];
			}
			else
			{
				$this->db->select($this->config_table,'config_value',array(
					'config_app'=>'phpgwapi',
					'config_name'=>'system_charset'
				),__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$this->system_charset = $this->db->f(0);
				}
			}
			// load multi-byte-string-extension if needed, and set its internal encodeing to your system_charset
			if ($this->system_charset && substr($this->system_charset,0,9) != 'iso-8859-1')
			{
				if ($this->mbstring = extension_loaded('mbstring') || @dl(PHP_SHLIB_PREFIX.'mbstring.'.PHP_SHLIB_SUFFIX))
				{
					ini_set('mbstring.internal_encoding',$this->system_charset);
					if (ini_get('mbstring.func_overload') < 7)
					{
						if ($warnings)
						{
							echo "<p>Warning: Please set <b>mbstring.func_overload = 7</b> in your php.ini for useing <b>$this->system_charset</b> as your charset !!!</p>\n";
						}
					}
				}
				else
				{
					if ($warnings)
					{
						echo "<p>Warning: Please get and/or enable the <b>mbstring extension</b> in your php.ini for useing <b>$this->system_charset</b> as your charset, we are defaulting to <b>iconv</b> for now !!!</p>\n";
					}
				}
			}
		}

		/**
		 * returns the charset to use (!$lang) or the charset of the lang-files or $lang
		 *
		 * @param string/boolean $lang=False return charset of the active user-lang, or $lang if specified
		 * @return string charset
		 */
		function charset($lang=False)
		{
			if ($lang)
			{
				if (!isset($this->charsets[$lang]))
				{
					$this->db->select($this->lang_table,'content',array(
						'lang'		=> $lang,
						'message_id'=> 'charset',
						'app_name'	=> 'common',
					),__LINE__,__FILE__);
					$this->charsets[$lang] = $this->db->next_record() ? strtolower($this->db->f(0)) : 'iso-8859-1';
				}
				return $this->charsets[$lang];
			}
			if ($this->system_charset)	// do we have a system-charset ==> return it
			{
				$charset = $this->system_charset;
			}
			else
			{
				// if no translations are loaded (system-startup) use a default, else lang('charset')
				$charset = !is_array(@$this->lang_arr) ? 'iso-8859-1' : strtolower($this->translate('charset'));
			}
			// we need to set our charset as mbstring.internal_encoding if mbstring.func_overlaod > 0
			// else we get problems for a charset is different from the default utf-8
			if (ini_get('mbstring.func_overload') && $this->mbstring_internal_encoding != $charset)
			{
				ini_set('mbstring.internal_encoding',$this->mbstring_internal_encoding = $charset);
			}			
			return $charset;
		}

		/**
		 * Initialises global lang-array and loads the 'common' and app-spec. translations
		 */
		function init()
		{
			if (!is_array(@$this->lang_arr))
			{
				$this->lang_arr = array();
			}

			if ($GLOBALS['egw_info']['user']['preferences']['common']['lang'])
			{
				$this->userlang = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
			}
			$this->add_app('common');
			if (!count($this->lang_arr))
			{
				$this->userlang = 'en';
				$this->add_app('common');
			}
			$this->add_app($GLOBALS['egw_info']['flags']['currentapp']);
		}

		/**
		 * translates a phrase and evtl. substitute some variables
		 *
		 * @param string $key phrase to translate, may contain placeholders %N (N=1,2,...) for vars
		 * @param array/boolean $vars=false vars to replace the placeholders, or false for none
		 * @param string $not_found='*' what to add to not found phrases, default '*'
		 * @return string with translation
		 */
		function translate($key, $vars=false, $not_found='*' )
		{
			if (!is_array(@$this->lang_arr) || !count($this->lang_arr))
			{
				$this->init();
			}
			$ret = $key.$not_found;	// save key if we dont find a translation

			if (isset($this->lang_arr[$key]))
			{
				$ret = $this->lang_arr[$key];
			}
			else
			{
				$new_key = strtolower(trim(substr($key,0,MAX_MESSAGE_ID_LENGTH)));

				if (isset($this->lang_arr[$new_key]))
				{
					// we save the original key for performance
					$ret = $this->lang_arr[$key] = $this->lang_arr[$new_key];
				}
			}
			if (is_array($vars) && count($vars))
			{
				if (count($vars) > 1)
				{
					$ret = str_replace($this->placeholders,$vars,$ret);
				}
				else
				{
					$ret = str_replace('%1',$vars[0],$ret);
				}
			}
			return $ret;
		}

		/**
		 * adds translations for an application from the database to the lang-array
		 *
		 * @param string $app name of the application to add (or 'common' for the general translations)
		 * @param string/boolean $lang=false 2 or 5 char lang-code or false for the users language
		 */
		function add_app($app,$lang=False)
		{
			$lang = $lang ? $lang : $this->userlang;

			if (!isset($this->loaded_apps[$app]) || $this->loaded_apps[$app] != $lang)
			{
				if ($app == 'setup') return $this->add_setup($lang);

				$this->db->select($this->lang_table,'message_id,content',array(
					'lang'		=> $lang,
					'app_name'	=> $app,
				),__LINE__,__FILE__);
				while ($this->db->next_record())
				{
					$this->lang_arr[strtolower ($this->db->f('message_id'))] = $this->db->f('content');
				}
				$this->loaded_apps[$app] = $lang;
			}
		}

		/**
		 * Adds setup's translations, they are not in the DB!
		 *
		 * @param string $lang 2 or 5 char lang-code
		 */
		function add_setup($lang)
		{
			$fn = EGW_SERVER_ROOT.'/setup/lang/phpgw_' . $lang . '.lang';
			if (!file_exists($fn))
			{
				$fn = EGW_SERVER_ROOT.'/setup/lang/phpgw_en.lang';
			}
			if (file_exists($fn))
			{
				$fp = fopen($fn,'r');
				while ($data = fgets($fp,8000))
				{
					// explode with "\t" and removing "\n" with str_replace, needed to work with mbstring.overload=7
					list($message_id,,,$content) = explode("\t",$data);
					$phrases[strtolower(trim($message_id))] = str_replace("\n",'',$content);
				}
				fclose($fp);
				
				foreach($phrases as $message_id => $content)
				{
					$this->lang_arr[$message_id] = $this->convert($content,$phrases['charset']);
				}
			}
			$this->loaded_apps['setup'] = $lang;
		}
		/**
		 * returns a list of installed langs
		 *
		 * @return array with lang-code => descriptiv lang-name pairs
		 */
		function get_installed_langs()
		{
			if (!is_array($this->langs))
			{
				$this->db->query("SELECT DISTINCT l.lang,lx.lang_name FROM phpgw_lang l,phpgw_languages lx WHERE l.lang = lx.lang_id",__LINE__,__FILE__);
				if (!$this->db->num_rows())
				{
					return False;
				}
				while ($this->db->next_record())
				{
					$this->langs[$this->db->f('lang')] = $this->db->f('lang_name');
				}
				foreach($this->langs as $lang => $name)
				{
					$this->langs[$lang] = $this->translate($name,False,'');
				}
			}
			return $this->langs;
		}

		/**
		 * returns a list of installed charsets
		 *
		 * @return array with charset as key and comma-separated list of langs useing the charset as data
		 */
		function get_installed_charsets()
		{
			if (!is_array($this->charsets))
			{
				$distinct = $this->db->capabilities['distinct_on_text'] ? 'DISTINCT' : '';

				$this->db->query("SELECT $distinct l.lang,lx.lang_name,l.content AS charset FROM phpgw_lang l,phpgw_languages lx WHERE l.lang = lx.lang_id AND l.message_id='charset'",__LINE__,__FILE__);
				if (!$this->db->num_rows())
				{
					return False;
				}
				while ($this->db->next_record())
				{
					$data = &$this->charsets[$charset = strtolower($this->db->f('charset'))];
					$lang = $this->db->f('lang_name').' ('.$this->db->f('lang').')';
					if ($distinct || strstr($data,$lang) === false)
					{
						$data .= ($data ? ', ' : $charset.': ').$lang;
					}						
				}
			}
			return $this->charsets;
		}

		/**
		 * converts a string $data from charset $from to charset $to
		 *
		 * @param string/array $data string(s) to convert
		 * @param string/boolean $from charset $data is in or False if it should be detected
		 * @param string/boolean $to charset to convert to or False for the system-charset the converted string
		 * @return string/array converted string(s) from $data
		 */
		function convert($data,$from=False,$to=False)
		{
			if (is_array($data))
			{
				foreach($data as $key => $str)
				{
					$ret[$key] = $this->convert($str,$from,$to);
				}
				return $ret;
			}

			if ($from)
			{
				$from = strtolower($from);
			}
			if ($to)
			{
				$to = strtolower($to);
			}

			if (!$from)
			{
				$from = $this->mbstring ? strtolower(mb_detect_encoding($data)) : 'iso-8859-1';
				if($from == 'ascii')
				{
					$from = 'iso-8859-1';
				}
				//echo "<p>autodetected charset of '$data' = '$from'</p>\n";
			}
			/*
				 php does not seem to support gb2312
				 but seems to be able to decode it as EUC-CN
			*/
			switch($from)
			{
				case 'gb2312':
				case 'gb18030':
					$from = 'EUC-CN';
					break;
				case 'us-ascii':
				case 'macroman':
					$from = 'iso-8859-1';
					break;
			}
			if (!$to)
			{
				$to = $this->charset();
			}
			if ($from == $to || !$from || !$to || !$data)
			{
				return $data;
			}
			if ($from == 'iso-8859-1' && $to == 'utf-8')
			{
				return utf8_encode($data);
			}
			if ($to == 'iso-8859-1' && $from == 'utf-8')
			{
				return utf8_decode($data);
			}
			if ($this->mbstring)
			{
				return @mb_convert_encoding($data,$to,$from);
			}
			if(function_exists('iconv'))
			{
			if($from=='EUC-CN') $from='gb18030';
			// the above is to workaround patch #962307
			// if using EUC-CN, for iconv it strickly follow GB2312 and fail 
			// in an email on the first Traditional/Japanese/Korean character, 
			// but in reality when people send mails in GB2312, UMA mostly use 
			// extended GB13000/GB18030 which allow T/Jap/Korean characters.
				if (($data = iconv($from,$to,$data)))
				{
					return $data;
				}
			}
			#die("<p>Can't convert from charset '$from' to '$to' without the <b>mbstring extension</b> !!!</p>");

			// this is not good, not convert did succed
			return $data;
		}

		/**
		 * installs translations for the selected langs into the database
		 *
		 * @param array $langs langs to install (as data NOT keys (!))
		 * @param string $upgrademethod='dumpold' 'dumpold' (recommended & fastest), 'addonlynew' languages, 'addmissing' phrases
		 * @param string/boolean $only_app=false app-name to install only one app or default false for all
		 */
		function install_langs($langs,$upgrademethod='dumpold',$only_app=False)
		{
			@set_time_limit(0);	// we might need some time
			//echo "<p>translation_sql::install_langs(".print_r($langs,true).",'$upgrademthod','$only_app')</p>\n";
			if (!isset($GLOBALS['egw_info']['server']) && $upgrademethod != 'dumpold')
			{
				$this->db->select($this->config_table,'config_value',array(
					'config_app'	=> 'phpgwapi',
					'config_name'	=> 'lang_ctimes',
				),__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$GLOBALS['egw_info']['server']['lang_ctimes'] = unserialize(stripslashes($this->db->f('config_value')));
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
				$this->db->delete($this->lang_table,array("app_name!='mainscreen'","app_name!='loginscreen'"),__LINE__,__FILE__);
				//echo '<br>Test: dumpold';
				$GLOBALS['egw_info']['server']['lang_ctimes'] = array();
			}
			foreach($langs as $lang)
			{
				//echo '<br>Working on: ' . $lang;
				$addlang = False;
				if ($upgrademethod == 'addonlynew')
				{
					//echo "<br>Test: addonlynew - select count(*) from phpgw_lang where lang='".$lang."'";
					$this->db->select($this->lang_table,'COUNT(*)',array(
						'lang' => $lang,
					),__LINE__,__FILE__);
					$this->db->next_record();

					if ($this->db->f(0) == 0)
					{
						//echo '<br>Test: addonlynew - True';
						$addlang = True;
					}
				}
				if ($addlang && $upgrademethod == 'addonlynew' || $upgrademethod != 'addonlynew')
				{
					//echo '<br>Test: loop above file()';
					if (!is_object($GLOBALS['egw_setup']))
					{
						$GLOBALS['egw_setup'] =& CreateObject('setup.setup');
						$GLOBALS['egw_setup']->db = clone($this->db);
					}
					$setup_info = $GLOBALS['egw_setup']->detection->get_versions();
					$setup_info = $GLOBALS['egw_setup']->detection->get_db_versions($setup_info);
					$raw = array();
					$apps = $only_app ? array($only_app) : array_keys($setup_info);
					// Visit each app/setup dir, look for a phpgw_lang file
					foreach($apps as $app)
					{
						$appfile = EGW_SERVER_ROOT . SEP . $app . SEP . 'setup' . SEP . 'phpgw_' . strtolower($lang) . '.lang';
						//echo '<br>Checking in: ' . $app;
						if($GLOBALS['egw_setup']->app_registered($app) && file_exists($appfile))
						{
							//echo '<br>Including: ' . $appfile;
							$lines = file($appfile);
							foreach($lines as $line)
							{
								// explode with "\t" and removing "\n" with str_replace, needed to work with mbstring.overload=7
								list($message_id,$app_name,,$content) = $_f_buffer = explode("\t",$line);
								$content=str_replace(array("\n","\r"),'',$content);
								if( count($_f_buffer) != 4 )
								{
									$line_display = str_replace(array("\t","\n"),
										array("<font color='red'><b>\\t</b></font>","<font color='red'><b>\\n</b></font>"), $line);
									$this->line_rejected[] = array(
										'appfile' => $appfile,
										'line'    => $line_display,
									);
								}
								$message_id = substr(strtolower(chop($message_id)),0,MAX_MESSAGE_ID_LENGTH);
								$app_name = chop($app_name);
								$raw[$app_name][$message_id] = $content;
							}
							if ($GLOBALS['egw_info']['server']['lang_ctimes'] && !is_array($GLOBALS['egw_info']['server']['lang_ctimes']))
							{
								$GLOBALS['egw_info']['server']['lang_ctimes'] = unserialize($GLOBALS['egw_info']['server']['lang_ctimes']);
							}
							$GLOBALS['egw_info']['server']['lang_ctimes'][$lang][$app] = filectime($appfile);
						}
					}
					$charset = strtolower(@$raw['common']['charset'] ? $raw['common']['charset'] : $this->charset($lang));
					//echo "<p>lang='$lang', charset='$charset', system_charset='$this->system_charset')</p>\n";
					//echo "<p>raw($lang)=<pre>".print_r($raw,True)."</pre>\n";
					foreach($raw as $app_name => $ids)
					{
						foreach($ids as $message_id => $content)
						{
							if ($this->system_charset)
							{
								$content = $this->convert($content,$charset,$this->system_charset);
							}
							$addit = False;
							//echo '<br>APPNAME:' . $app_name . ' PHRASE:' . $message_id;
							if ($upgrademethod == 'addmissing')
							{
								//echo '<br>Test: addmissing';
								$this->db->select($this->lang_table,"content,CASE WHEN app_name IN ('common') THEN 1 ELSE 0 END AS in_api",array(
									'message_id' 	=> $message_id,
									'lang'			=> $lang,
									$this->db->expression($this->lang_table,'(',array(
										'app_name' => $app_name
									)," OR app_name='common') ORDER BY in_api DESC")),__LINE__,__FILE__);

								if (!($row = $this->db->row(True)))
								{
									$addit = True;
								}
								else
								{
									if ($row['in_api'])		// same phrase is in the api
									{
										$addit = $row['content'] != $content;	// only add if not identical
									}
									$row2 = $this->db->row(True);
									if (!$row['in_api'] || $app_name=='common' || $row2)	// phrase is alread in the db
									{
										$addit = $content != ($row2 ? $row2['content'] : $row['content']);
										if ($addit)	// if we want to add/update it ==> delete it
										{
											$this->db->delete($this->lang_table,array(
												'message_id'	=> $message_id,
												'lang'			=> $lang,
												'app_name'		=> $app_name,
											),__LINE__,__FILE__);
										}
									}
								}
							}

							if ($addit || $upgrademethod == 'addonlynew' || $upgrademethod == 'dumpold')
							{
								if($message_id && $content)
								{
									// echo "<br>adding - insert into phpgw_lang values ('$message_id','$app_name','$lang','$content')";
									$result = $this->db->insert($this->lang_table,array(
										'message_id'	=> $message_id,
										'app_name'		=> $app_name,
										'lang'			=> $lang,
										'content'		=> $content,
									),False,__LINE__,__FILE__);

									if ((int)$result <= 0)
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
			$config =  CreateObject('phpgwapi.config');
			$config->save_value('lang_ctimes',$GLOBALS['egw_info']['server']['lang_ctimes'],'phpgwapi');
		}

		/**
		 * re-loads all (!) langfiles if one langfile for the an app and the language of the user has changed
		 */
		function autoload_changed_langfiles()
		{
			//echo "<h1>check_langs()</h1>\n";
			if ($GLOBALS['egw_info']['server']['lang_ctimes'] && !is_array($GLOBALS['egw_info']['server']['lang_ctimes']))
			{
				$GLOBALS['egw_info']['server']['lang_ctimes'] = unserialize($GLOBALS['egw_info']['server']['lang_ctimes']);
			}
			//_debug_array($GLOBALS['egw_info']['server']['lang_ctimes']);

			$lang = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
			$apps = $GLOBALS['egw_info']['user']['apps'];
			$apps['phpgwapi'] = True;	// check the api too
			foreach($apps as $app => $data)
			{
				$fname = EGW_SERVER_ROOT . "/$app/setup/phpgw_$lang.lang";

				if (file_exists($fname))
				{
					$ctime = filectime($fname);
					/* This is done to avoid string offset error at least in php5 */
					$tmp = $GLOBALS['egw_info']['server']['lang_ctimes'][$lang];
					$ltime = (int)$tmp[$app];
					unset($tmp);
					//echo "checking lang='$lang', app='$app', ctime='$ctime', ltime='$ltime'<br>\n";

					if ($ctime != $ltime)
					{
						// update all langs
						$installed = $this->get_installed_langs();
						//echo "<p>install_langs(".print_r($installed,True).")</p>\n";
						$this->install_langs($installed ? array_keys($installed) : array());
						break;
					}
				}
			}
		}

		/* Following functions are called for app (un)install */

		/**
		 * gets array of installed languages, e.g. array('de','en')
		 *
		 * @param boolean $DEBUG=false debug messages or not, default not
		 * @return array with installed langs
		 */
		function get_langs($DEBUG=False)
		{
			if($DEBUG)
			{
				echo '<br>get_langs(): checking db...' . "\n";
			}
			if (!$this->langs)
			{
				$this->get_installed_langs();
			}
			return $this->langs ? array_keys($this->langs) : array();
		}

		/**
		 * delete all lang entries for an application, return True if langs were found
		 *
		 * @param $appname app_name whose translations you want to delete
		 * @param boolean $DEBUG=false debug messages or not, default not
		 * @return boolean true if $appname had translations installed, false otherwise
		 */
		function drop_langs($appname,$DEBUG=False)
		{
			if($DEBUG)
			{
				echo '<br>drop_langs(): Working on: ' . $appname;
			}
			$this->db->select($this->lang_table,'COUNT(message_id)',array(
				'app_name' => $appname
			),__LINE__,__FILE__);
			
			if($this->db->next_record() && $this->db->f(0))
			{
				$this->db->delete($this->lang_table,array(
					'app_name' => $appname
				),__LINE__,__FILE__);
				return True;
			}
			return False;
		}

		/**
		 * process an application's lang files, calling get_langs() to see what langs the admin installed already
		 *
		 * @param string $appname app_name of application to process
		 * @param boolean $DEBUG=false debug messages or not, default not
		 * @param array/boolean $force_langs=false array with langs to install anyway (beside the allready installed ones), or false for none
		 */
		function add_langs($appname,$DEBUG=False,$force_langs=False)
		{
			$langs = $this->get_langs($DEBUG);
			if(is_array($force_langs))
			{
				foreach($force_langs as $lang)
				{
					if (!in_array($lang,$langs))
					{
						$langs[] = $lang;
					}
				}
			}

			if($DEBUG)
			{
				echo '<br>add_langs(): chose these langs: ';
				_debug_array($langs);
			}

			$this->install_langs($langs,'addmissing',$appname);
		}
	}
