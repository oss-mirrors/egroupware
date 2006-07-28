<?php
	/**************************************************************************\
	* eGroupWare - Setup                                                       *
	* http://www.eGroupWare.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	/* $Id$ */

	$time = time();
	$oProc->query("DELETE FROM egw_wiki_pages");
	foreach(array(
		'RecentChanges' => '[[! *]]',
		'eGroupWare' => "Welcome to '''Wiki''' - the eGroupWare Version of '''WikkiTikkiTavi'''. Wikis are a revolutionary new form of collaboration and online community.

'''eGroupWare''' is the groupware suite you are useing right now. For further information see http://www.eGroupWare.org",
		'WikkiTikkiTavi' => "= WikkiTikkiTavi =

WikkiTikkiTavi is the original version of this documentation system. Their [http://tavi.sourceforge.net documentation] is usable for the eGroupWare '''Wiki''' too.

'''Learn about Wiki formatting:'''
----
SmashWordsTogether to create a page link.  Click on the ? to edit the new page.

You can also create ((free links)) that aren't WordsSmashedTogether.  Type them like this: {{```((free links))```}}.  
----
{{```''italic text''```}} becomes ''italic text''
----
{{```'''bold text'''```}} becomes '''bold text'''
----
{{```{{monospace text}}```}} becomes {{monospace text}}
----
{{```----```}} becomes a horizontal rule:
----
Create a remote link simply by typing its URL: http://www.egroupware.org

If you like, enclose it in brackets to create a numbered reference and avoid cluttering the page; {{```[http://www.php.net]```}} becomes [http://www.php.net].

Or you can have a description instead of a numbered reference; {{```[http://www.php.net/manual/en/ PHP Manual]```}} becomes [http://www.php.net/manual/en/ PHP Manual]
----
You can put a picture in a page by typing the URL to the picture (it must end in gif, jpg, or png).  For example, {{```http://www.egroupware.org/egroupware/phpgwapi/templates/idots/images/logo.png```}} becomes
http://www.egroupware.org/egroupware/phpgwapi/templates/idots/images/logo.png
----
There are 2 possibilities for '''code formatting''':
{{'''{{\$is_admin = \$GLOBALS['phpgw_info']['user']['apps']['admin'];}}'''}}
or
{{<code>}}
if (\$_POST['add'])
{
   do_something();
}
{{</code>}}
becomes

{{\$GLOBALS['phpgw_info']['user']['apps']['admin']}}
or
<code>
if (\$_POST['add'])
{
   do_something();
}
</code>
----
You can indent by starting a paragraph with one or more colons.

{{```:Indent me!```}}
{{```::Me too!```}}
becomes

:Indent me
::Me too!
----
You can create bullet lists by starting a paragraph with one or more asterisks.

{{```*Bullet one```}}
{{```**Sub-bullet```}} 
becomes

*Bullet one
**Sub-bullet
----
You can create a description list by starting a paragraph with the following syntax 

{{```*;Item 1: Something```}}
{{```*;Item 2: Something else```}}

which gives

*;Item 1: Something
*;Item 2: Something else
----
Similarly, you can create numbered lists by starting a paragraph with one or more hashes.

{{```#Numero uno```}}
{{```#Number two```}}
{{```##Sub-item```}}
becomes

#Numero uno
#Number two
##Sub-item
----
You can mix and match list types:

<code>
#Number one
#*Bullet
#Number two
</code>
#Number one
#*Bullet
#Number two
----
You can make various levels of heading by putting = signs before and after the text =
= Level 1 heading =
== Level 2 heading ==
=== Level 3 heading ===
==== Level 4 heading ====
===== Level 5 heading =====
====== Level 6 heading ======
<code>
= Level 1 heading =
== Level 2 heading ==
=== Level 3 heading ===
==== Level 4 heading ====
===== Level 5 heading =====
====== Level 6 heading ======
</code>
----
You can create tables using pairs of vertical bars:

||cell one || cell two ||
|||| big ol' line ||
|| cell four || cell five ||
|| cell six || here's a very long cell ||

<code>
||cell one || cell two ||
|||| big ol' line ||
|| cell four || cell five ||
|| cell six || here's a very long cell ||
</code>
",
	) as $name => $body)
	{
		$oProc->insert('egw_wiki_pages',array(
			'wiki_id'        => 0,
			'wiki_name'      => $name,
			'wiki_lang'      => 'en',
			'wiki_version'   => 1,
			'wiki_time'      => $time,
			'wiki_supercede' => $time,
			'wiki_readable'  => 0,
			'wiki_writable'  => 0,
			'wiki_username'  => 'setup',
			'wiki_hostname'  => 'localhost',
			'wiki_title'     => $name,
			'wiki_body'      => $body,
			'wiki_comment'   => 'added by setup',
		),false,__LINE__,__FILE__,'wiki');
	}

	
