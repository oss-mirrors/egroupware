<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");

$query	= $_GET['query'];
$mode	= $_GET['mode'];

$query = sanitizeString($query);
$mode  = sanitizeString($mode);

$targetid = isset($targetid) ? $targetid : $settings->_rootFolderID;
$startFolder = getFolder($targetid);


function getTime() {
	if (function_exists('microtime')) {
		$tm = microtime();
		$tm = explode(' ', $tm);
		return (float) sprintf('%f', $tm[1] + $tm[0]);
	}
	return time();
}

function markQuery($str, $tag = "b") {
	GLOBAL $query;
	
	$querywords = split(" ", $query);
	
	foreach ($querywords as $queryword)
		$str = eregi_replace("($queryword)", "<" . $tag . ">\\1</" . $tag . ">", $str);
	
	return $str;
}

function matches($document) {
	GLOBAL $query, $mode, $searchin, $ownerid,  $creationdate, $lastupdate;
	GLOBAL $createstartmonth, $createstartday, $createstartyear;
	GLOBAL $createendmonth, $createendday, $createendyear;
	GLOBAL $updatestartmonth, $updatestartday, $updatestartyear;
	GLOBAL $updateendmonth, $updateendday, $updateendyear;
	
	if ($ownerid != -1) {
		if ($ownerid != $document->_ownerID)
			return false;
	}
	
	if ($creationdate == "true") {
		$startdate = mktime(0,0,0, $createstartmonth, $createstartday, $createstartyear);
		$stopdate  = mktime(23,59,59, $createendmonth, $createendday, $createendyear);
		$date      = $document->getDate();
		
		if (($date < $startdate ) || ($date > $stopdate))
			return false;
	}
	
	if ($lastupdate == "true") {
		$startdate = mktime(0,0,0, $updatestartmonth, $updatestartday, $updatestartyear);
		$stopdate  = mktime(23,59,59, $updateendmonth, $updateendday, $updateendyear);
		$latestContent = $document->getLatestContent();
		$date = $latestContent->getDate();
		
		if (($date < $startdate) || ($date > $stopdate))
			return false;
	}
	
	$str = "";
	if (in_array("keywords", $searchin))
		$str .= $document->getKeywords() . " ";
	if (in_array("name", $searchin))
		$str .= $document->getName() . " ";
	if (in_array("comment", $searchin))
		$str .= $document->getComment();
	
	$querywords = split(" ", strtolower($query));
	$keywords = split(" ", strtolower($str));
	
	$hitsCount = 0;
	foreach ($querywords as $queryword) {
		$found = false;
		
		foreach ($keywords as $keyword) {
			if ((substr_count($keyword, $queryword) > 0) || ($queryword == "%")) {
				$found = true;
				if ($mode == "or")
					return true;
			}
		}
		if ($mode == "and" && !$found)
			return false;
	}
	if ($mode == "and")
		return true;
	else
		return false;
}


function searchInFolder($folder) {
	GLOBAl $results, $user;
	
	$documents = $folder->getDocuments();
	$documents = filterAccess($documents, $user, M_READ);
	$subFolders = $folder->getSubFolders();
	$subFolders = filterAccess($subFolders, $user, M_READ);
	
	foreach ($documents as $document) {
		if (matches($document))
			array_push($results, $document);
	}
	foreach ($subFolders as $subFolder)
		searchInFolder($subFolder);
}

// ------------------------------------- Suche starten --------------------------------------------

$startTime = getTime();
$results = array();
searchInFolder($startFolder);
$searchTime = getTime() - $startTime;
$searchTime = round($searchTime, 2);

// ---------------------------------- Ausgabe der Ergebnisse --------------------------------------

printHTMLHead( getMLText("search") );
printTitleBar($startFolder);
printCenterStart();

printStartBox(getMLText("search_results"));

?>

<table width="100%">
	<tr>
		<td align="left" class="standardText">
			<?
				if (count($results) == 0)
					printMLText("search_no_results", array("query" => $query));
				else
					printMLText("search_report", array("query" => $query, "count" => count($results)));
			?>
		</td>
		<td align="right" class="standardText"><?printMLText("search_time", array("time" => $searchTime));?></td>
	</tr>
</table>

<?
if (count($results) == 0)
{
	printEndBox();
	printCenterEnd();
	printHTMLFoot();
	exit;
}

print "<p><ol>";

foreach ($results as $document) {
	print "<li class=\"standardText\"><b>" . getMLText("name") . ": " . "<a class=\"standardText\" href=\"../out/out.ViewDocument.php?documentid=".$document->getID()."\">" . markQuery($document->getName(), "i") . "</a></b><br>";
	$folder = $document->getFolder();
	$path = $folder->getPath();
	print getMLText("folder_path") . ": ";
	for ($i = 0; $i  < count($path); $i++)
	{
		print $path[$i]->getName();
		if ($i +1 < count($path))
			print " / ";
	}
	print "<br>";
	print markQuery($document->getComment());
	print "<br>&nbsp;</li>";
}

print "</ol>";

printEndBox();
printCenterEnd();
printHTMLFoot();
?>