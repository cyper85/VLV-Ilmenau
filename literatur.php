<?php

require_once("config.php");

$main->intern();
$main->su();
$main->getHeader();

if(isset($_POST['op']) AND isset($_POST['ref'])) {
	switch($_POST['op']) {
		case 'block':
			$db->real_query("INSERT INTO `vlv_literatur_blocked` (`literatur_ref`) VALUES ('".$db->real_escape_string(stripslashes($_POST['ref']))."')");;
			break;
		case 'addIsbn':
			if(isset($_POST['isbn']))
				$db->real_query("INSERT INTO `vlv_isbn` (`literatur_ref`,`isbn`) VALUES ('".$db->real_escape_string($_POST['ref'])."','".$db->real_escape_string($_POST['isbn'])."')");;
			break;
	}
}


$literatur = $db->query("SELECT DISTINCT `literatur_ref` FROM `vlv_literatur`");
$blocked = $db->query("SELECT DISTINCT `literatur_ref` FROM `vlv_literatur_blocked`");
$vorhandeneISBN = $db->query("SELECT DISTINCT `literatur_ref` FROM `vlv_isbn`");
$vorhandeneISSN = $db->query("SELECT DISTINCT `literatur_ref` FROM `vlv_issn`");

$literaturArray = array();

if($literatur->num_rows > 0)
	while($row = $literatur->fetch_assoc())
		$literaturArray[$row['literatur_ref']] = true;

if($vorhandeneISBN->num_rows > 0)
	while($row = $vorhandeneISBN->fetch_assoc())
		if(isset($literaturArray[$row['literatur_ref']]))
			unset($literaturArray[$row['literatur_ref']]);
			
if($vorhandeneISSN->num_rows > 0)
	while($row = $vorhandeneISSN->fetch_assoc())
		if(isset($literaturArray[$row['literatur_ref']]))
			unset($literaturArray[$row['literatur_ref']]);

if($blocked->num_rows > 0)
	while($row = $blocked->fetch_assoc())
		if(isset($literaturArray[$row['literatur_ref']]))
			unset($literaturArray[$row['literatur_ref']]);

if(count($literaturArray)>0)
	$literatur_ref = key($literaturArray);
else {
	print "<div class='error'>Keine neuen B&uuml;cher.</div>";
	$main->getFooter();
	exit;
}

print "<!-- ".count($literaturArray)." -->";

?>

<form action='literatur.php' method='post'>
	<div class='box blue'><?= $literatur_ref; ?></div>
	<input type='hidden' name='op' value='block' />
	<input type='hidden' name='ref' value='<?= addslashes($literatur_ref); ?>' />
	<input type='submit' value='block' />
</form>

<form id='litFind'>
	<input type='text' id='query' name='query' /> <input value='suche' type='submit' />
</form>

<ul id='isbnLiteratur'>
	
</ul>
<script>
	
	var literaturRef = "<?=addslashes($literatur_ref);?>";
	
	$(function() {
		$('#litFind').submit(function(){
			var ajaxPost = {};
			ajaxPost.type = "litFind";
			ajaxPost.query = $("#query").val();
			$.post('ajax.php',ajaxPost,function(data,status) {
				console.log(data);
				$('#isbnLiteratur').html("");
				if(typeof data.objects != "undefined") {
					for(var item in data.objects) {
						$('#isbnLiteratur').append(
							$("<li/>").append(
								$("<form method='post' acton='literatur.php' />").append(
									$("<span/>").html(data.objects[item].string)
								).append(
									$("<input type='hidden' />").attr('name',"ref").val(literaturRef)
								).append(
									$("<input type='hidden' />").attr('name',"op").val("addIsbn")
								).append(
									$("<input type='hidden' />").attr('name',"isbn").val(data.objects[item].ISBN)
								).append(
									$("<input type='submit' />").val("speichern")
								)
							)
						);
					}
				}
			});
			return false;
		});
	});
</script>
<?php 
$main->getFooter();
?>