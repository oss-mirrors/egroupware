<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");

if (!$user->isAdmin())
	die ("access denied");

printHTMLHead( getMLText("admin_tools") );
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

printStartBox(getMLText("admin_tools"));
?>
	<div class="standardText">
		<a href="out.Statistic.php"><?=getMLText("folders_and_documents_statistic")?></a><p>
		<!--a href="out.UsrMgr.php"><?=getMLText("user_management")?></a><p>
		<a href="out.GroupMgr.php"><?=getMLText("group_management")?></a><p-->
		<a href="out.DefaultKeywords.php"><?=getMLText("global_default_keywords")?></a>
	</div>
<?
printEndBox();


printCenterEnd();
printHTMLFoot();
?>