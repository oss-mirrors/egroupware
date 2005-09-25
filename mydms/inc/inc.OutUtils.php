<?

function getThemes()
{
	GLOBAL $settings;
	
	$themes = array();
	
	$path = $settings->_rootDir . "themes/";
	$handle = opendir($path);
	
	while ($entry = readdir($handle) )
	{
		if ($entry == ".." || $entry == ".")
			continue;
		else if (is_dir($path . $entry))
			array_push($themes, $entry);
	}
	closedir($handle);
	
	return $themes;
}

$theme = $settings->_theme;

function printHTMLHead($title)
{
	global $theme;

	$GLOBALS['egw_info']['flags'] = array(
        'currentapp' => 'mydms',
        'noheader'   => True,
        'nonavbar'   => True
);
	include_once('../../header.inc.php');
	
	if(!@is_object($GLOBALS['egw']->js))
	{
		$GLOBALS['egw']->js =& CreateObject('phpgwapi.javascript');
	}
	$GLOBALS['egw']->js->validate_file('dhtmlxtree','js/dhtmlXCommon');
	$GLOBALS['egw']->js->validate_file('dhtmlxtree','js/dhtmlXTree');

	$GLOBALS['egw']->js->validate_file('jscode','mydms','mydms');
                        
	$GLOBALS['egw']->common->phpgw_header();
	echo parse_navbar();

	if ( is_file("../themes/$theme/styles.css") )
		$stylesheet="../themes/$theme/styles.css";
	else
		$stylesheet="../themes/default/styles.css";
	
	if ( is_file("../themes/$theme/HTMLHead.html" ) )
		include("../themes/$theme/HTMLHead.html");
	else
		include("../themes/default/HTMLHead.html");
}

function printHTMLFoot()
{
	global $theme;

	$GLOBALS['egw']->common->phpgw_footer();	
	if ( is_file("../themes/$theme/HTMLFoot.html" ) )
	{
		include("../themes/$theme/HTMLFoot.html");
	} else {
		include("../themes/default/HTMLFoot.html");
	}
}

function printStartBox($header)
{
	global $theme;
	
	if ( is_file("../themes/$theme/StartBox.html" ) )
	{
		include("../themes/$theme/StartBox.html");
	} else {
		include("../themes/default/StartBox.html");
	}
}

function printNextBox($header)
{
	global $theme;
	
	if ( is_file("../themes/$theme/NextBox.html" ) )
	{
		include("../themes/$theme/NextBox.html");
	} else {
		include("../themes/default/NextBox.html");
	}
}

function printEndBox()
{
	global $theme;
	
	if ( is_file("../themes/$theme/EndBox.html" ) )
	{
		include("../themes/$theme/EndBox.html");
	} else {
		include("../themes/default/EndBox.html");
	}
}

function printCenterStart()
{
	print "<table width=\"100%\" height=\"60%\"><tr><td align=\"center\" valign=\"middle\">";
}

function printCenterEnd()
{
	print "</td></tr></table>";
}

function printGoBack()
{
	print "<p><a href=\"javascript:history.back()\">".getMLText("back")."</a>";
}

function printGoto($targets)
{
	print "<p>".getMLText("goto").": ";
	for ($i = 0; $i < count($targets); $i++)
	{
		print "<a href=\"".$targets[$i][1]."\">".$targets[$i][0]."</a>";
		if ($i+1 < count($targets))
			print " | ";
	}
}


function printTitleBar($folder)
{
	return; 
	
	global $user, $settings, $theme;
	
	$options=array();
	$options[getMLText("content")]=$settings->_httpRoot . "out/out.ViewFolder.php?folderid=" . $folder->getID();
	$options[getMLText("search")]=$settings->_httpRoot . "out/out.SearchForm.php?folderid=" . $folder->getID();
	
	if ($user->getID() != $settings->_guestID)
		//$options[getMLText("my_account")]=$settings->_httpRoot . "out/out.EditUserData.php";
	
	if ($user->isAdmin())
	{
		$options[getMLText("admin_tools")] = $settings->_httpRoot . "out/out.AdminTools.php";
	//	$options[getMLText("user_management")]=$settings->_httpRoot . "out/out.UsrMgr.php";
	//	$options[getMLText("group_management")]=$settings->_httpRoot . "out/out.GroupMgr.php";
	} else
		$options[getMLText("user_list")]=$settings->_httpRoot . "out/out.UserList.php";
	
	//$options[getMLText("logout")]=$settings->_httpRoot . "op/op.Logout.php";
	
	$title=getMLText("logged_in_as")." ".$user->getFullName();
	$menu = buildMenu( $options, "titlebar" );
	
	if ( is_file("../themes/$theme/TitleBar.html" ) )
	{
		include("../themes/$theme/TitleBar.html");
	} else {
		include("../themes/default/TitleBar.html");
	}
}


