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

	$GLOBALS['phpgw_info']['flags'] = array(
        'currentapp' => 'mydms',
        'noheader'   => True,
        'nonavbar'   => True
);
	include_once('../../header.inc.php');

	$GLOBALS['phpgw']->common->phpgw_header();
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

	$GLOBALS['phpgw']->common->phpgw_footer();	
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
	
	//$title=getMLText("logged_in_as")." ".$user->getFullName();
	//$menu = buildMenu( $options, "titlebar" );

	$title=getMLText("GTZ E-Office Document Management System");

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
	
	$folder = $path[$level];
	$subFolders = $folder->getSubFolders();
	$subFolders = filterAccess($subFolders, $user, M_READ);
	if ($level == count($path)-1)
	{
		$documents = $folder->getDocuments();
		$documents = filterAccess($documents, $user, M_READ);
	}
	else
		$documents = array();
	
	if ($level+1 < count($path))
		$nextFolderID = $path[$level+1]->getID();
	else
		$nextFolderID = -1;
	
	print "<table cellpadding=0 cellspacing=0>\n";
	print "  <tr>\n";
	print "    <td valign=\"top\"";
	if (count($subFolders) > 0 || count ($documents) > 0)
		print " background=\"".getImgPath("down.gif")."\"";
	print "><img src=\"";
		if ($level == 0) printImgPath("to_down.gif");
		else if ((count($subFolders) > 0) || (count($documents) > 0)) printImgPath("right_in_to_down.gif");
		else printImgPath("right_in.gif");
	print "\" border=0></td>\n";
	if (($folder->getID() == $activeObj->getID()) && $isFolder)
		print "    <td class=\"foldertree_active\"><a href=\"out.ViewFolder.php?folderid=".$folder->getID()."\" class=\"foldertree_active\"><img src=\"".getImgPath("folder_opened.gif")."\" width=18 height=18 border=0>".$folder->getName()."</a></td>\n";
	else
		print "    <td class=\"foldertree_inpath\"><a href=\"out.ViewFolder.php?folderid=".$folder->getID()."\" class=\"foldertree_inpath\"><img src=\"".getImgPath("folder_opened.gif")."\" width=18 height=18 border=0>".$folder->getName()."</a></td>\n";
	print "  </tr>\n";
	
	for ($i = 0; $i < count($subFolders); $i++)
	{
		print "<tr>";
		if (($i +1 < count($subFolders)) || (count($documents) != 0))
			print "<td background=\"".getImgPath("down.gif")."\" valign=\"top\"><img src=\"".getImgPath("right.gif")."\" border=0></td>";
		else
			print "<td valign=\"top\"><img src=\"".getImgPath("right_last.gif")."\" border=0></td>";
		print "<td>";
		if ($subFolders[$i]->getID() == $nextFolderID)
			printFolderTree($path, $level+1, $activeObj, $isFolder);
		else
			print "<table cellpadding=0 cellspacing=0><tr><td valign=\"top\"><img src=\"".getImgPath("right_in.gif")."\"></td><td class=\"foldertree\" valign=\"top\"><a href=\"out.ViewFolder.php?folderid=".$subFolders[$i]->getID()."\" class=\"foldertree\"><img src=\"".getImgPath("folder_closed.gif")."\" width=18 height=18 border=0>".$subFolders[$i]->getName()."</a></td></tr></table>";
		print "</td>";
		print "</tr>";
	}
	for ($i = 0; $i < count($documents); $i++)
	{
		print "<tr>";
		if ($i +1 < count($documents))
			print "<td background=\"".getImgPath("down.gif")."\" valign=\"top\"><img src=\"".getImgPath("right.gif")."\" border=0></td>";
		else
			print "<td valign=\"top\"><img src=\"".getImgPath("right_last.gif")."\" border=0></td>";
		print "<td>";
		if (!$isFolder  && $documents[$i]->getID() == $activeObj->getID())
			print "<table cellpadding=0 cellspacing=0><tr><td valign=\"top\"><img src=\"".getImgPath("right_in.gif")."\"></td><td class=\"foldertree_active\"><a href=\"out.ViewDocument.php?documentid=".$documents[$i]->getID()."\" class=\"foldertree_active\"><img src=\"".getImgPath("file.gif")."\" width=18 height=18 border=0>".$documents[$i]->getName()."</a></td></tr></table>";
		else
			print "<table cellpadding=0 cellspacing=0><tr><td valign=\"top\"><img src=\"".getImgPath("right_in.gif")."\"></td><td class=\"foldertree\"><a href=\"out.ViewDocument.php?documentid=".$documents[$i]->getID()."\" class=\"foldertree\"><img src=\"".getImgPath("file.gif")."\" width=18 height=18 border=0>".$documents[$i]->getName()."</a></td></tr></table>";
		print "</td>";
		print "</tr>";
	}
	
	print "</table>\n";
}


function printFolderPageStart($folder)
{
	global $theme;
	
	$title1 = getMLText("foldertree");
	$title2 = "<img src='".getImgPath("folder_opened.gif")."' ".
	               "width='18' height='18' alt='' border='0' align='absmiddle'> ".
	  	  getMLText("selected_folder") . ": " . $folder->getName();
	$txtpath = getMLText("folder_path") . ": ";
	$path = $folder->getPath();
	for ($i = 0; $i < count($path); $i++)
	{
		$txtpath .= "<a class=\"path\" href=\"out.ViewFolder.php?folderid=".$path[$i]->getID()."\">".
		            $path[$i]->getName()."</a>";
		if ($i +1 < count($path)) $txtpath .= " / ";
	}
	
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
	
	//$menu=buildMenu( $options, "editfolder" );
	$menu=buildMenu( $options , "editfolder_text");	
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
	
	$folder = $document->getFolder();
	$title1 = getMLText("foldertree");
	$title2 = "<img src='".getImgPath("file.gif")."' ".
	               "width='18' height='18' alt='' border='0' align='absmiddle'> ".
	  	  getMLText("selected_document") . ": " . $document->getName();
	$txtpath = getMLText("folder_path") . ": ";
	$path = $folder->getPath();
	for ($i = 0; $i < count($path); $i++)
	{
		$txtpath .= "<a class=\"path\" href=\"out.ViewFolder.php?folderid=".$path[$i]->getID()."\">".
		            $path[$i]->getName()."</a>";
		if ($i +1 < count($path)) $txtpath .= " / ";
	}
	
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
	
	//$menu=buildMenu( $options, "editfolder" );
	$menu=buildMenu( $options , "editfolder_text");

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
	?>
	<script language="JavaScript">
	var openDlg;
	function chooseDoc() {
		openDlg = open("out.FolderChooser.php?form=<?=$formName?>&mode=<?=$accessMode?>&exclude=<?=$exclude?>&folderid=<?=$settings->_rootFolderID?>", "openDlg", "width=300,height=450,scrollbars=yes,resizable=yes,status=yes");
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
		//$menu.="<nobr><a href='$url' class='$class'>$desc</a></nobr>\n";
		$menu.="<nobr><a href='$url'><span class='$class'>$desc</span></a></nobr>\n";
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
