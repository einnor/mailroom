//if (getCookie('theuser')=='GUEST' ||  getCookie('theuser') ==''){ window.location.replace('../sign_in.html'); }
readonload=-1;
$(document).ajaxStart(function () { $('.progress').show();}); $(document).ajaxStop(function () { $('.progress').hide(); })
$(document).ready(function(e) {
	href=window.location.href; msplit = href.split('?'); if (msplit.length>1){readonload=msplit[1]}
	$('.signout').click(function(){ window.location.replace('../sign_in.html'); })
	$('#theuser').html( getCookie('theuser') )
})
function callback() { setTimeout(function() { $( "#effect" ).removeAttr( "style" ).hide().fadeIn(); }, 1000 ); };
function newtoken(mvalue){
	mvalue=''+mvalue+'';
	var l = mvalue.length;
	var i = randomIntFromInterval(3,9) 
	var s = randomIntFromInterval(1,7)
	var str='';
	for (z=0;z<l;z++){ str+=mvalue.charAt(z)+''+randomStrwithLength(i); }
	str=l+''+randomStrwithLength(2)+''+s+''+randomStrwithLength(7).splice(s,0,str)+''+i+''+randomStrwithLength(3)
	return str
}
function formatNumber(num,dec,thou,pnt,curr1,curr2,n1,n2) {var x = Math.round(num * Math.pow(10,dec));if (x >= 0) n1=n2='';var y = (''+Math.abs(x)).split('');var z = y.length - dec; if (z<0) z--; for(var i = z; i < 0; i++) y.unshift('0'); if (z<0) z = 1; y.splice(z, 0, pnt); if(y[0] == pnt) y.unshift('0'); while (z > 3) {z-=3; y.splice(z,0,thou);}var r = curr1+n1+y.join('')+n2+curr2;return r;}
function randomIntFromInterval(min,max){  return Math.floor(Math.random()*(max-min+1)+min); }
function randomStrwithLength(limit){
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ9876543210abcdefghijklmnopqrstuvwxyz0123456789";
    for( var i=0; i < limit; i++ ){ text += possible.charAt(Math.floor(Math.random() * possible.length));}
	return text;
}
var removeElements = function(text, selector) {//REmove specific tags
    var wrapped = $("<div>" + text + "</div>");
    wrapped.find(selector).remove();
    return wrapped.html();
}
function extractEmails (text) { return text.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi); } // EXTRACT an Email from a a string
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
function checkCookie() {
    var user = getCookie("username");
    if (user != "") {
        alert("Welcome again " + user);
    } else {
        user = prompt("Please enter your name:", "");
        if (user != "" && user != null) {
            setCookie("username", user, 365);
        }
    }
}
function delete_cookie( name ) {
  document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}
function FORMAT_NUM(nStr){
	nStr += ''; x = nStr.split('.');x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '.00';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) { x1 = x1.replace(rgx, '$1' + ',' + '$2'); }
	return x1 + x2.substr(0,3);
	
}
function testAjax(type,datatosend,url,contenttype) {
  return $.ajax({
		type:		type,
		url:		url,
		async:		false,
		dataType:	"json",
		contentType:contenttype,
		data:		datatosend
	})
}
$.fn.serializeObject = function() {
	var o = {};
	var a = this.serializeArray();
	$.each(a, function() {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};
function monkeyPatchAutocomplete() {
	var oldFn = $.ui.autocomplete.prototype._renderItem;
	$.ui.autocomplete.prototype._renderItem = function( ul, item) {
		mynew = item.label
		$.each(this.term.split(' '),function(k,v){
		  var re = new RegExp(v,'i')
		  mynew = mynew.replace(re,'<span style="background-color:#FCFAF2; border:1px solid #FCEFA1">' + v + '</span>');
		})
		return $( "<li></li>" ).data( "item.autocomplete", item ).append( "<a>" + mynew + "</a>" ).appendTo( ul );
	};
}