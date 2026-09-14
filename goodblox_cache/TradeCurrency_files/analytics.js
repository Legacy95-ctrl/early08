/* eslint-disable no-var, semi, prefer-arrow-callback */

/**
 * Collection of methods for sending analytics events to Archive.org’s analytics server.
 *
 * These events are used for internal stats and sent (in anonymized form) to Google Analytics.
 *
 * @type {Object}
 */
window.archive_analytics = (function defineArchiveAnalytics() {
  var archive_analytics;
  var startTime = new Date();

  /**
   * @return {Boolean}
   */
  function isPerformanceTimingApiSupported() {
    return 'performance' in window && 'timing' in window.performance;
  }

  /**
   * Determines how many milliseconds elapsed between the browser starting to parse the DOM and
   * the current time.
   *
   * Uses the Performance API or a fallback value if it’s not available.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/API/Performance_API
   *
   * @return {Number}
   */
  function getLoadTime() {
    var start;

    if (isPerformanceTimingApiSupported())
      start = window.performance.timing.domLoading;
    else
      start = startTime.getTime();

    return new Date().getTime() - start;
  }

  /**
   * Determines how many milliseconds elapsed between the user navigating to the page and
   * the current time.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/API/Performance_API
   *
   * @return {Number|null} null if the browser doesn’t support the Performance API
   */
  function getNavToDoneTime() {
    if (!isPerformanceTimingApiSupported())
      return null;

    return new Date().getTime() - window.performance.timing.navigationStart;
  }

  /**
   * Performs an arithmetic calculation on a string with a number and unit, while maintaining
   * the unit.
   *
   * @param {String} original value to modify, with a unit
   * @param {Function} doOperation accepts one Number parameter, returns a Number
   * @returns {String}
   */
  function computeWithUnit(original, doOperation) {
    var number = parseFloat(original, 10);
    var unit = original.replace(/(\d*\.\d+)|\d+/, '');

    return doOperation(number) + unit;
  }

  /**
   * Computes the default font size of the browser.
   *
   * @returns {String|null} computed font-size with units (typically pixels), null if it cannot be computed
   */
  function getDefaultFontSize() {
    var fontSizeStr;

    if (!('getComputedStyle' in window))
      return null;

    fontSizeStr = window.getComputedStyle(document.documentElement).fontSize;

    // Don't modify the value if tracking book reader.
    if (document.documentElement.classList.contains('BookReaderRoot'))
      return fontSizeStr;

    return computeWithUnit(fontSizeStr, function reverseBootstrapFontSize(number) {
      // Undo the 62.5% size applied in the Bootstrap CSS.
      return number * 1.6;
    });
  }

  archive_analytics = {
    values: {},

    // 2nd param: [optional] callback to invoke once ping to analytics server is done
    // 3rd param: [optional] logical truthy -- set to true/1 to add some archive.org site-specific values
    send_ping: function send_ping(values, onload_callback, augment_for_ao_site) {
      var img_src = "//analytics.archive.org/0.gif";

      var format_ping = function(values) {
        var ret = [];
        var count = 2;
        var version = 2;

        for (var data in values) {
          ret.push(encodeURIComponent(data) + "=" + encodeURIComponent(values[data]));
          count = count + 1;
        }

        ret.push('version=' + version);
        ret.push('count=' + count);
        return ret.join("&");
      };


      if (augment_for_ao_site  &&  !values['service'])
        values['service'] = 'ao_2';

      var string = format_ping(values);

      var loadtime_img = new Image(100,25);
      if (onload_callback  &&  typeof(onload_callback)=='function')
        loadtime_img.onload = onload_callback;
      loadtime_img.src = img_src + "?" + string;
    },

    send_scroll_fetch_event: function send_scroll_fetch_event(page) {
      var values = {
        service: 'ao_2',
        kind: 'event',
        ec: 'page_action',
        ea: 'scroll_fetch',
        el: location.pathname,
        ev: page, // int
        cache_bust: Math.random()
      };

      var loadTime = getLoadTime();
      var navToDoneTime = getNavToDoneTime();

      if (loadTime)
        values.loadtime = loadTime;
      if (navToDoneTime)
        values.nav_to_done_ms = navToDoneTime;

      archive_analytics.send_ping(values);
    },

    send_scroll_fetch_base_event: function send_scroll_fetch_base_event() {
      var values = {
        service: 'ao_2',
        kind: 'event',
        ec: 'page_action',
        ea: 'scroll_fetch_base',
        el: location.pathname,
        cache_bust: Math.random(),
      };

      var loadTime = getLoadTime();
      var navToDoneTime = getNavToDoneTime();

      if (loadTime)
        values.loadtime = loadTime;
      if (navToDoneTime)
        values.nav_to_done_ms = navToDoneTime;

      archive_analytics.send_ping(values);
    },

    /**
     * Because this sends page load data, it’s highly recommended you call it after the `load`
     * event has fired, to ensure it captures the widest array of performance data.
     *
     * @param {Object} options
     * @param {String} [options.mediaType]
     */
    send_pageview: function send_pageview(options) {
      var settings = options || {};

      var defaultFontSize;
      var loadTime = getLoadTime();
      var mediaType = settings.mediaType;
      var navToDoneTime = getNavToDoneTime();

      /**
       * @return {String}
       */
      function get_locale() {
        if (navigator) {
          if (navigator.language)
            return navigator.language;

          else if (navigator.browserLanguage)
            return navigator.browserLanguage;

          else if (navigator.systemLanguage)
            return navigator.systemLanguage;

          else if (navigator.userLanguage)
            return navigator.userLanguage;
        }
        return '';
      }

      defaultFontSize = getDefaultFontSize();

      // Set field values
      archive_analytics.values.kind     = 'pageview';
      archive_analytics.values.timediff = (new Date().getTimezoneOffset()/60)*(-1); // *timezone* diff from UTC
      archive_analytics.values.locale   = get_locale();
      archive_analytics.values.referrer = (document.referrer == '' ? '-' : document.referrer);

      if (loadTime)
        archive_analytics.values.loadtime = loadTime;

      if (navToDoneTime)
        archive_analytics.values.nav_to_done_ms = navToDoneTime;

      if (defaultFontSize)
        archive_analytics.values.ga_cd1 = defaultFontSize;

      if ('devicePixelRatio' in window)
        archive_analytics.values.ga_cd2 = window.devicePixelRatio;

      if (mediaType)
        archive_analytics.values.ga_cd3 = mediaType;

      archive_analytics.send_ping(archive_analytics.values);
    },

    /**
     * Attaches an event listener that fires the pageview event when the page has fully loaded.
     *
     * @param {Object} options see archive_analytics.send_pageview options
     */
    send_pageview_on_load: function send_pageview_on_load(options) {
      window.addEventListener('load', function send_pageview_with_options() {
        archive_analytics.send_pageview(options);
      });
    },

    get_data_packets: function get_data_packets() {
      return [archive_analytics.values];
    },
  };

  return archive_analytics;
}());
