$(document).ready(mapHandlers);

function mapHandlers()
{
	$(".getFocus").focus(
		function(e){
			$(this).select();
		}
	);

	$(".translationEditBox").keypress(
		function(e){ 
			var input = $(this);
			if (e.which == 32 || (65 <= e.which && e.which <= 65 + 25)
						|| (97 <= e.which && e.which <= 97 + 25)
						|| e.which == 8)
			{
				input.addClass("dirty");

				var messageArea = input.parent().find(".message");
				if (messageArea.length == 0)
				{
					input.parent().append("<div class='message'>Hit enter to save changes.</div>");
					input.parent().find(".message").hide().fadeIn(1000);
				}
			}
			else if (e.which == 13)
			{
				var variableId = input.attr('name');
				
				input.removeClass("dirty");
				input.blur();
				input.attr("disabled","disabled");
				input.parent().find(".message").html("Saving changes...");
				$.post(window.location + "&a=update", { id: variableId, content: input.attr('value') }, function(data) {
					var message = input.parent().find(".message");
					message.html("Saved changes!").fadeOut(500,function(){message.remove();});
					
					updateRow(variableId);
				});
			}
		}
	);
	
	updateFilters();
}

function updateFilters()
{
    if ($("#showAccepted").is(":checked"))
        $(".accepted").parent().show();
    else
        $(".accepted").parent().hide();
    
    totalCount = $(".translationRow").size();
    visibleCount = $(".translationRow:visible").size();
        
    $("#filterInfo").html("Showing <b>" + visibleCount + " of " + totalCount + " translations</b>.");
}

function updateRow(variableId)
{
	$("#r"+variableId).load(window.location + "&a=redraw&id=" + variableId);
}

function edit(translationId,variableId,newEntry)
{
	var textField = $("#t" + translationId);
	var text = $($("#t" + translationId).children()[0]);
	
	$("#edit"+variableId).remove();
	
	textField.parent().append("<textarea class='translationEditBox' id='edit"+variableId+"' name='"+variableId+"'>"+text.html()+"</textarea>");
	
	if (!newEntry)
	{
		textField.remove();
		//textField.html("<textarea class='translationEditBox' name='"+variableId+"'>"+text.html()+"</textarea>");
	}
	
	mapHandlers();
	
	return false;
}

function vote(translationId,variableId, positive)
{
	$.get(window.location + "&a=vote&id="+variableId+"&tid="+translationId+"&weight=" + (positive?1:-1),
		function(data)
		{
			updateRow(variableId);
		}
	);
	return false;
}
