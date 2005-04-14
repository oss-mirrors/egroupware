<?


function getReadableDate($timestamp) {
	return date("d.m.Y", $timestamp);
}

function getLongReadableDate($timestamp) {
	return date("d.m.Y - H:i:s", $timestamp);
}

function sanitizeString($string) {
	$string = str_replace("'",  "", $string);
	$string = str_replace("--", "", $string);
	$string = str_replace("<",  "", $string);
	$string = str_replace(">",  "", $string);
	$string = str_replace("/*", "", $string);
	$string = str_replace("*/", "", $string);
	$string = str_replace("\"", "", $string);
	
	return $string;
}

?>