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
 
	# checkcurrent.pl (c) 2001 Miles Lott
	# Compares setup.inc.php to the tables_current.inc.php file and notes
	#   any descrepancies.

	sub print_array
	{
		while(<@_>)
		{
			print $_ . "\n";
		}
	}

	$errors = 0;

	open(FILE,'setup.inc.php');
	$start = 0;
	@setup_inc = ();

	# Grab table list from setup.inc.php
	while (<FILE>)
	{
		chomp $_;
		if(/setup_info/ and /tables/)
		{
			$start = 1;
			next;
		}
		elsif((/setup_info/ or /\);/) and !/tables/)
		{
			$start = 0;
		}

		if($start)
		{
			my $line = $_;
			$line =~ s/\t//g;
			$line =~ s/\'//g;
			$line =~ s/\,//g;
			push(@setup_inc,$line);
			#print $line . "\n";
		}
	}
	close(FILE);
	@setup_inc = sort(@setup_inc);

	open(FILE,'tables_current.inc.php');
	$start = 0;
	@current = ();

	# Grab table list from tables_current.inc.php
	while (<FILE>)
	{
		chomp $_;
		my $line = $_;
		$line =~ s/\t//g;
		$line =~ s/\s//g;
		$line =~ s/\'//g;

		if(/phpgw_/ and /array\(/ and !/baseline/)
		{
			$line =~ s/\=//g;
			$line =~ s/\>//g;
			$line =~ s/array\(//g;

			push(@current,$line);
			#print $line . "\n";
		}
	}
	close(FILE);
	@current = sort(@current);

	print "\nsetup.inc.php contains these tables:\n";
	print_array(@setup_inc);
	print "\ntables_current.inc.php contains these tables:\n";
	print_array(@current);

	# Test table count
	if($#current != $#setup_inc)
	{
		print "\nTable count does not match!";
		if($#current gt $#setup_inc)
		{
			print "  tables_current.inc.php has more valid table names than setup.inc.php.\n";
			$errors++;
		}
		else
		{
			print "  setup.inc.php has more valid table names than tables_current.inc.php.\n";
			$errors++;
		}
	}

	# Test table match
	$i = 0;
	$old = '';
	while(<@setup_inc>)
	{
		if($current[$i] ne $_)
		{
			print "\n$old seems to be missing from setup.inc.php.\n";
			print "Or, $_ is missing from tables_current.inc.php.\n";
			$errors++;
			last;
		}
		$old = $current[$i + 1];
		$i++;
	}

	if(!$errors)
	{
		$i = 0;
		$old = '';
		while(<@current>)
		{
			if($setup_inc[$i] ne $_)
			{
				print "\n$old seems to be missing from tables_current.inc.php.\n";
				print "Or, $_ is missing from setup.inc.php.\n";
				$errors++;
			}
			$old = $setup_inc[$i + 1];
			$i++;
		}
	}

	if(!$errors)
	{
		print "\nFiles check out - congratulations!\n";
	}
	else
	{
		print "\nPlease check your files again.\n";
	}