function printFolderTree($path, $level = 0, $activeObj, $isFolder)
{
	GLOBAL $user;

	#$folder = $path[$level];
	#$subFolders = $folder->getSubFolders();
	#$subFolders = filterAccess($subFolders, $user, M_READ);

	$allSubFolders = array();
	foreach($path as $folderObject)
	{
		$allSubFolders['_id'.$folderObject->getID()] = $folderObject;
		$subFoldersL1 = $folderObject->getSubFolders();
		foreach((array)$subFoldersL1 as $subFolderL1Object)
		{
			$allSubFolders['_id'.$subFolderL1Object->getID()] = $subFolderL1Object;
			$subFoldersL2 = $subFolderL1Object->getSubFolders();
			foreach((array)$subFoldersL2 as $subFolderL2Object)
			{
				$allSubFolders['_id'.$subFolderL2Object->getID()] = $subFolderL2Object;
			}
		}
	}

	$uimydms =& CreateObject('mydms.uimydms');
	print $uimydms->folderChooser($allSubFolders, $activeObj);
	
	return; 
}


function printFolderPageStart($folder)
{
	global $theme;
	
	$uimydms =& CreateObject('mydms.uimydms');
	
	$title1 = getMLText("foldertree");
	$title2 = "<img src='".getImgPath("folder_opened.gif")."' ".
	               "width='18' height='18' alt='' border='0' align='absmiddle'> ".
	  	  getMLText("selected_folder") . ": " . $folder->getName();
	$path = $folder->getPathNew();

	$txtpath = '';
	foreach($path as $folderObject)
	{
		if(!empty($txtpath)) $txtpath .= " / ";
		$txtpath .= "<a class=\"path\" href=\"out.ViewFolder.php?folderid=".$folderObject->getID()."\">".
		            $folderObject->getName()."</a>";
	}
	
	$txtpath = getMLText("folder_path") . ": " . $txtpath;
	
	if ( is_file("../themes/$theme/FolderPageStart.html" ) )
	{
		include("../themes/$theme/FolderPageStart.html");
	} else {
		include("../themes/default/FolderPageStart.html");
	}
	
}

function printFolderPageEnd($folder)
{
	GLOBAL $user, $theme;

	$title = getMLText("edit_folder");
	$options=array();
	$accessMode = $folder->getAccessMode($user);
	if ($accessMode >= M_READWRITE)
	{
		$options[getMLText("add_subfolder")] = "out.AddSubFolder.php?folderid=" . $folder->getID();
		$options[getMLText("add_document")] = "out.AddDocument.php?folderid=" . $folder->getID();
		$options[getMLText("edit_folder_props")] = "out.EditFolder.php?folderid=" . $folder->getID();
		$options[getMLText("edit_folder_notify")] = "out.FolderNotify.php?folderid=" . $folder->getID();
		$options[getMLText("move_folder")] = "out.MoveFolder.php?folderid=" . $folder->getID();
	}
	
	if ($accessMode == M_ALL)
	{
		$options[getMLText("rm_folder")] = "out.RemoveFolder.php?folderid=" . $folder->getID();
		$options[getMLText("edit_folder_access")] = "out.FolderAccess.php?folderid=" . $folder->getID();
	}
	
	$menu=buildMenu( $options, "editfolder" );
	
	if ( is_file("../themes/$theme/FolderPageEnd.html" ) )
	{
		include("../themes/$theme/FolderPageEnd.html");
	} else {
		include("../themes/default/FolderPageEnd.html");
	}
}

function printPageHeader($header)
{
	print "<p><div class=\"pageHeader\">".$header."</div></p>\n";
}

function printDocumentPageStart($document)
{
	global $theme;

	$uimydms =& CreateObject('mydms.uimydms');
	
	$folder = $document->getFolder();
	$title1 = getMLText("foldertree");
	$title2 = "<img src='".getImgPath("file.gif")."' ".
	               "width='18' height='18' alt='' border='0' align='absmiddle'> ".
	  	  getMLText("selected_document") . ": " . $document->getName();

	$path = $folder->getPathNew();

	$txtpath = '';
	foreach($path as $folderObject)
	{
		if(!empty($txtpath)) $txtpath .= " / ";
		$txtpath .= "<a class=\"path\" href=\"out.ViewFolder.php?folderid=".$folderObject->getID()."\">".
		            $folderObject->getName()."</a>";
	}
	
	$txtpath = getMLText("folder_path") . ": " . $txtpath;

	if ( is_file("../themes/$theme/DocumentPageStart.html" ) )
	{
		include("../themes/$theme/DocumentPageStart.html");
	} else {
		include("../themes/default/DocumentPageStart.html");
	}
}

