#!/usr/bin/perl

my $line;


open(SOURCE, "<$ARGV[0]") || die "Couldn't open source file due to $!.\n";
open(TARGET, ">$ARGV[0].new") || die "couldn't open target file due to $!.\n";
while (defined($line=<SOURCE>)) {					# while there is data to be read in, then...
#   $line =~ s/ {2,}/\n/g;
   $line = lc($line);
   print TARGET $line;
}
close TARGET;
close SOURCE;

unlink $ARGV[0];
rename "$ARGV[0].new", "$ARGV[0]";

