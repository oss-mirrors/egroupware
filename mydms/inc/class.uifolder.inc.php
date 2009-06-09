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

	class uifolder
	{
		var $public_functions = array
		(
			'copyFolder'		=> 'true',
			'getInitialFolderView'	=> 'true',
			'getSubFolder'		=> 'true',
		);

		function uifolder() {
			$this->charset  = $GLOBALS['egw']->translation->charset();
		}

		function copyFolder()
		{
			if(isset($_GET['folderid']) && isset($_POST['targetid']) && isset($_POST['copy']))
			{
				$bo	=& CreateObject('mydms.bofolder');

				$folderID = (int)$_GET['folderid'];
				$targetID = (int)$_POST['targetid'];
				$newFolderName = $_POST['newfoldername'];

				//var_export($_POST);
				//options
				$copySubFolder = ($_POST['copy_subfolder'] == 'on' ? true : false);
				$copyDocuments = ($_POST['copy_documents'] == 'on' ? true : false);

				if($newFolder = $bo->copyFolder($folderID, $targetID, $newFolderName, $copySubFolder, $copyDocuments))
				{
					$newFolderID = $newFolder->getID();
				}
				else
				{
					$newFolderID = 1;
				}
				$GLOBALS['egw']->redirect_link('/mydms/out/out.ViewFolder.php','folderid=' . $newFolderID);
			}
			elseif(isset($_GET['folderid']) && isset($_POST['cancel']))
			{
				$folderID = ((int)$_GET['folderid'] > 0 ? (int)$_GET['folderid'] : 1);
				// redirect
				$GLOBALS['egw']->redirect_link('/mydms/out/out.ViewFolder.php','folderid=' . $folderID);
			}

			$t 	=& CreateObject('phpgwapi.Template',EGW_APP_TPL);

			$folderID = (int)$_GET['folderid'];
			$formName = $_GET['form'];

			$folder = getFolder($folderID);
			$parent = $folder->getParent();

			$this->display_app_header();
			$this->translate($t);

			$t->set_file(array("copyFolder" => "copyFolder.tpl"));
			$t->set_block('copyFolder', 'main', 'main');

			$t->set_var('current_folder_id',$folderID);
			$t->set_var('newfoldername',$folder->getName());

			$t->set_var('foldername',$parent->getName());
			$t->set_var('folderid',$parent->getID());

			$linkData = array
			(
				'menuaction'    => 'mydms.uimydms.folderChooser',
			);
			$t->set_var('folderChooserURL', $GLOBALS['egw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'    => 'mydms.uimydms.folderChooser',
			);
			$t->set_var('folderChooserURL', $GLOBALS['egw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'    => 'mydms.uifolder.copyFolder',
				'folderid'	=> $folderID,
			);
			$t->set_var('form_action', $GLOBALS['egw']->link('/index.php',$linkData));

			$t->parse("out","main");

			print $t->get('out','main');
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
			$GLOBALS['egw']->js->validate_file('jscode','mydms','mydms');

			$GLOBALS['egw_info']['flags']['include_xajax'] = True;

			$GLOBALS['egw']->common->egw_header();

			echo parse_navbar();
		}

		function &generateXML($_folder, $_childID, $_targetID, &$_childContent) {

			$subFolders =& $_folder->getSubFolders();
			if (!is_array($subFolders)) $subFolders = array();

			foreach($subFolders as $subFolderObject) {
				$subFolderID	= $subFolderObject->getID();
				$subFolderName	= htmlspecialchars($subFolderObject->getName(), ENT_QUOTES, $this->charset);
				$hasSubfolder	= ($subFolderObject->getSubFolders() ? 1 : 0);
				$selectedNode	= ($subFolderID == $_targetID ? " select='1'" : '');
				$openNode	= ($subFolderID == $_childID ? " open='1'" : '');
				$retValue .="<item child='$hasSubfolder' id='$subFolderID' text='$subFolderName' im0='folderClosed.gif'$selectedNode$openNode>".
					($subFolderID == $_childID ? $_childContent : '').'</item>';
			}

			return $retValue;
		}

		function getInitialFolderView()
		{
			header("Content-type:text/xml");
			print("<?xml version=\"1.0\" encoding=\"$this->charset\"?>");

			if (isset($_GET["id"]))
				$folderID=$_GET["id"];
			else
				$folderID=1;

			if($folderObject = getFolder($folderID)) {

				$path = $folderObject->getPathNew();

				$xmlContent	= '';
				$clientID	= $folderID;

				// skip the last path part
				array_pop($path);

				while($subFolder = array_pop($path)) {
					$xmlContent	=& $this->generateXML($subFolder, $clientID, $folderID, $xmlContent);
					$clientID	= $subFolder->getID();
				}

			}
			print "<tree id='$clientID'>$xmlContent</tree>";

			$GLOBALS['egw']->common->egw_exit();
		}

		function getSubFolder()
		{
			header("Content-type:text/xml");
			print("<?xml version=\"1.0\" encoding=\"$this->charset\"?>");
			if (isset($_GET["id"]))
				$folderID=$_GET["id"];
			else
				$folderID=1;

			print("<tree id='".$folderID."'>");

			if($folderObject = getFolder($folderID)) {

				$subFolders = $folderObject->getSubFolders();


				foreach((array)$subFolders as $subFolderObject) {
					$subFolderID	= $subFolderObject->getID();
					$subFolderName	= htmlspecialchars($subFolderObject->getName(), ENT_QUOTES, $this->charset);
					$hasSubfolder	= ($subFolderObject->getSubFolders() ? 1 : 0);
					print("<item child='$hasSubfolder' id='$subFolderID' text='$subFolderName' im0='folderClosed.gif'></item>");
				}

			}

			print("</tree>");

			$GLOBALS['egw']->common->egw_exit();
		}

		function translate(&$template)
		{
			$template->set_var('lang_cancel',lang('cancel'));
			$template->set_var('lang_copy',lang('copy'));
			$template->set_var('lang_copy_documents',lang('copy documents'));
			$template->set_var('lang_copy_options',lang('copy options'));
			$template->set_var('lang_copy_subfolders',lang('copy subfolders'));
			$template->set_var('lang_folder',lang('folder'));
			$template->set_var('lang_name',lang('name'));
			$template->set_var('lang_select_folder',lang('select folder'));
			$template->set_var('lang_select_target_folder',lang('Select target folder'));
			$template->set_var('lang_target_folder',lang('target folder'));
		}
	}
?>