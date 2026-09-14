Type.registerNamespace('Roblox.Thumbs');
Roblox.Thumbs.Place=function() {
Roblox.Thumbs.Place.initializeBase(this);
this._timeout = 0;
this._userContext = null;
this._succeeded = null;
this._failed = null;
}
Roblox.Thumbs.Place.prototype={
RequestThumbnail:function(placeID,width,height,imageFormat,succeededCallback, failedCallback, userContext) {
return this._invoke(Roblox.Thumbs.Place.get_path(), 'RequestThumbnail',false,{placeID:placeID,width:width,height:height,imageFormat:imageFormat},succeededCallback,failedCallback,userContext); }}
Roblox.Thumbs.Place.registerClass('Roblox.Thumbs.Place',Sys.Net.WebServiceProxy);
Roblox.Thumbs.Place._staticInstance = new Roblox.Thumbs.Place();
Roblox.Thumbs.Place.set_path = function(value) { Roblox.Thumbs.Place._staticInstance._path = value; }
Roblox.Thumbs.Place.get_path = function() { return Roblox.Thumbs.Place._staticInstance._path; }
Roblox.Thumbs.Place.set_timeout = function(value) { Roblox.Thumbs.Place._staticInstance._timeout = value; }
Roblox.Thumbs.Place.get_timeout = function() { return Roblox.Thumbs.Place._staticInstance._timeout; }
Roblox.Thumbs.Place.set_defaultUserContext = function(value) { Roblox.Thumbs.Place._staticInstance._userContext = value; }
Roblox.Thumbs.Place.get_defaultUserContext = function() { return Roblox.Thumbs.Place._staticInstance._userContext; }
Roblox.Thumbs.Place.set_defaultSucceededCallback = function(value) { Roblox.Thumbs.Place._staticInstance._succeeded = value; }
Roblox.Thumbs.Place.get_defaultSucceededCallback = function() { return Roblox.Thumbs.Place._staticInstance._succeeded; }
Roblox.Thumbs.Place.set_defaultFailedCallback = function(value) { Roblox.Thumbs.Place._staticInstance._failed = value; }
Roblox.Thumbs.Place.get_defaultFailedCallback = function() { return Roblox.Thumbs.Place._staticInstance._failed; }
Roblox.Thumbs.Place.set_path("/Thumbs/Place.asmx");
Roblox.Thumbs.Place.RequestThumbnail= function(placeID,width,height,imageFormat,onSuccess,onFailed,userContext) {Roblox.Thumbs.Place._staticInstance.RequestThumbnail(placeID,width,height,imageFormat,onSuccess,onFailed,userContext); }
var gtc = Sys.Net.WebServiceProxy._generateTypedConstructor;
if (typeof(Roblox.Thumbs.ScriptThumbResult) === 'undefined') {
Roblox.Thumbs.ScriptThumbResult=gtc("Roblox.Thumbs.ScriptThumbResult");
Roblox.Thumbs.ScriptThumbResult.registerClass('Roblox.Thumbs.ScriptThumbResult');
}

/*
     FILE ARCHIVED ON 22:20:30 Nov 25, 2007 AND RETRIEVED FROM THE
     INTERNET ARCHIVE ON 17:08:07 Dec 10, 2017.
     JAVASCRIPT APPENDED BY WAYBACK MACHINE, COPYRIGHT INTERNET ARCHIVE.

     ALL OTHER CONTENT MAY ALSO BE PROTECTED BY COPYRIGHT (17 U.S.C.
     SECTION 108(a)(3)).
*/