<?php
  /**************************************************************************\
  * phpGroupWare - Translation Editor                                        *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class solangfile
	{
		var $total;
		var $debug = False;

		var $langarray;   // Currently loaded translations
		// array of missing phrases.
		var $missingarray;
		var $src_file;
		var $tgt_file;
		var $loaded_apps = array(); // Loaded app langs

		var $public_functions = array(
			'index' => True
		);

		function solangfile()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function isin_array($needle,$haystack)
		{
			while (list ($k,$v) = each($haystack))
			{
				if ($v == $needle)
				{
					return True;
				}
			}
			return False;
		}

		//Known Issue, if a phrase contains a ' or a " the parse will be thrown off. unless ' are nesed inside "
		function parse_php_app($fd,$plist)
		{
			define('SEP',filesystem_separator());
			$d=dir($fd);
			while ($fn=$d->read())
			{
				if (is_dir($fd.$fn.SEP))
				{
					if (($fn!='.')&&($fn!='..')&&($fn!='CVS'))
					{
						$plist=$this->parse_php_app($fd.$fn.SEP,$plist);
					}
				}
				elseif ((strpos($fn,'.php')>1) && (is_readable($fd.$fn)))
				{
					$fp=fopen($fd.SEP.$fn,'r');
					$fds=substr($fd,strpos($fd,SEP));
					while (!feof($fp))
					{
						$str=fgets($fp,8192);
						while ($pos=strpos($str,'lang('))
						{
							$str=substr($str,$pos);
							if (strpos($str,'$')==5)
							{
								$str=substr($str,6);
							}
							else
							{
								$str=substr($str,6);
								if (!strpos($str,'\")'))
								{
									$s2=substr($str,0,strpos($str,'\')'));
								}
								else
								{
									$s2=substr($str,0,strpos($str,'\")'));
								}
								if ($s2!='')
								{
									$plist[$s2]=$fds;
								}
							}
						}
					}
					fclose($fp);
				}
			}
			$d->close();
			return ($plist);
		}

		function missing_app($app,$userlang=en)
		{
			$cur_lang=$this->load_app($app,$userlang);
			define('SEP',filesystem_separator());
			$fd = PHPGW_SERVER_ROOT . SEP . $app . SEP;
			$plist=array();
			$plist = $this->parse_php_app($fd,$plist);
			reset($plist);
			return($plist);
		}

		/*!
		@function add_app
		@abstract loads all app phrases into langarray
		@param $lang	user lang variable (defaults to en)
		*/
		function add_app($app,$userlang='en')
		{
			define('SEP',filesystem_separator());

			$fd = PHPGW_SERVER_ROOT . SEP . $app . SEP . 'setup';
			$fn = $fd . SEP . 'phpgw_' . $userlang . '.lang';
			if (is_writeable($fn))
			{
				$wr = True;
			}
			elseif(!file_exists($fn) && is_writeable($fd))

			{
				$wr = True;
			}
			if (file_exists($fn))
			{
				$this->src_file = $fn;
				$fp = fopen($fn,'rb');
				while ($data = fgets($fp,8000))
				{
					list($message_id,$app_name,$null,$content) = explode("\t",$data);
					if(!$message_id)
					{
						continue;
					}
					//echo '<br>add_app(): adding phrase: $this->langarray["'.$message_id.'"]=' . trim($content);
					$this->langarray[$message_id]['message_id'] = trim($message_id);
					$this->langarray[$message_id]['app_name']   = trim($app_name);
					$this->langarray[$message_id]['content']    = trim($content);
				}
				fclose($fp);
			}
			else
			{
				$this->src_file = lang('no file');
			}
			// stuff class array listing apps that are included already
			$this->loaded_apps[$userlang]['filename']  = $fn;
			$this->loaded_apps[$userlang]['writeable'] = $wr;

			if($this->debug) { _debug_array($this->langarray); }
			@ksort($this->langarray);
			return $this->langarray;
		}

		/*!
		@function add_app
		@abstract loads all app phrases into langarray
		@param $lang user lang variable (defaults to en)
		*/
		function load_app($app,$userlang='en')
		{
			define('SEP',filesystem_separator());

			$fd = PHPGW_SERVER_ROOT . SEP . $app . SEP . 'setup';
			$fn = $fd . SEP . 'phpgw_' . $userlang . '.lang';
			if (is_writeable($fn))
			{
				$wr = True;
			}
			elseif(!file_exists($fn) && is_writeable($fd))
			{
				$wr = True;
			}

			if (file_exists($fn))
			{
				$this->tgt_file = $fn;
				$fp = fopen($fn,'rb');
				while ($data = fgets($fp,8000))
				{
					list($message_id,$app_name,$null,$content) = explode("\t",$data);
					if(!$message_id)
					{
						continue;
					}
					//echo '<br>add_app(): adding phrase: $this->langarray["'.$message_id.'"]=' . trim($content);
					$langarray[$message_id]['message_id'] = trim($message_id);
					$langarray[$message_id]['app_name']   = trim($app_name);
					$langarray[$message_id]['content']    = trim($content);
				}
				fclose($fp);
			}
			else
			{
				$this->tgt_file = lang('no file');
			}
			// stuff class array listing apps that are included already
			$this->loaded_apps[$userlang]['filename']  = $fn;
			$this->loaded_apps[$userlang]['writeable'] = $wr;
			if($this->debug) { _debug_array($langarray); }
			@ksort($langarray);
			return $langarray;
		}

		function list_apps()
		{
			$this->db->query("SELECT * FROM phpgw_applications",__LINE__,__FILE__);
			if($this->db->num_rows())
			{
				while($this->db->next_record())
				{
					$name   = $this->db->f('app_name');
					$title  = $this->db->f('app_title');
					$apps[$name] = array(
						'title'  => $title,
						'name'   => $name
					);
				}
			}
			@reset($apps);
			$this->total = count($apps);
			if ($this->debug) { _debug_array($apps); }
			return $apps;
		}

		function list_langs()
		{
			$this->db->query("SELECT lang_id,lang_name FROM languages ORDER BY lang_name");
			$i = 0;
			while ($this->db->next_record())
			{
				$languages[$i]['lang_id']   = $this->db->f('lang_id');
				$languages[$i]['lang_name'] = $this->db->f('lang_name');
				$i++;
			}
			@reset($languages);
			if($this->debug) { _debug_array($languages); }
			return $languages;
		}

		function write_file($app_name,$langarray,$userlang)
		{
			$fn = PHPGW_SERVER_ROOT . SEP . $app_name . SEP . 'setup' . SEP . 'phpgw_' . $userlang . '.lang';
			$fp = fopen($fn,'wb');
			while(list($mess_id,$data) = each($langarray))
			{
				fwrite($fp,$mess_id . "\t" . $data['app_name'] . "\t" . $userlang . "\t" . $data['content'] . "\n");
			}
			fclose($fp);
			return;
		}

		function loaddb($app_name,$userlang)
		{
			$this->db->transaction_begin();

			$langarray = $this->load_app($app_name,$userlang);

			@reset($langarray);
			while (list($x,$data) = @each($langarray))
			{
				$addit = False;
				/* echo '<pre> checking ' . $data['message_id'] . "\t" . $data['app_name'] . "\t" . $userlang . "\t" . $data['content']; */
				$this->db->query("SELECT COUNT(*) FROM lang WHERE message_id='" . $data['message_id'] . "' and lang='$userlang'",__LINE__,__FILE__);
				$this->db->next_record();

				if ($this->db->f(0) == 0)
				{
					$addit = True;
					/* echo '... no</pre>'; */
				}
				else
				{
					/* echo '... yes</pre>'; */
				}

				if ($addit)
				{
					if($data['message_id'] && $data['content'])
					{
						/* echo "<br>adding - insert into lang values ('" . $data['message_id'] . "','$app_name','$userlang','" . $data['content'] . "')"; */
						$this->db->query("INSERT into lang VALUES ('"
							. addslashes($data['message_id']) . "','"
							. $data['app_name'] . "','$userlang','"
							. addslashes($data['content']) . "')",__LINE__,__FILE__);
					}
				}
			}
			$this->db->transaction_commit();
			return lang('done');
		}
	}
?>
