Type.registerNamespace('Roblox.PlaceLauncher');
Roblox.PlaceLauncher.Service=function() {
Roblox.PlaceLauncher.Service.initializeBase(this);
this._timeout = 0;
this._userContext = null;
this._succeeded = null;
this._failed = null;
}
Roblox.PlaceLauncher.Service.prototype={
RequestGame:function(placeID,succeededCallback, failedCallback, userContext) {
return this._invoke(Roblox.PlaceLauncher.Service.get_path(), 'RequestGame',false,{placeID:placeID},succeededCallback,failedCallback,userContext); },
CheckGameJobStatus:function(jobID,waitTime,succeededCallback, failedCallback, userContext) {
return this._invoke(Roblox.PlaceLauncher.Service.get_path(), 'CheckGameJobStatus',false,{jobID:jobID,waitTime:waitTime},succeededCallback,failedCallback,userContext); }}
Roblox.PlaceLauncher.Service.registerClass('Roblox.PlaceLauncher.Service',Sys.Net.WebServiceProxy);
Roblox.PlaceLauncher.Service._staticInstance = new Roblox.PlaceLauncher.Service();
Roblox.PlaceLauncher.Service.set_path = function(value) { Roblox.PlaceLauncher.Service._staticInstance._path = value; }
Roblox.PlaceLauncher.Service.get_path = function() { return Roblox.PlaceLauncher.Service._staticInstance._path; }
Roblox.PlaceLauncher.Service.set_timeout = function(value) { Roblox.PlaceLauncher.Service._staticInstance._timeout = value; }
Roblox.PlaceLauncher.Service.get_timeout = function() { return Roblox.PlaceLauncher.Service._staticInstance._timeout; }
Roblox.PlaceLauncher.Service.set_defaultUserContext = function(value) { Roblox.PlaceLauncher.Service._staticInstance._userContext = value; }
Roblox.PlaceLauncher.Service.get_defaultUserContext = function() { return Roblox.PlaceLauncher.Service._staticInstance._userContext; }
Roblox.PlaceLauncher.Service.set_defaultSucceededCallback = function(value) { Roblox.PlaceLauncher.Service._staticInstance._succeeded = value; }
Roblox.PlaceLauncher.Service.get_defaultSucceededCallback = function() { return Roblox.PlaceLauncher.Service._staticInstance._succeeded; }
Roblox.PlaceLauncher.Service.set_defaultFailedCallback = function(value) { Roblox.PlaceLauncher.Service._staticInstance._failed = value; }
Roblox.PlaceLauncher.Service.get_defaultFailedCallback = function() { return Roblox.PlaceLauncher.Service._staticInstance._failed; }
Roblox.PlaceLauncher.Service.set_path("/Game/PlaceLauncher.asmx");
Roblox.PlaceLauncher.Service.RequestGame= function(placeID,onSuccess,onFailed,userContext) {Roblox.PlaceLauncher.Service._staticInstance.RequestGame(placeID,onSuccess,onFailed,userContext); }
Roblox.PlaceLauncher.Service.CheckGameJobStatus= function(jobID,waitTime,onSuccess,onFailed,userContext) {Roblox.PlaceLauncher.Service._staticInstance.CheckGameJobStatus(jobID,waitTime,onSuccess,onFailed,userContext); }
var gtc = Sys.Net.WebServiceProxy._generateTypedConstructor;
if (typeof(Roblox.PlaceLauncher.Result) === 'undefined') {
Roblox.PlaceLauncher.Result=gtc("Roblox.PlaceLauncher.Result");
Roblox.PlaceLauncher.Result.registerClass('Roblox.PlaceLauncher.Result');
}

/*
     FILE ARCHIVED ON 22:19:53 Nov 25, 2007 AND RETRIEVED FROM THE
     INTERNET ARCHIVE ON 17:08:06 Dec 10, 2017.
     JAVASCRIPT APPENDED BY WAYBACK MACHINE, COPYRIGHT INTERNET ARCHIVE.

     ALL OTHER CONTENT MAY ALSO BE PROTECTED BY COPYRIGHT (17 U.S.C.
     SECTION 108(a)(3)).
*/