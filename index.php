<?php

require_once("config.php");

if($main->getUid()):

$idArray = $main->privateVlv($main->getUid());

$output = "Stelle f&uuml;r deinen individuellen Stundenplan in den <a href='settings.php'>Einstellungen</a> ein, an welchen Veranstaltungen du teilnehmen m&ouml;chtest!";
if(count($idArray)>0) {
	$result = $db->query("SELECT `id`, UNIX_TIMESTAMP(`from`) as `from`, UNIX_TIMESTAMP(`to`) as `to` FROM `vlv_entry2date` ".
			"WHERE `id` IN (".implode(', ',$idArray).") AND `to` > NOW() ORDER BY `from` LIMIT 100");
	$Termine = array();
	while($row = $result->fetch_assoc())
		$Termine[] = $row;
	$return = array();
	$return['dates'] = $Termine;
	$TerminIDsArray = array();
	foreach($Termine as $Termin)
		$TerminIDsArray[] = $Termin['id'];

	$return['content'] = $main->getVLVdata($TerminIDsArray);
	
	$output = $main->vlvSite($return);
	
}

$command = $db->query("SELECT `iCal_string` FROM `user` WHERE `iCal` = 0 AND `uid` = ".$main->getUid());
$ical = "";
if($command->num_rows==1) {
	$row = $command->fetch_assoc();
	$ical = "<a id='vlvICal' href='http://vlv-ilmenau.de/ical.php?id=".urlencode($row['iCal_string'])."' class='fa fa-calendar' style='display: inline-block;'>iCal</a>";
}
$main->getHeader();


print $ical;
?>
<div id='vlv_plan'>
<?php print $output; ?>
</div>

<?php

else:

$vlvArray = $main->getVLVArray();

$output = "";
$studiengang = "";
$semester = "";
$seminargruppe = "";
$ical = "";

if(isset($_GET['_escaped_fragment_'])) {
	$getArray = explode('|',urldecode($_GET['_escaped_fragment_']));
	$post = array();
	
	if(count($getArray)== 3) {
		$post['type'] = "vlv";
		$post["Studiengang"] = $getArray[0];
		$post["Semester"] = $getArray[1];
		$post["Gruppe"] = $getArray[2];
		$main->setTitle(implode(' / ',$getArray));
	}
	elseif(count($getArray)== 2) {
		$post['type'] = "vlv";
		$post["Studiengang"] = $getArray[0];
		$post["Semester"] = $getArray[1];
		$main->setTitle(implode(' / ',$getArray));
	}
	if(count($post)) {
		
		foreach($vlvArray as $sgang => $sem) {
			$studiengang .= "<option value='{$sgang}'";
			if($sgang == $post['Studiengang']) {
				$studiengang .= " selected";
				foreach($sem as $seme => $gruppen) {
					$semester .= "<option value='{$seme}'";
					if($seme == $post['Semester']) {
						$semester .= " selected";
						if(count($gruppen)>0) {
							foreach($gruppen as $gruppe) {
								$seminargruppe .= "<option value='{$gruppe}'";
								if($gruppe == $post['Gruppe'])
									$seminargruppe .= " selected";
								$seminargruppe .= ">{$gruppe}</option>";
							}
						}
					}
					$semester .= ">{$seme}</option>";
				}
			}
			$studiengang .= ">{$sgang}</option>";
			
		}
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,"http://vlv-ilmenau.de/ajax.php");
		curl_setopt($ch, CURLOPT_POST, count($post));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		
		// in real life you should use something like:
		// curl_setopt($ch, CURLOPT_POSTFIELDS, 
		//          http_build_query(array('postvar1' => 'value1')));

		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = $main->vlvSite(json_decode(curl_exec ($ch),1));
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		if($http_status != "200 OK") {
			header('HTTP/1.1 410 Gone');
			if(isset($_SERVER['HTTPS']))
				header('Location: https://vlv-ilmenau.de/');
			else
				header('Location: http://vlv-ilmenau.de/');
			die();
		}
		unset($post['type']);
		$ical = "?".htmlentities(http_build_query($post));
	}
}

$main->getDateFkt();
$main->getMonat();
$main->getWochentag();
$main->getHeader();
?>

<div class='menu'>
<select id='studiengang'>
<?php print $studiengang; ?>
</select>
<select id='semester'<?php if(strlen($semester)==0) echo" class='hidden'"; ?>>
<?php print $semester; ?>
</select>
<select id='gruppe'<?php if(strlen($seminargruppe)==0) echo" class='hidden'"; ?>>
<?php print $seminargruppe; ?>
</select>
<a id='vlvICal' href='http://vlv-ilmenau.de/ical.php<?php echo $ical; ?>' class="fa fa-calendar">iCal</a>
</div>
<div id='vlv_plan'>
<?php print $output; ?>
</div>

