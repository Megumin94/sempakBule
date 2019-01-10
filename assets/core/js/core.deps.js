
/* core.deps.js */

(function($, Core) {

var loader;
var loaded = {};
var loading = {};
var events = Core.EventEmitter();

if (Core.ie8) {
  
  loader = function(scripts, callback) {
    var cb, load = [].concat(scripts);
    $.getScript(load.shift(), cb = function() {
      if (load.length === 0) {
        callback();
      } else {
        loader(load, cb);
      }
    });
  }

} else {
  
  loader = function(scripts, callback) {
    head.js.apply(null, scripts.concat(callback));
  }
  
}

Core.deps = {
  
  load: function(dep, callback) {
    if (dep instanceof Array) { var cb;
      this.load(dep.shift(), cb = function() {
        if (dep.length === 0) {
          callback();
        } else {
          Core.deps.load(dep, cb);
        }
      });
    } else if (dep in loading) {
      events.once(dep, callback);
    } else if (dep in loaded) {
      callback();
    } else if (dep in this) {
      loading[dep] = true;
      events.once(dep, callback);
      
      loader(_.map(this[dep], resolve), function() {
        delete loading[dep];
        loaded[dep] = true;
        events.emit(dep);
      });
      
    } else {
      throw new Error("Unable to load: " + dep);
    }
  },
  
  "google-code-prettify": [
    "../assets/core/js/google-code-prettify/run_prettify.js"
  ],

  "jcarousel": [
    "../assets/core/js/jcarousel/jquery.jcarousel.js"
  ],
  
  "jquery-base64": [
    "../assets/core/js/jquery/jquery.base-sixty-four.js"
  ],

  "jquery-validate": [
    "../assets/core/js/jquery/jquery.form.min.js",
    "../assets/core/js/jquery/jquery.validate.js",
    "../assets/core/js/jquery/jquery.validate-extras.js",
  ],
  
  "locache": [
    "../assets/core/js/lib/locache.js"
  ],
  
  "md5": [
    "../assets/core/js/lib/md5.js"
  ],
  
  "mediaelement": [
    "../assets/core/js/mediaelement/mediaelement-and-player.min.js"
  ],
  
  "prettyphoto": [
    "../assets/core/js/prettyphoto/jquery.prettyphoto.js"
  ],
  
  "videojs": [
    "../assets/core/js/video-js/video.js"
  ],
  
  "core.canvas": [
    "../assets/core/js/core.canvas.js"
  ],
  
  "core.slider": [
    "../assets/core/js/core.slider.js"
  ],
  
  "client.flickr": [
    "../assets/core/js/client.flickr.js"
  ],
  
  "client.forms": [
    "../assets/core/js/client.forms.js"
  ],
  
  "client.maps": [
    "../assets/core/js/client.maps.js"
  ],
  
  "client.search": [
    "../assets/core/js/client.search.js"
  ],
  
  "client.social": [
    "../assets/core/js/client.social.js"
  ],
  
  "client.twitter": [
    "../assets/core/js/client.twitter.js"
  ],
  
  "meteor.jcarousel": [
    "../assets/core/js/meteor.jcarousel.js"
  ],
  
  "meteor.posts-scroller": [
    "../assets/core/js/meteor.posts-scroller.js"
  ],
  
  "meteor.slider": [
    "../assets/core/js/meteor.slider.js"
  ],
  
  "meteor.vslider": [
    "../assets/core/js/meteor.vslider.js"
  ]

}

function resolve(path) {
  return Core.path(path, true);
}

})(jQuery, window.Core);