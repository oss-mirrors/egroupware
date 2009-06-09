<?php

define("M_NONE", 1);		//Keine Rechte
define("M_READ", 2);		//Lese-Recht
define("M_READWRITE", 3);	//Schreib-Lese-Recht
define("M_ALL", 4);		//Unbeschr�nkte Rechte

define("T_FOLDER", 1);		//TargetType = Folder
define("T_DOCUMENT", 2);	//    "      = Document

//Sortiert aus dem Array $objArr (entweder Folder- oder Document-Objeckte) alle Elemente heraus, auf
//die der Benutzer $user nicht mindestens den Zugriff $minMode hat und gib die restlichen Elemente zur�ck
function &filterAccess(&$objArr, $user, $minMode)
{
	$newArr = array();
	foreach ($objArr as $obj)
	{
		if ($obj->getAccessMode($user) >= $minMode)
			array_push($newArr, $obj);
	}
	return $newArr;
}

//Sortiert aus dem Benutzer-Array $users alle Benutzer heraus, die auf den Ordner oder das Dokument $obj
//nicht mindestens den Zugriff $minMode haben und gibt die restlichen Benutzer zur�ck
function filterUsersByAccess($obj, $users, $minMode)
{
	$newArr = array();
	foreach ($users as $currUser)
	{
		if ($obj->getAccessMode($currUser) >= $minMode)
			array_push($newArr, $currUser);
	}
	return $newArr;
}
?>