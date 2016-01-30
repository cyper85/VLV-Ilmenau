<?php


if(!\filter_has_var(INPUT_GET,'id') AND !\filter_has_var(INPUT_GET,'vid')) {
	header('Content-type: application/json');
	exit;
}
require_once("config.php");
$vlvIds = array();
?>
<div>
<?php
if(\filter_has_var(INPUT_GET,'id')) {
$command = $db->query("SELECT `Titel`,`Fachgebiet`,`Fachverantwortlicher`,`lang`,`LP`,`exam`,`Vorkenntnisse`,`Lernergebnisse`,`Inhalt` ".
		"FROM `vlv2_object` WHERE `id` = '".((int) \filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT))."'");

$vlvEntry = $command->fetch_assoc();

$main->setTitle($vlvEntry['Titel']);

$main->getHeader();
?>

<h1 class='vlvEntry' itemprop='name'><?php echo htmlentities($vlvEntry['Titel']); ?></h1>

<div class="vlvEntryObject vlvLeft">
	<label for='vlvVerantwortlich' class='vlvLabel'>Fachverantwortliche(r)</label>
	<span itemprop='provider' itemscope itemtype='http://schema.org/Person' id='vlvVerantwortlich' class='vlvObject'><meta itemprop='name' content='<?=preg_replace('/(\,.*)$/','',$vlvEntry['Fachverantwortlicher'])?>"' /><?= htmlentities(html_entity_decode($vlvEntry['author'])); ?></span>

</div>
<div class="vlvEntryObject vlvLeft">
	<label for='vlvSprache' class='vlvLabel'>Sprache</label>
	<span id='vlvSprache' class='vlvObject' itemprop='inLanguage'><?php echo strtolower($vlvEntry['lang']); ?></span>
</div>
<div class="vlvEntryObject vlvLeft">
	<label for='vlvExam' class='vlvLabel'>Abschluss</label>
	<span id='vlvExam' class='vlvObject'><?php echo htmlentities($vlvEntry['exam']); ?></span>
</div>
<div class="vlvEntryObject">
	<label for='vlvLP' class='vlvLabel'>Leistungspunkte</label>
	<span id='vlvLP' class='vlvObject'><?php echo htmlentities($vlvEntry['LP']); ?></span>
</div>
<div class="vlvEntryObject">
	<label for='vlvVoraus' class='vlvLabel'>Vorkenntnisse</label>
	<span id='vlvVoraus' class='vlvObject'><?php echo ($vlvEntry['Vorkenntnisse']); ?></span>
</div>
<div class="vlvEntryObject">
	<label for='vlvInhalt' class='vlvLabel'>Inhalt</label>
	<span id='vlvInhalt' class='vlvObject' itemprop='description'><?php echo($vlvEntry['Inhalt']); ?></span>
</div>
<div class="vlvEntryObject">
	<label for='vlvErgebnis' class='vlvLabel'>Lernergebnisse</label>
	<span id='vlvErgebnis' class='vlvObject'><?php echo $vlvEntry['Lernergebnisse']; ?></span>
</div>
<!--Literaturempfehlungen-->
<!--weitere Fächer-->
<?php
$command = $db->query("SELECT `Titel`,`id` FROM `vlv2_object` WHERE `Fachgebiet` = ".((int) $vlvEntry['Fachgebiet'])." AND `id` != ".((int) \filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT))." ORDER BY `Titel`");
$vlvOther = array();
while($row = $command->fetch_assoc())
	$vlvOther[] = $row;
if(count($vlvOther)>0){
?>
<div class="vlvEntryObject">
	<label for='vlvWeitere' class='vlvLabel'>Weitere F&auml;cher im Fachgebiet</label>
	<ul id='vlvErgebnis' class='vlvObject'>
		<?php 
foreach ($vlvOther as $other) {
      echo "<li><a href='?r=site/vlvEntry&id=".$other['id']."'>".$other['Titel']."</a></li>\n"; 
}
?>
	</ul>
</div>
<?php
}

$command = $db->query("SELECT DISTINCT `vlv_id` FROM `vlv_zusammenfassung` WHERE `description` = '".((int) \filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT))."'");
if($command->num_rows>0)
	while($row = $command->fetch_assoc())
		$vlvIds[] = "'".$db->real_escape_string($row['vlv_id'])."'";

}
elseif(isset($_GET['vid'])) {
$command = $db->query("SELECT `title`,`author`,`url` FROM `vlv_zusammenfassung` WHERE `vlv_id` = '".$db->real_escape_string(\filter_input(INPUT_GET,'vid'))."'");

$vlvEntry = $command->fetch_assoc();

$main->setTitle($vlvEntry['title']);

$main->getHeader();

?>

<h1 class='vlvEntry'><?php echo htmlentities(html_entity_decode($vlvEntry['title'])); ?></h1>

<div class="vlvEntryObject vlvLeft">
	<label for='vlvVerantwortlich' class='vlvLabel'>Referent(in)</label>
	<span id='vlvVerantwortlich' class='vlvObject'><?= htmlentities(html_entity_decode($vlvEntry['author'])); ?></span>
</div>

<div class="vlvEntryObject vlvLeft">
	<label for='vlvURL' class='vlvLabel'>Seite im Vorlesungsverzeichnis</label>
	<span id='vlvVerantwortlich' class='vlvObject'><a href='<?= htmlentities(html_entity_decode($vlvEntry['url'])); ?>'><?= htmlentities(html_entity_decode($vlvEntry['url'])); ?></a></span>
</div>
<?php
$vlvIds[] = "'".$db->real_escape_string(\filter_input(INPUT_GET,'vid'))."'";
}

