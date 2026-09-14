(function(){
var b=null;if(window.a==b)window.a={};window.GS_googleAddAdSenseService=function(a){return d.addService(d.ADSENSE,a)};window.GS_googleEnableAllServices=function(){d.enableAll()};window.GS_googleResetAllServices=function(){window.a={}};window.GS_googleGetIdsForAdSenseService=function(){var a=d.ADSENSE;return d.getIdsForService(a)};window.GS_googleFindService=function(a){return d.findService(a)};function d(a){this.f=a;this.b=[]}d.ADSENSE="adsense";d.ANALYTICS="urchin";d.UNKNOWN="unknown";
d.prototype.toString=function(){var a="["+this.f+" ids: ";for(var c=0;c<this.b.length;c++){if(c>0)a+=",";a+=this.b[c]}a+="]";return a};d.prototype.d=function(){return this.b.join()};d.isValidId=function(a){return a!=b&&typeof a=="string"&&a.length>0};d.newInstance=function(a){return a==d.ADSENSE?new f:new g};d.addService=function(a,c){if(!this.isValidId(c))return b;if(a==b)return b;var e=window.a[a];if(e==b){e=d.newInstance(a);window.a[a]=e}e.c(c);return e};
d.prototype.c=function(a){for(var c=0;c<this.b.length;c++)if(a==this.b[c])return;this.b[this.b.length]=a};d.enableAll=function(){for(var a in window.a){var c=window.a[a];if(typeof c=="function")continue;c.enable()}};d.getCount=function(){var a=0;for(var c in window.a){var e=window.a[c];if(typeof e=="function")continue;a++}return a};d.toString=function(){var a=[];for(var c in window.a){var e=window.a[c];if(typeof e=="function")continue;a.push("Service type="+c);a.push("value="+e.toString())}return a.join()};
d.findService=function(a){var c=a==b?b:window.a[a];return c};d.getIdsForService=function(a){var c=a==b?b:window.a[a];return c==b?"":c.d()};function g(){this.superclass=d;this.superclass(d.UNKNOWN)}g.prototype=new d(d.UNKNOWN);g.prototype.enable=function(){};function f(){this.superclass=d;this.superclass(d.ADSENSE);this.e=false}f.prototype=new d(d.ADSENSE);
f.prototype.enable=function(){if(this.e)return;document.write("<script src='https://web.archive.org/web/20081205003306/http://partner.googleadservices.com/gampad/google_ads.js'><\/script>");this.e=true};
})()

/*
     FILE ARCHIVED ON 00:33:06 Dec 05, 2008 AND RETRIEVED FROM THE
     INTERNET ARCHIVE ON 17:34:52 Mar 15, 2018.
     JAVASCRIPT APPENDED BY WAYBACK MACHINE, COPYRIGHT INTERNET ARCHIVE.

     ALL OTHER CONTENT MAY ALSO BE PROTECTED BY COPYRIGHT (17 U.S.C.
     SECTION 108(a)(3)).
*/