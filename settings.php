<?php

require_once("config.php");
$main->intern();
$main->settings();
$main->getVLVArray();
$main->getDateFkt();
$main->getMonat();
$main->getWochentag();
$main->getHeader();
?>
<menu><a href='#auswahl' class='button blue'>Veranstaltungsauswahl</a> <a href='#user' class='button blue'>Nutzereinstellungen</a></menu>
<h2><a name='auswahl'></a>Veranstaltungsauswahl</h2>

<div class='menu'>
<select id='studiengang'>
</select>
<select id='semester'>
</select>
<select id='gruppe'>
</select>
</div>
<div id='vlv_plan'>
</div>

<h2><a name='user'></a>Nutzereinstellungen</h2>

<div class='inputContainer'>
	<label for='vlvUser'>Nutzername</label>
	<span id='vlvUser'><?php echo $main->getUser(); ?></span>
</div>
<div class='inputContainer'>
	<label for='vlvEmail'>Uni-Mailadresse</label>
	<span id='vlvEmail'><?php echo $main->getEmail(); ?></span>
</div>
<div class='inputContainer'>
	<label for='vlvPassword'>Passwort</label>
	<span class='editable' data-editable-type='password' data-editable-ajax-type='changePassword' id='vlvPassword'>****<span style='padding-left:5px;' class='fa fa-pencil-square-o'></span>
</div>
<div class='inputContainer'>
	<label for='vlvIcal'>iCal</label>
	<button id='vlvIcal' class='button green'>Schl&uuml;ssel zur&uuml;cksetzen</button>
</div>



