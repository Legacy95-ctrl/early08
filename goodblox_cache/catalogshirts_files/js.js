Type.registerNamespace('Roblox.Thumbs');
Roblox.Thumbs.Asset=function() {
Roblox.Thumbs.Asset.initializeBase(this);
this._timeout = 0;
this._userContext = null;
this._succeeded = null;
this._failed = null;
}
Roblox.Thumbs.Asset.prototype={
_get_path:function() {
 var p = this.get_path();
 if (p) return p;
 else return Roblox.Thumbs.Asset._staticInstance.get_path();},
RequestThumbnail:function(assetVersionId,width,height,imageFormat,succeededCallback, failedCallback, userContext) {
return this._invoke(this._get_path(), 'RequestThumbnail',false,{assetVersionId:assetVersionId,width:width,height:height,imageFormat:imageFormat},succeededCallback,failedCallback,userContext); }}
Roblox.Thumbs.Asset.registerClass('Roblox.Thumbs.Asset',Sys.Net.WebServiceProxy);
Roblox.Thumbs.Asset._staticInstance = new Roblox.Thumbs.Asset();
Roblox.Thumbs.Asset.set_path = function(value) { Roblox.Thumbs.Asset._staticInstance.set_path(value); }
Roblox.Thumbs.Asset.get_path = function() { return Roblox.Thumbs.Asset._staticInstance.get_path(); }
Roblox.Thumbs.Asset.set_timeout = function(value) { Roblox.Thumbs.Asset._staticInstance.set_timeout(value); }
Roblox.Thumbs.Asset.get_timeout = function() { return Roblox.Thumbs.Asset._staticInstance.get_timeout(); }
Roblox.Thumbs.Asset.set_defaultUserContext = function(value) { Roblox.Thumbs.Asset._staticInstance.set_defaultUserContext(value); }
Roblox.Thumbs.Asset.get_defaultUserContext = function() { return Roblox.Thumbs.Asset._staticInstance.get_defaultUserContext(); }
Roblox.Thumbs.Asset.set_defaultSucceededCallback = function(value) { Roblox.Thumbs.Asset._staticInstance.set_defaultSucceededCallback(value); }
Roblox.Thumbs.Asset.get_defaultSucceededCallback = function() { return Roblox.Thumbs.Asset._staticInstance.get_defaultSucceededCallback(); }
Roblox.Thumbs.Asset.set_defaultFailedCallback = function(value) { Roblox.Thumbs.Asset._staticInstance.set_defaultFailedCallback(value); }
Roblox.Thumbs.Asset.get_defaultFailedCallback = function() { return Roblox.Thumbs.Asset._staticInstance.get_defaultFailedCallback(); }
Roblox.Thumbs.Asset.set_path("/Thumbs/Asset.asmx");
Roblox.Thumbs.Asset.RequestThumbnail= function(assetVersionId,width,height,imageFormat,onSuccess,onFailed,userContext) {Roblox.Thumbs.Asset._staticInstance.RequestThumbnail(assetVersionId,width,height,imageFormat,onSuccess,onFailed,userContext); }
var gtc = Sys.Net.WebServiceProxy._generateTypedConstructor;
if (typeof(Roblox.Thumbs.ScriptThumbResult) === 'undefined') {
Roblox.Thumbs.ScriptThumbResult=gtc("Roblox.Thumbs.ScriptThumbResult");
Roblox.Thumbs.ScriptThumbResult.registerClass('Roblox.Thumbs.ScriptThumbResult');
}

/*
     FILE ARCHIVED ON 06:42:26 Sep 08, 2008 AND RETRIEVED FROM THE
     INTERNET ARCHIVE ON 11:56:46 Dec 27, 2017.
     JAVASCRIPT APPENDED BY WAYBACK MACHINE, COPYRIGHT INTERNET ARCHIVE.

     ALL OTHER CONTENT MAY ALSO BE PROTECTED BY COPYRIGHT (17 U.S.C.
     SECTION 108(a)(3)).
*/