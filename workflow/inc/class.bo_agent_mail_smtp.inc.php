<?php
	/**************************************************************************\
	* eGroupWare Workflow - Mail SMTP Agent Connector - business layer         *
	* ------------------------------------------------------------------------ *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published           *
	* by the Free Software Foundation; either version 2 of the License, or     *
	* any later version.                                                       *
	\**************************************************************************/

	/* $Id$ */


	/*!
	 * Mail-SMTP Agent : business layer
	 *
	 * This class connects the workflow agent to the egroupware phpmailer and emailadmin
	 * This let the workflow activities send emails. It contains some logic to replace
	 * known tokens by workflow information (user, owner, activity name, etc...)
	 *
	 * @package workflow
	 * @author regis.leroy@glconseil.com
	 * @license GPL
	 */


	require_once(dirname(__FILE__) . SEP . 'class.bo_agent.inc.php');
	
	//some define for the send mode
	if (!defined('_SMTP_MAIL_AGENT_SND_COMP')) define('_SMTP_MAIL_AGENT_SND_COMP', 0);
	if (!defined('_SMTP_MAIL_AGENT_SND_POST')) define('_SMTP_MAIL_AGENT_SND_POST', 1);
	if (!defined('_SMTP_MAIL_AGENT_SND_AUTO_PRE')) define('_SMTP_MAIL_AGENT_SND_AUTO_PRE', 2);
	if (!defined('_SMTP_MAIL_AGENT_SND_AUTO_POS')) define('_SMTP_MAIL_AGENT_SND_AUTO_POS', 3);
	
	class bo_agent_mail_smtp extends bo_agent
	{
		var $public_functions = array(
			'bo_agent_mail_smtp'		=> true,
			'load'				=> true,
			'save'				=> true,
			'getAdminActivityOptions'	=> true,
			'decode_fields_in_final_array' 	=> true,
		);
			
		//the phpmailer object used at runtime to send email
		var $mail = null;
		//the emailadmin bo object to retriev egroupware mail configuration
		var $bo_emailadmin = null;
		//the emailadmin profile id
		var $profileID;
		// some maybe usefull egroupware or engine objects. Vars usefull to create only the first time
		// to avoid multiple SQL queries
		var $role_manager;
		var $account;
		var $process_name = '';
		var $process_version = '';
		var $process_id = '';
		var $activity_id = '';
		var $instance_id = '';
		//array containing part or this->fields recomputed to handle real email address and real values
		var $final_array = Array();
		// can be usefull to test mails building without sending them
		var $debugmode = false;
		
		function bo_agent_mail_smtp()
		{
			parent::bo_agent();
			$this->so_agent =& CreateObject('workflow.so_agent_mail_smtp');
			$this->bo_emailadmin =& CreateObject('emailadmin.bo');
			//the showProcessConfigurationFields is not done here, quite harder to build
			$this->ProcessConfigurationFieldsdefault = array(
				'mail_smtp_profile' 		=> false,
				'mail_smtp_signature'		=> lang('Mail automatically sent by Mail SMTP Agent for eGroupware\'s Workflow'),
				'mail_smtp_local_link_prefix'	=> '',
				'mail_smtp_debug'		=> false,
			);
			
			$this->title = lang('Mail Smtp Agent');
			$this->description = lang('This agent gives the activity the possibility to send an SMTP message (mail)');
			$this->help = lang('Use <a href="%1">EmailAdmin</a> to create mail profiles', $GLOBALS['phpgw']->link('/index.php',array('menuaction' => 'emailadmin.ui.listProfiles')));
			$this->help .= "<br />\n".lang('Mails can be sent at the begining or at the end of the activity, For interactive activities only it can be sent after completion.');
			$this->help .= "<br />\n".lang('Be carefull with interactive activity, end and start of theses activities are multiple.');
			$this->help .= "<br />\n".lang('You can use special values with this mail agent:');
			$this->help .= "<ul>\n";
			$this->help .=  '<li>'.lang('<strong>%user%</strong> is the instance user email')."\n";
			$this->help .=  '<li>'.lang('<strong>%owner%</strong> is the instance owner email')."\n";
			$this->help .=  '<li>'.lang('<strong>%roles%</strong> are the emails of all users mapped to any role on this activity')."\n";
			$this->help .=  '<li>'.lang('<strong>%role_XX%</strong> are all the emails of all users mapped to the role XX')."\n";
			$this->help .=  '<li>'.lang('<strong>%user_XX%</strong> is the email of the acount XX')."\n";
			$this->help .=  '<li>'.lang('<strong>%property_XX%</strong> is the content of the instance\'s property XX')."\n";
			$this->help .=  '<li>'.lang('<strong>%signature%</strong> is the agent signature defined in the process configuration')."\n";
			$this->help .=  '<li>'.lang('see as well <strong>%instance_name%</strong>, <strong>%activity_name%</strong>, <strong>%process_name%</strong>,<strong>%process_version%</strong>, <strong>%instance_id%</strong>, <strong>%activity_id%</strong> and <strong>%process_id%</strong>')."\n";
			$this->help .=  '<li>'.lang('finally you have links with <strong>%link_XX|YY%</strong> syntax, XX is the address part, YY the text part.')."\n";
			$this->help .= lang('Special known values for local links address are <strong>userinstance</strong>, <strong>viewinstance</strong>, <strong>viewniceinstance</strong> and <strong>admininstance</strong>.');
			$this->help .= lang('Link addresses are considered local if not containing <strong>http://</strong>. They will get appended the configured local prefix and scanned by egroupware link engine');
			$this->help .= "</ul>\n";
			$this->fields = array(
				'wf_to'		=> array(
					'type'		=> 'text',
					'label'		=> lang('To:'),
					'size'		=> 255,
					'value'		=> '',
					),
				'wf_cc'		=> array(
					'type'		=> 'text',
					'label'		=> lang('Cc:'),
					'size'		=> 255,
					'value'		=> '',
					),
				'wf_bcc'	=> array(
					'type'		=> 'text',
					'label'		=> lang('Bcc:'),
					'size'		=> 255,
					'value'		=> '',
					),
				'wf_from'	=> array(
					'type'		=> 'text',
					'label'		=> lang('From:'),
					'size'		=> 255,
					'value'		=> '',
					),
				'wf_replyto'	=> array(
					'type'		=> 'text',
					'label'		=> lang('ReplyTo:'),
					'size'		=> 255,
					'value'		=> '',
					),
				'wf_subject'	=> array(
					'type'		=> 'text',
					'label'		=> lang('Subject:'),
					'size'		=> 255,
					'value'		=> '',
					),
				'wf_message'	=> array(
					'type'		=> 'textarea',
					'label'		=> lang('Message:'),
					'value'		=> '',
					),
				'wf_send_mode'	=> array(
					'type'	=> 'select',
					'label'	=> lang('When to send the Message:'),
					'value' => '',
					'values'=>  array(
						_SMTP_MAIL_AGENT_SND_COMP	=> lang('send after interactive activity is completed'),
						/*_SMTP_MAIL_AGENT_SND_POST	=> lang("send when wf_agent_mail_smtp['submit_send'] is posted"),*/
						_SMTP_MAIL_AGENT_SND_AUTO_PRE	=> lang("send when the activity is starting"),
						_SMTP_MAIL_AGENT_SND_AUTO_POS	=> lang("send when the activity is ending"),
						),
					),
			);
			
		}

		/*!
		* Factory: Load the agent values stored somewhere in the agent object and retain the agent id
		* @param $agent_id is the agent id
		* @param $really_load boolean, true by default, if false the data wont be loaded from database and
		* the only thing done by this function is storing the agent_id (usefull if you know you wont need actual data)
		* @return false if the agent cannot be loaded, true else
		*/
		function load($agent_id, $really_load=true)
		{
			//read values from the so_object
			if ($really_load)
			{
				$values =& $this->so_agent->read($agent_id);
				foreach($values as $key => $value)
				{
					//load only known fields
					if (isset($this->fields[$key]))
					{
						$this->fields[$key]['value'] = $value;
						//echo "<br> DEBUG loading value $value for $key";
					}
				}
			}
			//store the id
			$this->agent_id = $agent_id;
		}

		/*!
		* Save the agent
		* @return false if the agent cannot be saved, true else
		*/
		function save()
		{
			//make a simplified version of $this->fields with just values
			$simplefields = Array();
			foreach ($this->fields as $field => $arrayfield)
			{
				$simplefields[$field] = $arrayfield['value'];
			}
			return $this->so_agent->save($this->agent_id, $simplefields);
		}

		
		/*!
		* this function lists activity level options avaible for the agent
		* @return an associative array which can be empty
		*/
		function getAdminActivityOptions ()
		{
			return $this->fields;
		}
		
		/*!
		* This function tell the engine which process level options have to be set
		* for the agent. Theses options will be initialized for all processes by the engine
		* and can be different for each process.
		* @return an array which can be empty
		*/
		function listProcessConfigurationFields()
		{
			$profile_list = $this->bo_emailadmin->getProfileList();
			foreach($profile_list as $profile)
			{
				$my_profile_list[$profile['profileID']] = $profile['description'];
			}
			$this->showProcessConfigurationFields = array(
				'Mail SMTP Agent' 		=> 'title',
				'mail_smtp_profile' 		=> $my_profile_list,
				'mail_smtp_signature'		=> 'text',
				'mail_smtp_local_link_prefix'	=> 'text',
				'mail_smtp_debug'		=> 'yesno',
			);
			return $this->showProcessConfigurationFields;
		}
	
		/*!
		* return the SMTP config values stored by the emailadmin egw application
		* @return an associative array containing the'emailConfigValid' token at true if
		* it was ok, and at false else
		*/
		function getSMTPConfiguration()
		{
			$data =Array();
			$this->profileID = $this->conf['mail_smtp_profile'];
			$data['emailConfigValid'] = true;
			//code inspired by felamimail bo_preferences
			$profileData = $this->bo_emailadmin->getProfile($this->profileID);
			if(!is_array($profileData))
			{
				$data['emailConfigValid'] = false;
				return $data;
			}
			elseif ($this->profileID != $profileData['profileID'])
			{
				$this->profileID = $profileData['profileID'];
			}
			
			// set values to the global values
			$data['defaultDomainname']	= $profileData['defaultDomain'];
			$data['smtpServerAddress']	= $profileData['smtpServer'];
			$data['smtpPort']		= $profileData['smtpPort'];
			$data['smtpAuth']		= $profileData['smtpAuth'];
			$data['smtpType']               = $profileData['smtpType'];
			$useremail = $this->bo_emailadmin->getAccountEmailAddress($GLOBALS['phpgw_info']['user']['userid'], $this->profileID);
			$data['emailAddress']           = $useremail[0]['address'];
			return $data;
		}

		
		//initialize objects we will need for the mailing and retrieve the conf
		function init()
		{
			$this->mail = CreateObject('phpgwapi.phpmailer');
			//set the $this->conf
			$this->getProcessConfigurationFields($this->activity->getProcessId());
			if ($this->conf['mail_smtp_debug']) $this->debugmode = true;
			
		}
		
		/*!
		* @return true if the conf says that we send email on POSTed forms, else false.
		*/
		function sendOnPosted()
		{
			return ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_POST);
		}
		
		/*!
		* If this activity is defined as an activity sending the email when starting we'll send it now
		* WARNING : on interactive queries the user code is parsed several times and this function is called
		* each time you reach the begining of the code, this means at least the first time when you show the form
		* and every time you loop on the form + the last time when you complete the code (if the user did not cancel).
		* @return true if everything was ok, false if something went wrong
		*/
		function send_start()
		{
			if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_AUTO_PRE)
			{
				if (!($this->prepare_mail())) return false;
				return $this->send();
			}
			else
			{
				return true;
			}
		}

		
		/*!
		* If this activity is defined as an activity sending the email when finishing the code we'll send it now
		* WARNING : on interactive queries the user code is parsed several times and this function is called
		* each time you reach the end of the code without completing, this means at least the first time
		* and every time you loop on the form.
		* @return true if everything was ok, false if something went wrong
		*/
		function send_end()
		{
			if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_AUTO_POS)
			{
				if (!($this->prepare_mail())) return false;
				return $this->send();
			}
			else
			{
				return true;
			}
		}

		/*!
		* If this activity is defined as an activity sending the email when the user post a command for it
		* we'll send it now
		* @return true if everything was ok, false if something went wrong
		*/
		function send_post()
		{
			if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_POST)
			{
				if (!($this->prepare_mail())) return false;
				return $this->send();
			}
			else
			{
				return true;
			}
		}
		
		/*!
		*  If this activity is defined as an activity sending the email when completing we'll send it now
		* @return true if everything was ok, false if something went wrong
		*/
		function send_completed()
		{
			if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_COMP)
			{
				if (!($this->prepare_mail())) return false;
				return $this->send();
			}
			else
			{
				return true;
			}
		}

		//! Buid the email fields
		function prepare_mail()
		{
			$userLang = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
			$langFile = PHPGW_SERVER_ROOT."/phpgwapi/setup/phpmailer.lang-$userLang.php";
			if(file_exists($langFile))
			{
				$this->mail->SetLanguage($userLang, PHPGW_SERVER_ROOT."/phpgwapi/setup/");
			}
			else
			{
				$this->mail->SetLanguage("en", PHPGW_SERVER_ROOT."/phpgwapi/setup/");
			}
			$this->mail->PluginDir = PHPGW_SERVER_ROOT."/phpgwapi/inc/";
			$this->mail->IsSMTP();
			
			//SMTP Conf
			$smtpconf =& $this->getSMTPConfiguration();
			if (!($smtpconf['emailConfigValid']))
			{
				$this->error[] = lang('The SMTP configuration cannot be loaded by the mail_smtp workflow agent');
				return false;
			}
			$this->mail->Host 	= $smtpconf['smtpServerAddress'];
			$this->mail->Port	= $smtpconf['smtpPort'];
			//SMTP Auth?
			if ($smtpconf['smtpAuth'])
			{
				$this->mail->SMTPAuth	= true;
				$this->mail->Username	= $GLOBALS['phpgw_info']['user']['userid'];
				$this->mail->Password	= $GLOBALS['phpgw_info']['user']['passwd'];
			}
			
			$this->mail->Encoding = '8bit';
			//TODO: handle Charset
			//$this->mail->CharSet	= $this->displayCharset;
			$this->mail->AddCustomHeader("X-Mailer: Egroupware Workflow");
			$this->mail->WordWrap = 76;
			//we need HTMl for handling nicely links
			$this->mail->IsHTML(true);
			//compute $this->final_fields if not done already
			if (!( $this->decode_fields_in_final_fields() ))
			{
				$this->error[] = lang('We were not able to build the message');
				return false;
			}
			$this->mail->From 	= $this->mail->EncodeHeader($this->final_fields['wf_from']);
			$this->mail->FromName 	= $this->activity->getName();
			$this->mail->Subject 	= $this->mail->EncodeHeader($this->final_fields['wf_subject']);
			$this->mail->Body    	= str_replace("\n",'<br />',$this->final_fields['wf_message']);
			$this->mail->AltBody	= $this->final_fields['wf_message'];
			$this->mail->ClearAllRecipients();
			foreach ($this->final_fields['wf_to'] as $email)
			{
				if (!(empty($email))) $this->mail->AddAddress($email);
			}
			foreach ($this->final_fields['wf_cc'] as $email)
			{
				if (!(empty($email))) $this->mail->AddCC($email);
			}
			foreach ($this->final_fields['wf_bcc'] as $email)
			{
				if (!(empty($email))) $this->mail->AddBCC($email);
			}
			foreach ($this->final_fields['wf_replyto'] as $email)
			{
				if (!(empty($email))) $this->mail->AddReplyTo($email);
			}
			return true;
		}
		
		/*!
		* This function is used to decode admin instructions about the final value or the activity
		* fields. i.e.: decoding %user% in toto@foo.com for example
		*	* If you call this function twice the final result will NOT be recalculated. except with the $force 
		*	parameter. This is done so that you can call this function sooner than the engine and add or remove
		*	emails from final fields. The engine will not recompute automatically theses fields if you done it already.
		* @param $force is falmse by default, if true the final are recalculated even if they are already there
		* @return true/false and set the $this->final_fields array containing the fields with the 'real' final value and for 
		* the wf_to, wf_bcc and wf_cc fields you'll have arrays with email values.
		*/
		function decode_fields_in_final_fields($force=false)
		{
			if ($force || (!(isset($this->final_fields['calculated']))) )
			{
				$res = Array();
				$result = Array();
				$address_array = Array();
				$email_list = Array();
				foreach ($this->fields as $key => $value)
				{
					$res[$key] =& $this->replace_tokens($value['value']);
					//for all adresse fields we make an email array to detect repetitions
					if (($key=='wf_to') || ($key=='wf_cc') || ($key=='wf_bcc'))
					{
						//warning, need to handle < and > as valid chars for emails
						$address_array  = imap_rfc822_parse_adrlist(str_replace('&gt;','>',str_replace('&lt;','<',$res[$key])),'');
						if (is_array($address_array) && (!(empty($address_array))))
						{
							foreach ($address_array as $val)
							{
								//we retain this email is used in To or Bcc or Cc
								//and we affect this email only the first time
								if ($val->host == '.SYNTAX-ERROR.')
								{
									$this->error[] = lang("at least one email address cannot be validated.");
									if ($this->debugmode)
									{
										$this->error[] = $res[$key];
									}
									return false;
								}
								$his_email = $val->mailbox.'@'. $val->host;
								if (!isset($email_list[$his_email]))
								{
									$email_list[$his_email] = $key;
									$result[$key][]= $his_email;
								}
							}
						}
						else
						{
							$result[$key] = Array();
						}
					}
					elseif ( ($key=='wf_from') || ($key=='wf_replyto'))
					{
						//warning, need to handle < and > as valid chars for emails
						$result[$key] = str_replace('&gt;','>',str_replace('&lt;','<',$res[$key]));
					}
					else
					{
						$result[$key] = $res[$key];
					}
				}
				$this->final_fields =& $result;
				$this->final_fields['calculated']=true;
			}
			return true;
		}
		/*!
		* This function is used to find and replace tokens in the fields
		* @param $string is the string to analyse
		* @return the modified string
		*/
		function replace_tokens(&$string)
		{
			//first we need to escape the \% before the analysis
			$string = str_replace('\%','&workflowpourcent;',$string);
			$matches = Array();
			preg_match_all("/%([^%]+)%/",$string, $matches);
			$final = $string;
			if ($this->activity_id =='') $this->activity_id = $this->activity->getActivityId();
			if ($this->instance_id =='') $this->instance_id = $this->instance->getInstanceId();
			if ($this->process_id =='') $this->process_id = $this->activity->getProcessId();
			foreach($matches[1] as $key => $value)
			{
				//$value is our %token%
				switch($value)
				{
					case 'signature':
								$matches[1][$key] = $this->conf['mail_smtp_signature'];
								break;
					case 'instance_name' :
						$matches[1][$key] = $this->instance->getName();
						break;
					case 'activity_name' :
						$matches[1][$key] = $this->activity->getName();
						break;
					case 'process_name' :
						if ($this->process_name=='')
						{
							$process =& CreateObject('workflow.workflow_process');
							$process->getProcess($this->process_id);
							$this->process_name = $process->getName();
							$this->process_version = $process->getVersion();
							unset ($process);
						}
						$matches[1][$key] = $this->process_name;
						break;
					case 'process_version' :
						if ($this->process_version=='')
						{
							$process =& CreateObject('workflow.workflow_process');
							$process->getProcess($this->process_id);
							$this->process_name = $process->getName();
							$this->process_version = $process->getVersion();
							unset ($process);
						}
						$matches[1][$key] = $this->process_version;
						break;
					case 'process_id' :
						$matches[1][$key] = $this->process_id;
						break;
					case 'instance_id' :
						$matches[1][$key] = $this->instance_id;
						break;
					case 'activity_id' :
						$matches[1][$key] = $this->activity_id;
						break;
					case 'user' :
						//the current instance/activity user which is in fact running
						//this class actually
						$matches[1][$key] = $GLOBALS['phpgw_info']['user']['email'];
						break;
					case 'owner' :
						//the owner of the instance
						if (!is_object($this->account))
						{
							$this->account =& CreateObject('phpgwapi.accounts');
						}
						$ask_user = $this->instance->getOwner();
						$matches[1][$key] = $this->account->id2name($ask_user, 'account_email');
						break;
					case 'roles' :
						//all users having at least one role on this activity
						if (!is_object($this->role_manager))
						{
							$this->role_manager =& CreateObject('workflow.workflow_rolemanager');
						}
						if (!is_object($this->account))
						{
							$this->account =& CreateObject('phpgwapi.accounts');
						}
						$my_subset = array('wf_activity_name' => $this->activity->getName());
						$listing =& $this->role_manager->list_mapped_users($this->instance->getProcessId(),true, $my_subset);
						$matches[1][$key] = '';
						foreach ($listing as $user_id => $user_name)
						{
							$user_email = $this->account->id2name($user_id);
							if ($matches[1][$key] == '')
							{
								$matches[1][$key] = $this->account->id2name($user_id, 'account_email');
							}
							else
							{
								$matches[1][$key] .= ', '.$this->account->id2name($user_id, 'account_email');
							}
						}
						break;
					default:
						//Now we need to handle role_foo or property_bar or user_foobar
						$matches2 = Array();
						//echo "<br>2nd analysis on ".$value;
						preg_match_all("/([^_]+)([_])([A-z0-9\|:\/\.\?\= ]*)/",$value, $matches2);
						$first_part = $matches2[1][0];
						$second_part = $matches2[3][0];
						switch ($first_part)
						{
							case 'user' :
								//we retrieve the asked user email
								if (!is_object($this->account))
								{
									$this->account =& CreateObject('phpgwapi.accounts');
								}
								$ask_user = $this->account->name2id($second_part);
								$matches[1][$key] = $this->account->id2name($ask_user, 'account_email');
								break;
							case 'property' :
								//we take the content of the given property on the instance
								$matches[1][$key] = $this->instance->get($second_part);
								break;
							case 'role' :
								//all user mapped to this role
								if (!is_object($this->role_manager))
								{
									$this->role_manager =& CreateObject('workflow.workflow_rolemanager');
								}
								if (!is_object($this->account))
								{
									$this->account =& CreateObject('phpgwapi.accounts');
								}
								$my_subset = array('wf_role_name' => $second_part);
								$listing =& $this->role_manager->list_mapped_users($this->instance->getProcessId(),true, $my_subset);
								//_debug_array($listing);
								$matches[1][$key] = '';
								foreach ($listing as $user_id => $user_name)
								{
									$user_email = $this->account->id2name($user_id);
									if ($matches[1][$key] == '')
									{
										$matches[1][$key] = $this->account->id2name($user_id, 'account_email');
									}
									else
									{
										$matches[1][$key] .= ', '.$this->account->id2name($user_id, 'account_email');
									}
								}
								break;
							case 'link' :
								//we want a link
								//the HTML characters are escaped, so we need this function
								//and we now some usefull links:
								//	* link to the ui_userinstance with instance filter
								//	* link to the ui_admininstance for this instance
								//	* link to the ui_userviewinstance for this instance
								//$second_part should be in this form link adress|text
								$matches3 = Array();
								//echo "<br>3rd analysis on ".$second_part;
								preg_match_all("/([^\|]+)([\|])([A-z0-9 ]*)/",$second_part, $matches3);
								$link_part = $matches3[1][0];
								$text_part = $matches3[3][0];
								//need something in the text
								if (empty($text_part)) $text_part=$link_part;
								//and something in the link
								switch ($link_part)
								{
									case 'userinstance' :
										$my_link = $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link('/index.php',array(
											'menuaction' 		=> 'workflow.ui_userinstances.form',
											'filter_instance'	=> $this->instance_id,
											)
										);
										break;
									case 'viewinstance' :
										$my_link =  $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link('/index.php',array(
											'menuaction'	=> 'workflow.ui_userviewinstance.form',
											'iid'		=> $this->instance_id,
											)
										);
										break;
									case 'viewniceinstance' :
										$GUI =& CreateObject('workflow.workflow_gui');
										$view_activity = $GUI->gui_get_process_user_view_activity($this->process_id,$GLOBALS['phpgw_info']['user']['account_id']);
										unset($GUI);
										if (!($view_activity))
										{//link on default view
											$my_link = $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link('/index.php',array(
												'menuaction'	=> 'workflow.ui_userviewinstance.form',
												'iid'		=> $this->instance_id,
												)
											);	
										}
										else
										{//link on this special activity
											$my_link = $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link('/index.php',array(
												'menuaction'	=> 'workflow.run_activity.go',
												'iid'		=> $this->instance_id,
												'activity_id'	=> $view_activity,
												)
											);
										}
										break;
									case 'admininstance' :
										$my_link = $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link('/index.php',array(
											'menuaction'	=> 'workflow.ui_admininstance.form',
											'iid'		=> $this->instance_id,
											)
										);
										break;
									default:
										//now it can be an external or local link
										if (substr($link_part,0,7)=='http://')
										{//external link
											$my_link = $link_part;
										}
										else
										{//local link
											$my_link = $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link($link_part);
										}
								}
								$matches[1][$key] = '<a href="'.$my_link.'">'.$text_part.'</a>';
								break;
							
							default:
								$matches[1][$key] = '';
						}
				}
				$final = str_replace($matches[0][$key],$matches[1][$key],$final);
			}
			//now get back the % escaped before the analysis
			$final = str_replace('&workflowpourcent;','%',$final);
			return $final;
		}
		
		function Send()
		{
			//$this->mail->SMTPDebug = 10;
			if (!($this->debugmode))
			{
				if(!$this->mail->Send())
				{
					$this->error[] = $this->mail->ErrorInfo;
					return false;
				}
			}
			else
			{
				//_debug_array($this->mail);
				$this->error[] = 'DEBUG mode: '.lang('if not in debug mail_smtp agent would have sent this email:');
				$this->error[] = 'DEBUG mode: Host:'.$this->mail->Host;
				$this->error[] = 'DEBUG mode: Port:'.$this->mail->Port;
				$this->error[] = 'DEBUG mode: From:'.htmlentities($this->mail->From);
				$this->error[] = 'DEBUG mode: FromName:'.htmlentities($this->mail->FromName);
				$msg = 'DEBUG mode: ReplyTo:';
				foreach ($this->mail->ReplyTo as $address)
				{
					$msg .= htmlentities($address[0]);
				}
				$this->error[] = $msg;
				$msg = 'DEBUG mode: To:';
				foreach ($this->mail->to as $address)
				{
					$msg .= htmlentities($address[0]);
				}
				$this->error[] = $msg;
				$msg = 'DEBUG mode: Cc:';
				foreach ($this->mail->cc as $address)
				{
					$msg .= htmlentities($address[0]);
				}
				$this->error[] = $msg;
				$msg = 'DEBUG mode: Bcc:';
				foreach ($this->mail->bcc as $address)
				{
					$msg .= ' '.htmlentities($address[0]);
				}
				$this->error[] = $msg;
				$this->error[] = 'DEBUG mode: Subject:'.htmlentities($this->mail->Subject);
				$this->error[] = 'DEBUG mode: AltBody:'.htmlentities($this->mail->AltBody);
				$this->error[] = 'DEBUG mode: Body (hmtl):'.$this->mail->Body;
			}
			return true;
		}
		
	}
?>