function printDocumentPageEnd($document)
{
	GLOBAL $user, $theme;
	
	$docid=".php?documentid=" . $document->getID();
	$title = getMLText("edit_document");
	$options=array();
	$accessMode = $document->getAccessMode($user);
	if ($accessMode >= M_READWRITE)
	{
	  if (!$document->isLocked())
	  {
	    $options[getMLText("update_document")]	= "out.UpdateDocument" . $docid;
	    $options[getMLText("lock_document")]	= "../op/op.LockDocument" . $docid;
	    $options[getMLText("edit_document_props")]	= "out.EditDocument" . $docid;
	    $options[getMLText("expires")]		= "out.SetExpires" . $docid;
	    $options[getMLText("edit_document_notify")]	= "out.DocumentNotify" . $docid;
	    $options[getMLText("move_document")]	= "out.MoveDocument" . $docid;
 	  } else {
	    $lockingUser = $document->getLockingUser();
	    if (($lockingUser->getID() == $user->getID()) || ($document->getAccessMode($user) == M_ALL))
	    {
	      $options[getMLText("update_document")]	= "out.UpdateDocument" . $docid;
	      $options[getMLText("unlock_document")]	= "../op/op.UnlockDocument" . $docid;
	      $options[getMLText("edit_document_props")]= "out.EditDocument" . $docid;
	      $options[getMLText("expires")]		= "out.SetExpires" . $docid;
	      $options[getMLText("edit_document_notify")]= "out.DocumentNotify" . $docid;
	      $options[getMLText("move_document")]	= "out.MoveDocument" . $docid;
	    }
	  }
	}
	
	if ($accessMode == M_ALL)
	{
	  $options[getMLText("rm_document")]		= "out.RemoveDocument" . $docid;
	  $options[getMLText("edit_document_access")]	= "out.DocumentAccess" . $docid;
	}
	
	$menu=buildMenu( $options, "editfolder" );
	
	if ( is_file("../themes/$theme/DocumentPageEnd.html" ) )
	{
		include("../themes/$theme/DocumentPageEnd.html");
	} else {
		include("../themes/default/DocumentPageEnd.html");
	}
}

function printDateChooser($defDate = -1, $varName)
{
	if ($defDate == -1)
		$defDate = mktime();
	$day   = date("d", $defDate);
	$month = date("m", $defDate);
	$year  = date("Y", $defDate);
	
	print "<select name=\"" . $varName . "day\">\n";
	for ($i = 1; $i <= 31; $i++)
	{
		print "<option value=\"" . $i . "\"";
		if (intval($day) == $i)
			print " selected";
		print ">" . $i . "</option>\n";
	}
	print "</select>.\n";
	print "<select name=\"" . $varName . "month\">\n";
	for ($i = 1; $i <= 12; $i++)
	{
		print "<option value=\"" . $i . "\"";
		if (intval($month) == $i)
			print " selected";
		print ">" . $i . "</option>\n";
	}
	print "</select>.\n";
	print "<select name=\"" . $varName . "year\">\n";
	for ($i = 2004; $i <= 2010; $i++)
	{
		print "<option value=\"" . $i . "\"";
		if (intval($year) == $i)
			print " selected";
		print ">" . $i . "</option>\n";
	}
	print "</select>";
}


function printSequenceChooser($objArr, $keepID = -1)
{
	if (count($objArr) > 0)
	{
		$max = $objArr[count($objArr)-1]->getSequence() + 1;
		$min = $objArr[0]->getSequence() - 1;
	}
	else
		$max = 1.0;
	
	print "<select name=\"sequence\">\n";
	if ($keepID != -1)
		print "  <option value=\"keep\">" . getMLText("seq_keep");
	print "  <option value=\"".$max."\">" . getMLText("seq_end");
	if (count($objArr) > 0)
		print "  <option value=\"".$min."\">" . getMLText("seq_start");
	
	for ($i = 0; $i < count($objArr) - 1; $i++)
	{
		if (($objArr[$i]->getID() == $keepID) || (($i + 1 < count($objArr)) && ($objArr[$i+1]->getID() == $keepID)))
			continue;
		$index = ($objArr[$i]->getSequence() + $objArr[$i+1]->getSequence()) / 2;
		print "  <option value=\"".$index."\">" . getMLText("seq_after", array("prevname" => $objArr[$i]->getName() ) );
	}
	
	print "</select>";
}

