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

	include("inc/config.php");
	include("inc/std_func.php");
	$HeadContents = getHeadContents(1);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//NL">
<html>
<head>
<title><?php echo $HeadContents['title'] ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="Copyright" content="<?php echo $HeadContents['meta_copyright'] ?>">
		<meta name="Keywords" lang="nl" content="<?php echo $HeadContents['meta_keywords'] ?>">
			<meta name="Description" lang="nl" content="<?php echo $HeadContents['meta_description'] ?>">
				<meta name="Abstract" lang="nl" content="<?php echo $HeadContents['meta_description'] ?>">
					<meta name="Robots" content="<?php echo $HeadContents['meta_robots'] ?>">
						<meta name="Author" content="<?php echo $HeadContents['meta_author'] ?>">
							<meta name="Url" content="<?php echo $HeadContents['meta_url'] ?>">
								</head>
								<body>
								<!-- start introduction text -->
								<?php

									$text=getRecordField('dir_text','text','id=1');
									if($text) echo $text;
								?>
								<!-- end introduction text -->
								<p>&nbsp;</p>
								<!-- start categories and links -->
								<?php
									$categories=getRecordField('dir_categories','name','');
									if(is_array($categories) && count($categories)>0)
									{
										foreach($categories as $categorie)
										{
											$links=getLinksFromCategory($categorie);
											if(count($links)>0)
											{
												echo
												'<table width="100%" border="0" cellspacing="0" cellpadding="0">'.
												'<tr>'.
												'<td><strong>'.
												$categorie.
												'</strong></td>'.
												'</tr>'.
												'<tr>'.
												'<td><ul>';

												foreach($links as $link)
												{
													echo '<li><a href="'.$link['url'].'" title="'.$link['description'].'">'.$link['name'].'</a></li>';
												}

												echo '</ul></td>'.
												'</tr>'.
												'</table>'.
												'<br>';
											}

										}

									}


								?>
								<!-- end categories & links -->
								</body>
								</html>
