<?php
$charset = "utf-8";
$mime = (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) ? "application/xhtml+xml" : "text/html";

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
    <?php
    require_once('config.php');

    $vlv_groups = $main->getVLVArray();

    foreach ($vlv_groups as $sgang => $semester) {
        foreach ($semester as $s => $groups) {
            if (count($groups) > 0) {
                foreach ($groups as $g) {
                    // Last-Change herausfinden
                    $result = $db->query("SELECT MAX(`vlv_entry`.`last_change`) AS `last_change` FROM `vlv_entry` INNER JOIN `vlv_entry2stud` ON `vlv_entry`.`id` = `vlv_entry2stud`.`id` WHERE `vlv_entry2stud`.`studiengang` = '".$sgang."' AND `vlv_entry2stud`.`seminargruppe` = '".$g."' AND `vlv_entry2stud`.`semester` = '".$g."' AND `vlv_entry`.`last_change` <= DATE_FORMAT(NOW(),'%Y-%m-%d');");
                    if(($row = $result->fetch_assoc()) AND (strlen($row['last_change'])>0)) {
                        print "\n\t<url>\n\t\t<lastmod>{$row['last_change']}</lastmod>\n\t\t<loc>https://vlv-ilmenau.de/#!" . $sgang . "|" . $s . "|" . $g . "</loc>\n\t\t<changefreq>daily</changefreq>\n\t\t<priority>0.2</priority>\n\t</url>";
                    }
                    else {
                        print "\n\t<url>\n\t\t<loc>https://vlv-ilmenau.de/#!" . $sgang . "|" . $s . "|" . $g . "</loc>\n\t\t<changefreq>daily</changefreq>\n\t\t<priority>0.2</priority>\n\t</url>";
                    }
                }
            } else {
                // Last-Change herausfinden
                $result = $db->query("SELECT MAX(`vlv_entry`.`last_change`) AS `last_change` FROM `vlv_entry` INNER JOIN `vlv_entry2stud` ON `vlv_entry`.`id` = `vlv_entry2stud`.`id` WHERE `vlv_entry2stud`.`studiengang` = '".$sgang."' AND `vlv_entry2stud`.`semester` = '".$g."' AND `vlv_entry`.`last_change` <= DATE_FORMAT(NOW(),'%Y-%m-%d');");
                $row = $result->fetch_assoc();
                if(($row = $result->fetch_assoc()) AND (strlen($row['last_change'])>0)) {
                    print "\n\t<url>\n\t\t<lastmod>{$row['last_change']}</lastmod>\n\t\t<loc>https://vlv-ilmenau.de/#!" . $sgang . "|" . $s . "</loc>\n\t\t<changefreq>daily</changefreq>\n\t\t<priority>0.2</priority>\n\t</url>";
                }
                else {
                    print "\n\t<url>\n\t\t<loc>https://vlv-ilmenau.de/#!" . $sgang . "|" . $s . "</loc>\n\t\t<changefreq>daily</changefreq>\n\t\t<priority>0.2</priority>\n\t</url>";
                }
            }
        }
    }

    $command = $db->query("SELECT DISTINCT `description` FROM `vlv_zusammenfassung` WHERE `description` IS NOT NULL AND `description` != ''");

    while ($row = $command->fetch_assoc()) {
        $lastModCommand = $db->query("SELECT MAX(`last_change`) as `last_change` FROM `vlv_entry` WHERE `vlv_id` IN (SELECT DISTINCT `vlv_id` FROM `vlv_zusammenfassung` WHERE `description`='" . ((int) $row['description']) . "') AND `vlv_entry`.`last_change` <= DATE_FORMAT(NOW(),'%Y-%m-%d')");
        if(($lastMod = $lastModCommand->fetch_assoc())AND(  strlen($lastMod['last_change'])>0)) {
            print "\n\t<url>\n\t\t<lastmod>{$lastMod['last_change']}</lastmod>\n\t\t<loc>https://vlv-ilmenau.de/vlvData.php?id=" . ((int) $row['description']) . "</loc>\n\t\t<changefreq>monthly</changefreq>\n\t\t<priority>0.1</priority>\n\t</url>";
        } else {
            print "\n\t<url>\n\t\t<loc>https://vlv-ilmenau.de/vlvData.php?id=" . ((int) $row['description']) . "</loc>\n\t\t<changefreq>monthly</changefreq>\n\t\t<priority>0.1</priority>\n\t</url>";
        }
    }

    $command = $db->query("SELECT DISTINCT `vlv_id` FROM `vlv_zusammenfassung` WHERE `description` IS NULL OR `description` = ''");

    while ($row = $command->fetch_assoc()) {
        $lastModCommand = $db->query("SELECT MAX(`last_change`) as `last_change` FROM `vlv_entry` WHERE `vlv_id` = '" . $db->real_escape_string($row['vlv_id']) . "' AND `vlv_entry`.`last_change` <= DATE_FORMAT(NOW(),'%Y-%m-%d')");
        if(($lastMod = $lastModCommand->fetch_assoc())AND(  strlen($lastMod['last_change'])>0)) {
            print "\n\t<url>\n\t\t<lastmod>{$lastMod['last_change']}</lastmod>\n\t\t<loc>https://vlv-ilmenau.de/vlvData.php?vid=" . urlencode($row['vlv_id']) . "</loc>\n\t\t<changefreq>monthly</changefreq>\n\t\t<priority>0.1</priority>\n\t</url>";
        } else {
            print "\n\t<url>\n\t\t<loc>https://vlv-ilmenau.de/vlvData.php?vid=" . urlencode($row['vlv_id']) . "</loc>\n\t\t<changefreq>monthly</changefreq>\n\t\t<priority>0.1</priority>\n\t</url>";
        }
    }
    ?>
</urlset>