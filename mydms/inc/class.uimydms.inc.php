<?php
	/***************************************************************************\
	* eGroupWare - mydms                                                        *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; version 2 of the License.                       *
	\***************************************************************************/

	/* $Id$ */

	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.Settings.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.DBAccess.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.AccessUtils.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassAccess.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassUser.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassGroup.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassFolder.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassDocument.php');

	class uimydms
	{
		var $icons = array(
			'odt'   => 'writer.png',
			'ods'   => 'calc.png',
			'odp'   => 'impress.png',
			'odg'   => 'draw.png',
			'odb'   => 'base.png',
			'odf'   => 'math.png',
			'txt'	=> 'txt.png',
			'doc'	=> 'word.png',
			'rtf'	=> 'document.png',
			'xls'	=> 'excel.png',
			'ppt'	=> 'powerpoint.png',
			'exe'	=> 'binary.png',
			'html'	=> 'html.png',
			'htm'	=> 'html.png',
			'gif'	=> 'image.png',
			'jpg'	=> 'image.png',
			'bmp'	=> 'image.png',
			'png'	=> 'image.png',
			'log'	=> 'log.png',
			'midi'	=> 'midi.png',
			'pdf'	=> 'pdf.png',
			'wav'	=> 'sound.png',
			'mp3'	=> 'sound.png',
			'c'	=> 'source_c.png',
			'cpp'	=> 'source_cpp.png',
			'h'	=> 'source_h.png',
			'java'	=> 'source_java.png',
			'py'	=> 'source_py.png',
			'tar'	=> 'tar.png',
			'gz'	=> 'gz.png',
			'zip'	=> 'gz.png',
			'mpg'	=> 'video.png',
			'avi'	=> 'video.png',
			'tex'	=> 'tex.png',
			'default' => 'default.png',
		);

		var $public_functions = array
		(
			'addACL'		=> 'true',
			'addNotification'	=> 'true',
			'deleteACL'		=> 'true',
			'deleteDocument'	=> 'true',
			'deleteFile'		=> 'true',
			'deleteLink'		=> 'true',
			'addLink'		=> 'true',
			'deleteNotification'	=> 'true',
			'folderChooser'		=> 'true',
			'inheritACL'		=> 'true',
			'setDefaultAccess'	=> 'true',
			'updateACL'		=> 'true',
			'updateDocument'	=> 'true',
			'updateFile'		=> 'true',
			'setOwner'		=> 'true',
			'viewDocument'		=> 'true',
		);

		function uimydms()
		{
			$this->t 		=& CreateObject('phpgwapi.Template',EGW_APP_TPL);
			$this->bomydms 		=& CreateObject('mydms.bomydms');

			if (!is_object($GLOBALS['egw']->jscalendar))
			{
				$GLOBALS['egw']->jscalendar = CreateObject('phpgwapi.jscalendar');
			}
		}

		function addACL()
		{
			$userID		= (int)$_POST['userid'];
			$groupID	= (int)$_POST['groupid'];
			$access		= (int)$_POST['access'];

			$documentID	= (int)$_GET['documentid'];

			if($documentID)
			{
				$this->bomydms->addACL($documentID, ($userID == 'unselected' ? false : (int)$userID), ($groupID == 'unselected' ? false : (int)$groupID), $access);
				$this->viewDocument($documentID);
			}
		}

		function addNotification()
		{
			$userID		= $_POST['userid'];
			$groupID	= $_POST['groupid'];

			$documentID	= (int)$_GET['documentid'];

			if($documentID)
			{
				$this->bomydms->addNotification($documentID, ($userID == 'unselected' ? false : (int)$userID), ($groupID == 'unselected' ? false : (int)$groupID));
				$this->viewDocument($documentID);
			}
		}

		function folderChooser($_folderID=false)
		{
			$folderID = ($_folderID === false ? (int)$_GET['folderid'] : $_folderID);
			$formName = $_GET['form'];

			$folder = getFolder($folderID);

			$this->display_app_header();

			$this->t->set_file(array("folderChooser" => "folderChooser.tpl"));
			$this->t->set_block('folderChooser', 'main', 'main');

			$this->t->set_var('folderTree',$this->getFolderTree($folder->getPathNew(), 0, $folder, true));
			$this->t->set_var('formName',$formName);

			$this->t->parse("out","main");

			print $this->t->get('out','main');
		}

		/**
		* create a folder tree
		*
		* this function will create a foldertree based on javascript
		* on click the sorounding form gets submitted
		*
		* @param _folders array containing the list of folders
		* @param _selected string containing the selected folder
		* @param _topFolderName string containing the top folder name
		* @param _topFolderDescription string containing the description for the top folder
		* @param _formName string name of the sorounding form
		* @param _hiddenVar string hidden form value, transports the selected folder
		*
		* @return string the html code, to be added into the template
		*/
		function createHTMLFolder($_folders, $_selected, $_divName, $_displayCheckBox=false)
		{
			$allFolders = $_folders;

			$folderImageDir = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/templates/default/images/';

			$folder_tree_new  = '<link rel="STYLESHEET" type="text/css" href="'.$GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/dhtmlxtree/css/dhtmlXTree.css">';
			$folder_tree_new .= "<script type='text/javascript'>";
			$folder_tree_new .= "tree=new dhtmlXTreeObject('$_divName','100%','100%',0);\n";
			$folder_tree_new .= "tree.setImagePath('$folderImageDir/dhtmlxtree/');\n";
			$folder_tree_new .= "tree.setOnClickHandler('onNodeSelect');\n";
			$folder_tree_new .= "tree.setOnRightClickHandler('onNodeSelectRight');\n";

			$linkData = array
			(
				'menuaction'	=> 'mydms.uifolder.getSubFolder',
			);
			$xmlAutoLoadURL = $GLOBALS['egw']->link('/index.php',$linkData);

			$folder_tree_new .= "tree.setXMLAutoLoading('$xmlAutoLoadURL');\n";

			if($_displayCheckBox)
			{
				$folder_tree_new .= "tree.enableCheckBoxes(1);";
				$folder_tree_new .= "tree.setOnCheckHandler('onCheckHandler');";
			}

			// generate object for main folder
			$folderObject 	= array_shift($allFolders);
			$image1 	= "'folderClosed.gif'";
			$image2 	= "0";
			$image3		= "0";

			$entryOptions	= 'CHILD,CHECKED';
			$folderID	= $folderObject->getID();
			$folderName	= $folderObject->getName();

			$selectedFolderID = $_selected->getID();

			$folder_tree_new .= "tree.insertNewItem('0','$folderID','$folderName',onNodeSelect,$image1,$image2,$image3,'$entryOptions');\n";


			//get the data about the last object
			$lastFolderID = 1;
			if($folderObject	= array_pop($allFolders))
			{
				$lastFolderID = $folderObject->getID();
			}

			$linkData = array
			(
				'menuaction'	=> 'mydms.uifolder.getInitialFolderView',
				'id'		=> $lastFolderID,
			);
			$xmlInitialLoadURL = $GLOBALS['egw']->link('/index.php',$linkData);

			if($selectedFolderID == 1) {
				$folder_tree_new .= "tree.selectItem('$folderID',false);";
			}

			$folder_tree_new .= "tree.loadXML('$xmlInitialLoadURL');";

			if($selectedFolderID == 1) {
				$folder_tree_new .= "tree.openItem('1');";
			}

			$folder_tree_new.= '</script>';

			return $folder_tree_new;
		}

/*		function createHTMLFolder_old($_folders, $_selected, $_divName, $_displayCheckBox=false)
		{
			$allFolders = $_folders;

			$folderImageDir = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/templates/default/images/';

			$folder_tree_new  = '<link rel="STYLESHEET" type="text/css" href="'.$GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/dhtmlxtree/css/dhtmlXTree.css">';
			$folder_tree_new .= "<script type='text/javascript'>";
			$folder_tree_new .= "tree=new dhtmlXTreeObject('$_divName','100%','100%',0);";
			$folder_tree_new .= "tree.setImagePath('$folderImageDir/dhtmlxtree/');";
			$folder_tree_new .= "tree.setOnClickHandler('onNodeSelect');";

			$linkData = array
			(
				'menuaction'	=> 'mydms.uifolder.getSubFolder',
			);
			$xmlAutoLoadURL = $GLOBALS['egw']->link('/index.php',$linkData);

			$folder_tree_new .= "tree.setXMLAutoLoading('$xmlAutoLoadURL');";
			if($_displayCheckBox)
			{
				$folder_tree_new .= "tree.enableCheckBoxes(1);";
				$folder_tree_new .= "tree.setOnCheckHandler('onCheckHandler');";
			}

			#foreach($allFolders as $folderID => $folderObject)
			#{
				$folderObject = array_shift($allFolders);
				$image1 = "'folderClosed.gif'";
				$image2 = "0";
				$image3 = "0";

			$linkData = array
			(
				'menuaction'	=> 'mydms.uifolder.getInitialFolderView',
				'id'		=> $folderObject->getID(),
			);
			$xmlAutoLoadURL = $GLOBALS['egw']->link('/index.php',$linkData);

				if(empty($parentName)) $parentName = '--topfolder--';

				$entryOptions = 'CHILD,CHECKED';

				// highlight currently selected mailbox
				if ($folderObject->getID() == $_selected->getID())
				{
					$entryOptions .= ',SELECT';
				}

				$folderID	= $folderObject->getID();
				$folderName	= $folderObject->getName();
				$parentID	= ($folderObject->_parentID ? $folderObject->_parentID : 0);

				$folder_tree_new .= "tree.insertNewItem('$parentID','$folderID','$folderName',onNodeSelect,$image1,$image2,$image3,'$entryOptions');\n";
				$folder_tree_new .= "tree.openItem('$folderID');";
				$folder_tree_new .= "tree.loadXML('$xmlAutoLoadURL&id=1');";
				$folder_tree_new .= "tree.openItem(1);";
				#$folder_tree_new .= "tree.loadXML('$xmlAutoLoadURL&id=6');";
				#if($_displayCheckBox)
				#	$folder_tree_new .= "tree.setCheck('$longName','".(int)$obj->subscribed."');";
			#}


#			foreach($allFolders as $folderID => $folderObject)
#			{
#				$folder_tree_new .= "tree.loadXML('$xmlAutoLoadURL&id=".$folderObject->getID()."');";
#				$folder_tree_new .= "tree.openItem('".$folderObject->getID()."');";
#			}


			#$selectedID = $_selected->getID();
			#$folder_tree_new.= "tree.closeAllItems(0);tree.openItem('$selectedID');"
			$folder_tree_new.= '</script><a href="#" onclick="javascript:tree.openItem(1); return false;">test</a><a href="#" onclick="javascript:tree.closeItem(1); return false;">test b</a>';

			return $folder_tree_new;
		}
*/
		function deleteACL()
		{
			$userID		= (int)$_GET['userid'];
			$groupID	= (int)$_GET['groupid'];

			$documentID	= (int)$_GET['documentid'];

			if($documentID)
			{
				$this->bomydms->deleteACL($documentID, ($userID == 'unselected' ? false : (int)$userID), ($groupID == 'unselected' ? false : (int)$groupID));
				$this->viewDocument($documentID);
			}
		}

		function deleteDocument()
		{
			$documentID	= (int)$_GET['documentid'];

			if($documentID)
			{
				$this->bomydms->deleteDocument($documentID);
			}
			print '<script language="JavaScript">window.close();</script>';
		}

		function deleteFile()
		{
			$documentID	= (int)$_GET['documentid'];
			$version	= (int)$_GET['version'];

			if($documentID && $version)
			{
				$this->bomydms->deleteFile($documentID, $version);
				$this->viewDocument($documentID);
			}
		}

		//tim
		function deleteLink()
		{
			$documentID	= (int)$_GET['documentid'];
			$linkid		= (int)$_GET['linkid'];

			if($documentID && $linkid)
			{
				$this->bomydms->deleteLink($documentID, $linkid);
				$this->viewDocument($documentID);
			}
		}
		//-----

		//tim
		function addLink()
		{
			$documentID	= (int)get_var('documentid','GET','');
			$docid 		= (int)get_var('docid','POST','');
			$public 	= get_var('public','POST',false);

			if($documentID && $docid)
			{
				$this->bomydms->addLink($documentID, $docid, $public);
				$this->viewDocument($documentID);
			}
			else
			{
				$this->viewDocument($documentID);
				echo "<div style=\"text-align: center; color: rgb(255, 0, 0);\"><br>".lang('Not selected document!')."</div>";
			}
		}
		//-----

		function deleteNotification()
		{
			$documentID	= (int)$_GET['documentid'];
			$userID		= (int)$_GET['userid'];
			$groupID	= (int)$_GET['groupid'];

			if($documentID && ($userID || $groupID))
			{
				$this->bomydms->deleteNotification($documentID, (!$userID ? false : $userID), (!$groupID ? false : $groupID));
				$this->viewDocument($documentID);
			}
		}

		function display_app_header()
		{
			if(!@is_object($GLOBALS['egw']->js))
			{
				$GLOBALS['egw']->js =& CreateObject('phpgwapi.javascript');
			}
			if (!is_object($GLOBALS['egw']->jscalendar))
			{
				$GLOBALS['egw']->jscalendar = CreateObject('phpgwapi.jscalendar');
			}
			$GLOBALS['egw']->js->validate_file('dhtmlxtree','js/dhtmlXCommon');
			$GLOBALS['egw']->js->validate_file('dhtmlxtree','js/dhtmlXTree');
			switch($_GET['menuaction'])
			{
				case 'mydms.uimydms.addACL':
				case 'mydms.uimydms.addNotification':
				case 'mydms.uimydms.deleteACL':
				case 'mydms.uimydms.deleteFile':
				case 'mydms.uimydms.deleteLink':   //tim
				case 'mydms.uimydms.addLink':   //tim
				case 'mydms.uimydms.deleteNotification':
				case 'mydms.uimydms.setDefaultAccess':
				case 'mydms.uimydms.setOwner':
				case 'mydms.uimydms.updateACL':
				case 'mydms.uimydms.updateFile':
				case 'mydms.uimydms.viewDocument':
				case 'mydms.uimydms.inheritACL':
					$GLOBALS['egw']->js->validate_file('tabs','tabs');
					$GLOBALS['egw']->js->validate_file('jscode','viewDocument','mydms');
					$GLOBALS['egw']->js->validate_file('jscode','mydms','mydms');

					$GLOBALS['egw']->js->set_onload('javascript:initTabs();');
					break;
				case 'mydms.uimydms.folderChooser':
					$GLOBALS['egw']->js->validate_file('jscode','mydms','mydms');
					$GLOBALS['egw']->js->validate_file('jscode','folderChooser','mydms');
					break;
			}

			$GLOBALS['egw_info']['flags']['include_xajax'] = True;

			$GLOBALS['egw']->common->egw_header();
			#if(!$this->mailPreferences['messageNewWindow'])
			#	echo parse_navbar();
		}

		function getFolderTree($path, $level = 0, $_activeObj, $isFolder)
		{
			return $this->createHTMLFolder($path, $_activeObj, 'mydmstreebox');
		}

		function inheritACL()
		{
			$documentID	= (int)$_GET['documentid'];
			$action		= $_GET['action'];
			$mode		= $_GET['mode'];

			if($documentID && $action)
			{
				$this->bomydms->inheritACL($documentID, $action, $mode);
				$this->viewDocument($documentID);
			}
		}

		function setDefaultAccess()
		{
			$documentID	= (int)$_GET['documentid'];
			$mode		= $_POST['defaultAccess'];

			if($documentID && $mode)
			{
				$this->bomydms->setDefaultAccess($documentID, $mode);
				$this->viewDocument($documentID);
			}
		}

		function setOwner()
		{
			$documentID	= (int)$_GET['documentid'];
			$owner		= (int)$_POST['owner'];

			if($documentID && $owner)
			{
				$this->bomydms->setOwner($documentID, $owner);
				$this->viewDocument($documentID);
			}
		}

		function translate()
		{
			$this->t->set_var('lang_informations',lang('informations'));
			$this->t->set_var('lang_all_versions',lang('all versions'));
			$this->t->set_var('lang_linked_documents',lang('linked documents'));
			$this->t->set_var('lang_owner',lang('owner'));
			$this->t->set_var('lang_comment',lang('comment'));
			$this->t->set_var('lang_keywords',lang('keywords'));
			$this->t->set_var('lang_download',lang('download'));
			$this->t->set_var('lang_last_update',lang('last update'));
			$this->t->set_var('lang_current_version',lang('current version'));
			$this->t->set_var('lang_uploaded_by',lang('uploaded by'));
			$this->t->set_var('lang_file_size',lang('file size'));
			$this->t->set_var('lang_filename',lang('filename'));
			$this->t->set_var('lang_mime_type',lang('mime type'));
			$this->t->set_var('lang_comment_for_current_version',lang('comment for current version'));
			$this->t->set_var('lang_creation_date',lang('creation date'));
			$this->t->set_var('lang_lock_status',lang('lock status'));
			$this->t->set_var('lang_expires',lang('expires'));
			$this->t->set_var('lang_version',lang('version'));
			$this->t->set_var('lang_upload_date',lang('upload date'));
			$this->t->set_var('lang_save',lang('save'));
			$this->t->set_var('lang_cancel',lang('cancel'));
			$this->t->set_var('lang_delete',lang('delete'));
			$this->t->set_var('lang_view_online',lang('view online'));
			$this->t->set_var('lang_confirm_delete',lang('Do you really want to delete this document?'));
			$this->t->set_var('lang_update_document',lang('update document'));
			$this->t->set_var('lang_update',lang('update'));
			$this->t->set_var('lang_acl',lang('acl'));
			$this->t->set_var('lang_name',lang('name'));
			$this->t->set_var('lang_move_document',lang('move document'));
			$this->t->set_var('lang_folder',lang('folder'));
			$this->t->set_var('lang_user',lang('user'));
			$this->t->set_var('lang_group',lang('group'));
			$this->t->set_var('lang_acl',lang('acl'));
			$this->t->set_var('lang_acl_get_inherited',lang('acl get inherited'));
			$this->t->set_var('lang_notifications',lang('notifications'));
			$this->t->set_var('lang_file_gets_unlocked',lang('this document gets unlocked by you.'));
			$this->t->set_var('lang_file_gets_locked',lang('this document gets locked by you!'));
			$this->t->set_var('lang_delete_this_notification',lang('delete this notification'));
			$this->t->set_var('lang_add_notification',lang('add notification'));
			$this->t->set_var('lang_information_about_last_update',lang('information about last update'));
			$this->t->set_var('lang_general_information',lang('general information'));
			$this->t->set_var('lang_copy_acl',lang('copy acl'));
			$this->t->set_var('lang_create_empty_acl',lang('create empty acl'));
			$this->t->set_var('lang_inherit_acl_again',lang('inherit acl again'));
			$this->t->set_var('lang_default_access',lang('default access'));
			$this->t->set_var('lang_permission',lang('permission'));
			$this->t->set_var('lang_add_acl',lang('add acl'));
			$this->t->set_var('lang_current_acl',lang('current acl'));
			$this->t->set_var('lang_access_mode',lang('access mode'));
			$this->t->set_var('lang_confirm_acl_delete',lang('Do you really want to delete this acl?'));
			//tim
			$this->t->set_var('lang_document_link_by',lang('Linked by'));
			$this->t->set_var('lang_document_link_public',lang('Public'));
			$this->t->set_var('lang_choose_target_document',lang('Choose document'));
			$this->t->set_var('lang_add_document_link',lang('Add link'));
			//---
		}

		function updateACL()
		{
			$userID		= (int)$_GET['userid'];
			$groupID	= (int)$_GET['groupid'];
			$access		= (int)$_POST['mode'];

			$documentID	= (int)$_GET['documentid'];

			if($documentID)
			{
				$this->bomydms->updateACL($documentID, ($userID == 'unselected' ? false : (int)$userID), ($groupID == 'unselected' ? false : (int)$groupID), $access);
				$this->viewDocument($documentID);
			}
		}

		function updateDocument()
		{
			$fileName	= $_POST['fname'];
			$comment	= $_POST['comment'];
			$keywords	= $_POST['keywords'];
			$expire		= $_POST['expire'];
			$expire_date	= $GLOBALS['egw']->jscalendar->input2date($_POST['expire_date']);
			$lockStatus	= $_POST['lockStatus'];
			$targetID	= $_POST['targetid'];

			$documentID	= (int)$_GET['documentid'];

			if($documentID)
			{
				$this->bomydms->updateDocument($documentID,$fileName,$comment,$keywords,($expire ? $expire_date['raw'] : 0));
				if($lockStatus != 'unchanged')
				{
					$this->bomydms->updateLockStatus($documentID, $lockStatus);
				}
				if($targetID != 'unchanged')
				{
					$this->bomydms->moveDocument($documentID, (int)$targetID);
				}
			}
			print '<script language="JavaScript">window.close();</script>';
		}

		function updateFile()
		{
			$comment	= $_POST['comment'];
			$expire		= $_POST['expire'];
			$expire_date	= $GLOBALS['egw']->jscalendar->input2date($_POST['expire_date_update']);
			$documentID	= (int)$_GET['documentid'];
			$userfile	= $_FILES['userfile'];

			if($documentID && is_uploaded_file($userfile['tmp_name']))
			{
				$this->bomydms->updateFile($documentID, $userfile, $comment, ($expire ? $expire_date['raw'] : 0));
				$this->viewDocument($documentID);
			}
		}

    		//tim
		function getMimeIcon($fileType)
		{
			$ext = substr($fileType, 1);
			if (isset($this->icons[$ext]))
				return $this->icons[$ext];
			else
				return $this->icons["default"];
		}//--------------

		function viewDocument($_documentID=false)
		{
			$documentID = ($_documentID === false ? (int)$_GET['documentid'] : $_documentID);

			if(!$document	= getDocument($documentID))
			{
				print "Access denied!"; exit;
			}
			$user 		= getUser($GLOBALS['egw_info']['user']['account_id']);
			$accessMode	= $document->getAccessMode($user);
			$owner		= $document->getOwner();
			$folder		= $document->getFolder();
			$latestContent	= $document->getLatestContent();
			$versions	= $document->getContent();
			$notifyList	= $document->getNotifyList();
			$accessList	= $document->getAccessList();
			$updatingUser	= $latestContent->getUser();

			#_debug_array($document);

			$this->display_app_header();

			$this->t->set_file(array("viewDocument" => "viewDocument.tpl"));
			$this->t->set_block('viewDocument', 'main', 'main');
			$this->t->set_block('viewDocument', 'acl_row', 'acl_row');
			$this->t->set_block('viewDocument', 'lock_row', 'lock_row');
			$this->t->set_block('viewDocument', 'version_row', 'version_row');
			$this->t->set_block('viewDocument', 'notification_row', 'notification_row');
			$this->t->set_block('viewDocument', 'information_ro', 'information_ro');
			$this->t->set_block('viewDocument', 'information_rw', 'information_rw');
			$this->t->set_block('viewDocument', 'block_download', 'block_download');
			$this->t->set_block('viewDocument', 'block_view_online', 'block_view_online');
			$this->t->set_block('viewDocument', 'block_delete', 'block_delete');
			$this->t->set_block('viewDocument', 'block_change_owner', 'block_change_owner');
			$this->t->set_block('viewDocument', 'block_acl_inherite', 'block_acl_inherite');
			$this->t->set_block('viewDocument', 'block_acl_notinherite', 'block_acl_notinherite');
			//tim link_tab
			$this->t->set_block('viewDocument', 'link_tab', 'link_tab');
			$this->t->set_block('viewDocument', 'link_tab_cont', 'link_tab_cont');
			$this->t->set_block('viewDocument', 'link_cel', 'link_cel');
			//---

			$downloadImage 	= $GLOBALS['egw']->common->image('mydms','download');
			$viewImage 	= $GLOBALS['egw']->common->image('mydms','view');
			$deleteImage 	= $GLOBALS['egw']->common->image('mydms','del');
			$groupImage	= $GLOBALS['egw']->common->image('mydms','groupicon');
			$userImage	= $GLOBALS['egw']->common->image('mydms','usericon');
			$saveImage	= $GLOBALS['egw']->common->image('mydms','save');
			$this->t->set_var('download_image',$downloadImage);
			$this->t->set_var('view_image',$viewImage);
			$this->t->set_var('delete_image',$deleteImage);
			$this->t->set_var('save_image',$saveImage);


			$this->translate();

			$linkData = array
			(
				'menuaction'	=> 'mydms.uimydms.folderChooser',
			);
			$this->t->set_var('folderChooserURL', $GLOBALS['egw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'	=> 'mydms.uimydms.updateDocument',
				'documentid'	=> $documentID,
			);
			$this->t->set_var('action_informations', $GLOBALS['egw']->link('/index.php',$linkData));

			// download link
			$linkData = array
			(
				'documentid'	=> $documentID,
				'version'	=> $latestContent->getVersion(),
			);
			$this->t->set_var('download_link', $GLOBALS['egw']->link('/mydms/op/op.Download.php',$linkData));
			$this->t->parse('download','block_download',True);

			// view Online
			if ($latestContent->viewOnline())
			{
				$linkData = array
				(
					'documentid'	=> $documentID,
					'version'	=> $latestContent->getVersion()
				);
				$this->t->set_var('view_link', $GLOBALS['egw']->link('/mydms/op/op.ViewOnline.php',$linkData));
				$this->t->parse('view_online','block_view_online',True);
			}

			// delete link
			if ($accessMode == M_ALL)
			{
				$linkData = array
				(
					'menuaction'	=> 'mydms.uimydms.deleteDocument',
					'documentid'	=> $documentID,
				);
				$this->t->set_var('delete_link', $GLOBALS['egw']->link('/index.php',$linkData));
				$this->t->parse('delete','block_delete',True);
			}

			$this->t->set_var('owner_fullname',	$owner->getFullName());
			$this->t->set_var('owner_email',	$owner->getEmail());

			$this->t->set_var('updater_fullname',	$updatingUser->getFullName());
			$this->t->set_var('updater_email',	$updatingUser->getEmail());
			//tim добавлена переменная 'name_icon'  вшаблон и функция getMimeIcon
			$this->t->set_var('name_icon',		$this->getMimeIcon($latestContent->getFileType()));

			$this->t->set_var('filename',		$document->getName());
			$this->t->set_var('comment',		$document->getComment());
			$this->t->set_var('keywords',		$document->getKeywords());
			$this->t->set_var('creation_date',	date("d.m.Y - H:i:s",$document->getDate()));

			$this->t->set_var('foldername',		$folder->getName());
			$this->t->set_var('current_folder_id',	$folder->getID());

			// lock status
			// lock select box

			if ($document->isLocked())
			{
				$lockingUser = $document->getLockingUser();

				$this->t->set_var('lang_current_status', lang('this document is locked by <a href="mailto:%1">%2</a>.',$lockingUser->getEmail(),$lockingUser->getFullName()));
				$this->t->set_var('checked_lock_status', 'checked');
				$this->t->parse('locking','lock_row',True);
			}
			else
			{
				$this->t->set_var('lang_current_status', lang('this document is currently not locked.'));

				$this->t->parse('locking','lock_row', True);
			}

			// expire select box
			$selectOptions = array('0' => lang('does not expire'), '1' => lang('expires after'));
			$this->t->set_var('select_expire',$GLOBALS['egw']->html->select('expire',(!$document->getExpires() ? 0 : 1),$selectOptions,true,"onchange=\"toggleJSCal(this);\""));
			#$this->t->set_var('select_expire',$GLOBALS['egw']->html->select('expire',(!$document->getExpires() ? 0 : 1),$selectOptions,true,"onchange=\"alert('test')\""));

			if (!$document->getExpires())
			{
				$this->t->set_var('expire_date_ro',lang('does not expire'));
				$this->t->set_var('expire_date',$GLOBALS['egw']->jscalendar->input('expire_date',''));
				$this->t->set_var('expire_class', 'inactive');
			}
			else
			{
				$this->t->set_var('expire_date_ro',lang('expires after').' '.date("d.m.Y",$document->getExpires()));
				$this->t->set_var('expire_date',$GLOBALS['egw']->jscalendar->input('expire_date',$document->getExpires()));
				$this->t->set_var('expire_class', 'active');
			}

			$this->t->set_var('current_version',	$latestContent->getVersion());
			$this->t->set_var('current_comment',	$latestContent->getComment());
			$this->t->set_var('mime_type',		$latestContent->getMimeType());
			$this->t->set_var('file_size',		filesize($GLOBALS['phpgw_info']['server']['files_dir'] . '/mydms/' . $latestContent->getPath()));
			$this->t->set_var('last_update',	date("d.m.Y - H:i:s",$latestContent->getDate()));
			//tim


			$this->t->set_var('rownum',		count($versions)+1);

			if (($accessMode >= M_READWRITE))
			{
				$this->t->parse('informations','information_rw',True);
			}
			else
			{
				$this->t->parse('informations','information_ro',True);
			}

			for ($i = count($versions)-1; $i >= 0; $i--)
			{
				$version = $versions[$i];
				$uploadingUser = $version->getUser();
				$comment = $version->getComment();
				//if (strlen($comment) > 25) $comment = substr($comment, 0, 22) . "...";

				$this->t->set_var('version_version',		$version->getVersion());
				$this->t->set_var('version_comment',		$version->getComment());
				$this->t->set_var('version_uploadingUser',	$uploadingUser->getFullName());
				$this->t->set_var('version_date',		date("d.m.Y - H:i:s",$version->getDate()));
				if ($version->viewOnline())
					$this->t->set_var('url_view_online', "<a target=\"_blank\" href=\"../op/viewonline" . $version->getURL()."\"><img src=\"images/view.gif\" width=18 height=18 border=0 title=\"".lang('view online')."\"></a>");
				else
					$this->t->set_var('url_view_online',	'');

				$linkData = array
				(
					'documentid'	=> $documentID,
					'version'	=> $version->getVersion()
				);
				$this->t->set_var('url_download_file', $GLOBALS['egw']->link('/mydms/op/op.Download.php',$linkData));

				if (($accessMode == M_ALL) && (count($versions) > 1))
				{
					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.deleteFile',
						'documentid'	=> $documentID,
						'version'	=> $version->getVersion(),
						'tabpage'	=> 2,
					);
					$this->t->set_var('url_delete_file',
						"<a href=\"".$GLOBALS['egw']->link('/index.php',$linkData).
							"\" onClick=\"return confirm('".lang('do you really want to delete this version of the document?').
							"');\"><img src=\"$deleteImage\" width=15 height=15 border=0 title=\"".
							lang("delete")."\"></a>");
				}

				$this->t->parse('versions','version_row',True);
			}

			//tim
			// div 3 notifications
			//------------------------------------------------------
			$settings = $GLOBALS['mydms']->settings;
			$links = $document->getDocumentLinks();
			$links = filterDocumentLinks($user, $links);
			$l_rownum = count($links)+1;

			$this->t->set_var('l_rownum',$l_rownum);

			if ($l_rownum > 1)
			{
				$this->t->parse('link_head','link_tab',True);

				foreach($links as $link)
				{
					$targetDoc = $link->getTarget();
					if(!$targetDoc) continue;
					$responsibleUser = $link->getUser();
					$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.viewDocument',
							'documentid'	=> $targetDoc->getID(),
							'tabpage'	=> 1,
						);
					$this->t->set_var('link_id',($GLOBALS['egw']->link('/index.php',$linkData)));
					//old
					//$this->t->set_var('link_id',"mydms/out/out.ViewDocument.php?documentid=".($targetDoc->getID()));
					$this->t->set_var('link_name',$targetDoc->getName());
					$this->t->set_var('link_comment',$targetDoc->getComment());
					$this->t->set_var('link_fullname',$responsibleUser->getFullName());
					$this->t->set_var('link_public',($link->isPublic()) ? lang('yes') : lang('no'));
					if (($user->getID() == $responsibleUser->getID()) || ($user->getID() == $settings->_adminID) || ($link->isPublic() && ($document->getAccessMode($user) >= M_READWRITE )))
					{
						$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.deleteLink',
							'documentid'	=> $documentID,
							'linkid'	=> $link->getID(),
							'tabpage'	=> 3,
						);
						$link_del = "<a href=\"".$GLOBALS['egw']->link('/index.php',$linkData).
							"\" onClick=\"return confirm('".lang('do you really want to delete this link for document?').
							"');\"><img src=\"$deleteImage\" width=15 height=15 border=0 title=\"".
							lang("delete")."\"></a>";
						//old
						//$link_del = "<a href=\"mydms/op/op.RemoveDocumentLink.php?documentid=".$documentID."&linkid=".$link->getID()."\"><img src=\"mydms/out/images/del.gif\" border=0></a>";

						$this->t->set_var('link_del',$link_del);
					}

					$this->t->parse('link_body','link_tab_cont',True);
				}
			}
			else $this->t->set_var('link_head',"<tr><td class=\"filelist\">&nbsp;&nbsp;".lang('No related documents')."</td></tr>");

			if (($user->getID() != $settings->_guestID) && ($document->getAccessMode($user) >= M_READWRITE))
			{
				$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.addLink',
							'documentid'	=> $documentID,
							'tabpage'	=> 3,
						);
				$this->t->set_var('link_actadd', ($GLOBALS['egw']->link('/index.php',$linkData)));
				//$this->t->set_var('link_actadd', "mydms/op/op.AddDocumentLink.php");
				$this->t->set_var('link_documentid', $documentID);
				$this->t->set_var('link_yes',lang('yes'));
				$this->t->set_var('link_no',lang('no'));
				$this->t->set_var('link_folder', ($settings->_rootFolderID));
				$this->t->parse('link_cell','link_cel',True);
			}