if(count($vlvIds>0)) {
	$command = $db->query("SELECT `id`, `type`, `weekday`, `rules`, `time_period`, `location` FROM `vlv_entry` WHERE `vlv_id` IN (".implode(',',$vlvIds).")");
	print "<div style='clear:both;'/><div class='vlvEntryObject'><label for='vlvVerantwortlich' class='vlvLabel'>Veranstaltungen im Semester</label>\n";
	while($row = $command->fetch_assoc()) {
		$locationCommand = $db->query("SELECT `url` FROM `location` WHERE `location` = '".$db->real_escape_string($row['location'])."'");
		$locationURLSchema = $locationURLNonSchema = array();
		if($locationCommand->num_rows == 1) {
			$locationRow = $locationCommand->fetch_assoc();
			$locationURLSchema[] = "<span class='TerminOrt' itemprop='location' itemscope itemtype='http://schema.org/Place'><span itemprop='name'>".$row['location']."</span><a href='".htmlentities($locationRow['url'])."' class='fa fa-map-marker' itemprop='sameAs'></a></span>";
			$locationURLNonSchema[] = "<span class='TerminOrt'>".$row['location']."<a href='".htmlentities($locationRow['url'])."' class='fa fa-map-marker'></a></span>";
		}
		elseif($locationCommand->num_rows == 0) {
			$locations = preg_split('/\s*[,]\s*/',$row['location']);
			if(count($locations)>1) {
				foreach($locations as $l) {
					$locationsCommand = $db->query("SELECT `url` FROM `location` WHERE `location` = '".$db->real_escape_string(trim($l))."'");
					if($locationsCommand->num_rows == 1) {
						$locationsRow = $locationsCommand->fetch_assoc();
						$locationURLSchema[] = "<span class='TerminOrt' itemprop='location' itemscope itemtype='http://schema.org/Place'><span itemprop='name'>{$l}</span><a href='".htmlentities($locationsRow['url'])."' class='fa fa-map-marker' itemprop='sameAs'></a></span>";
						$locationURLNonSchema[] = "<span class='TerminOrt'>{$l}<a href='".htmlentities($locationsRow['url'])."' class='fa fa-map-marker'></a></span>";
					}
					else {
						$locationURLSchema[] = "<span class='TerminOrt' itemprop='location' itemscope itemtype='http://schema.org/Place'><span itemprop='name'>{$l}</span></span>";
						$locationURLNonSchema[] = "<span class='TerminOrt'>{$l}</span>";
					}
				}
			}
			else {
				$locationURLSchema[] = "<span class='TerminOrt' itemprop='location' itemscope itemtype='http://schema.org/Place'><span itemprop='name'>".$row['location']."</span></span>";
				$locationURLNonSchema[] = "<span class='TerminOrt'>".$row['location']."</span>";
			}
		}
	
		$eventCommand = $db->query("SELECT UNIX_TIMESTAMP(`from`) AS `from`, UNIX_TIMESTAMP(`to`) AS `to` FROM `vlv_entry2date` WHERE `id` = '".$row['id']."' AND `from`> NOW()");
		$location = implode('',$locationURLNonSchema);
		$studCommand = $db->query("SELECT `studiengang`, `semester`, `seminargruppe` FROM `vlv_entry2stud` WHERE `id` = '".$row['id']."' GROUP BY `studiengang`, `semester`, `seminargruppe` ORDER BY `studiengang`, `semester`, `seminargruppe`");
		$studArray = array();
		while($studRow = $studCommand->fetch_assoc()) {
			if(strlen($studRow['seminargruppe'])>0)
				$studArray[] = "<a href='/#!".$studRow['studiengang']."|".$studRow['semester']."|".$studRow['seminargruppe']."'>".$studRow['studiengang']."|".$studRow['semester']."|".$studRow['seminargruppe']."</a>";
			else
				$studArray[] = "<a href='/#!".$studRow['studiengang']."|".$studRow['semester']."'>".$studRow['studiengang']."|".$studRow['semester']."</a>";
				
		}
		$studString = implode(", ",$studArray);
		
		if($eventCommand->num_rows>0) {
			print "<div itemscope itemtype='http://schema.org/EducationEvent' style='display:inline-block' class='TerminObject vlvObject'>";
			while($eventRow = $eventCommand->fetch_assoc()) {
				print "<time itemprop='startDate' datetime='".date('c',$eventRow['from'])."' ></time>
					<time itemprop='endDate' datetime='".date('c',$eventRow['to'])."' ></time>
					";
			}
			print "<span itemprop='name' class='TerminTitel'>".singular($row['type'])."</span>";
			print "<a itemprop='url' href='{$_SERVER['REQUEST_URI']}'></a>";
			$location = implode($locationURLSchema);
		}
		else
			print "<div style='display:inline-block' class='TerminObject vlvObject'>
			<span class='TerminTitel'>".$row['type']."</span>";
		
		print "
				<span class='TerminWochentag'>".singular($row['weekday'])."</span>
				<span class='TerminZeit'>".$row['time_period']."</span>
				<span class='TerminRRule'>".$row['rules']."</span>
				{$location}
				<span class='TerminFuer'>{$studString}</span>
			</div>";
			
	}
		print_r($row);
}

?>
</div>
<?php
$main->getFooter();
?>