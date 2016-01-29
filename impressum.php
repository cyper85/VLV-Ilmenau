<?php

require_once("config.php");
$main->getHeader();
?>

<h1>Impressum</h1>

<div itemscope itemtype="http://schema.org/Person">
	<b>Betreuer:</b><br/>
	<span itemprop="name">Andreas Neumann</span>
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<span itemprop="streetAddress">Manggasse 8</span><br/>
		<span itemprop="postalCode">98693</span> <span itemprop="addressLocality">Ilmenau</span>
	</div><br/>
	<label for='mymail'>E-Mail:</label> <a href="&#x6d;&#97;&#105;&#x6c;&#116;&#111;&#x3a;&#97;&#110;&#100;&#114;&#46;&#110;&#x65;&#117;&#x6d;&#97;&#x6e;&#110;&#x40;&#103;&#111;&#111;&#103;&#x6c;&#101;&#x6d;&#x61;&#x69;&#108;&#x2e;&#99;&#x6f;&#x6d;" id='mymail' class="fa fa-envelope" itemprop="email">andr.neumann&#x0040;googlemail.com</a><br/>
	<small><label for='mypgp'>PGP-Fingerprint:</label> <span id='mypgp'>8E03 0F10 431C 3333 8E59 3623 D10A 0316 1195 437D</span></small><br/>
	<label for='mybtmessage'>BitMessage:</label> <a id='mybtmessage' itemprop="btmessage" href="bitmessage:BM-GuGcBPZg1Ugintxf9ksWSnv7QfsteZTr">BM-GuGcBPZg1Ugintxf9ksWSnv7QfsteZTr</a><br/>
	<a itemprop="url" href="http://blog.stadtplan-ilmenau.de" class='fa fa-large fa-comments'></a> 
	<a itemprop="url" href="https://plus.google.com/+AndreasNeumannIlmenau?rel=author" class='fa fa-large fa-google-plus-square'></a> 
	<a href="https://www.facebook.com/andreas.neumann.731?rel=author" class='fa fa-large fa-facebook-square' itemprop="url"></a> 
	<a href="https://www.twitter.com/nunAmen" class='fa fa-large fa-twitter-square' itemprop="url"></a> 
</div>
<?php 
$main->getFooter();
?>