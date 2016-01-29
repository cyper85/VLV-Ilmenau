var editable = function() {
	var uneditable = function() {
		object = $(this);
		if(!object.hasClass('editableActive')) {
			object = $(this).parents(".editableActive");
		}
		object.unbind("dblclick")
			.html(object.data("editable-oldData"))
			.dblclick(editable)
			.removeClass('editableActive');
	}
	$(".editableActive").trigger("uneditable");
	
	$(this).data("editable-oldData",$(this).html()).addClass("editableActive");
	$(this).unbind("dblclick").on("uneditable",uneditable);
	
	var form = $("<form />").append($("<div/>").hide().addClass("editableError"));
	switch($(this).data("editable-type")) {
		case 'password':
			form.append(
				$("<div/>")
					.append($("<label/>").attr('for',$(this).attr('id')+"-password").html("Passwort"))
					.append($("<input/>")
						.attr('type','password')
						.attr('maxLength','32')
						.attr('required','required')
						.attr('id',$(this).attr('id')+"-password")
						.attr('name',$(this).attr('id')+"-password")
						.change(function(){
							$(this)[0].setCustomValidity("");
							if($(this).val().length < 6) {
								$(".editableError").text("Das Passwort ist zu kurz (min. 6 Zeichen).").show();
								$(this)[0].setCustomValidity('Das Passwort ist zu kurz (min. 6 Zeichen).');
							}
							else if($(this).val().length > 32) {
								$(".editableError").text("Das Passwort ist zu lang (max. 32 Zeichen).").show();
								$(this)[0].setCustomValidity('Das Passwort ist zu kurz (max. 32 Zeichen).');
							}
							else if(
								!$(this).val().match(/[a-z]+/g) ||
								!$(this).val().match(/[A-Z]+/g) ||
								!$(this).val().match(/[0-9]+/g)
								
							) {
								$(".editableError").text("Das Passwort muss Ziffern, Groß- und Kleinbuchstaben beinhalten. Sonderzeichen sind erlaubt.").show();
								$(this)[0].setCustomValidity('Das Passwort muss Ziffern, Groß- und Kleinbuchstaben beinhalten. Sonderzeichen sind erlaubt.');
							}
						})
					)
				   )
			.append(
				$("<div/>")
					.append($("<label/>").attr('for',$(this).attr('id')+"-password-verify").html("Passwort Wiederholen"))
					.append($("<input/>")
						.attr('type','password')
						.attr('maxLength','32')
						.attr('required','required')
						.attr('id',$(this).attr('id')+"-password-verify")
						.attr('name',$(this).attr('id')+"-password-verify")
						.change(function(){
							var id = $(this).attr('id').replace(/-verify$/,"");
							$(this)[0].setCustomValidity("");
							if($(this).val() != $("#"+id).val()) {
								$(".editableError").text("Die Passwörter stimmen nicht überein.").show();
								$(this)[0].setCustomValidity('Die Passwörter stimmen nicht überein.');
							}
						})
					)
			);
			break;
		default:
			return;
	}
	form.append(
		$('<input />').attr("type","submit").val("speichern")
	).append(
		$('<input />').attr("type","reset").val("abbrechen").click(uneditable)
	).submit(function(event){
		var id = $(this).parent().attr('id');
		
		var data = $(this).serializeArray();
		data.push({name: 'type', value: $(this).parent().data('editable-ajax-type')});
		
		// Post-Section
		$.post("ajax.php", data,function(data,status) {
			if(typeof data.error != "undefined") {
				$(".editableError").text(data.error).show();
			}
			else if(typeof data.content != "undefined") {
				$(".editableActive").unbind("dblclick")
					.html(data.content)
					.append($("<span />").css('padding-left','5px').addClass('fa').addClass('fa-pencil-square-o'))
					.removeData("editable-oldData")
					.dblclick(editable)
					.removeClass('editableActive');
			}
			else {
				alert("Server antwortet nicht. Änderung nicht möglich!");
			}
		});
		event.preventDefault();
	});
	$(this).html(form);
}

// Bind an vorgefertigte Elemente
$(function(){
	$(".editable").dblclick(editable);
});