<script>	
	var generate_sgang = function() {
		$("a#vlvICal").hide();
		$("select#semester").hide();
		$("select#gruppe").hide();
		$("select#studiengang").html("<option value='' disabled selected>W&auml;hle deinen Studiengang</option>");
		for(sgang in vlv_groups) {
			$("select#studiengang").append("<option value='"+sgang+"'>"+sgang+"</option>");
		}
	};
	var generate_semester = function() {
		$("a#vlvICal").hide();
		$("select#semester").show();
		$("select#gruppe").hide();
		var sgang = $("select#studiengang").val();
		$("select#semester").html("<option value='' disabled selected>W&auml;hle dein Semester</option>");
		for(semester in vlv_groups[sgang]) {
			$("select#semester").append("<option value='"+semester+"'>"+semester+"</option>");
		}
	};
	var generate_group = function() {
		// Teste, ob es Gruppen gibt
		var sgang = $("select#studiengang").val();
		var semester = $("select#semester").val();
		
		if(vlv_groups[sgang][semester].length > 0) {
			$("select#gruppe").show();
			$("select#gruppe").html("<option value='' disabled selected>W&auml;hle deinen Seminargruppe</option>");
			for(gruppe in vlv_groups[sgang][semester]) {
				$("select#gruppe").append("<option value='"+vlv_groups[sgang][semester][gruppe]+"'>"+vlv_groups[sgang][semester][gruppe]+"</option>");
			}
		}
		else {
			$("select#gruppe").hide();
			$("select#gruppe").html("<option value='' disabled selected>W&auml;hle deinen Seminargruppe</option>");
			makeVLV();
		}
	};
	var makeVLV = function()  {
		// Ajax-Hash neu erzeugen
		var newHash = "!"+$("select#studiengang").val()+"|"+$("select#semester").val();
		var ajaxPost = {};
		ajaxPost.type = "vlv";
		ajaxPost.Studiengang = $("select#studiengang").val();
		ajaxPost.Semester = $("select#semester").val();
		if(vlv_groups[$("select#studiengang").val()][$("select#semester").val()].length > 0) {
			newHash = newHash + "|"+$("select#gruppe").val();
			ajaxPost.Gruppe = $("select#gruppe").val();
		}
		if(window.location.hash.replace(/^#!/,'') != newHash.replace(/^!/,'')) {
			console.log(window.location.hash.replace(/^#?!?/,''));
			console.log(newHash.replace(/^!/,''));
			window.location.hash = newHash;
			$('link[rel=canonical]').attr('href',"http://vlv-ilmenau.de"+unescape(unescape(window.location.hash)));
			$('meta[property="og:url"]').attr('content',"http://vlv-ilmenau.de"+unescape(unescape(window.location.hash)));
			$('meta[name="twitter:url"]').attr('content',"http://vlv-ilmenau.de"+unescape(unescape(window.location.hash)));
			$('title').text("Mein Vorlesungsverzeichnis Ilmenau / "+unescape(unescape(window.location.hash)).replace(/^#!/,'').split("|").join(" / "));
			$('meta[name="og:title"]').attr("content",unescape(unescape(window.location.hash)).replace(/^#!/,'').split("|").join(" / "));
			setCookie('hash',window.location.hash.replace(/^#!/,''),100);
			try {
				console.log(window.location+"");
				_paq.push(['setCustomUrl',window.location]);
				_paq.push(['setCustomUrl',window.location]);
				_paq.push(['setDocumentTitle',$('title').html()]);
				_paq.push(['trackPageView']);
			}catch(err) {
				console.log('Piwik funktioniert nicht');
			}
		}
		// VLV neu laden
		$.post('ajax.php',ajaxPost,function(data,status) {
			var aktuellesDatumObj = new Date();
			var aktuellesDatum = (aktuellesDatumObj.getFullYear()*10000)+((aktuellesDatumObj.getMonth()+1)*100)+aktuellesDatumObj.getDate();
			var datumToken = 0;
			delete aktuellesDatumObj;
			if(typeof data.dates != "undefined") {
				if(!$("select#gruppe").is(":visible")) {
					$("a#vlvICal").attr('href','http://vlv-ilmenau.de/ical.php?Studiengang='+escape($("select#studiengang").val())+"&Semester="+escape($("select#semester").val())).show();
				} else {
					$("a#vlvICal").attr('href','http://vlv-ilmenau.de/ical.php?Studiengang='+escape($("select#studiengang").val())+"&Semester="+escape($("select#semester").val())+"&Gruppe="+escape($("select#gruppe").val())).show();
				}
				
				$("a#vlvICal").show();
				$('div#vlv_plan').html("");
				for(var date in data.dates) {
					var container = $('<div/>').addClass("vlvTermin");
					var datumTokenObj = new Date(data.dates[date]['from']*1000);
					var newDatumToken = (datumTokenObj.getFullYear()*10000)+((datumTokenObj.getMonth()+1)*100)+datumTokenObj.getDate();
					if(newDatumToken>datumToken) {
						// Neuen Datumsbalken erstellen
						datumToken = newDatumToken;
						delete newDatumToken;
						var datumText = "";
						
						if(datumToken == aktuellesDatum) { datumText = "Heute" }
						else if (datumToken == (aktuellesDatum+1)) { datumText = "Morgen" }
						else if (datumToken == (aktuellesDatum+2)) { datumText = "&Uuml;bermorgen" }
						else {
							datumText = wochentag[datumTokenObj.getDay()]+", "+f0(datumTokenObj.getDate())+". "+monat[datumTokenObj.getMonth()];
						}
						$('div#vlv_plan').append(
							$('<div />').addClass('vlvDate').html(datumText)
						);
						delete datumText;
					}
					// Uhrzeit
					
					container.append(
						$('<span />').addClass('vlvTimerange')
							.append($('<span />').addClass('vlvFrom').html(makeTime(data.dates[date]['from'])).after("&nbsp;&ndash;&nbsp;"))
							.append("&nbsp;&ndash;&nbsp;")
							.append($('<span />').addClass('vlvTo').html(makeTime(data.dates[date]['to'])))
					);
					
					if(data.content[data.dates[date]['id']].type.match(/vorlesung/i)) {
						  container.addClass('vlvVorlesung');
					}
					if(data.content[data.dates[date]['id']].type.match(/bung/i)) {
						  container.addClass('vlvUebung');
					}
					if(data.content[data.dates[date]['id']].type.match(/klausur/i)) {
						  container.addClass('vlvKlausur');
					}
					if(data.content[data.dates[date]['id']].type.match(/seminar/i)) {
						  container.addClass('vlvSeminar');
					}
					if(data.content[data.dates[date]['id']].type.match(/praktikum/i)) {
						  container.addClass('vlvSeminar');
					}
					if(data.content[data.dates[date]['id']].type.match(/praktika/i)) {
						  container.addClass('vlvSeminar');
					}
					
					// Inhalt
					container.append($('<span />').addClass('vlvPrefix').html(data.content[data.dates[date]['id']].type)).append(":&nbsp;");
					container.append($('<span />').addClass('vlvTitle').html(data.content[data.dates[date]['id']].title));
					container.append($('<span />').addClass('vlvLocation').html(" ("+data.content[data.dates[date]['id']].location+")"));
					if(data.content[data.dates[date]['id']].description.length>0) {
						container.append($('<a />').attr('href','vlvData.php?id='+data.content[data.dates[date]['id']].description).addClass('vlvDesc').addClass('fa').addClass('fa-info-circle'));
					}
					$('div#vlv_plan').append(container);
					delete container;
				}
			}
		});
	}
	
	$(function() {
		$("select#studiengang").change(generate_semester);
		$("select#semester").change(generate_group);
		$("select#gruppe").change(makeVLV);
		generate_sgang();
		if((window.location.hash.length>0)||(window.location.search.substring(1).match(/^_escaped_fragment_=/))) {
			var newHash = unescape(window.location.hash.replace(/^#!/,'')).split('|');
			if(newHash.length==1) {
				newHash = unescape(unescape(window.location.search.substring(1).replace(/^_escaped_fragment_=/,''))).split('|');
				window.location.hash = unescape(unescape(window.location.search.substring(1).replace(/^_escaped_fragment_=/,'')));
			}
			if(newHash.length==1) {
				newHash = getCookie('hash').split('|');
				window.location.hash = getCookie('hash');
			}
			$('link[rel=canonical]').attr('href',"http://vlv-ilmenau.de"+unescape(unescape(window.location.hash)));
			$('meta[property="og:url"]').attr('content',"http://vlv-ilmenau.de"+unescape(unescape(window.location.hash)));
			$('meta[name="twitter:url"]').attr('content',"http://vlv-ilmenau.de"+unescape(unescape(window.location.hash)));
			$('title').html("Mein Vorlesungsverzeichnis Ilmenau / "+unescape(unescape(window.location.hash)).replace(/^#!/,'').split("|").join(" / "));
			$('meta[name="og:title"]').attr("content",unescape(unescape(window.location.hash)).replace(/^#!/,'').split("|").join(" / "));
			setCookie('hash',window.location.hash.replace(/^#!/,''),100);
			
			$("select#studiengang").val(newHash[0]).change();
			if(typeof newHash[1] != "undefined") {
				$("select#semester").val(newHash[1]).change();
				if(typeof newHash[2] != "undefined") {
					$("select#gruppe").val(newHash[2]).change();
				}
			}
		}
	});
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-69847843-4', 'auto');
  ga('send', 'pageview');

</script>
<?php 
endif;
$main->getFooter();
?>
