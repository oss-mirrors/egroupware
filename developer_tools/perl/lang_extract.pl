#!/usr/bin/perl
  #**************************************************************************#
  # phpGroupWare                                                             #
  # http://www.phpgroupware.org                                              #
  # --------------------------------------------                             #
  #  This program is free software; you can redistribute it and/or modify it #
  #  under the terms of the GNU General Public License as published by the   #
  #  Free Software Foundation; either version 2 of the License, or (at your  #
  #  option) any later version.                                              #
  #**************************************************************************#

  # $Id$ #
 
	# lang_extract.pl (c) 2001 Miles Lott
	# grep the current dir for lang calls, parse into an english lang file
	# Requires perl and the source files.
	# May only work in bash also.  Makes system calls to grep.
	# Takes one arg, the appname (default is 'appname')

	$tmpdir = '/tmp/';
	$appname = $ARGV[0] || 'appname';
	%all_lang = `grep 'lang\(' *.php`;

	srand(100000);
	$tmpfile = $tmpdir . int(rand(100000));
	open (TMP,">$tmpfile");

	for $line (%all_lang)
	{
		$_ = $line;
		chomp $_;
		if(/(.*?)lang\(\'(.*?)\'\)/ or /(.*?)lang\(\"(.*?)\"\)/)
		{
			$lhs = lc($2);
			print TMP $lhs . "\t" . $appname . "\ten\t" . $2 . "\n";
		}
	}
	close TMP;
	`sort $tmpfile > phpgw_en.lang`;
	unlink $tmpfile;
1;
