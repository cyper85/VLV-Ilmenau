<?php
$charset = "utf-8";
 $mime    = (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) ? "application/xhtml+xml" : "text/html";

 header("content-type:$mime;charset=$charset");
 
 echo "<?xml version='1.0' encoding='UTF-8' ?>\n"; 
?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
 <url>
 <loc>http://vlv-ilmenau.de</loc>
 <changefreq>daily</changefreq>
 <priority>0.4</priority>
 </url>
 <url>
 <loc>https://vlv-ilmenau.de</loc>
 <changefreq>daily</changefreq>
 <priority>0.4</priority>
 </url>
 <url>
 <loc>https://vlv-ilmenau.de/login.php</loc>
 <changefreq>monthly</changefreq>
 <priority>0.1</priority>
 </url>
 <url>
 <loc>https://vlv-ilmenau.de/register.php</loc>
 <changefreq>monthly</changefreq>
 <priority>0.1</priority>
 </url>
 <url>
 <loc>http://vlv-ilmenau.de/impressum.php</loc>
 <changefreq>monthly</changefreq>
 <priority>0.1</priority>
 </url>
 <url>
 <loc>https://vlv-ilmenau.de/impressum.php</loc>
 <changefreq>monthly</changefreq>
 <priority>0.1</priority>
 </url>
 <?php
require_once('config.php');

$vlv_groups = $main->getVLVArray();

foreach($vlv_groups as $sgang => $semester) {
	foreach($semester as $s => $groups) {
		if(count($groups)>0) {
			foreach($groups as $g) {
				print "<url><loc>http://vlv-ilmenau.de/#!".$sgang."|".$s."|".$g."</loc><changefreq>daily</changefreq><priority>0.2</priority></url>".
				"<url><loc>https://vlv-ilmenau.de/#!".$sgang."|".$s."|".$g."</loc><changefreq>daily</changefreq><priority>0.2</priority></url>";
			}
		}
		else
			print "<url><loc>http://vlv-ilmenau.de/#!".$sgang."|".$s."</loc><changefreq>daily</changefreq><priority>0.2</priority></url>".
			"<url><loc>https://vlv-ilmenau.de/#!".$sgang."|".$s."</loc><changefreq>daily</changefreq><priority>0.2</priority></url>";
	}
}

$command = $db->query("SELECT DISTINCT `description` FROM `vlv_zusammenfassung` WHERE `description` IS NOT NULL AND `description` != ''");

while($row = $command->fetch_assoc()) {
	$lastModCommand = $db->query("SELECT MAX(`last_change`) as `last_change` FROM `vlv_entry` WHERE `vlv_id` IN (SELECT DISTINCT `vlv_id` FROM `vlv_zusammenfassung` WHERE `description`='".((int) $row['description'])."')");
	$lastMod = $lastModCommand->fetch_assoc();
	print "<url><lastmod>{$lastMod['last_change']}</lastmod><loc>http://vlv-ilmenau.de/vlvData.php?id=".((int)$row['description'])."</loc><changefreq>monthly</changefreq><priority>0.1</priority></url>".
	"<url><lastmod>{$lastMod['last_change']}</lastmod><loc>https://vlv-ilmenau.de/vlvData.php?id=".((int)$row['description'])."</loc><changefreq>monthly</changefreq><priority>0.1</priority></url>";
}

$command = $db->query("SELECT DISTINCT `vlv_id` FROM `vlv_zusammenfassung` WHERE `description` IS NULL OR `description` = ''");

while($row = $command->fetch_assoc()) {
	$lastModCommand = $db->query("SELECT MAX(`last_change`) as `last_change` FROM `vlv_entry` WHERE `vlv_id` = '".$db->real_escape_string($row['vlv_id'])."'");
	$lastMod = $lastModCommand->fetch_assoc();
	print "<url><lastmod>{$lastMod['last_change']}</lastmod><loc>http://vlv-ilmenau.de/vlvData.php?vid=".urlencode($row['vlv_id'])."</loc><changefreq>monthly</changefreq><priority>0.1</priority></url>".
	"<url><lastmod>{$lastMod['last_change']}</lastmod><loc>https://vlv-ilmenau.de/vlvData.php?vid=".urlencode($row['vlv_id'])."</loc><changefreq>monthly</changefreq><priority>0.1</priority></url>";
}

?>
</urlset>