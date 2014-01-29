<?php

/**
 * Configuration Section
 */

//Commands
$RRDTOOL = '/usr/bin/rrdtool';
$SHOREWALL = '/sbin/shorewall';
$GREP = '/bin/grep';

//Network
$NETWORK = '10.200.0.0/24';
$FILENAME = '/var/log/mrtg/untrusted.rrd';

//Thresholds
$DURATION = 3600 * 24 * 7; //7 days
$THRESHOLD = 5 * 1024 * 1024 * 1024; //5 GB
//What percentage of THRESHOLD to ban at
$BAN_AT = 1.0; //100% of THRESHOLD
//What percentage of THRESHOLD to unban at
$UNBAN_AT = 0.9; //90% of THRESHOLD

if($fp = popen("{$RRDTOOL} fetch {$FILENAME} AVERAGE -s -{$DURATION} -e +3600", 'r'))
{
	$lastdate = $lastin = $lastout = 0;
	$totalin = $totalout = 0;
	fgets($fp);
	while(!feof($fp))
	{
		$line = trim(fgets($fp));
		if('' == $line) continue;

		list($date, $in, $out) = split('(:)*( )+', $line);
		if(false == is_numeric($in))
		{
			$in = 0;
		}
		if(false == is_numeric($out))
		{
			$out = 0;
		}
		
		if(0 < $lastdate)
		{
			$totalin += $lastin * ($date - $lastdate);
			$totalout += $lastout * ($date - $lastdate);
		}
		$lastdate = $date;
		$lastin = $in;
		$lastout = $out;
	}

	$totalin = round($totalin);
	$totalout = round($totalout);
	//echo "Total in: ".number_format($totalin)." B\nTotal out: ".number_format($totalout)." B\n";
	echo date('r').': '.number_format($totalin+$totalout)." B transferred\n";
}

$results = exec("{$SHOREWALL} show dynamic | {$GREP} -Fc {$NETWORK}");
$is_banned = ($results > 0);

if(false === $is_banned && $totalin >= $THRESHOLD * $BAN_AT)
{
	//Ban network
	echo "Banning {$NETWORK}...";
	exec("{$SHOREWALL} reject from {$NETWORK}");
	exec("{$SHOREWALL} reject to {$NETWORK}");
	echo "done!\n";
} elseif(true === $is_banned && $totalin < $THRESHOLD * $UNBAN_AT) {
	//Unban network
	echo "Unbanning {$NETWORK}...";
	exec("{$SHOREWALL} allow from {$NETWORK}");
	exec("{$SHOREWALL} allow to {$NETWORK}");
	echo "done!\n";
}

