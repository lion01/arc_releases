var theForm;
var theData;

window.addEvent('domready', function()
{
	theForm = document.getElementById( 'osubjselection' );
	// top row disabilities
	theForm.ocoursebtngen.disabled = true;
	theForm.ocoursebtnfwd.disabled = true;
	theForm.ogroupbtnback.disabled = true;
	theForm.ogroupbtnfwd.disabled = true;
	theForm.opupilbtnback.disabled = true;
	theForm.elements["ogroup[]"].disabled = true;
	theForm.elements["opupil[]"].disabled = true;
	// second row disabilities
	theForm.otutorbtngen.disabled = true;
	theForm.otutorbtnfwd.disabled = true;
	theForm.omemberbtnback.disabled = true;
	theForm.omemberbtnfwd.disabled = true;
	theForm.ocourse2btnback.disabled = true;
	theForm.elements["omember[]"].disabled = true;
	theForm.elements["ocourse2[]"].disabled = true;
	
	theForm.onsubmit = function()
	{
		theForm.elements["ocourse[]"].disabled = false;
		theForm.elements["ogroup[]"].disabled = false;
		theForm.elements["opupil[]"].disabled = false;
		theForm.elements["otutor[]"].disabled = false;
		theForm.elements["omember[]"].disabled = false;
		theForm.elements["ocourse2[]"].disabled = false;
	}
});

function populate( selObj, resp )
{
	var outDiv = document.getElementById( 'debug' );
	if( outDiv != null ) {
		outDiv.innerHTML = '<h4>response:</h4>' + resp;
	}
	decoded = Json.evaluate( resp, true );
	
	selObj.options.length = 0;
	for( var i = 0; i < decoded.length; i ++ ) {
		selObj.options[i] = new Option( decoded[i].text, decoded[i].value );
	}
}

function selectChange( selObj )
{
	isDisabled = true;
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if (selObj.options[i].selected) {
			isDisabled = false;
			i = len;
		}
	}
	
	if( selObj.id == 'ocourse') {
		subBtn = document.getElementById( 'ocoursebtngen' );
		subBtn.disabled = isDisabled;
	}
	else if(selObj.id == 'otutor') {
		subBtn = document.getElementById( 'otutorbtngen' );
		subBtn.disabled = isDisabled;
	}
	
	eval('var tmp = theForm.' + selObj.id + 'btnfwd;');
	if( tmp != undefined ) {
		tmp.disabled = isDisabled;
	}
}

function goForward( from, to )
{
	eval('var fromObj = theForm.' + from + ';');
	eval('var toObj = theForm.' + to + ';');
	toObj.options[0] = new Option( 'Working, please wait' );
	
	var opts = new Array();
	for(var i = 0; i < fromObj.options.length; i++) {
		if( fromObj.options[i].selected ) {
			opts[opts.length] = fromObj.options[i].value;
		}
	}
	var ser = new PHP_Serializer();
	var encoded = ser.serialize(opts);
	var postStr = 'fieldname=' + fromObj.id + '&' + fromObj.id + '=' + encoded;
	
	var url = $('url').value;
	var theAjax = new Ajax(url, {
		method: 'post',
		postBody: postStr
	});
	theAjax.addEvent('onSuccess', function(resp) {
		populate( toObj, resp );
	});
	theAjax.request();
	
	if( toObj.options.length > 0 ) {
		eval('try{ theForm.' + from + 'btnfwd.disabled = true;  } catch(err) {}');
		eval('try{ theForm.' + from + 'btnback.disabled = true; } catch(err) {}');
		eval('try{ theForm.' + to + 'btnfwd.disabled = false;   } catch(err) {}');
		eval('try{ theForm.' + to + 'btnback.disabled = false;  } catch(err) {}');
		
		eval('try{ theForm.' + from + '.disabled = true; } catch(err) {}');
		eval('try{ theForm.' + to + '.disabled = false;  } catch(err) {}');
	}
}

function goBack( from, to )
{
	// remove options from abandonned list
	eval('var fromObj = theForm.' + from + ';');
	fromObj.length = 0;
	
	eval('try{ theForm.' + from + 'btnfwd.disabled = true;   } catch(err) {}');
	eval('try{ theForm.' + from + 'btnback.disabled = true; } catch(err) {}');
	eval('try{ theForm.' + to + 'btnfwd.disabled = false;    } catch(err) {}');
	eval('try{ theForm.' + to + 'btnback.disabled = false;    } catch(err) {}');
	eval('try{ theForm.' + from + '.disabled = true;    } catch(err) {}');
	eval('try{ theForm.' + to + '.disabled = false;    } catch(err) {}');
}