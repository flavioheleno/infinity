(function ($) {
	showMessage = function(title, text, type) {
		var footer = '<a href="javascript:history.back(-1);">Volte para a p&aacute;gina anterior</a> ou <a href="#" id="aClose">feche esta mensagem</a>.';
		showDialog(title, text, footer, type);
		$('#aClose').click(function () {
			hideDialog();
		});
	}
	
	showConfirmation = function(title, text, yes, no, action, hidden) {
		var footer = '					<form action="'+action+'" method="post" enctype="application/x-www-form-urlencoded" id="frmConfirmation">'+"\n";
		footer += '						<input type="submit" id="btnYes" name="btnYes" value="'+yes+'" /\>'+"\n";
		footer += '						<input type="button" id="btnNo" name="btnNo" value="'+no+'" /\>'+"\n";
		for (var i = 0; i < hidden.length; i++)
			if (hidden[i].length == 2)
				footer += '						<input type="hidden" name="'+hidden[i][0]+'" value="'+hidden[i][1]+'" /\>'+"\n";
		footer += '					</form>'+"\n";
		showDialog(title, text, footer, 'alert');
		$('#btnNo').click(function () {
			hideDialog();
		});
	}
	
	showDialog = function(title, text, footer, type) {
		var v = '		<div id="mcontainer">'+"\n";
		v += '			<div id="mcontent" class="'+type+'">'+"\n";
		v += '				<div id="mtitle">'+"\n";
		v += '					'+title+"\n";
		v += '				</div>'+"\n";
		v += '				<div id="mtext">'+"\n";
		v += '					'+text+"\n";
		v += '				</div>'+"\n";
		v += '				<div id="mfooter">'+"\n";
		v += '					'+footer+"\n";
		v += '				</div>'+"\n";
		v += '			</div>'+"\n";
		v += '		</div>'+"\n";
		$(v).appendTo('body');
	}
	
	hideDialog = function() {
		if ($('#mcontainer').length)
			$('#mcontainer').remove();
	}

})(jQuery);
