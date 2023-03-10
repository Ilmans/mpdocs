/*!
 Docsyard by LivelyWorks - http://livelyworks.net
 version: 1.x
 --------------------------------------------------------------------- 
 Build Timestamp: 1632901508
 Build Date: Wed Sep 29 2021 13:15:08 GMT+0530 (India Standard Time)
--------------------------------------------------------------------- */

/**
 * Require the given path.
 *
 * @param {String} path
 * @return {Object} exports
 * @api public
 */

function require(path, parent, orig) {
  var resolved = require.resolve(path);

  // lookup failed
  if (null == resolved) {
    orig = orig || path;
    parent = parent || 'root';
    var err = new Error('Failed to require "' + orig + '" from "' + parent + '"');
    err.path = orig;
    err.parent = parent;
    err.require = true;
    throw err;
  }

  var module = require.modules[resolved];

  // perform real require()
  // by invoking the module's
  // registered function
  if (!module._resolving && !module.exports) {
    var mod = {};
    mod.exports = {};
    mod.client = mod.component = true;
    module._resolving = true;
    module.call(this, mod.exports, require.relative(resolved), mod);
    delete module._resolving;
    module.exports = mod.exports;
  }

  return module.exports;
}

/**
 * Registered modules.
 */

require.modules = {};

/**
 * Registered aliases.
 */

require.aliases = {};

/**
 * Resolve `path`.
 *
 * Lookup:
 *
 *   - PATH/index.js
 *   - PATH.js
 *   - PATH
 *
 * @param {String} path
 * @return {String} path or null
 * @api private
 */

require.resolve = function(path) {
  if (path.charAt(0) === '/') path = path.slice(1);

  var paths = [
    path,
    path + '.js',
    path + '.json',
    path + '/index.js',
    path + '/index.json'
  ];

  for (var i = 0; i < paths.length; i++) {
    var path = paths[i];
    if (require.modules.hasOwnProperty(path)) return path;
    if (require.aliases.hasOwnProperty(path)) return require.aliases[path];
  }
};

/**
 * Normalize `path` relative to the current path.
 *
 * @param {String} curr
 * @param {String} path
 * @return {String}
 * @api private
 */

require.normalize = function(curr, path) {
  var segs = [];

  if ('.' != path.charAt(0)) return path;

  curr = curr.split('/');
  path = path.split('/');

  for (var i = 0; i < path.length; ++i) {
    if ('..' == path[i]) {
      curr.pop();
    } else if ('.' != path[i] && '' != path[i]) {
      segs.push(path[i]);
    }
  }

  return curr.concat(segs).join('/');
};

/**
 * Register module at `path` with callback `definition`.
 *
 * @param {String} path
 * @param {Function} definition
 * @api private
 */

require.register = function(path, definition) {
  require.modules[path] = definition;
};

/**
 * Alias a module definition.
 *
 * @param {String} from
 * @param {String} to
 * @api private
 */

require.alias = function(from, to) {
  if (!require.modules.hasOwnProperty(from)) {
    throw new Error('Failed to alias "' + from + '", it does not exist');
  }
  require.aliases[to] = from;
};

/**
 * Return a require function relative to the `parent` path.
 *
 * @param {String} parent
 * @return {Function}
 * @api private
 */