<script>
	var classRegExp = /[().:\ ?"'/|]/g;
	var vlvOwns;
	var makeVlvContainer = function(data,vlv) {
		var container = $('<div/>').addClass("vlvVeranstaltung").addClass('id-'+vlv);
		if(vlvOwns && vlvOwns[vlv]) {
			container.append(
				$('<div/>').addClass('vlvTitle').html(data.title+" ("+data.author+")").append(
					$("<button/>").addClass("button").addClass('fa').addClass('fa-check-square-o').addClass("green").data('vlvId',vlv).click(unChooseEvent)
				).append(
					$("<button/>").addClass("button").addClass("fa").addClass('fa-chevron-circle-down').addClass("blue").click(fieldsetDown)
				)
			);
			fieldsetHide = false;
		}
		else {
			container.append(
				$('<div/>').addClass('vlvTitle').html(data.title+" ("+data.author+")").append(
					$("<button/>").addClass("button").addClass('fa').addClass('fa-square-o').addClass("red").data('vlvId',vlv).click(chooseEvent)
				).append(
					$("<button/>").addClass("button").addClass("fa").addClass('fa-chevron-circle-down').addClass("blue").click(fieldsetDown)
				)
			);
		}
		for(var type in data['date']) {delete container;
			var typeContainer = $('<fieldset/>').append($('<legend/>').html(type)).hide();
			for(var Studis in data['date'][type]) {
				var buttonColor = "red";
				var buttonCallback = chooseEvent;
				if(vlvOwns && vlvOwns[vlv] && (((typeof vlvOwns[vlv][type] == "undefined") && (vlvOwns[vlv]['root'] == Studis)) || (vlvOwns[vlv][type] && (vlvOwns[vlv][type] == Studis)))) {
					buttonColor = "green";
					buttonCallback = unChooseEvent;
				}
				typeContainer.append(
					$('<button />').addClass("id_"+vlv).addClass("type_"+type.replace(classRegExp,"_")).addClass("group_"+Studis.replace(classRegExp,"_")).data('vlvId',vlv).data('vlvType',type).data('vlvStudi',Studis).addClass('button').addClass(buttonColor).click(buttonCallback).html("<b>"+Studis+"</b><br/>"+data['date'][type][Studis].join('<br/>'))
				);
				
			}
			container.append(typeContainer);
		}
		return container;
	};
	var createVlvOwns = function() {
		var ajaxPost = {};
		ajaxPost.type = 'vlvOwns';
		$.post('ajax.php',ajaxPost,function(data,status) {
			vlvOwns = data.own;
			$('.vlvTitle button.fa-check-square-o').addClass('fa-square-o').addClass('red').unbind("click").click(chooseEvent).removeClass('fa-check-square-o').removeClass('green');
			$(".vlvVeranstaltung fieldset button.green").addClass('red').unbind("click").click(chooseEvent).removeClass('green');
			for(vlv in vlvOwns) {
				$('.id-'+vlv+' button.fa-square-o').addClass('fa-check-square-o').addClass('green').unbind("click").click(unChooseEvent).removeClass('fa-square-o').removeClass('red');
				if(typeof vlvOwns[vlv]['root'] != "undefined") {
					$(".id_"+vlv+".group_"+vlvOwns[vlv]['root'].replace(classRegExp,"_")).addClass('green').unbind("click").click(unChooseEvent).removeClass('red');
					delete vlvOwns[vlv]['root'];
				}
				for(type in vlvOwns[vlv]) {
					$(".id_"+vlv+".type_"+type.replace(classRegExp,"_")).addClass('red').unbind("click").click(chooseEvent).removeClass('green');
					$(".id_"+vlv+".type_"+type.replace(classRegExp,"_")+".group_"+vlvOwns[vlv][type].replace(classRegExp,"_")).addClass('green').unbind("click").click(unChooseEvent).removeClass('red');
				}
			}
		});
	}
	var chooseEvent = function() {
		var ajaxPost = {};
		ajaxPost.type = 'chooseEvent';
		ajaxPost.vlvId = $(this).data('vlvId');
		if($(this).data('vlvType')) {
			ajaxPost.vlvType = $(this).data('vlvType');
			ajaxPost.vlvStudi = $(this).data('vlvStudi');
		}
		else {
			ajaxPost.sgang = $("select#studiengang").val();
			ajaxPost.semester = $("select#semester").val();
			ajaxPost.group = $("select#gruppe").val();
		}
		$.post('ajax.php',ajaxPost,function(data,status) {
			createVlvOwns();
		});
	}
	var unChooseEvent = function() {
		var ajaxPost = {};
		ajaxPost.type = 'unChooseEvent';
		ajaxPost.vlvId = $(this).data('vlvId');
		if($(this).data('vlvType')) {
			ajaxPost.vlvType = $(this).data('vlvType');
			ajaxPost.vlvStudi = $(this).data('vlvStudi');
		}
		else {
			ajaxPost.sgang = $("select#studiengang").val();
			ajaxPost.semester = $("select#semester").val();
			ajaxPost.group = $("select#gruppe").val();
		}
		$.post('ajax.php',ajaxPost,function(data,status) {
			console.log(data);
			createVlvOwns();
		});
	}
	var fieldsetDown = function() {
		$(this).parent().parent().children('fieldset').slideDown();
		$(this).removeClass("fa-chevron-circle-down").addClass("fa-chevron-circle-up").unbind("click").click(fieldsetUp);
	}
	var fieldsetUp = function() {
		$(this).parent().parent().children('fieldset').slideUp();
		$(this).removeClass("fa-chevron-circle-up").addClass("fa-chevron-circle-down").unbind("click").click(fieldsetDown);
	}
	var generate_sgang = function() {
		$("a#vlvICal").hide();
		$("select#semester").hide();
		$("select#gruppe").hide();delete container;
		$("select#studiengang").html("<option value='' disabled selected>W&auml;hle deinen Studiengang</option>");
		for(sgang in vlv_groups) {
			$("select#studiengang").append("<option value='"+sgang+"'>"+sgang+"</option>");
		}
	};
	var generate_semester = function() {
		$("select#semester").show();
		$("select#gruppeStudiengang").hide();
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
			makeVLV();
		}
	};
	var makeVLV = function()  {
		// Ajax-Hash neu erzeugen
		var newHash = "!"+$("select#studiengang").val()+"|"+$("select#semester").val();
		var ajaxPost = {};
		ajaxPost.type = "auswahl";
		ajaxPost.Studiengang = $("select#studiengang").val();
		ajaxPost.Semester = $("select#semester").val();
		if(vlv_groups[$("select#studiengang").val()][$("select#semester").val()].length > 0) {
			newHash = newHash + "|"+$("select#gruppe").val();
		ajaxPost.Gruppe = $("select#gruppe").val();
		}
		window.location.hash = newHash;
		setCookie('hash',window.location.hash.replace(/^#!/,''),100);
		
		// VLV neu laden
		$.post('ajax.php',ajaxPost,function(data,status) {
			if(typeof data != "undefined") {
				var sgang = $("select#studiengang").val();
				var semester = $("select#semester").val();
				var group = $("select#gruppe").val();
				
				$('div#vlv_plan').html("");
				for(var vlv in data) {
					$('div#vlv_plan').append(makeVlvContainer(data[vlv],vlv));
				}
			}
		});
	}
	$(function() {
	
		createVlvOwns();
		$("select#studiengang").change(generate_semester);
		$("select#semester").change(generate_group);
		$("select#gruppe").change(makeVLV);
		$("button#vlvIcal").click(function(){
			if(confirm("Wollen Sie ihren iCal-Schlüssel wirklich erneuern?")) {
				var ajaxPost = {};
				ajaxPost.type = "changeICal";
				$.post('ajax.php',ajaxPost,function(data,status) { alert("Operation beendet.");});
			}
		});
		generate_sgang();
		if(window.location.hash.length>0) {
			var newHash = window.location.hash.replace(/^#!/,'').split("|");
			if(newHash.length==1) {
				newHash = unescape(unescape(window.location.search.substring(1).replace(/^_escaped_fragment_=/,''))).split('|');
				window.location.hash = unescape(unescape(window.location.search.substring(1).replace(/^_escaped_fragment_=/,'')));
			}
			if(newHash.length==1) {
				newHash = getCookie('hash').split('|');
				window.location.hash = getCookie('hash');
			}
			if(newHash.length==1) {
				newHash = "";
			}
			setCookie('hash',window.location.hash.replace(/^#!/,''),100);
			$("select#studiengang").val(newHash[0]).change();
			if(typeof newHash[1] != "undefined") {
				$("select#semester").val(newHash[1]).change();
				if(typeof newHash[2] != "undefined") {
					$("select#gruppe").val(newHash[2]).change();
				}
			}
		}<?php if(isset($_SESSION['studiengang'])) {?>
		else {
			$("select#studiengang").val('<?php echo $_SESSION['studiengang'];?>').change();
			<?php if(isset($_SESSION['semester'])) {?>$("select#semester").val('<?php echo $_SESSION['semester'];?>').change();
			<?php if(isset($_SESSION['gruppe'])) {?>$("select#gruppe").val('<?php echo $_SESSION['gruppe'];?>').change();<?php }}?>
		}<?php }?>
	});
</script>
<?php 
$main->getFooter();
?>