//-----------------------------------------------------------------------------------------------------------------------------
			// div 4 notifications
			$linkData = array
			(
				'menuaction'	=> 'mydms.uimydms.addNotification',
				'documentid'	=> $documentID,
				'tabpage'	=> 4,
			);
			$this->t->set_var('notify_form_action', $GLOBALS['egw']->link('/index.php',$linkData));

			foreach ((array)$notifyList["users"] as $userNotify)
			{
				$this->t->set_var('notify_username',$userNotify->getFullName());
				$this->t->set_var('notify_image',$userImage);

				$linkData = array
				(
					'menuaction'	=> 'mydms.uimydms.deleteNotification',
					'documentid'	=> $documentID,
					'userid'	=> $userNotify->getID(),
					'tabpage'	=> 4,
				);
				$this->t->set_var('link_notify_delete', $GLOBALS['egw']->link('/index.php',$linkData));

				$this->t->parse('notifications','notification_row',True);
			}

			foreach ((array)$notifyList["groups"] as $groupNotify)
			{
				$this->t->set_var('notify_username',$groupNotify->getName());
				$this->t->set_var('notify_image',$groupImage);

				$linkData = array
				(
					'menuaction'	=> 'mydms.uimydms.deleteNotification',
					'documentid'	=> $documentID,
					'groupid'	=> $groupNotify->getID(),
					'tabpage'	=> 4,
				);
				$this->t->set_var('link_notify_delete', $GLOBALS['egw']->link('/index.php',$linkData));

				$this->t->parse('notifications','notification_row',True);
			}

			$allUsers = getAllUsers();
			$allUsersOptions = array('unselected' => lang('please select').'...');
			foreach ($allUsers as $userObj)
			{
				$allUsersOptions[$userObj->getID()] = $userObj->getFullName();
			}
			$this->t->set_var('select_userid',$GLOBALS['egw']->html->select('userid',0,$allUsersOptions,true,"style=\"width: 300px;\" onchange=\"javascript:document.notify_form.submit();\""));

			$allGroups = getAllGroups();
			$allGroupsOptions = array('unselected' => lang('please select').'...');
			foreach ($allGroups as $groupObj)
			{
				$allGroupsOptions[$groupObj->getID()] = $groupObj->getName();
			}
			$this->t->set_var('select_groupid',$GLOBALS['egw']->html->select('groupid',0,$allGroupsOptions,true,"style=\"width: 300px;\" onchange=\"javascript:document.notify_form.submit();\""));

	// div 5 ACL
			if ($accessMode == M_ALL)
			{
				if ($user->isAdmin())
				{
					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.setOwner',
						'documentid'	=> $documentID,
						'tabpage'	=> 5,
					);
					$this->t->set_var('action_change_owner', $GLOBALS['egw']->link('/index.php',$linkData));

					$allUsers = getAllUsers();
					$allUsersOptions = array();
					foreach ($allUsers as $userObj)
					{
						$allUsersOptions[$userObj->getID()] = $userObj->getFullName();
					}
					$this->t->set_var('select_ownerid',$GLOBALS['egw']->html->select('owner',$owner->getID(),$allUsersOptions,true,"style=\"width: 300px;\" onchange=\"javascript:document.change_owner.submit();\""));

					$this->t->parse('change_owner','block_change_owner',True);
				}

				if ($document->inheritsAccess())
				{
					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.inheritACL',
						'documentid'	=> $documentID,
						'action'	=> 'notinherit',
						'mode'		=> 'copy',
						'tabpage'	=> 5,
					);
					$this->t->set_var('link_acl_copy', $GLOBALS['egw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.inheritACL',
						'documentid'	=> $documentID,
						'action'	=> 'notinherit',
						'mode'		=> 'empty',
						'tabpage'	=> 5,
					);
					$this->t->set_var('link_acl_empty', $GLOBALS['egw']->link('/index.php',$linkData));

					$this->t->parse('display_acl','block_acl_inherite',True);
				}
				else
				{
					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.inheritACL',
						'documentid'	=> $documentID,
						'action'	=> 'inherit',
						'tabpage'	=> 5,
					);
					$this->t->set_var('link_acl_inherit_again', $GLOBALS['egw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.setDefaultAccess',
						'documentid'	=> $documentID,
						'tabpage'	=> 5,
					);
					$this->t->set_var('action_change_default_access', $GLOBALS['egw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'mydms.uimydms.addACL',
						'documentid'	=> $documentID,
						'tabpage'	=> 5,
					);
					$this->t->set_var('action_add_acl', $GLOBALS['egw']->link('/index.php',$linkData));


					$defaultACL = array
					(
						M_NONE 		=> 'access_mode_none',
						M_READ		=> 'access_mode_read',
						M_READWRITE	=> 'access_mode_readwrite',
						M_ALL		=> 'access_mode_all',
					);
					$this->t->set_var('select_default_access',$GLOBALS['egw']->html->select('defaultAccess',$document->getDefaultAccess(),$defaultACL,false,"style=\"width: 300px;\" onchange=\"javascript:document.change_default_access.submit();\""));

					foreach ((array)$accessList["users"] as $userAccess)
					{
						$userObj	= $userAccess->getUser();
						$userMode	= $userAccess->getMode();

						$this->t->set_var('acl_username',$userObj->getFullName());
						$this->t->set_var('acl_image',$userImage);

						$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.updateACL',
							'documentid'	=> $documentID,
							'userid'	=> $userObj->getID(),
							'tabpage'	=> 5,
						);
						$this->t->set_var('action_acl_row', $GLOBALS['egw']->link('/index.php',$linkData));

						$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.deleteACL',
							'documentid'	=> $documentID,
							'userid'	=> $userObj->getID(),
							'tabpage'	=> 5,
						);
						$this->t->set_var('link_acl_delete', $GLOBALS['egw']->link('/index.php',$linkData));

						$this->t->set_var('acl_selectbox',$GLOBALS['egw']->html->select('mode',$userAccess->getMode(),$defaultACL,false,"style=\"width: 300px;\""));

						$this->t->parse('acls','acl_row',True);
					}

					foreach ((array)$accessList["groups"] as $groupAccess)
					{
						$groupObj	= $groupAccess->getGroup();
						$groupMode	= $groupAccess->getMode();

						$this->t->set_var('acl_username',$groupObj->getName());
						$this->t->set_var('acl_image',$groupImage);

						$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.updateACL',
							'documentid'	=> $documentID,
							'groupid'	=> $groupObj->getID(),
							'tabpage'	=> 5,
						);
						$this->t->set_var('action_acl_row', $GLOBALS['egw']->link('/index.php',$linkData));

						$linkData = array
						(
							'menuaction'	=> 'mydms.uimydms.deleteACL',
							'documentid'	=> $documentID,
							'groupid'	=> $groupObj->getID(),
							'tabpage'	=> 5,
						);
						$this->t->set_var('link_acl_delete', $GLOBALS['egw']->link('/index.php',$linkData));

						$this->t->set_var('acl_selectbox',$GLOBALS['egw']->html->select('mode',$groupAccess->getMode(),$defaultACL,false,"style=\"width: 300px;\""));

						$this->t->parse('acls','acl_row',True);
					}


					$allUsers = getAllUsers();
					$allUsersOptions = array('unselected' => lang('please select').'...');
					foreach ($allUsers as $userObj)
					{
						$allUsersOptions[$userObj->getID()] = $userObj->getFullName();
					}
					$this->t->set_var('select_add_acl_userid',$GLOBALS['egw']->html->select('userid',0,$allUsersOptions,true,"style=\"width: 300px;\" oonchange=\"javascript:document.notify_form.submit();\""));

					$allGroups = getAllGroups();
					$allGroupsOptions = array('unselected' => lang('please select').'...');
					foreach ($allGroups as $groupObj)
					{
						$allGroupsOptions[$groupObj->getID()] = $groupObj->getName();
					}
					$this->t->set_var('select_add_acl_groupid',$GLOBALS['egw']->html->select('groupid',0,$allGroupsOptions,true,"style=\"width: 300px;\" oonchange=\"javascript:document.notify_form.submit();\""));

					$this->t->set_var('select_add_acl_permission',$GLOBALS['egw']->html->select('access',M_READ,$defaultACL,false,"style=\"width: 300px;\" oonchange=\"javascript:document.change_default_access.submit();\""));

					$this->t->parse('display_acl','block_acl_notinherite',True);
				}
			}

	// div 6 update file

			$linkData = array
			(
				'menuaction'	=> 'mydms.uimydms.updateFile',
				'documentid'	=> $documentID,
			);
			$this->t->set_var('action_update_file', $GLOBALS['egw']->link('/index.php',$linkData));

			// expire select box
			$this->t->set_var('select_expire_update',$GLOBALS['egw']->html->select('expire',0,$selectOptions,true,"onchange=\"toggleJSCalUpdate(this);\""));

			$this->t->set_var('expire_date_update',$GLOBALS['egw']->jscalendar->input('expire_date_update',''));
			$this->t->set_var('expire_class_update', 'inactive');


			$this->t->parse("out","main");

			print $this->t->get('out','main');
		}

	}

?>