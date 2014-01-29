bw-monitor
==========

A simple script that, in conjunction with Shorewall, can ban heavy network users.

Purpose
-------

I wrote this script after runaway internet usage lead to my ISP slapping me with a $200 overage charge one month. The purpose of it is to isolate "untrusted" network users and limit their network usage within a configured window of time. It can easily be adapted for other, similar purposes.

Usage
-----

This script depends on Shorewall being configured with appropriate accounting rules for the network usage you want to monitor, and MRTG being configured to collect that data into RRD databases. `rrdtool` must be installed.

After supplying the appropriate paths in the configuration section at the beginning of the script, and your desired values for the threshold and duration, simply add this script to a cron job (I run it every 30 minutes).

Tips
----

To avoid rapidly banning/unbanning your target network, the `BAN_AT` and `UNBAN_AT` values can be configured for different percentages of your threshold; I use 100% (1.00) for `BAN_AT` and 90% (0.90) for `UNBAN_AT`, which means that the network won't be unbanned until it has 10% of its limited usage available.

Note that, by default, Shorewall is not configured to block existing connections, so a single runaway connection can continue to drain your network usage pool unless you change `BLACKLISTNEWONLY` in shorewall.conf from 'Yes' to 'No'.

Of course this script has limited utility if you can not control or isolate the "untrusted" users you want to limit. Since this is for my own personal network, where I control DHCP leases and the IP space entirely, this works for me, but if you rely on the same DHCP leases your "untrusted" users use you easily risk banning yourself from your own network. Use with caution.
