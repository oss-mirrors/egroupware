<?php
/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	phpGroupWare - http://www.phpgroupware.org
	
	This file is part of JiNN

	JiNN is free software; you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation; either version 2 of the License, or (at your 
	option) any later version.

	JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License 
	along with JiNN; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
 */
	 function getRelaties()
	{
		if ($conn=openDbConn())
		{
			$SQL= "SELECT * FROM dir_links ORDER BY name";

			if ($result = mysql_query ($SQL,$conn))
			{


				while($record = mysql_fetch_object($result))
				{
					$relaties[]= array
					(
						'url'=>$record->url,
						'name'=>$record->name,
						'description'=>$record->description
					);
				}
			}

			mysql_close($conn);
		}
		//var_dump($relaties);
		return $relaties;


	}


	function getLinksFromCatagory($catagory_name)
	{
	        $cid=getRecordField('dir_catagories','id',"name='$catagory_name'");

		$conn=openDbConn();

		if(!$cid || !$conn) return;

		$SQL="SELECT * FROM dir_links
		INNER JOIN dir_links_catagories
		ON dir_links_catagories.link_id=dir_links.id
		WHERE dir_links_catagories.catagory_id=$cid";

		//die($SQL);
		$queryresult = mysql_query($SQL, $conn);

		if(!$queryresult) return;

		while ($record = mysql_fetch_object ($queryresult))
		{
		        $links[]=array(
			        'id'=>$record->id,
				'url'=>$record->url,
				'name'=>$record->name,
				'description'=>$record->description
			);
		}

		return $links;
	}



	function getHeadContents($pagehead_id)
	{
		if ($conn=openDbConn())
		{
			$SQL= "SELECT * FROM pageheads WHERE id=$pagehead_id LIMIT 1";

			if ($result = mysql_query ($SQL,$conn))
			{
				$record = mysql_fetch_object($result);
				$pagehead= array
				(
					'title'=>$record->title,
					'meta_description'=>$record->meta_description,
					'meta_keywords'=>$record->meta_keywords,
					'meta_robots'=>$record->meta_robots,
					'meta_url'=>$record->meta_url,
					'meta_author'=>$record->meta_author,
					'meta_copyright'=>$record->meta_copyright
				);
			}

			mysql_close($conn);
		}
		//die (var_dump ($pagehead));
		return $pagehead;
	}



	function makeNewsDetail($id) {

		global $number_news_items;
		$conn=openDbConn();

		if ($id) $WHERE="WHERE id='$id'";


		$query_results = mysql_query("SELECT * FROM nieuws $WHERE ORDER BY date desc LIMIT 0, 5 ", $conn);
		$num_rows = mysql_num_rows($query_results);

		if($num_rows>0)
		{
			if($num_rows>$number_news_items) $num_rows=$number_news_items;
			$i=0;
			while ($num_rows>$i)
			{
				$rec = mysql_fetch_object($query_results);

				$id = trim($rec->id);
				$date = format_date($rec->date);

				$author = trim($rec->author);
				$subject = trim($rec->headline);
				$body = trim($rec->body);
				$picture = $rec->image_path;
				$output = $output . "

				<br>
				<span class=\"textsmall\"><b>$subject</b></span>
				<br>
				<span class=\"textsmall\">door $author op $date</span>
				<P>$body
				<br>
				<hr>
				";

				$i++ ;
			}
		}
		if($num_rows>=5)
		{
			$output.='	<BR><a href="oudernieuws.php">ouder nieuws</a>';
		}
		mysql_close($conn);
		return $output;
	}

	function archivedNews()
	{
		global $number_news_items;

		$conn=openDbConn();

		if ($id) $WHERE="WHERE id='$id'";


		$query_results = mysql_query("SELECT * FROM nieuws $WHERE ORDER BY date desc LIMIT 5,100", $conn);
		$num_rows = mysql_num_rows($query_results);

		if($num_rows>0)
		{
			if($num_rows>$number_news_items) $num_rows=$number_news_items;
			$i=0;
			while ($num_rows>$i)
			{
				$rec = mysql_fetch_object($query_results);

				$id = trim($rec->id);
				$date = format_date($rec->date);

				$author = trim($rec->author);
				$subject = trim($rec->headline);
				$body = trim($rec->body);
				$picture = $rec->image_path;
				$output = $output . "

				<br>
				<span class=\"textsmall\"><b>$subject</b></span>
				<br>
				<span class=\"textsmall\">door $author op $date</span>
				<P>$body
				<br>
				<hr>
				";

				$i++ ;
			}
		}
		mysql_close($conn);
		return $output;

	}


	function format_date($input)
	{
		// Deze functie converteert bv. 200124061216  naar:  24-06-2001 12:16
		$jaar = substr($input,0,4);
		$maand = substr($input,4,2);
		$dag = substr($input,6,2);
		$uren = substr($input,8,2);
		$minuten = substr($input,10,2);
		return("$dag-$maand-$jaar $uren:$minuten");
	}


	function getRecordField($table,$field,$where){

		if( !$table || !$field ) return;
		$conn=openDbConn();

		if(!$conn) return;

		if ($where) $SQL_WHERE="WHERE $where";

		$SQL="SELECT $field FROM $table $SQL_WHERE";
		//die($SQL);
		$queryresult = mysql_query($SQL, $conn);

		if(!$queryresult) return;

		if(mysql_num_rows($queryresult)==1)
		{
		        $record = mysql_fetch_row ($queryresult);
		        return $record[0];
		}
		else
		{
                        while ($record = mysql_fetch_row ($queryresult))
			{
			        $return_array[]=$record[0];
			}
			return $return_array;
		}
	}

	function openDbConn() {

		global $mysqluser;
		global $mysqlpwd;
		global $mysqldb;

		$conn=mysql_pconnect('',$mysqluser,$mysqlpwd);
		mysql_select_db($mysqldb, $conn);

		if (!$conn) {
			$conn=-1;
		}

		return $conn;

	}
?>