require.relative = function(parent) {
  var p = require.normalize(parent, '..');

  /**
   * lastIndexOf helper.
   */

  function lastIndexOf(arr, obj) {
    var i = arr.length;
    while (i--) {
      if (arr[i] === obj) return i;
    }
    return -1;
  }

  /**
   * The relative require() itself.
   */

  function localRequire(path) {
    var resolved = localRequire.resolve(path);
    return require(resolved, parent, path);
  }

  /**
   * Resolve relative to the parent.
   */

  localRequire.resolve = function(path) {
    var c = path.charAt(0);
    if ('/' == c) return path.slice(1);
    if ('.' == c) return require.normalize(p, path);

    // resolve deps by returning
    // the dep in the nearest "deps"
    // directory
    var segs = parent.split('/');
    var i = lastIndexOf(segs, 'deps') + 1;
    if (!i) i = 0;
    path = segs.slice(0, i + 1).join('/') + '/deps/' + path;
    return path;
  };

  /**
   * Check if module is defined at `path`.
   */

  localRequire.exists = function(path) {
    return require.modules.hasOwnProperty(localRequire.resolve(path));
  };

  return localRequire;
};
require.register("switchery/switchery.js", function(exports, require, module){

/**
 * Switchery 0.1.1
 * http://abpetkov.github.io/switchery/
 *
 * Authored by Alexander Petkov
 * https://github.com/abpetkov
 *
 * Copyright 2013, Alexander Petkov
 * License: The MIT License (MIT)
 * http://opensource.org/licenses/MIT
 *
 */

/**
 * Expose `Switchery`.
 */

module.exports = Switchery;

/**
 * Set Switchery default values.
 *
 * @api public
 */

var defaults = {
    color    : '#64bd63'
  , className: 'switchery'
  , disabled : false
  , speed    : '0.1s'
};

/**
 * Create Switchery object.
 *
 * @param {Object} element
 * @param {Object} options
 * @api public
 */

function Switchery(element, options) {
  if (!(this instanceof Switchery)) return new Switchery(options);

  this.element = element;
  this.options = options || {};

  for (var i in defaults) {
    if (!(i in this.options)) {
      this.options[i] = defaults[i];
    }
  }

  if (this.element.type == 'checkbox') this.init();
}

/**
 * Hide the target element.
 *
 * @api private
 */

Switchery.prototype.hide = function() {
  this.element.style.display = 'none';
};

/**
 * Show custom switch after the target element.
 *
 * @api private
 */

Switchery.prototype.show = function() {
  var switcher = this.create();
  this.element.parentNode.appendChild(switcher);
};

/**
 * Create custom switch.
 *
 * @returns {Object} this.switcher
 * @api private
 */

Switchery.prototype.create = function() {
  this.switcher = document.createElement('span');
  this.jack = document.createElement('small');
  this.switcher.appendChild(this.jack);
  this.switcher.className = this.options.className;

  return this.switcher;
};

/**
 * See if input is checked.
 *
 * @returns {Boolean}
 * @api private
 */

Switchery.prototype.isChecked = function() {
  return this.element.checked;
};

/**
 * See if switcher should be disabled.
 *
 * @returns {Boolean}
 * @api private
 */

Switchery.prototype.isDisabled = function() {
  return this.options.disabled || this.element.disabled;
};

/**
 * Set switch jack proper position.
 *
 * @param {Boolean} clicked - we need this in order to uncheck the input when the switch is clicked
 * @api private
 */

Switchery.prototype.setPosition = function (clicked) {
  var checked = this.isChecked()
    , switcher = this.switcher
    , jack = this.jack;

  if (clicked && checked) checked = false;
  else if (clicked && !checked) checked = true;

  if (checked === true) {
    this.element.checked = true;

    if (window.getComputedStyle) jack.style.left = parseInt(window.getComputedStyle(switcher).width) - jack.offsetWidth + 'px';
    else jack.style.left = parseInt(switcher.currentStyle['width']) - jack.offsetWidth + 'px';

    if (this.options.color) this.colorize();
  } else {
    jack.style.left = '0';
    this.element.checked = false;
    this.switcher.style.backgroundColor = '';
    this.switcher.style.borderColor =  '';
  }
};

/**
 * Set speed.
 *
 * @api private
 */

Switchery.prototype.setSpeed = function() {
  this.switcher.style.transitionDuration = this.options.speed;
  this.jack.style.transitionDuration = this.options.speed;
};

/**
 * Copy the input name and id attributes.
 *
 * @api private
 */

Switchery.prototype.setAttributes = function() {
  var id = this.element.getAttribute('id')
    , name = this.element.getAttribute('name');

  if (id) this.switcher.setAttribute('id', id);
  if (name) this.switcher.setAttribute('name', name);
};

/**
 * Set switch color.
 *
 * @api private
 */

Switchery.prototype.colorize = function() {
  this.switcher.style.backgroundColor = this.options.color;
  this.switcher.style.borderColor = this.options.color;
};

/**
 * Handle the switch click event.
 *
 * @api private
 */

Switchery.prototype.handleClick = function() {
  var $this = this
    , switcher = this.switcher;

  if (this.isDisabled() === false) {
    if (switcher.addEventListener) {
      switcher.addEventListener('click', function() {
        $this.setPosition(true);
      });
    } else {
      switcher.attachEvent('onclick', function() {
        $this.setPosition(true);
      });
    }
  } else {
    this.element.disabled = true;
  }
};

/**
 * Initialize Switchery.
 *
 * @api private
 */

Switchery.prototype.init = function() {
  this.hide();
  this.show();
  this.setSpeed();
  this.setPosition();
  this.setAttributes();
  this.handleClick();
};
});
require.alias("switchery/switchery.js", "switchery/index.js");;
!function(){function t(e){var n=t.modules[e];if(!n)throw new Error('failed to require "'+e+'"');return"exports"in n||"function"!=typeof n.definition||(n.client=n.component=!0,n.definition.call(this,n.exports={},n),delete n.definition),n.exports}t.loader="component",t.helper={},t.helper.semVerSort=function(t,e){for(var n=t.version.split("."),i=e.version.split("."),o=0;o<n.length;++o){var r=parseInt(n[o],10),s=parseInt(i[o],10);if(r!==s)return r>s?1:-1;var c=n[o].substr((""+r).length),a=i[o].substr((""+s).length);if(""===c&&""!==a)return 1;if(""!==c&&""===a)return-1;if(""!==c&&""!==a)return c>a?1:-1}return 0},t.latest=function(e,n){function i(t){throw new Error('failed to find latest module of "'+t+'"')}var o=/(.*)~(.*)@v?(\d+\.\d+\.\d+[^\/]*)$/,r=/(.*)~(.*)/;r.test(e)||i(e);for(var s=Object.keys(t.modules),c=[],a=[],l=0;l<s.length;l++){var h=s[l];if(new RegExp(e+"@").test(h)){var u=h.substr(e.length+1),d=o.exec(h);null!=d?c.push({version:u,name:h}):a.push({version:u,name:h})}}if(0===c.concat(a).length&&i(e),c.length>0){var p=c.sort(t.helper.semVerSort).pop().name;return n===!0?p:t(p)}var p=a.sort(function(t,e){return t.name>e.name})[0].name;return n===!0?p:t(p)},t.modules={},t.register=function(e,n){t.modules[e]={definition:n}},t.define=function(e,n){t.modules[e]={exports:n}},t.register("abpetkov~transitionize@0.0.3",function(t,e){function n(t,e){return this instanceof n?(this.element=t,this.props=e||{},void this.init()):new n(t,e)}e.exports=n,n.prototype.isSafari=function(){return/Safari/.test(navigator.userAgent)&&/Apple Computer/.test(navigator.vendor)},n.prototype.init=function(){var t=[];for(var e in this.props)t.push(e+" "+this.props[e]);this.element.style.transition=t.join(", "),this.isSafari()&&(this.element.style.webkitTransition=t.join(", "))}}),t.register("ftlabs~fastclick@v0.6.11",function(t,e){function n(t){"use strict";var e,i=this;if(this.trackingClick=!1,this.trackingClickStart=0,this.targetElement=null,this.touchStartX=0,this.touchStartY=0,this.lastTouchIdentifier=0,this.touchBoundary=10,this.layer=t,!t||!t.nodeType)throw new TypeError("Layer must be a document node");this.onClick=function(){return n.prototype.onClick.apply(i,arguments)},this.onMouse=function(){return n.prototype.onMouse.apply(i,arguments)},this.onTouchStart=function(){return n.prototype.onTouchStart.apply(i,arguments)},this.onTouchMove=function(){return n.prototype.onTouchMove.apply(i,arguments)},this.onTouchEnd=function(){return n.prototype.onTouchEnd.apply(i,arguments)},this.onTouchCancel=function(){return n.prototype.onTouchCancel.apply(i,arguments)},n.notNeeded(t)||(this.deviceIsAndroid&&(t.addEventListener("mouseover",this.onMouse,!0),t.addEventListener("mousedown",this.onMouse,!0),t.addEventListener("mouseup",this.onMouse,!0)),t.addEventListener("click",this.onClick,!0),t.addEventListener("touchstart",this.onTouchStart,!1),t.addEventListener("touchmove",this.onTouchMove,!1),t.addEventListener("touchend",this.onTouchEnd,!1),t.addEventListener("touchcancel",this.onTouchCancel,!1),Event.prototype.stopImmediatePropagation||(t.removeEventListener=function(e,n,i){var o=Node.prototype.removeEventListener;"click"===e?o.call(t,e,n.hijacked||n,i):o.call(t,e,n,i)},t.addEventListener=function(e,n,i){var o=Node.prototype.addEventListener;"click"===e?o.call(t,e,n.hijacked||(n.hijacked=function(t){t.propagationStopped||n(t)}),i):o.call(t,e,n,i)}),"function"==typeof t.onclick&&(e=t.onclick,t.addEventListener("click",function(t){e(t)},!1),t.onclick=null))}n.prototype.deviceIsAndroid=navigator.userAgent.indexOf("Android")>0,n.prototype.deviceIsIOS=/iP(ad|hone|od)/.test(navigator.userAgent),n.prototype.deviceIsIOS4=n.prototype.deviceIsIOS&&/OS 4_\d(_\d)?/.test(navigator.userAgent),n.prototype.deviceIsIOSWithBadTarget=n.prototype.deviceIsIOS&&/OS ([6-9]|\d{2})_\d/.test(navigator.userAgent),n.prototype.needsClick=function(t){"use strict";switch(t.nodeName.toLowerCase()){case"button":case"select":case"textarea":if(t.disabled)return!0;break;case"input":if(this.deviceIsIOS&&"file"===t.type||t.disabled)return!0;break;case"label":case"video":return!0}return/\bneedsclick\b/.test(t.className)},n.prototype.needsFocus=function(t){"use strict";switch(t.nodeName.toLowerCase()){case"textarea":return!0;case"select":return!this.deviceIsAndroid;case"input":switch(t.type){case"button":case"checkbox":case"file":case"image":case"radio":case"submit":return!1}return!t.disabled&&!t.readOnly;default:return/\bneedsfocus\b/.test(t.className)}},n.prototype.sendClick=function(t,e){"use strict";var n,i;document.activeElement&&document.activeElement!==t&&document.activeElement.blur(),i=e.changedTouches[0],n=document.createEvent("MouseEvents"),n.initMouseEvent(this.determineEventType(t),!0,!0,window,1,i.screenX,i.screenY,i.clientX,i.clientY,!1,!1,!1,!1,0,null),n.forwardedTouchEvent=!0,t.dispatchEvent(n)},n.prototype.determineEventType=function(t){"use strict";return this.deviceIsAndroid&&"select"===t.tagName.toLowerCase()?"mousedown":"click"},n.prototype.focus=function(t){"use strict";var e;this.deviceIsIOS&&t.setSelectionRange&&0!==t.type.indexOf("date")&&"time"!==t.type?(e=t.value.length,t.setSelectionRange(e,e)):t.focus()},n.prototype.updateScrollParent=function(t){"use strict";var e,n;if(e=t.fastClickScrollParent,!e||!e.contains(t)){n=t;do{if(n.scrollHeight>n.offsetHeight){e=n,t.fastClickScrollParent=n;break}n=n.parentElement}while(n)}e&&(e.fastClickLastScrollTop=e.scrollTop)},n.prototype.getTargetElementFromEventTarget=function(t){"use strict";return t.nodeType===Node.TEXT_NODE?t.parentNode:t},n.prototype.onTouchStart=function(t){"use strict";var e,n,i;if(t.targetTouches.length>1)return!0;if(e=this.getTargetElementFromEventTarget(t.target),n=t.targetTouches[0],this.deviceIsIOS){if(i=window.getSelection(),i.rangeCount&&!i.isCollapsed)return!0;if(!this.deviceIsIOS4){if(n.identifier===this.lastTouchIdentifier)return t.preventDefault(),!1;this.lastTouchIdentifier=n.identifier,this.updateScrollParent(e)}}return this.trackingClick=!0,this.trackingClickStart=t.timeStamp,this.targetElement=e,this.touchStartX=n.pageX,this.touchStartY=n.pageY,t.timeStamp-this.lastClickTime<200&&t.preventDefault(),!0},n.prototype.touchHasMoved=function(t){"use strict";var e=t.changedTouches[0],n=this.touchBoundary;return Math.abs(e.pageX-this.touchStartX)>n||Math.abs(e.pageY-this.touchStartY)>n?!0:!1},n.prototype.onTouchMove=function(t){"use strict";return this.trackingClick?((this.targetElement!==this.getTargetElementFromEventTarget(t.target)||this.touchHasMoved(t))&&(this.trackingClick=!1,this.targetElement=null),!0):!0},n.prototype.findControl=function(t){"use strict";return void 0!==t.control?t.control:t.htmlFor?document.getElementById(t.htmlFor):t.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea")},n.prototype.onTouchEnd=function(t){"use strict";var e,n,i,o,r,s=this.targetElement;if(!this.trackingClick)return!0;if(t.timeStamp-this.lastClickTime<200)return this.cancelNextClick=!0,!0;if(this.cancelNextClick=!1,this.lastClickTime=t.timeStamp,n=this.trackingClickStart,this.trackingClick=!1,this.trackingClickStart=0,this.deviceIsIOSWithBadTarget&&(r=t.changedTouches[0],s=document.elementFromPoint(r.pageX-window.pageXOffset,r.pageY-window.pageYOffset)||s,s.fastClickScrollParent=this.targetElement.fastClickScrollParent),i=s.tagName.toLowerCase(),"label"===i){if(e=this.findControl(s)){if(this.focus(s),this.deviceIsAndroid)return!1;s=e}}else if(this.needsFocus(s))return t.timeStamp-n>100||this.deviceIsIOS&&window.top!==window&&"input"===i?(this.targetElement=null,!1):(this.focus(s),this.deviceIsIOS4&&"select"===i||(this.targetElement=null,t.preventDefault()),!1);return this.deviceIsIOS&&!this.deviceIsIOS4&&(o=s.fastClickScrollParent,o&&o.fastClickLastScrollTop!==o.scrollTop)?!0:(this.needsClick(s)||(t.preventDefault(),this.sendClick(s,t)),!1)},n.prototype.onTouchCancel=function(){"use strict";this.trackingClick=!1,this.targetElement=null},n.prototype.onMouse=function(t){"use strict";return this.targetElement?t.forwardedTouchEvent?!0:t.cancelable&&(!this.needsClick(this.targetElement)||this.cancelNextClick)?(t.stopImmediatePropagation?t.stopImmediatePropagation():t.propagationStopped=!0,t.stopPropagation(),t.preventDefault(),!1):!0:!0},n.prototype.onClick=function(t){"use strict";var e;return this.trackingClick?(this.targetElement=null,this.trackingClick=!1,!0):"submit"===t.target.type&&0===t.detail?!0:(e=this.onMouse(t),e||(this.targetElement=null),e)},n.prototype.destroy=function(){"use strict";var t=this.layer;this.deviceIsAndroid&&(t.removeEventListener("mouseover",this.onMouse,!0),t.removeEventListener("mousedown",this.onMouse,!0),t.removeEventListener("mouseup",this.onMouse,!0)),t.removeEventListener("click",this.onClick,!0),t.removeEventListener("touchstart",this.onTouchStart,!1),t.removeEventListener("touchmove",this.onTouchMove,!1),t.removeEventListener("touchend",this.onTouchEnd,!1),t.removeEventListener("touchcancel",this.onTouchCancel,!1)},n.notNeeded=function(t){"use strict";var e,i;if("undefined"==typeof window.ontouchstart)return!0;if(i=+(/Chrome\/([0-9]+)/.exec(navigator.userAgent)||[,0])[1]){if(!n.prototype.deviceIsAndroid)return!0;if(e=document.querySelector("meta[name=viewport]")){if(-1!==e.content.indexOf("user-scalable=no"))return!0;if(i>31&&window.innerWidth<=window.screen.width)return!0}}return"none"===t.style.msTouchAction?!0:!1},n.attach=function(t){"use strict";return new n(t)},"undefined"!=typeof define&&define.amd?define(function(){"use strict";return n}):"undefined"!=typeof e&&e.exports?(e.exports=n.attach,e.exports.FastClick=n):window.FastClick=n}),t.register("component~indexof@0.0.3",function(t,e){e.exports=function(t,e){if(t.indexOf)return t.indexOf(e);for(var n=0;n<t.length;++n)if(t[n]===e)return n;return-1}}),t.register("component~classes@1.2.1",function(e,n){function i(t){if(!t)throw new Error("A DOM element reference is required");this.el=t,this.list=t.classList}var o=t("component~indexof@0.0.3"),r=/\s+/,s=Object.prototype.toString;n.exports=function(t){return new i(t)},i.prototype.add=function(t){if(this.list)return this.list.add(t),this;var e=this.array(),n=o(e,t);return~n||e.push(t),this.el.className=e.join(" "),this},i.prototype.remove=function(t){if("[object RegExp]"==s.call(t))return this.removeMatching(t);if(this.list)return this.list.remove(t),this;var e=this.array(),n=o(e,t);return~n&&e.splice(n,1),this.el.className=e.join(" "),this},i.prototype.removeMatching=function(t){for(var e=this.array(),n=0;n<e.length;n++)t.test(e[n])&&this.remove(e[n]);return this},i.prototype.toggle=function(t,e){return this.list?("undefined"!=typeof e?e!==this.list.toggle(t,e)&&this.list.toggle(t):this.list.toggle(t),this):("undefined"!=typeof e?e?this.add(t):this.remove(t):this.has(t)?this.remove(t):this.add(t),this)},i.prototype.array=function(){var t=this.el.className.replace(/^\s+|\s+$/g,""),e=t.split(r);return""===e[0]&&e.shift(),e},i.prototype.has=i.prototype.contains=function(t){return this.list?this.list.contains(t):!!~o(this.array(),t)}}),t.register("component~event@0.1.4",function(t,e){var n=window.addEventListener?"addEventListener":"attachEvent",i=window.removeEventListener?"removeEventListener":"detachEvent",o="addEventListener"!==n?"on":"";t.bind=function(t,e,i,r){return t[n](o+e,i,r||!1),i},t.unbind=function(t,e,n,r){return t[i](o+e,n,r||!1),n}}),t.register("component~query@0.0.3",function(t,e){function n(t,e){return e.querySelector(t)}t=e.exports=function(t,e){return e=e||document,n(t,e)},t.all=function(t,e){return e=e||document,e.querySelectorAll(t)},t.engine=function(e){if(!e.one)throw new Error(".one callback required");if(!e.all)throw new Error(".all callback required");return n=e.one,t.all=e.all,t}}),t.register("component~matches-selector@0.1.5",function(e,n){function i(t,e){if(!t||1!==t.nodeType)return!1;if(s)return s.call(t,e);for(var n=o.all(e,t.parentNode),i=0;i<n.length;++i)if(n[i]==t)return!0;return!1}var o=t("component~query@0.0.3"),r=Element.prototype,s=r.matches||r.webkitMatchesSelector||r.mozMatchesSelector||r.msMatchesSelector||r.oMatchesSelector;n.exports=i}),t.register("component~closest@0.1.4",function(e,n){var i=t("component~matches-selector@0.1.5");n.exports=function(t,e,n,o){for(t=n?{parentNode:t}:t,o=o||document;(t=t.parentNode)&&t!==document;){if(i(t,e))return t;if(t===o)return}}}),t.register("component~delegate@0.2.3",function(e,n){var i=t("component~closest@0.1.4"),o=t("component~event@0.1.4");e.bind=function(t,e,n,r,s){return o.bind(t,n,function(n){var o=n.target||n.srcElement;n.delegateTarget=i(o,e,!0,t),n.delegateTarget&&r.call(t,n)},s)},e.unbind=function(t,e,n,i){o.unbind(t,e,n,i)}}),t.register("component~events@1.0.9",function(e,n){function i(t,e){if(!(this instanceof i))return new i(t,e);if(!t)throw new Error("element required");if(!e)throw new Error("object required");this.el=t,this.obj=e,this._events={}}function o(t){var e=t.split(/ +/);return{name:e.shift(),selector:e.join(" ")}}var r=t("component~event@0.1.4"),s=t("component~delegate@0.2.3");n.exports=i,i.prototype.sub=function(t,e,n){this._events[t]=this._events[t]||{},this._events[t][e]=n},i.prototype.bind=function(t,e){function n(){var t=[].slice.call(arguments).concat(h);a[e].apply(a,t)}var i=o(t),c=this.el,a=this.obj,l=i.name,e=e||"on"+l,h=[].slice.call(arguments,2);return i.selector?n=s.bind(c,i.selector,l,n):r.bind(c,l,n),this.sub(l,e,n),n},i.prototype.unbind=function(t,e){if(0==arguments.length)return this.unbindAll();if(1==arguments.length)return this.unbindAllOf(t);var n=this._events[t];if(n){var i=n[e];i&&r.unbind(this.el,t,i)}},i.prototype.unbindAll=function(){for(var t in this._events)this.unbindAllOf(t)},i.prototype.unbindAllOf=function(t){var e=this._events[t];if(e)for(var n in e)this.unbind(t,n)}}),t.register("switchery",function(e,n){function i(t,e){if(!(this instanceof i))return new i(t,e);this.element=t,this.options=e||{};for(var n in a)null==this.options[n]&&(this.options[n]=a[n]);null!=this.element&&"checkbox"==this.element.type&&this.init(),this.isDisabled()===!0&&this.disable()}var o=t("abpetkov~transitionize@0.0.3"),r=t("ftlabs~fastclick@v0.6.11"),s=t("component~classes@1.2.1"),c=t("component~events@1.0.9");n.exports=i;var a={color:"#64bd63",secondaryColor:"#dfdfdf",jackColor:"#fff",jackSecondaryColor:null,className:"switchery",disabled:!1,disabledOpacity:.5,speed:"0.4s",size:"default"};i.prototype.hide=function(){this.element.style.display="none"},i.prototype.show=function(){var t=this.create();this.insertAfter(this.element,t)},i.prototype.create=function(){return this.switcher=document.createElement("span"),this.jack=document.createElement("small"),this.switcher.appendChild(this.jack),this.switcher.className=this.options.className,this.events=c(this.switcher,this),this.switcher},i.prototype.insertAfter=function(t,e){t.parentNode.insertBefore(e,t.nextSibling)},i.prototype.setPosition=function(t){var e=this.isChecked(),n=this.switcher,i=this.jack;t&&e?e=!1:t&&!e&&(e=!0),e===!0?(this.element.checked=!0,i.style.left=window.getComputedStyle?parseInt(window.getComputedStyle(n).width)-parseInt(window.getComputedStyle(i).width)+"px":parseInt(n.currentStyle.width)-parseInt(i.currentStyle.width)+"px",this.options.color&&this.colorize(),this.setSpeed()):(i.style.left=0,this.element.checked=!1,this.switcher.style.boxShadow="inset 0 0 0 0 "+this.options.secondaryColor,this.switcher.style.borderColor=this.options.secondaryColor,this.switcher.style.backgroundColor=this.options.secondaryColor!==a.secondaryColor?this.options.secondaryColor:"#fff",this.jack.style.backgroundColor=this.options.jackSecondaryColor!==this.options.jackColor?this.options.jackSecondaryColor:this.options.jackColor,this.setSpeed())},i.prototype.setSpeed=function(){var t={},e={"background-color":this.options.speed,left:this.options.speed.replace(/[a-z]/,"")/2+"s"};t=this.isChecked()?{border:this.options.speed,"box-shadow":this.options.speed,"background-color":3*this.options.speed.replace(/[a-z]/,"")+"s"}:{border:this.options.speed,"box-shadow":this.options.speed},o(this.switcher,t),o(this.jack,e)},i.prototype.setSize=function(){var t="switchery-small",e="switchery-default",n="switchery-large";switch(this.options.size){case"small":s(this.switcher).add(t);break;case"large":s(this.switcher).add(n);break;default:s(this.switcher).add(e)}},i.prototype.colorize=function(){var t=this.switcher.offsetHeight/2;this.switcher.style.backgroundColor=this.options.color,this.switcher.style.borderColor=this.options.color,this.switcher.style.boxShadow="inset 0 0 0 "+t+"px "+this.options.color,this.jack.style.backgroundColor=this.options.jackColor},i.prototype.handleOnchange=function(t){if(document.dispatchEvent){var e=document.createEvent("HTMLEvents");e.initEvent("change",!0,!0),this.element.dispatchEvent(e)}else this.element.fireEvent("onchange")},i.prototype.handleChange=function(){var t=this,e=this.element;e.addEventListener?e.addEventListener("change",function(){t.setPosition()}):e.attachEvent("onchange",function(){t.setPosition()})},i.prototype.handleClick=function(){var t=this.switcher;r(t),this.events.bind("click","bindClick")},i.prototype.bindClick=function(){var t=this.element.parentNode.tagName.toLowerCase(),e="label"===t?!1:!0;this.setPosition(e),this.handleOnchange(this.element.checked)},i.prototype.markAsSwitched=function(){this.element.setAttribute("data-switchery",!0)},i.prototype.markedAsSwitched=function(){return this.element.getAttribute("data-switchery")},i.prototype.init=function(){this.hide(),this.show(),this.setSize(),this.setPosition(),this.markAsSwitched(),this.handleChange(),this.handleClick()},i.prototype.isChecked=function(){return this.element.checked},i.prototype.isDisabled=function(){return this.options.disabled||this.element.disabled||this.element.readOnly},i.prototype.destroy=function(){this.events.unbind()},i.prototype.enable=function(){this.options.disabled&&(this.options.disabled=!1),this.element.disabled&&(this.element.disabled=!1),this.element.readOnly&&(this.element.readOnly=!1),this.switcher.style.opacity=1,this.events.bind("click","bindClick")},i.prototype.disable=function(){this.options.disabled||(this.options.disabled=!0),this.element.disabled||(this.element.disabled=!0),this.element.readOnly||(this.element.readOnly=!0),this.switcher.style.opacity=this.options.disabledOpacity,this.destroy()}}),"object"==typeof exports?module.exports=t("switchery"):"function"==typeof define&&define.amd?define("Switchery",[],function(){return t("switchery")}):(this||window).Switchery=t("switchery")}(),angular.module("NgSwitchery",[]).directive("uiSwitch",["$window","$timeout","$log","$parse",function(t,e,n,i){function o(n,o,r,s){function c(){e(function(){h&&angular.element(h.switcher).remove(),h=new t.Switchery(o[0],a);var e=h.element;e.checked=n.initValue,h.setPosition(!1),e.addEventListener("change",function(t){n.$apply(function(){s.$setViewValue(e.checked)})})},0)}if(!s)return!1;var a={};try{a=i(r.uiSwitch)(n)}catch(l){}var h,u;r.$observe("disabled",function(t){void 0!=t&&t!=u&&(u=t,c())}),c()}return{require:"ngModel",restrict:"AE",scope:{initValue:"=ngModel"},link:o}}]);