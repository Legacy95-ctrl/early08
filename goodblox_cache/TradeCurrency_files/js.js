var EconomyServices=function() {
EconomyServices.initializeBase(this);
this._timeout = 0;
this._userContext = null;
this._succeeded = null;
this._failed = null;
}
EconomyServices.prototype={
_get_path:function() {
 var p = this.get_path();
 if (p) return p;
 else return EconomyServices._staticInstance.get_path();},
GetEstimatedTradeReturnForTickets:function(ticketsToTrade,succeededCallback, failedCallback, userContext) {
return this._invoke(this._get_path(), 'GetEstimatedTradeReturnForTickets',false,{ticketsToTrade:ticketsToTrade},succeededCallback,failedCallback,userContext); },
GetEstimatedTradeReturnForRobux:function(robuxToTrade,succeededCallback, failedCallback, userContext) {
return this._invoke(this._get_path(), 'GetEstimatedTradeReturnForRobux',false,{robuxToTrade:robuxToTrade},succeededCallback,failedCallback,userContext); }}
EconomyServices.registerClass('EconomyServices',Sys.Net.WebServiceProxy);
EconomyServices._staticInstance = new EconomyServices();
EconomyServices.set_path = function(value) { EconomyServices._staticInstance.set_path(value); }
EconomyServices.get_path = function() { return EconomyServices._staticInstance.get_path(); }
EconomyServices.set_timeout = function(value) { EconomyServices._staticInstance.set_timeout(value); }
EconomyServices.get_timeout = function() { return EconomyServices._staticInstance.get_timeout(); }
EconomyServices.set_defaultUserContext = function(value) { EconomyServices._staticInstance.set_defaultUserContext(value); }
EconomyServices.get_defaultUserContext = function() { return EconomyServices._staticInstance.get_defaultUserContext(); }
EconomyServices.set_defaultSucceededCallback = function(value) { EconomyServices._staticInstance.set_defaultSucceededCallback(value); }
EconomyServices.get_defaultSucceededCallback = function() { return EconomyServices._staticInstance.get_defaultSucceededCallback(); }
EconomyServices.set_defaultFailedCallback = function(value) { EconomyServices._staticInstance.set_defaultFailedCallback(value); }
EconomyServices.get_defaultFailedCallback = function() { return EconomyServices._staticInstance.get_defaultFailedCallback(); }
EconomyServices.set_path("/Marketplace/EconomyServices.asmx");
EconomyServices.GetEstimatedTradeReturnForTickets= function(ticketsToTrade,onSuccess,onFailed,userContext) {EconomyServices._staticInstance.GetEstimatedTradeReturnForTickets(ticketsToTrade,onSuccess,onFailed,userContext); }
EconomyServices.GetEstimatedTradeReturnForRobux= function(robuxToTrade,onSuccess,onFailed,userContext) {EconomyServices._staticInstance.GetEstimatedTradeReturnForRobux(robuxToTrade,onSuccess,onFailed,userContext); }

/*
     FILE ARCHIVED ON 03:44:06 Nov 12, 2010 AND RETRIEVED FROM THE
     INTERNET ARCHIVE ON 17:34:47 Mar 15, 2018.
     JAVASCRIPT APPENDED BY WAYBACK MACHINE, COPYRIGHT INTERNET ARCHIVE.

     ALL OTHER CONTENT MAY ALSO BE PROTECTED BY COPYRIGHT (17 U.S.C.
     SECTION 108(a)(3)).
*/