function printDocumentChooser($formName) {
	GLOBAL $settings;
	?>
	<script language="JavaScript">
	var openDlg;
	function chooseDoc() {
		openDlg = open("out.DocumentChooser.php?folderid=<?=$settings->_rootFolderID?>&form=<?=urlencode($formName)?>", "openDlg", "width=300,height=450,scrollbars=yes,resizable=yes,status=yes");
	}
	</script>
	<?
	print "<input type=\"Hidden\" name=\"docid\">";
	print "<input disabled name=\"docname\">";
	print "&nbsp;&nbsp;<input type=\"Button\" value=\"Open...\" onclick=\"chooseDoc();\">";
}


function printFolderChooser($formName, $accessMode, $exclude = -1, $default = false) {
	GLOBAL $settings;
	
	$linkData = array
	(
		'menuaction'	=> 'mydms.uimydms.folderChooser',
		'form'		=> $formName,
		'mode'		=> $accessMode,
		'exlcude'	=> $exclude,
		'folderid'	=> $settings->_rootFolderID
	);
	$link = $GLOBALS['egw']->link('/index.php',$linkData);
	
	?>
	<script language="JavaScript">
	var openDlg;
	function chooseDoc() {
		//openDlg = open("out.FolderChooser.php?form=<?=$formName?>&mode=<?=$accessMode?>&exclude=<?=$exclude?>&folderid=<?=$settings->_rootFolderID?>", "openDlg", "width=300,height=450,scrollbars=yes,resizable=yes,status=yes");
		openDlg = open("<?=$link?>", "openDlg", "width=300,height=450,scrollbars=yes,resizable=yes,status=yes");
	}
	</script>
	<?
	print "<input type=\"Hidden\" name=\"targetid\" value=\"". (($default) ? $default->getID() : "") ."\">";
	print "<input disabled name=\"targetname\" value=\"". (($default) ? $default->getName() : "") ."\">";
	print "&nbsp;&nbsp;<input type=\"Button\" value=\"Open...\" onclick=\"chooseDoc();\">";
}

/* ---------------------------------------- ICONS --------------------------------------------- */

$icons = array();
$icons["txt"]  = "txt.png";
$icons["doc"]  = "word.png";
$icons["rtf"]  = "document.png";
$icons["xls"]  = "excel.png";
$icons["ppt"]  = "powerpoint.png";
$icons["exe"]  = "binary.png";
$icons["html"] = "html.png";
$icons["htm"]  = "html.png";
$icons["gif"]  = "image.png";
$icons["jpg"]  = "image.png";
$icons["bmp"]  = "image.png";
$icons["png"]  = "image.png";
$icons["log"]  = "log.png";
$icons["midi"] = "midi.png";
$icons["pdf"]  = "pdf.png";
$icons["wav"]  = "sound.png";
$icons["mp3"]  = "sound.png";
$icons["c"]    = "source_c.png";
$icons["cpp"]  = "source_cpp.png";
$icons["h"]    = "source_h.png";
$icons["java"] = "source_java.png";
$icons["py"]   = "source_py.png";
$icons["tar"]  = "tar.png";
$icons["gz"]   = "gz.png";
$icons["zip"]  = "gz.png";
$icons["mpg"]  = "video.png";
$icons["avi"]  = "video.png";
$icons["tex"]  = "tex.png";
$icons["default"] = "default.png";

function getImgPath($img) {
  global $theme;
  
  if ( is_file("../themes/$theme/images/$img") )
  {
    return "../themes/$theme/images/$img";
  }
  return "../out/images/$img";
}

function printImgPath($img)
{
  print getImgPath($img);
}

function buildMenu( $options , $class="titlebar" )
{
    $sep = "";
	$menu = "";
	reset($options);
	while ( list($desc,$url) = each($options) )
	{
		$menu.=$sep;
		$menu.="<nobr><a href='$url' class='$class'>$desc</a></nobr>\n";
		$sep=" | \n";
	}
	return $menu;
}

function getMimeIcon($fileType)
{
	GLOBAL $icons;
	
	$ext = substr($fileType, 1);
	if (isset($icons[$ext]))
		return $icons[$ext];
	else
		return $icons["default"];
}
?>
