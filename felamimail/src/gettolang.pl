#!/usr/bin/perl

	open(IN,$ARGV[0]);
	#open(IN,'felamimail.po');

	$i=0;
	while (<IN>)
	{
		chomp $_;
		if (/\Amsgid(.*)/)
		{
			my $str = $1; $str =~ s/\"//g;
			$str =~ s/%s/x/;
			$msgid[$i] = $str;
			#print "MSGID: $str\n";
		}
		elsif (/\Amsgstr(.*)/)
		{
			my $str = $1; $str =~ s/\"//g;
			$str =~ s/%s/%1/;
			$mgsstr[$i] = $str;
			$i++;
			next;
			#print "MSGSTR: $str\n";
		}
	}
	close IN;

	for ($i=0;$i<$#msgid;$i++)
	{
		print $msgid[$i]."\tfelamimail\t" . $ARGV[1] . "\t".$mgsstr[$i]."\n";
	}
