<?php
	print_r(preg_split("/(ISBN[^a-z]+)/i","Jillek, W., Keller, G.: Handbuch der Leiterplattentechnik Band 4, Eugen Leuze Verlag 2003, ISBN3-87480-184-5; Tummala, R. Fundamentals of Microsystems Packaging, McGraw Hill 2001, ISBN 0071371699 Lehrbrief Elektroniktechnologie - Hybridtechnik (Thust, Mü",-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE));
?>