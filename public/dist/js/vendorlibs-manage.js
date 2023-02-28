/*!
 Docsyard by LivelyWorks - http://livelyworks.net
 version: 1.x
 --------------------------------------------------------------------- 
 Build Timestamp: 1632901508
 Build Date: Wed Sep 29 2021 13:15:08 GMT+0530 (India Standard Time)
--------------------------------------------------------------------- */
//! moment.js

;(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    global.moment = factory()
}(this, (function () { 'use strict';

    var hookCallback;

    function hooks () {
        return hookCallback.apply(null, arguments);
    }

    // This is done to register the method called with moment()
    // without creating circular dependencies.
    function setHookCallback (callback) {
        hookCallback = callback;
    }

    function isArray(input) {
        return input instanceof Array || Object.prototype.toString.call(input) === '[object Array]';
    }

    function isObject(input) {
        // IE8 will treat undefined and null as object if it wasn't for
        // input != null
        return input != null && Object.prototype.toString.call(input) === '[object Object]';
    }

    function isObjectEmpty(obj) {
        if (Object.getOwnPropertyNames) {
            return (Object.getOwnPropertyNames(obj).length === 0);
        } else {
            var k;
            for (k in obj) {
                if (obj.hasOwnProperty(k)) {
                    return false;
                }
            }
            return true;
        }
    }

    function isUndefined(input) {
        return input === void 0;
    }

    function isNumber(input) {
        return typeof input === 'number' || Object.prototype.toString.call(input) === '[object Number]';
    }

    function isDate(input) {
        return input instanceof Date || Object.prototype.toString.call(input) === '[object Date]';
    }

    function map(arr, fn) {
        var res = [], i;
        for (i = 0; i < arr.length; ++i) {
            res.push(fn(arr[i], i));
        }
        return res;
    }

    function hasOwnProp(a, b) {
        return Object.prototype.hasOwnProperty.call(a, b);
    }

    function extend(a, b) {
        for (var i in b) {
            if (hasOwnProp(b, i)) {
                a[i] = b[i];
            }
        }

        if (hasOwnProp(b, 'toString')) {
            a.toString = b.toString;
        }

        if (hasOwnProp(b, 'valueOf')) {
            a.valueOf = b.valueOf;
        }

        return a;
    }

    function createUTC (input, format, locale, strict) {
        return createLocalOrUTC(input, format, locale, strict, true).utc();
    }

    function defaultParsingFlags() {
        // We need to deep clone this object.
        return {
            empty           : false,
            unusedTokens    : [],
            unusedInput     : [],
            overflow        : -2,
            charsLeftOver   : 0,
            nullInput       : false,
            invalidMonth    : null,
            invalidFormat   : false,
            userInvalidated : false,
            iso             : false,
            parsedDateParts : [],
            meridiem        : null,
            rfc2822         : false,
            weekdayMismatch : false
        };
    }

    function getParsingFlags(m) {
        if (m._pf == null) {
            m._pf = defaultParsingFlags();
        }
        return m._pf;
    }

    var some;
    if (Array.prototype.some) {
        some = Array.prototype.some;
    } else {
        some = function (fun) {
            var t = Object(this);
            var len = t.length >>> 0;

            for (var i = 0; i < len; i++) {
                if (i in t && fun.call(this, t[i], i, t)) {
                    return true;
                }
            }

            return false;
        };
    }

    function isValid(m) {
        if (m._isValid == null) {
            var flags = getParsingFlags(m);
            var parsedParts = some.call(flags.parsedDateParts, function (i) {
                return i != null;
            });
            var isNowValid = !isNaN(m._d.getTime()) &&
                flags.overflow < 0 &&
                !flags.empty &&
                !flags.invalidMonth &&
                !flags.invalidWeekday &&
                !flags.weekdayMismatch &&
                !flags.nullInput &&
                !flags.invalidFormat &&
                !flags.userInvalidated &&
                (!flags.meridiem || (flags.meridiem && parsedParts));

            if (m._strict) {
                isNowValid = isNowValid &&
                    flags.charsLeftOver === 0 &&
                    flags.unusedTokens.length === 0 &&
                    flags.bigHour === undefined;
            }

            if (Object.isFrozen == null || !Object.isFrozen(m)) {
                m._isValid = isNowValid;
            }
            else {
                return isNowValid;
            }
        }
        return m._isValid;
    }

    function createInvalid (flags) {
        var m = createUTC(NaN);
        if (flags != null) {
            extend(getParsingFlags(m), flags);
        }
        else {
            getParsingFlags(m).userInvalidated = true;
        }

        return m;
    }

    // Plugins that add properties should also add the key here (null value),
    // so we can properly clone ourselves.
    var momentProperties = hooks.momentProperties = [];

    function copyConfig(to, from) {
        var i, prop, val;

        if (!isUndefined(from._isAMomentObject)) {
            to._isAMomentObject = from._isAMomentObject;
        }
        if (!isUndefined(from._i)) {
            to._i = from._i;
        }
        if (!isUndefined(from._f)) {
            to._f = from._f;
        }
        if (!isUndefined(from._l)) {
            to._l = from._l;
        }
        if (!isUndefined(from._strict)) {
            to._strict = from._strict;
        }
        if (!isUndefined(from._tzm)) {
            to._tzm = from._tzm;
        }
        if (!isUndefined(from._isUTC)) {
            to._isUTC = from._isUTC;
        }
        if (!isUndefined(from._offset)) {
            to._offset = from._offset;
        }
        if (!isUndefined(from._pf)) {
            to._pf = getParsingFlags(from);
        }
        if (!isUndefined(from._locale)) {
            to._locale = from._locale;
        }

        if (momentProperties.length > 0) {
            for (i = 0; i < momentProperties.length; i++) {
                prop = momentProperties[i];
                val = from[prop];
                if (!isUndefined(val)) {
                    to[prop] = val;
                }
            }
        }

        return to;
    }

    var updateInProgress = false;

    // Moment prototype object
    function Moment(config) {
        copyConfig(this, config);
        this._d = new Date(config._d != null ? config._d.getTime() : NaN);
        if (!this.isValid()) {
            this._d = new Date(NaN);
        }
        // Prevent infinite loop in case updateOffset creates new moment
        // objects.
        if (updateInProgress === false) {
            updateInProgress = true;
            hooks.updateOffset(this);
            updateInProgress = false;
        }
    }

    function isMoment (obj) {
        return obj instanceof Moment || (obj != null && obj._isAMomentObject != null);
    }

    function absFloor (number) {
        if (number < 0) {
            // -0 -> 0
            return Math.ceil(number) || 0;
        } else {
            return Math.floor(number);
        }
    }

    function toInt(argumentForCoercion) {
        var coercedNumber = +argumentForCoercion,
            value = 0;

        if (coercedNumber !== 0 && isFinite(coercedNumber)) {
            value = absFloor(coercedNumber);
        }

        return value;
    }

    // compare two arrays, return the number of differences
    function compareArrays(array1, array2, dontConvert) {
        var len = Math.min(array1.length, array2.length),
            lengthDiff = Math.abs(array1.length - array2.length),
            diffs = 0,
            i;
        for (i = 0; i < len; i++) {
            if ((dontConvert && array1[i] !== array2[i]) ||
                (!dontConvert && toInt(array1[i]) !== toInt(array2[i]))) {
                diffs++;
            }
        }
        return diffs + lengthDiff;
    }

    function warn(msg) {
        if (hooks.suppressDeprecationWarnings === false &&
                (typeof console !==  'undefined') && console.warn) {
            console.warn('Deprecation warning: ' + msg);
        }
    }

    function deprecate(msg, fn) {
        var firstTime = true;

        return extend(function () {
            if (hooks.deprecationHandler != null) {
                hooks.deprecationHandler(null, msg);
            }
            if (firstTime) {
                var args = [];
                var arg;
                for (var i = 0; i < arguments.length; i++) {
                    arg = '';
                    if (typeof arguments[i] === 'object') {
                        arg += '\n[' + i + '] ';
                        for (var key in arguments[0]) {
                            arg += key + ': ' + arguments[0][key] + ', ';
                        }
                        arg = arg.slice(0, -2); // Remove trailing comma and space
                    } else {
                        arg = arguments[i];
                    }
                    args.push(arg);
                }
                warn(msg + '\nArguments: ' + Array.prototype.slice.call(args).join('') + '\n' + (new Error()).stack);
                firstTime = false;
            }
            return fn.apply(this, arguments);
        }, fn);
    }

    var deprecations = {};

    function deprecateSimple(name, msg) {
        if (hooks.deprecationHandler != null) {
            hooks.deprecationHandler(name, msg);
        }
        if (!deprecations[name]) {
            warn(msg);
            deprecations[name] = true;
        }
    }

    hooks.suppressDeprecationWarnings = false;
    hooks.deprecationHandler = null;

    function isFunction(input) {
        return input instanceof Function || Object.prototype.toString.call(input) === '[object Function]';
    }

    function set (config) {
        var prop, i;
        for (i in config) {
            prop = config[i];
            if (isFunction(prop)) {
                this[i] = prop;
            } else {
                this['_' + i] = prop;
            }
        }
        this._config = config;
        // Lenient ordinal parsing accepts just a number in addition to
        // number + (possibly) stuff coming from _dayOfMonthOrdinalParse.
        // TODO: Remove "ordinalParse" fallback in next major release.
        this._dayOfMonthOrdinalParseLenient = new RegExp(
            (this._dayOfMonthOrdinalParse.source || this._ordinalParse.source) +
                '|' + (/\d{1,2}/).source);
    }

    function mergeConfigs(parentConfig, childConfig) {
        var res = extend({}, parentConfig), prop;
        for (prop in childConfig) {
            if (hasOwnProp(childConfig, prop)) {
                if (isObject(parentConfig[prop]) && isObject(childConfig[prop])) {
                    res[prop] = {};
                    extend(res[prop], parentConfig[prop]);
                    extend(res[prop], childConfig[prop]);
                } else if (childConfig[prop] != null) {
                    res[prop] = childConfig[prop];
                } else {
                    delete res[prop];
                }
            }
        }
        for (prop in parentConfig) {
            if (hasOwnProp(parentConfig, prop) &&
                    !hasOwnProp(childConfig, prop) &&
                    isObject(parentConfig[prop])) {
                // make sure changes to properties don't modify parent config
                res[prop] = extend({}, res[prop]);
            }
        }
        return res;
    }

    function Locale(config) {
        if (config != null) {
            this.set(config);
        }
    }

    var keys;

    if (Object.keys) {
        keys = Object.keys;
    } else {
        keys = function (obj) {
            var i, res = [];
            for (i in obj) {
                if (hasOwnProp(obj, i)) {
                    res.push(i);
                }
            }
            return res;
        };
    }

    var defaultCalendar = {
        sameDay : '[Today at] LT',
        nextDay : '[Tomorrow at] LT',
        nextWeek : 'dddd [at] LT',
        lastDay : '[Yesterday at] LT',
        lastWeek : '[Last] dddd [at] LT',
        sameElse : 'L'
    };

    function calendar (key, mom, now) {
        var output = this._calendar[key] || this._calendar['sameElse'];
        return isFunction(output) ? output.call(mom, now) : output;
    }

    var defaultLongDateFormat = {
        LTS  : 'h:mm:ss A',
        LT   : 'h:mm A',
        L    : 'MM/DD/YYYY',
        LL   : 'MMMM D, YYYY',
        LLL  : 'MMMM D, YYYY h:mm A',
        LLLL : 'dddd, MMMM D, YYYY h:mm A'
    };

    function longDateFormat (key) {
        var format = this._longDateFormat[key],
            formatUpper = this._longDateFormat[key.toUpperCase()];

        if (format || !formatUpper) {
            return format;
        }

        this._longDateFormat[key] = formatUpper.replace(/MMMM|MM|DD|dddd/g, function (val) {
            return val.slice(1);
        });

        return this._longDateFormat[key];
    }

    var defaultInvalidDate = 'Invalid date';

    function invalidDate () {
        return this._invalidDate;
    }

    var defaultOrdinal = '%d';
    var defaultDayOfMonthOrdinalParse = /\d{1,2}/;

    function ordinal (number) {
        return this._ordinal.replace('%d', number);
    }

    var defaultRelativeTime = {
        future : 'in %s',
        past   : '%s ago',
        s  : 'a few seconds',
        ss : '%d seconds',
        m  : 'a minute',
        mm : '%d minutes',
        h  : 'an hour',
        hh : '%d hours',
        d  : 'a day',
        dd : '%d days',
        M  : 'a month',
        MM : '%d months',
        y  : 'a year',
        yy : '%d years'
    };

    function relativeTime (number, withoutSuffix, string, isFuture) {
        var output = this._relativeTime[string];
        return (isFunction(output)) ?
            output(number, withoutSuffix, string, isFuture) :
            output.replace(/%d/i, number);
    }

    function pastFuture (diff, output) {
        var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
        return isFunction(format) ? format(output) : format.replace(/%s/i, output);
    }

    var aliases = {};

    function addUnitAlias (unit, shorthand) {
        var lowerCase = unit.toLowerCase();
        aliases[lowerCase] = aliases[lowerCase + 's'] = aliases[shorthand] = unit;
    }

    function normalizeUnits(units) {
        return typeof units === 'string' ? aliases[units] || aliases[units.toLowerCase()] : undefined;
    }

    function normalizeObjectUnits(inputObject) {
        var normalizedInput = {},
            normalizedProp,
            prop;

        for (prop in inputObject) {
            if (hasOwnProp(inputObject, prop)) {
                normalizedProp = normalizeUnits(prop);
                if (normalizedProp) {
                    normalizedInput[normalizedProp] = inputObject[prop];
                }
            }
        }

        return normalizedInput;
    }

    var priorities = {};

    function addUnitPriority(unit, priority) {
        priorities[unit] = priority;
    }

    function getPrioritizedUnits(unitsObj) {
        var units = [];
        for (var u in unitsObj) {
            units.push({unit: u, priority: priorities[u]});
        }
        units.sort(function (a, b) {
            return a.priority - b.priority;
        });
        return units;
    }

    function zeroFill(number, targetLength, forceSign) {
        var absNumber = '' + Math.abs(number),
            zerosToFill = targetLength - absNumber.length,
            sign = number >= 0;
        return (sign ? (forceSign ? '+' : '') : '-') +
            Math.pow(10, Math.max(0, zerosToFill)).toString().substr(1) + absNumber;
    }

    var formattingTokens = /(\[[^\[]*\])|(\\)?([Hh]mm(ss)?|Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Qo?|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|kk?|mm?|ss?|S{1,9}|x|X|zz?|ZZ?|.)/g;

    var localFormattingTokens = /(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g;

    var formatFunctions = {};

    var formatTokenFunctions = {};

    // token:    'M'
    // padded:   ['MM', 2]
    // ordinal:  'Mo'
    // callback: function () { this.month() + 1 }
    function addFormatToken (token, padded, ordinal, callback) {
        var func = callback;
        if (typeof callback === 'string') {
            func = function () {
                return this[callback]();
            };
        }
        if (token) {
            formatTokenFunctions[token] = func;
        }
        if (padded) {
            formatTokenFunctions[padded[0]] = function () {
                return zeroFill(func.apply(this, arguments), padded[1], padded[2]);
            };
        }
        if (ordinal) {
            formatTokenFunctions[ordinal] = function () {
                return this.localeData().ordinal(func.apply(this, arguments), token);
            };
        }
    }

    function removeFormattingTokens(input) {
        if (input.match(/\[[\s\S]/)) {
            return input.replace(/^\[|\]$/g, '');
        }
        return input.replace(/\\/g, '');
    }

    function makeFormatFunction(format) {
        var array = format.match(formattingTokens), i, length;

        for (i = 0, length = array.length; i < length; i++) {
            if (formatTokenFunctions[array[i]]) {
                array[i] = formatTokenFunctions[array[i]];
            } else {
                array[i] = removeFormattingTokens(array[i]);
            }
        }

        return function (mom) {
            var output = '', i;
            for (i = 0; i < length; i++) {
                output += isFunction(array[i]) ? array[i].call(mom, format) : array[i];
            }
            return output;
        };
    }

    // format date using native date object
    function formatMoment(m, format) {
        if (!m.isValid()) {
            return m.localeData().invalidDate();
        }

        format = expandFormat(format, m.localeData());
        formatFunctions[format] = formatFunctions[format] || makeFormatFunction(format);

        return formatFunctions[format](m);
    }

    function expandFormat(format, locale) {
        var i = 5;

        function replaceLongDateFormatTokens(input) {
            return locale.longDateFormat(input) || input;
        }

        localFormattingTokens.lastIndex = 0;
        while (i >= 0 && localFormattingTokens.test(format)) {
            format = format.replace(localFormattingTokens, replaceLongDateFormatTokens);
            localFormattingTokens.lastIndex = 0;
            i -= 1;
        }

        return format;
    }

    var match1         = /\d/;            //       0 - 9
    var match2         = /\d\d/;          //      00 - 99
    var match3         = /\d{3}/;         //     000 - 999
    var match4         = /\d{4}/;         //    0000 - 9999
    var match6         = /[+-]?\d{6}/;    // -999999 - 999999
    var match1to2      = /\d\d?/;         //       0 - 99
    var match3to4      = /\d\d\d\d?/;     //     999 - 9999
    var match5to6      = /\d\d\d\d\d\d?/; //   99999 - 999999
    var match1to3      = /\d{1,3}/;       //       0 - 999
    var match1to4      = /\d{1,4}/;       //       0 - 9999
    var match1to6      = /[+-]?\d{1,6}/;  // -999999 - 999999

    var matchUnsigned  = /\d+/;           //       0 - inf
    var matchSigned    = /[+-]?\d+/;      //    -inf - inf

    var matchOffset    = /Z|[+-]\d\d:?\d\d/gi; // +00:00 -00:00 +0000 -0000 or Z
    var matchShortOffset = /Z|[+-]\d\d(?::?\d\d)?/gi; // +00 -00 +00:00 -00:00 +0000 -0000 or Z

    var matchTimestamp = /[+-]?\d+(\.\d{1,3})?/; // 123456789 123456789.123

    // any word (or two) characters or numbers including two/three word month in arabic.
    // includes scottish gaelic two word and hyphenated months
    var matchWord = /[0-9]{0,256}['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFF07\uFF10-\uFFEF]{1,256}|[\u0600-\u06FF\/]{1,256}(\s*?[\u0600-\u06FF]{1,256}){1,2}/i;

    var regexes = {};

    function addRegexToken (token, regex, strictRegex) {
        regexes[token] = isFunction(regex) ? regex : function (isStrict, localeData) {
            return (isStrict && strictRegex) ? strictRegex : regex;
        };
    }

    function getParseRegexForToken (token, config) {
        if (!hasOwnProp(regexes, token)) {
            return new RegExp(unescapeFormat(token));
        }

        return regexes[token](config._strict, config._locale);
    }

    // Code from http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript
    function unescapeFormat(s) {
        return regexEscape(s.replace('\\', '').replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function (matched, p1, p2, p3, p4) {
            return p1 || p2 || p3 || p4;
        }));
    }

    function regexEscape(s) {
        return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    var tokens = {};

    function addParseToken (token, callback) {
        var i, func = callback;
        if (typeof token === 'string') {
            token = [token];
        }
        if (isNumber(callback)) {
            func = function (input, array) {
                array[callback] = toInt(input);
            };
        }
        for (i = 0; i < token.length; i++) {
            tokens[token[i]] = func;
        }
    }

    function addWeekParseToken (token, callback) {
        addParseToken(token, function (input, array, config, token) {
            config._w = config._w || {};
            callback(input, config._w, config, token);
        });
    }

    function addTimeToArrayFromToken(token, input, config) {
        if (input != null && hasOwnProp(tokens, token)) {
            tokens[token](input, config._a, config, token);
        }
    }

    var YEAR = 0;
    var MONTH = 1;
    var DATE = 2;
    var HOUR = 3;
    var MINUTE = 4;
    var SECOND = 5;
    var MILLISECOND = 6;
    var WEEK = 7;
    var WEEKDAY = 8;

    // FORMATTING

    addFormatToken('Y', 0, 0, function () {
        var y = this.year();
        return y <= 9999 ? '' + y : '+' + y;
    });

    addFormatToken(0, ['YY', 2], 0, function () {
        return this.year() % 100;
    });

    addFormatToken(0, ['YYYY',   4],       0, 'year');
    addFormatToken(0, ['YYYYY',  5],       0, 'year');
    addFormatToken(0, ['YYYYYY', 6, true], 0, 'year');

    // ALIASES

    addUnitAlias('year', 'y');

    // PRIORITIES

    addUnitPriority('year', 1);

    // PARSING

    addRegexToken('Y',      matchSigned);
    addRegexToken('YY',     match1to2, match2);
    addRegexToken('YYYY',   match1to4, match4);
    addRegexToken('YYYYY',  match1to6, match6);
    addRegexToken('YYYYYY', match1to6, match6);

    addParseToken(['YYYYY', 'YYYYYY'], YEAR);
    addParseToken('YYYY', function (input, array) {
        array[YEAR] = input.length === 2 ? hooks.parseTwoDigitYear(input) : toInt(input);
    });
    addParseToken('YY', function (input, array) {
        array[YEAR] = hooks.parseTwoDigitYear(input);
    });
    addParseToken('Y', function (input, array) {
        array[YEAR] = parseInt(input, 10);
    });

    // HELPERS

    function daysInYear(year) {
        return isLeapYear(year) ? 366 : 365;
    }

    function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
    }

    // HOOKS

    hooks.parseTwoDigitYear = function (input) {
        return toInt(input) + (toInt(input) > 68 ? 1900 : 2000);
    };

    // MOMENTS

    var getSetYear = makeGetSet('FullYear', true);

    function getIsLeapYear () {
        return isLeapYear(this.year());
    }

    function makeGetSet (unit, keepTime) {
        return function (value) {
            if (value != null) {
                set$1(this, unit, value);
                hooks.updateOffset(this, keepTime);
                return this;
            } else {
                return get(this, unit);
            }
        };
    }

    function get (mom, unit) {
        return mom.isValid() ?
            mom._d['get' + (mom._isUTC ? 'UTC' : '') + unit]() : NaN;
    }

    function set$1 (mom, unit, value) {
        if (mom.isValid() && !isNaN(value)) {
            if (unit === 'FullYear' && isLeapYear(mom.year()) && mom.month() === 1 && mom.date() === 29) {
                mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value, mom.month(), daysInMonth(value, mom.month()));
            }
            else {
                mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value);
            }
        }
    }

    // MOMENTS

    function stringGet (units) {
        units = normalizeUnits(units);
        if (isFunction(this[units])) {
            return this[units]();
        }
        return this;
    }


    function stringSet (units, value) {
        if (typeof units === 'object') {
            units = normalizeObjectUnits(units);
            var prioritized = getPrioritizedUnits(units);
            for (var i = 0; i < prioritized.length; i++) {
                this[prioritized[i].unit](units[prioritized[i].unit]);
            }
        } else {
            units = normalizeUnits(units);
            if (isFunction(this[units])) {
                return this[units](value);
            }
        }
        return this;
    }

    function mod(n, x) {
        return ((n % x) + x) % x;
    }

    var indexOf;

    if (Array.prototype.indexOf) {
        indexOf = Array.prototype.indexOf;
    } else {
        indexOf = function (o) {
            // I know
            var i;
            for (i = 0; i < this.length; ++i) {
                if (this[i] === o) {
                    return i;
                }
            }
            return -1;
        };
    }

    function daysInMonth(year, month) {
        if (isNaN(year) || isNaN(month)) {
            return NaN;
        }
        var modMonth = mod(month, 12);
        year += (month - modMonth) / 12;
        return modMonth === 1 ? (isLeapYear(year) ? 29 : 28) : (31 - modMonth % 7 % 2);
    }

    // FORMATTING

    addFormatToken('M', ['MM', 2], 'Mo', function () {
        return this.month() + 1;
    });

    addFormatToken('MMM', 0, 0, function (format) {
        return this.localeData().monthsShort(this, format);
    });

    addFormatToken('MMMM', 0, 0, function (format) {
        return this.localeData().months(this, format);
    });

    // ALIASES

    addUnitAlias('month', 'M');

    // PRIORITY

    addUnitPriority('month', 8);

    // PARSING

    addRegexToken('M',    match1to2);
    addRegexToken('MM',   match1to2, match2);
    addRegexToken('MMM',  function (isStrict, locale) {
        return locale.monthsShortRegex(isStrict);
    });
    addRegexToken('MMMM', function (isStrict, locale) {
        return locale.monthsRegex(isStrict);
    });

    addParseToken(['M', 'MM'], function (input, array) {
        array[MONTH] = toInt(input) - 1;
    });

    addParseToken(['MMM', 'MMMM'], function (input, array, config, token) {
        var month = config._locale.monthsParse(input, token, config._strict);
        // if we didn't find a month name, mark the date as invalid.
        if (month != null) {
            array[MONTH] = month;
        } else {
            getParsingFlags(config).invalidMonth = input;
        }
    });

    // LOCALES

    var MONTHS_IN_FORMAT = /D[oD]?(\[[^\[\]]*\]|\s)+MMMM?/;
    var defaultLocaleMonths = 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_');
    function localeMonths (m, format) {
        if (!m) {
            return isArray(this._months) ? this._months :
                this._months['standalone'];
        }
        return isArray(this._months) ? this._months[m.month()] :
            this._months[(this._months.isFormat || MONTHS_IN_FORMAT).test(format) ? 'format' : 'standalone'][m.month()];
    }

    var defaultLocaleMonthsShort = 'Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'.split('_');
    function localeMonthsShort (m, format) {
        if (!m) {
            return isArray(this._monthsShort) ? this._monthsShort :
                this._monthsShort['standalone'];
        }
        return isArray(this._monthsShort) ? this._monthsShort[m.month()] :
            this._monthsShort[MONTHS_IN_FORMAT.test(format) ? 'format' : 'standalone'][m.month()];
    }

    function handleStrictParse(monthName, format, strict) {
        var i, ii, mom, llc = monthName.toLocaleLowerCase();
        if (!this._monthsParse) {
            // this is not used
            this._monthsParse = [];
            this._longMonthsParse = [];
            this._shortMonthsParse = [];
            for (i = 0; i < 12; ++i) {
                mom = createUTC([2000, i]);
                this._shortMonthsParse[i] = this.monthsShort(mom, '').toLocaleLowerCase();
                this._longMonthsParse[i] = this.months(mom, '').toLocaleLowerCase();
            }
        }

        if (strict) {
            if (format === 'MMM') {
                ii = indexOf.call(this._shortMonthsParse, llc);
                return ii !== -1 ? ii : null;
            } else {
                ii = indexOf.call(this._longMonthsParse, llc);
                return ii !== -1 ? ii : null;
            }
        } else {
            if (format === 'MMM') {
                ii = indexOf.call(this._shortMonthsParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._longMonthsParse, llc);
                return ii !== -1 ? ii : null;
            } else {
                ii = indexOf.call(this._longMonthsParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._shortMonthsParse, llc);
                return ii !== -1 ? ii : null;
            }
        }
    }

    function localeMonthsParse (monthName, format, strict) {
        var i, mom, regex;

        if (this._monthsParseExact) {
            return handleStrictParse.call(this, monthName, format, strict);
        }

        if (!this._monthsParse) {
            this._monthsParse = [];
            this._longMonthsParse = [];
            this._shortMonthsParse = [];
        }

        // TODO: add sorting
        // Sorting makes sure if one month (or abbr) is a prefix of another
        // see sorting in computeMonthsParse
        for (i = 0; i < 12; i++) {
            // make the regex if we don't have it already
            mom = createUTC([2000, i]);
            if (strict && !this._longMonthsParse[i]) {
                this._longMonthsParse[i] = new RegExp('^' + this.months(mom, '').replace('.', '') + '$', 'i');
                this._shortMonthsParse[i] = new RegExp('^' + this.monthsShort(mom, '').replace('.', '') + '$', 'i');
            }
            if (!strict && !this._monthsParse[i]) {
                regex = '^' + this.months(mom, '') + '|^' + this.monthsShort(mom, '');
                this._monthsParse[i] = new RegExp(regex.replace('.', ''), 'i');
            }
            // test the regex
            if (strict && format === 'MMMM' && this._longMonthsParse[i].test(monthName)) {
                return i;
            } else if (strict && format === 'MMM' && this._shortMonthsParse[i].test(monthName)) {
                return i;
            } else if (!strict && this._monthsParse[i].test(monthName)) {
                return i;
            }
        }
    }

    // MOMENTS

    function setMonth (mom, value) {
        var dayOfMonth;

        if (!mom.isValid()) {
            // No op
            return mom;
        }

        if (typeof value === 'string') {
            if (/^\d+$/.test(value)) {
                value = toInt(value);
            } else {
                value = mom.localeData().monthsParse(value);
                // TODO: Another silent failure?
                if (!isNumber(value)) {
                    return mom;
                }
            }
        }

        dayOfMonth = Math.min(mom.date(), daysInMonth(mom.year(), value));
        mom._d['set' + (mom._isUTC ? 'UTC' : '') + 'Month'](value, dayOfMonth);
        return mom;
    }

    function getSetMonth (value) {
        if (value != null) {
            setMonth(this, value);
            hooks.updateOffset(this, true);
            return this;
        } else {
            return get(this, 'Month');
        }
    }

    function getDaysInMonth () {
        return daysInMonth(this.year(), this.month());
    }

    var defaultMonthsShortRegex = matchWord;
    function monthsShortRegex (isStrict) {
        if (this._monthsParseExact) {
            if (!hasOwnProp(this, '_monthsRegex')) {
                computeMonthsParse.call(this);
            }
            if (isStrict) {
                return this._monthsShortStrictRegex;
            } else {
                return this._monthsShortRegex;
            }
        } else {
            if (!hasOwnProp(this, '_monthsShortRegex')) {
                this._monthsShortRegex = defaultMonthsShortRegex;
            }
            return this._monthsShortStrictRegex && isStrict ?
                this._monthsShortStrictRegex : this._monthsShortRegex;
        }
    }

    var defaultMonthsRegex = matchWord;
    function monthsRegex (isStrict) {
        if (this._monthsParseExact) {
            if (!hasOwnProp(this, '_monthsRegex')) {
                computeMonthsParse.call(this);
            }
            if (isStrict) {
                return this._monthsStrictRegex;
            } else {
                return this._monthsRegex;
            }
        } else {
            if (!hasOwnProp(this, '_monthsRegex')) {
                this._monthsRegex = defaultMonthsRegex;
            }
            return this._monthsStrictRegex && isStrict ?
                this._monthsStrictRegex : this._monthsRegex;
        }
    }

    function computeMonthsParse () {
        function cmpLenRev(a, b) {
            return b.length - a.length;
        }

        var shortPieces = [], longPieces = [], mixedPieces = [],
            i, mom;
        for (i = 0; i < 12; i++) {
            // make the regex if we don't have it already
            mom = createUTC([2000, i]);
            shortPieces.push(this.monthsShort(mom, ''));
            longPieces.push(this.months(mom, ''));
            mixedPieces.push(this.months(mom, ''));
            mixedPieces.push(this.monthsShort(mom, ''));
        }
        // Sorting makes sure if one month (or abbr) is a prefix of another it
        // will match the longer piece.
        shortPieces.sort(cmpLenRev);
        longPieces.sort(cmpLenRev);
        mixedPieces.sort(cmpLenRev);
        for (i = 0; i < 12; i++) {
            shortPieces[i] = regexEscape(shortPieces[i]);
            longPieces[i] = regexEscape(longPieces[i]);
        }
        for (i = 0; i < 24; i++) {
            mixedPieces[i] = regexEscape(mixedPieces[i]);
        }

        this._monthsRegex = new RegExp('^(' + mixedPieces.join('|') + ')', 'i');
        this._monthsShortRegex = this._monthsRegex;
        this._monthsStrictRegex = new RegExp('^(' + longPieces.join('|') + ')', 'i');
        this._monthsShortStrictRegex = new RegExp('^(' + shortPieces.join('|') + ')', 'i');
    }

    function createDate (y, m, d, h, M, s, ms) {
        // can't just apply() to create a date:
        // https://stackoverflow.com/q/181348
        var date;
        // the date constructor remaps years 0-99 to 1900-1999
        if (y < 100 && y >= 0) {
            // preserve leap years using a full 400 year cycle, then reset
            date = new Date(y + 400, m, d, h, M, s, ms);
            if (isFinite(date.getFullYear())) {
                date.setFullYear(y);
            }
        } else {
            date = new Date(y, m, d, h, M, s, ms);
        }

        return date;
    }

    function createUTCDate (y) {
        var date;
        // the Date.UTC function remaps years 0-99 to 1900-1999
        if (y < 100 && y >= 0) {
            var args = Array.prototype.slice.call(arguments);
            // preserve leap years using a full 400 year cycle, then reset
            args[0] = y + 400;
            date = new Date(Date.UTC.apply(null, args));
            if (isFinite(date.getUTCFullYear())) {
                date.setUTCFullYear(y);
            }
        } else {
            date = new Date(Date.UTC.apply(null, arguments));
        }

        return date;
    }

    // start-of-first-week - start-of-year
    function firstWeekOffset(year, dow, doy) {
        var // first-week day -- which january is always in the first week (4 for iso, 1 for other)
            fwd = 7 + dow - doy,
            // first-week day local weekday -- which local weekday is fwd
            fwdlw = (7 + createUTCDate(year, 0, fwd).getUTCDay() - dow) % 7;

        return -fwdlw + fwd - 1;
    }

    // https://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
    function dayOfYearFromWeeks(year, week, weekday, dow, doy) {
        var localWeekday = (7 + weekday - dow) % 7,
            weekOffset = firstWeekOffset(year, dow, doy),
            dayOfYear = 1 + 7 * (week - 1) + localWeekday + weekOffset,
            resYear, resDayOfYear;

        if (dayOfYear <= 0) {
            resYear = year - 1;
            resDayOfYear = daysInYear(resYear) + dayOfYear;
        } else if (dayOfYear > daysInYear(year)) {
            resYear = year + 1;
            resDayOfYear = dayOfYear - daysInYear(year);
        } else {
            resYear = year;
            resDayOfYear = dayOfYear;
        }

        return {
            year: resYear,
            dayOfYear: resDayOfYear
        };
    }

    function weekOfYear(mom, dow, doy) {
        var weekOffset = firstWeekOffset(mom.year(), dow, doy),
            week = Math.floor((mom.dayOfYear() - weekOffset - 1) / 7) + 1,
            resWeek, resYear;

        if (week < 1) {
            resYear = mom.year() - 1;
            resWeek = week + weeksInYear(resYear, dow, doy);
        } else if (week > weeksInYear(mom.year(), dow, doy)) {
            resWeek = week - weeksInYear(mom.year(), dow, doy);
            resYear = mom.year() + 1;
        } else {
            resYear = mom.year();
            resWeek = week;
        }

        return {
            week: resWeek,
            year: resYear
        };
    }

    function weeksInYear(year, dow, doy) {
        var weekOffset = firstWeekOffset(year, dow, doy),
            weekOffsetNext = firstWeekOffset(year + 1, dow, doy);
        return (daysInYear(year) - weekOffset + weekOffsetNext) / 7;
    }

    // FORMATTING

    addFormatToken('w', ['ww', 2], 'wo', 'week');
    addFormatToken('W', ['WW', 2], 'Wo', 'isoWeek');

    // ALIASES

    addUnitAlias('week', 'w');
    addUnitAlias('isoWeek', 'W');

    // PRIORITIES

    addUnitPriority('week', 5);
    addUnitPriority('isoWeek', 5);

    // PARSING

    addRegexToken('w',  match1to2);
    addRegexToken('ww', match1to2, match2);
    addRegexToken('W',  match1to2);
    addRegexToken('WW', match1to2, match2);

    addWeekParseToken(['w', 'ww', 'W', 'WW'], function (input, week, config, token) {
        week[token.substr(0, 1)] = toInt(input);
    });

    // HELPERS

    // LOCALES

    function localeWeek (mom) {
        return weekOfYear(mom, this._week.dow, this._week.doy).week;
    }

    var defaultLocaleWeek = {
        dow : 0, // Sunday is the first day of the week.
        doy : 6  // The week that contains Jan 6th is the first week of the year.
    };

    function localeFirstDayOfWeek () {
        return this._week.dow;
    }

    function localeFirstDayOfYear () {
        return this._week.doy;
    }

    // MOMENTS

    function getSetWeek (input) {
        var week = this.localeData().week(this);
        return input == null ? week : this.add((input - week) * 7, 'd');
    }

    function getSetISOWeek (input) {
        var week = weekOfYear(this, 1, 4).week;
        return input == null ? week : this.add((input - week) * 7, 'd');
    }

    // FORMATTING

    addFormatToken('d', 0, 'do', 'day');

    addFormatToken('dd', 0, 0, function (format) {
        return this.localeData().weekdaysMin(this, format);
    });

    addFormatToken('ddd', 0, 0, function (format) {
        return this.localeData().weekdaysShort(this, format);
    });

    addFormatToken('dddd', 0, 0, function (format) {
        return this.localeData().weekdays(this, format);
    });

    addFormatToken('e', 0, 0, 'weekday');
    addFormatToken('E', 0, 0, 'isoWeekday');

    // ALIASES

    addUnitAlias('day', 'd');
    addUnitAlias('weekday', 'e');
    addUnitAlias('isoWeekday', 'E');

    // PRIORITY
    addUnitPriority('day', 11);
    addUnitPriority('weekday', 11);
    addUnitPriority('isoWeekday', 11);

    // PARSING

    addRegexToken('d',    match1to2);
    addRegexToken('e',    match1to2);
    addRegexToken('E',    match1to2);
    addRegexToken('dd',   function (isStrict, locale) {
        return locale.weekdaysMinRegex(isStrict);
    });
    addRegexToken('ddd',   function (isStrict, locale) {
        return locale.weekdaysShortRegex(isStrict);
    });
    addRegexToken('dddd',   function (isStrict, locale) {
        return locale.weekdaysRegex(isStrict);
    });

    addWeekParseToken(['dd', 'ddd', 'dddd'], function (input, week, config, token) {
        var weekday = config._locale.weekdaysParse(input, token, config._strict);
        // if we didn't get a weekday name, mark the date as invalid
        if (weekday != null) {
            week.d = weekday;
        } else {
            getParsingFlags(config).invalidWeekday = input;
        }
    });

    addWeekParseToken(['d', 'e', 'E'], function (input, week, config, token) {
        week[token] = toInt(input);
    });

    // HELPERS

    function parseWeekday(input, locale) {
        if (typeof input !== 'string') {
            return input;
        }

        if (!isNaN(input)) {
            return parseInt(input, 10);
        }

        input = locale.weekdaysParse(input);
        if (typeof input === 'number') {
            return input;
        }

        return null;
    }

    function parseIsoWeekday(input, locale) {
        if (typeof input === 'string') {
            return locale.weekdaysParse(input) % 7 || 7;
        }
        return isNaN(input) ? null : input;
    }

    // LOCALES
    function shiftWeekdays (ws, n) {
        return ws.slice(n, 7).concat(ws.slice(0, n));
    }

    var defaultLocaleWeekdays = 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_');
    function localeWeekdays (m, format) {
        var weekdays = isArray(this._weekdays) ? this._weekdays :
            this._weekdays[(m && m !== true && this._weekdays.isFormat.test(format)) ? 'format' : 'standalone'];
        return (m === true) ? shiftWeekdays(weekdays, this._week.dow)
            : (m) ? weekdays[m.day()] : weekdays;
    }

    var defaultLocaleWeekdaysShort = 'Sun_Mon_Tue_Wed_Thu_Fri_Sat'.split('_');
    function localeWeekdaysShort (m) {
        return (m === true) ? shiftWeekdays(this._weekdaysShort, this._week.dow)
            : (m) ? this._weekdaysShort[m.day()] : this._weekdaysShort;
    }

    var defaultLocaleWeekdaysMin = 'Su_Mo_Tu_We_Th_Fr_Sa'.split('_');
    function localeWeekdaysMin (m) {
        return (m === true) ? shiftWeekdays(this._weekdaysMin, this._week.dow)
            : (m) ? this._weekdaysMin[m.day()] : this._weekdaysMin;
    }

    function handleStrictParse$1(weekdayName, format, strict) {
        var i, ii, mom, llc = weekdayName.toLocaleLowerCase();
        if (!this._weekdaysParse) {
            this._weekdaysParse = [];
            this._shortWeekdaysParse = [];
            this._minWeekdaysParse = [];

            for (i = 0; i < 7; ++i) {
                mom = createUTC([2000, 1]).day(i);
                this._minWeekdaysParse[i] = this.weekdaysMin(mom, '').toLocaleLowerCase();
                this._shortWeekdaysParse[i] = this.weekdaysShort(mom, '').toLocaleLowerCase();
                this._weekdaysParse[i] = this.weekdays(mom, '').toLocaleLowerCase();
            }
        }

        if (strict) {
            if (format === 'dddd') {
                ii = indexOf.call(this._weekdaysParse, llc);
                return ii !== -1 ? ii : null;
            } else if (format === 'ddd') {
                ii = indexOf.call(this._shortWeekdaysParse, llc);
                return ii !== -1 ? ii : null;
            } else {
                ii = indexOf.call(this._minWeekdaysParse, llc);
                return ii !== -1 ? ii : null;
            }
        } else {
            if (format === 'dddd') {
                ii = indexOf.call(this._weekdaysParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._shortWeekdaysParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._minWeekdaysParse, llc);
                return ii !== -1 ? ii : null;
            } else if (format === 'ddd') {
                ii = indexOf.call(this._shortWeekdaysParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._weekdaysParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._minWeekdaysParse, llc);
                return ii !== -1 ? ii : null;
            } else {
                ii = indexOf.call(this._minWeekdaysParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._weekdaysParse, llc);
                if (ii !== -1) {
                    return ii;
                }
                ii = indexOf.call(this._shortWeekdaysParse, llc);
                return ii !== -1 ? ii : null;
            }
        }
    }

    function localeWeekdaysParse (weekdayName, format, strict) {
        var i, mom, regex;

        if (this._weekdaysParseExact) {
            return handleStrictParse$1.call(this, weekdayName, format, strict);
        }

        if (!this._weekdaysParse) {
            this._weekdaysParse = [];
            this._minWeekdaysParse = [];
            this._shortWeekdaysParse = [];
            this._fullWeekdaysParse = [];
        }

        for (i = 0; i < 7; i++) {
            // make the regex if we don't have it already

            mom = createUTC([2000, 1]).day(i);
            if (strict && !this._fullWeekdaysParse[i]) {
                this._fullWeekdaysParse[i] = new RegExp('^' + this.weekdays(mom, '').replace('.', '\\.?') + '$', 'i');
                this._shortWeekdaysParse[i] = new RegExp('^' + this.weekdaysShort(mom, '').replace('.', '\\.?') + '$', 'i');
                this._minWeekdaysParse[i] = new RegExp('^' + this.weekdaysMin(mom, '').replace('.', '\\.?') + '$', 'i');
            }
            if (!this._weekdaysParse[i]) {
                regex = '^' + this.weekdays(mom, '') + '|^' + this.weekdaysShort(mom, '') + '|^' + this.weekdaysMin(mom, '');
                this._weekdaysParse[i] = new RegExp(regex.replace('.', ''), 'i');
            }
            // test the regex
            if (strict && format === 'dddd' && this._fullWeekdaysParse[i].test(weekdayName)) {
                return i;
            } else if (strict && format === 'ddd' && this._shortWeekdaysParse[i].test(weekdayName)) {
                return i;
            } else if (strict && format === 'dd' && this._minWeekdaysParse[i].test(weekdayName)) {
                return i;
            } else if (!strict && this._weekdaysParse[i].test(weekdayName)) {
                return i;
            }
        }
    }

    // MOMENTS

    function getSetDayOfWeek (input) {
        if (!this.isValid()) {
            return input != null ? this : NaN;
        }
        var day = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
        if (input != null) {
            input = parseWeekday(input, this.localeData());
            return this.add(input - day, 'd');
        } else {
            return day;
        }
    }

    function getSetLocaleDayOfWeek (input) {
        if (!this.isValid()) {
            return input != null ? this : NaN;
        }
        var weekday = (this.day() + 7 - this.localeData()._week.dow) % 7;
        return input == null ? weekday : this.add(input - weekday, 'd');
    }

    function getSetISODayOfWeek (input) {
        if (!this.isValid()) {
            return input != null ? this : NaN;
        }

        // behaves the same as moment#day except
        // as a getter, returns 7 instead of 0 (1-7 range instead of 0-6)
        // as a setter, sunday should belong to the previous week.

        if (input != null) {
            var weekday = parseIsoWeekday(input, this.localeData());
            return this.day(this.day() % 7 ? weekday : weekday - 7);
        } else {
            return this.day() || 7;
        }
    }

    var defaultWeekdaysRegex = matchWord;
    function weekdaysRegex (isStrict) {
        if (this._weekdaysParseExact) {
            if (!hasOwnProp(this, '_weekdaysRegex')) {
                computeWeekdaysParse.call(this);
            }
            if (isStrict) {
                return this._weekdaysStrictRegex;
            } else {
                return this._weekdaysRegex;
            }
        } else {
            if (!hasOwnProp(this, '_weekdaysRegex')) {
                this._weekdaysRegex = defaultWeekdaysRegex;
            }
            return this._weekdaysStrictRegex && isStrict ?
                this._weekdaysStrictRegex : this._weekdaysRegex;
        }
    }

    var defaultWeekdaysShortRegex = matchWord;
    function weekdaysShortRegex (isStrict) {
        if (this._weekdaysParseExact) {
            if (!hasOwnProp(this, '_weekdaysRegex')) {
                computeWeekdaysParse.call(this);
            }
            if (isStrict) {
                return this._weekdaysShortStrictRegex;
            } else {
                return this._weekdaysShortRegex;
            }
        } else {
            if (!hasOwnProp(this, '_weekdaysShortRegex')) {
                this._weekdaysShortRegex = defaultWeekdaysShortRegex;
            }
            return this._weekdaysShortStrictRegex && isStrict ?
                this._weekdaysShortStrictRegex : this._weekdaysShortRegex;
        }
    }

    var defaultWeekdaysMinRegex = matchWord;
    function weekdaysMinRegex (isStrict) {
        if (this._weekdaysParseExact) {
            if (!hasOwnProp(this, '_weekdaysRegex')) {
                computeWeekdaysParse.call(this);
            }
            if (isStrict) {
                return this._weekdaysMinStrictRegex;
            } else {
                return this._weekdaysMinRegex;
            }
        } else {
            if (!hasOwnProp(this, '_weekdaysMinRegex')) {
                this._weekdaysMinRegex = defaultWeekdaysMinRegex;
            }
            return this._weekdaysMinStrictRegex && isStrict ?
                this._weekdaysMinStrictRegex : this._weekdaysMinRegex;
        }
    }


    function computeWeekdaysParse () {
        function cmpLenRev(a, b) {
            return b.length - a.length;
        }

        var minPieces = [], shortPieces = [], longPieces = [], mixedPieces = [],
            i, mom, minp, shortp, longp;
        for (i = 0; i < 7; i++) {
            // make the regex if we don't have it already
            mom = createUTC([2000, 1]).day(i);
            minp = this.weekdaysMin(mom, '');
            shortp = this.weekdaysShort(mom, '');
            longp = this.weekdays(mom, '');
            minPieces.push(minp);
            shortPieces.push(shortp);
            longPieces.push(longp);
            mixedPieces.push(minp);
            mixedPieces.push(shortp);
            mixedPieces.push(longp);
        }
        // Sorting makes sure if one weekday (or abbr) is a prefix of another it
        // will match the longer piece.
        minPieces.sort(cmpLenRev);
        shortPieces.sort(cmpLenRev);
        longPieces.sort(cmpLenRev);
        mixedPieces.sort(cmpLenRev);
        for (i = 0; i < 7; i++) {
            shortPieces[i] = regexEscape(shortPieces[i]);
            longPieces[i] = regexEscape(longPieces[i]);
            mixedPieces[i] = regexEscape(mixedPieces[i]);
        }

        this._weekdaysRegex = new RegExp('^(' + mixedPieces.join('|') + ')', 'i');
        this._weekdaysShortRegex = this._weekdaysRegex;
        this._weekdaysMinRegex = this._weekdaysRegex;

        this._weekdaysStrictRegex = new RegExp('^(' + longPieces.join('|') + ')', 'i');
        this._weekdaysShortStrictRegex = new RegExp('^(' + shortPieces.join('|') + ')', 'i');
        this._weekdaysMinStrictRegex = new RegExp('^(' + minPieces.join('|') + ')', 'i');
    }

    // FORMATTING

    function hFormat() {
        return this.hours() % 12 || 12;
    }

    function kFormat() {
        return this.hours() || 24;
    }

    addFormatToken('H', ['HH', 2], 0, 'hour');
    addFormatToken('h', ['hh', 2], 0, hFormat);
    addFormatToken('k', ['kk', 2], 0, kFormat);

    addFormatToken('hmm', 0, 0, function () {
        return '' + hFormat.apply(this) + zeroFill(this.minutes(), 2);
    });

    addFormatToken('hmmss', 0, 0, function () {
        return '' + hFormat.apply(this) + zeroFill(this.minutes(), 2) +
            zeroFill(this.seconds(), 2);
    });

    addFormatToken('Hmm', 0, 0, function () {
        return '' + this.hours() + zeroFill(this.minutes(), 2);
    });

    addFormatToken('Hmmss', 0, 0, function () {
        return '' + this.hours() + zeroFill(this.minutes(), 2) +
            zeroFill(this.seconds(), 2);
    });

    function meridiem (token, lowercase) {
        addFormatToken(token, 0, 0, function () {
            return this.localeData().meridiem(this.hours(), this.minutes(), lowercase);
        });
    }

    meridiem('a', true);
    meridiem('A', false);

    // ALIASES

    addUnitAlias('hour', 'h');

    // PRIORITY
    addUnitPriority('hour', 13);

    // PARSING

    function matchMeridiem (isStrict, locale) {
        return locale._meridiemParse;
    }

    addRegexToken('a',  matchMeridiem);
    addRegexToken('A',  matchMeridiem);
    addRegexToken('H',  match1to2);
    addRegexToken('h',  match1to2);
    addRegexToken('k',  match1to2);
    addRegexToken('HH', match1to2, match2);
    addRegexToken('hh', match1to2, match2);
    addRegexToken('kk', match1to2, match2);

    addRegexToken('hmm', match3to4);
    addRegexToken('hmmss', match5to6);
    addRegexToken('Hmm', match3to4);
    addRegexToken('Hmmss', match5to6);

    addParseToken(['H', 'HH'], HOUR);
    addParseToken(['k', 'kk'], function (input, array, config) {
        var kInput = toInt(input);
        array[HOUR] = kInput === 24 ? 0 : kInput;
    });
    addParseToken(['a', 'A'], function (input, array, config) {
        config._isPm = config._locale.isPM(input);
        config._meridiem = input;
    });
    addParseToken(['h', 'hh'], function (input, array, config) {
        array[HOUR] = toInt(input);
        getParsingFlags(config).bigHour = true;
    });
    addParseToken('hmm', function (input, array, config) {
        var pos = input.length - 2;
        array[HOUR] = toInt(input.substr(0, pos));
        array[MINUTE] = toInt(input.substr(pos));
        getParsingFlags(config).bigHour = true;
    });
    addParseToken('hmmss', function (input, array, config) {
        var pos1 = input.length - 4;
        var pos2 = input.length - 2;
        array[HOUR] = toInt(input.substr(0, pos1));
        array[MINUTE] = toInt(input.substr(pos1, 2));
        array[SECOND] = toInt(input.substr(pos2));
        getParsingFlags(config).bigHour = true;
    });
    addParseToken('Hmm', function (input, array, config) {
        var pos = input.length - 2;
        array[HOUR] = toInt(input.substr(0, pos));
        array[MINUTE] = toInt(input.substr(pos));
    });
    addParseToken('Hmmss', function (input, array, config) {
        var pos1 = input.length - 4;
        var pos2 = input.length - 2;
        array[HOUR] = toInt(input.substr(0, pos1));
        array[MINUTE] = toInt(input.substr(pos1, 2));
        array[SECOND] = toInt(input.substr(pos2));
    });

    // LOCALES

    function localeIsPM (input) {
        // IE8 Quirks Mode & IE7 Standards Mode do not allow accessing strings like arrays
        // Using charAt should be more compatible.
        return ((input + '').toLowerCase().charAt(0) === 'p');
    }

    var defaultLocaleMeridiemParse = /[ap]\.?m?\.?/i;
    function localeMeridiem (hours, minutes, isLower) {
        if (hours > 11) {
            return isLower ? 'pm' : 'PM';
        } else {
            return isLower ? 'am' : 'AM';
        }
    }


    // MOMENTS

    // Setting the hour should keep the time, because the user explicitly
    // specified which hour they want. So trying to maintain the same hour (in
    // a new timezone) makes sense. Adding/subtracting hours does not follow
    // this rule.
    var getSetHour = makeGetSet('Hours', true);

    var baseConfig = {
        calendar: defaultCalendar,
        longDateFormat: defaultLongDateFormat,
        invalidDate: defaultInvalidDate,
        ordinal: defaultOrdinal,
        dayOfMonthOrdinalParse: defaultDayOfMonthOrdinalParse,
        relativeTime: defaultRelativeTime,

        months: defaultLocaleMonths,
        monthsShort: defaultLocaleMonthsShort,

        week: defaultLocaleWeek,

        weekdays: defaultLocaleWeekdays,
        weekdaysMin: defaultLocaleWeekdaysMin,
        weekdaysShort: defaultLocaleWeekdaysShort,

        meridiemParse: defaultLocaleMeridiemParse
    };

    // internal storage for locale config files
    var locales = {};
    var localeFamilies = {};
    var globalLocale;

    function normalizeLocale(key) {
        return key ? key.toLowerCase().replace('_', '-') : key;
    }

    // pick the locale from the array
    // try ['en-au', 'en-gb'] as 'en-au', 'en-gb', 'en', as in move through the list trying each
    // substring from most specific to least, but move to the next array item if it's a more specific variant than the current root
    function chooseLocale(names) {
        var i = 0, j, next, locale, split;

        while (i < names.length) {
            split = normalizeLocale(names[i]).split('-');
            j = split.length;
            next = normalizeLocale(names[i + 1]);
            next = next ? next.split('-') : null;
            while (j > 0) {
                locale = loadLocale(split.slice(0, j).join('-'));
                if (locale) {
                    return locale;
                }
                if (next && next.length >= j && compareArrays(split, next, true) >= j - 1) {
                    //the next array item is better than a shallower substring of this one
                    break;
                }
                j--;
            }
            i++;
        }
        return globalLocale;
    }

    function loadLocale(name) {
        var oldLocale = null;
        // TODO: Find a better way to register and load all the locales in Node
        if (!locales[name] && (typeof module !== 'undefined') &&
                module && module.exports) {
            try {
                oldLocale = globalLocale._abbr;
                var aliasedRequire = require;
                aliasedRequire('./locale/' + name);
                getSetGlobalLocale(oldLocale);
            } catch (e) {}
        }
        return locales[name];
    }

    // This function will load locale and then set the global locale.  If
    // no arguments are passed in, it will simply return the current global
    // locale key.
    function getSetGlobalLocale (key, values) {
        var data;
        if (key) {
            if (isUndefined(values)) {
                data = getLocale(key);
            }
            else {
                data = defineLocale(key, values);
            }

            if (data) {
                // moment.duration._locale = moment._locale = data;
                globalLocale = data;
            }
            else {
                if ((typeof console !==  'undefined') && console.warn) {
                    //warn user if arguments are passed but the locale could not be set
                    console.warn('Locale ' + key +  ' not found. Did you forget to load it?');
                }
            }
        }

        return globalLocale._abbr;
    }

    function defineLocale (name, config) {
        if (config !== null) {
            var locale, parentConfig = baseConfig;
            config.abbr = name;
            if (locales[name] != null) {
                deprecateSimple('defineLocaleOverride',
                        'use moment.updateLocale(localeName, config) to change ' +
                        'an existing locale. moment.defineLocale(localeName, ' +
                        'config) should only be used for creating a new locale ' +
                        'See http://momentjs.com/guides/#/warnings/define-locale/ for more info.');
                parentConfig = locales[name]._config;
            } else if (config.parentLocale != null) {
                if (locales[config.parentLocale] != null) {
                    parentConfig = locales[config.parentLocale]._config;
                } else {
                    locale = loadLocale(config.parentLocale);
                    if (locale != null) {
                        parentConfig = locale._config;
                    } else {
                        if (!localeFamilies[config.parentLocale]) {
                            localeFamilies[config.parentLocale] = [];
                        }
                        localeFamilies[config.parentLocale].push({
                            name: name,
                            config: config
                        });
                        return null;
                    }
                }
            }
            locales[name] = new Locale(mergeConfigs(parentConfig, config));

            if (localeFamilies[name]) {
                localeFamilies[name].forEach(function (x) {
                    defineLocale(x.name, x.config);
                });
            }

            // backwards compat for now: also set the locale
            // make sure we set the locale AFTER all child locales have been
            // created, so we won't end up with the child locale set.
            getSetGlobalLocale(name);


            return locales[name];
        } else {
            // useful for testing
            delete locales[name];
            return null;
        }
    }

    function updateLocale(name, config) {
        if (config != null) {
            var locale, tmpLocale, parentConfig = baseConfig;
            // MERGE
            tmpLocale = loadLocale(name);
            if (tmpLocale != null) {
                parentConfig = tmpLocale._config;
            }
            config = mergeConfigs(parentConfig, config);
            locale = new Locale(config);
            locale.parentLocale = locales[name];
            locales[name] = locale;

            // backwards compat for now: also set the locale
            getSetGlobalLocale(name);
        } else {
            // pass null for config to unupdate, useful for tests
            if (locales[name] != null) {
                if (locales[name].parentLocale != null) {
                    locales[name] = locales[name].parentLocale;
                } else if (locales[name] != null) {
                    delete locales[name];
                }
            }
        }
        return locales[name];
    }

    // returns locale data
    function getLocale (key) {
        var locale;

        if (key && key._locale && key._locale._abbr) {
            key = key._locale._abbr;
        }

        if (!key) {
            return globalLocale;
        }

        if (!isArray(key)) {
            //short-circuit everything else
            locale = loadLocale(key);
            if (locale) {
                return locale;
            }
            key = [key];
        }

        return chooseLocale(key);
    }

    function listLocales() {
        return keys(locales);
    }

    function checkOverflow (m) {
        var overflow;
        var a = m._a;

        if (a && getParsingFlags(m).overflow === -2) {
            overflow =
                a[MONTH]       < 0 || a[MONTH]       > 11  ? MONTH :
                a[DATE]        < 1 || a[DATE]        > daysInMonth(a[YEAR], a[MONTH]) ? DATE :
                a[HOUR]        < 0 || a[HOUR]        > 24 || (a[HOUR] === 24 && (a[MINUTE] !== 0 || a[SECOND] !== 0 || a[MILLISECOND] !== 0)) ? HOUR :
                a[MINUTE]      < 0 || a[MINUTE]      > 59  ? MINUTE :
                a[SECOND]      < 0 || a[SECOND]      > 59  ? SECOND :
                a[MILLISECOND] < 0 || a[MILLISECOND] > 999 ? MILLISECOND :
                -1;

            if (getParsingFlags(m)._overflowDayOfYear && (overflow < YEAR || overflow > DATE)) {
                overflow = DATE;
            }
            if (getParsingFlags(m)._overflowWeeks && overflow === -1) {
                overflow = WEEK;
            }
            if (getParsingFlags(m)._overflowWeekday && overflow === -1) {
                overflow = WEEKDAY;
            }

            getParsingFlags(m).overflow = overflow;
        }

        return m;
    }

    // Pick the first defined of two or three arguments.
    function defaults(a, b, c) {
        if (a != null) {
            return a;
        }
        if (b != null) {
            return b;
        }
        return c;
    }

    function currentDateArray(config) {
        // hooks is actually the exported moment object
        var nowValue = new Date(hooks.now());
        if (config._useUTC) {
            return [nowValue.getUTCFullYear(), nowValue.getUTCMonth(), nowValue.getUTCDate()];
        }
        return [nowValue.getFullYear(), nowValue.getMonth(), nowValue.getDate()];
    }

    // convert an array to a date.
    // the array should mirror the parameters below
    // note: all values past the year are optional and will default to the lowest possible value.
    // [year, month, day , hour, minute, second, millisecond]
    function configFromArray (config) {
        var i, date, input = [], currentDate, expectedWeekday, yearToUse;

        if (config._d) {
            return;
        }

        currentDate = currentDateArray(config);

        //compute day of the year from weeks and weekdays
        if (config._w && config._a[DATE] == null && config._a[MONTH] == null) {
            dayOfYearFromWeekInfo(config);
        }

        //if the day of the year is set, figure out what it is
        if (config._dayOfYear != null) {
            yearToUse = defaults(config._a[YEAR], currentDate[YEAR]);

            if (config._dayOfYear > daysInYear(yearToUse) || config._dayOfYear === 0) {
                getParsingFlags(config)._overflowDayOfYear = true;
            }

            date = createUTCDate(yearToUse, 0, config._dayOfYear);
            config._a[MONTH] = date.getUTCMonth();
            config._a[DATE] = date.getUTCDate();
        }

        // Default to current date.
        // * if no year, month, day of month are given, default to today
        // * if day of month is given, default month and year
        // * if month is given, default only year
        // * if year is given, don't default anything
        for (i = 0; i < 3 && config._a[i] == null; ++i) {
            config._a[i] = input[i] = currentDate[i];
        }

        // Zero out whatever was not defaulted, including time
        for (; i < 7; i++) {
            config._a[i] = input[i] = (config._a[i] == null) ? (i === 2 ? 1 : 0) : config._a[i];
        }

        // Check for 24:00:00.000
        if (config._a[HOUR] === 24 &&
                config._a[MINUTE] === 0 &&
                config._a[SECOND] === 0 &&
                config._a[MILLISECOND] === 0) {
            config._nextDay = true;
            config._a[HOUR] = 0;
        }

        config._d = (config._useUTC ? createUTCDate : createDate).apply(null, input);
        expectedWeekday = config._useUTC ? config._d.getUTCDay() : config._d.getDay();

        // Apply timezone offset from input. The actual utcOffset can be changed
        // with parseZone.
        if (config._tzm != null) {
            config._d.setUTCMinutes(config._d.getUTCMinutes() - config._tzm);
        }

        if (config._nextDay) {
            config._a[HOUR] = 24;
        }

        // check for mismatching day of week
        if (config._w && typeof config._w.d !== 'undefined' && config._w.d !== expectedWeekday) {
            getParsingFlags(config).weekdayMismatch = true;
        }
    }

    function dayOfYearFromWeekInfo(config) {
        var w, weekYear, week, weekday, dow, doy, temp, weekdayOverflow;

        w = config._w;
        if (w.GG != null || w.W != null || w.E != null) {
            dow = 1;
            doy = 4;

            // TODO: We need to take the current isoWeekYear, but that depends on
            // how we interpret now (local, utc, fixed offset). So create
            // a now version of current config (take local/utc/offset flags, and
            // create now).
            weekYear = defaults(w.GG, config._a[YEAR], weekOfYear(createLocal(), 1, 4).year);
            week = defaults(w.W, 1);
            weekday = defaults(w.E, 1);
            if (weekday < 1 || weekday > 7) {
                weekdayOverflow = true;
            }
        } else {
            dow = config._locale._week.dow;
            doy = config._locale._week.doy;

            var curWeek = weekOfYear(createLocal(), dow, doy);

            weekYear = defaults(w.gg, config._a[YEAR], curWeek.year);

            // Default to current week.
            week = defaults(w.w, curWeek.week);

            if (w.d != null) {
                // weekday -- low day numbers are considered next week
                weekday = w.d;
                if (weekday < 0 || weekday > 6) {
                    weekdayOverflow = true;
                }
            } else if (w.e != null) {
                // local weekday -- counting starts from beginning of week
                weekday = w.e + dow;
                if (w.e < 0 || w.e > 6) {
                    weekdayOverflow = true;
                }
            } else {
                // default to beginning of week
                weekday = dow;
            }
        }
        if (week < 1 || week > weeksInYear(weekYear, dow, doy)) {
            getParsingFlags(config)._overflowWeeks = true;
        } else if (weekdayOverflow != null) {
            getParsingFlags(config)._overflowWeekday = true;
        } else {
            temp = dayOfYearFromWeeks(weekYear, week, weekday, dow, doy);
            config._a[YEAR] = temp.year;
            config._dayOfYear = temp.dayOfYear;
        }
    }

    // iso 8601 regex
    // 0000-00-00 0000-W00 or 0000-W00-0 + T + 00 or 00:00 or 00:00:00 or 00:00:00.000 + +00:00 or +0000 or +00)
    var extendedIsoRegex = /^\s*((?:[+-]\d{6}|\d{4})-(?:\d\d-\d\d|W\d\d-\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?::\d\d(?::\d\d(?:[.,]\d+)?)?)?)([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/;
    var basicIsoRegex = /^\s*((?:[+-]\d{6}|\d{4})(?:\d\d\d\d|W\d\d\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?:\d\d(?:\d\d(?:[.,]\d+)?)?)?)([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/;

    var tzRegex = /Z|[+-]\d\d(?::?\d\d)?/;

    var isoDates = [
        ['YYYYYY-MM-DD', /[+-]\d{6}-\d\d-\d\d/],
        ['YYYY-MM-DD', /\d{4}-\d\d-\d\d/],
        ['GGGG-[W]WW-E', /\d{4}-W\d\d-\d/],
        ['GGGG-[W]WW', /\d{4}-W\d\d/, false],
        ['YYYY-DDD', /\d{4}-\d{3}/],
        ['YYYY-MM', /\d{4}-\d\d/, false],
        ['YYYYYYMMDD', /[+-]\d{10}/],
        ['YYYYMMDD', /\d{8}/],
        // YYYYMM is NOT allowed by the standard
        ['GGGG[W]WWE', /\d{4}W\d{3}/],
        ['GGGG[W]WW', /\d{4}W\d{2}/, false],
        ['YYYYDDD', /\d{7}/]
    ];

    // iso time formats and regexes
    var isoTimes = [
        ['HH:mm:ss.SSSS', /\d\d:\d\d:\d\d\.\d+/],
        ['HH:mm:ss,SSSS', /\d\d:\d\d:\d\d,\d+/],
        ['HH:mm:ss', /\d\d:\d\d:\d\d/],
        ['HH:mm', /\d\d:\d\d/],
        ['HHmmss.SSSS', /\d\d\d\d\d\d\.\d+/],
        ['HHmmss,SSSS', /\d\d\d\d\d\d,\d+/],
        ['HHmmss', /\d\d\d\d\d\d/],
        ['HHmm', /\d\d\d\d/],
        ['HH', /\d\d/]
    ];

    var aspNetJsonRegex = /^\/?Date\((\-?\d+)/i;

    // date from iso format
    function configFromISO(config) {
        var i, l,
            string = config._i,
            match = extendedIsoRegex.exec(string) || basicIsoRegex.exec(string),
            allowTime, dateFormat, timeFormat, tzFormat;

        if (match) {
            getParsingFlags(config).iso = true;

            for (i = 0, l = isoDates.length; i < l; i++) {
                if (isoDates[i][1].exec(match[1])) {
                    dateFormat = isoDates[i][0];
                    allowTime = isoDates[i][2] !== false;
                    break;
                }
            }
            if (dateFormat == null) {
                config._isValid = false;
                return;
            }
            if (match[3]) {
                for (i = 0, l = isoTimes.length; i < l; i++) {
                    if (isoTimes[i][1].exec(match[3])) {
                        // match[2] should be 'T' or space
                        timeFormat = (match[2] || ' ') + isoTimes[i][0];
                        break;
                    }
                }
                if (timeFormat == null) {
                    config._isValid = false;
                    return;
                }
            }
            if (!allowTime && timeFormat != null) {
                config._isValid = false;
                return;
            }
            if (match[4]) {
                if (tzRegex.exec(match[4])) {
                    tzFormat = 'Z';
                } else {
                    config._isValid = false;
                    return;
                }
            }
            config._f = dateFormat + (timeFormat || '') + (tzFormat || '');
            configFromStringAndFormat(config);
        } else {
            config._isValid = false;
        }
    }

    // RFC 2822 regex: For details see https://tools.ietf.org/html/rfc2822#section-3.3
    var rfc2822 = /^(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s)?(\d{1,2})\s(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s(\d{2,4})\s(\d\d):(\d\d)(?::(\d\d))?\s(?:(UT|GMT|[ECMP][SD]T)|([Zz])|([+-]\d{4}))$/;

    function extractFromRFC2822Strings(yearStr, monthStr, dayStr, hourStr, minuteStr, secondStr) {
        var result = [
            untruncateYear(yearStr),
            defaultLocaleMonthsShort.indexOf(monthStr),
            parseInt(dayStr, 10),
            parseInt(hourStr, 10),
            parseInt(minuteStr, 10)
        ];

        if (secondStr) {
            result.push(parseInt(secondStr, 10));
        }

        return result;
    }

    function untruncateYear(yearStr) {
        var year = parseInt(yearStr, 10);
        if (year <= 49) {
            return 2000 + year;
        } else if (year <= 999) {
            return 1900 + year;
        }
        return year;
    }

    function preprocessRFC2822(s) {
        // Remove comments and folding whitespace and replace multiple-spaces with a single space
        return s.replace(/\([^)]*\)|[\n\t]/g, ' ').replace(/(\s\s+)/g, ' ').replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    }

    function checkWeekday(weekdayStr, parsedInput, config) {
        if (weekdayStr) {
            // TODO: Replace the vanilla JS Date object with an indepentent day-of-week check.
            var weekdayProvided = defaultLocaleWeekdaysShort.indexOf(weekdayStr),
                weekdayActual = new Date(parsedInput[0], parsedInput[1], parsedInput[2]).getDay();
            if (weekdayProvided !== weekdayActual) {
                getParsingFlags(config).weekdayMismatch = true;
                config._isValid = false;
                return false;
            }
        }
        return true;
    }

    var obsOffsets = {
        UT: 0,
        GMT: 0,
        EDT: -4 * 60,
        EST: -5 * 60,
        CDT: -5 * 60,
        CST: -6 * 60,
        MDT: -6 * 60,
        MST: -7 * 60,
        PDT: -7 * 60,
        PST: -8 * 60
    };

    function calculateOffset(obsOffset, militaryOffset, numOffset) {
        if (obsOffset) {
            return obsOffsets[obsOffset];
        } else if (militaryOffset) {
            // the only allowed military tz is Z
            return 0;
        } else {
            var hm = parseInt(numOffset, 10);
            var m = hm % 100, h = (hm - m) / 100;
            return h * 60 + m;
        }
    }

    // date and time from ref 2822 format
    function configFromRFC2822(config) {
        var match = rfc2822.exec(preprocessRFC2822(config._i));
        if (match) {
            var parsedArray = extractFromRFC2822Strings(match[4], match[3], match[2], match[5], match[6], match[7]);
            if (!checkWeekday(match[1], parsedArray, config)) {
                return;
            }

            config._a = parsedArray;
            config._tzm = calculateOffset(match[8], match[9], match[10]);

            config._d = createUTCDate.apply(null, config._a);
            config._d.setUTCMinutes(config._d.getUTCMinutes() - config._tzm);

            getParsingFlags(config).rfc2822 = true;
        } else {
            config._isValid = false;
        }
    }

    // date from iso format or fallback
    function configFromString(config) {
        var matched = aspNetJsonRegex.exec(config._i);

        if (matched !== null) {
            config._d = new Date(+matched[1]);
            return;
        }

        configFromISO(config);
        if (config._isValid === false) {
            delete config._isValid;
        } else {
            return;
        }

        configFromRFC2822(config);
        if (config._isValid === false) {
            delete config._isValid;
        } else {
            return;
        }

        // Final attempt, use Input Fallback
        hooks.createFromInputFallback(config);
    }

    hooks.createFromInputFallback = deprecate(
        'value provided is not in a recognized RFC2822 or ISO format. moment construction falls back to js Date(), ' +
        'which is not reliable across all browsers and versions. Non RFC2822/ISO date formats are ' +
        'discouraged and will be removed in an upcoming major release. Please refer to ' +
        'http://momentjs.com/guides/#/warnings/js-date/ for more info.',
        function (config) {
            config._d = new Date(config._i + (config._useUTC ? ' UTC' : ''));
        }
    );

    // constant that refers to the ISO standard
    hooks.ISO_8601 = function () {};

    // constant that refers to the RFC 2822 form
    hooks.RFC_2822 = function () {};

    // date from string and format string
    function configFromStringAndFormat(config) {
        // TODO: Move this to another part of the creation flow to prevent circular deps
        if (config._f === hooks.ISO_8601) {
            configFromISO(config);
            return;
        }
        if (config._f === hooks.RFC_2822) {
            configFromRFC2822(config);
            return;
        }
        config._a = [];
        getParsingFlags(config).empty = true;

        // This array is used to make a Date, either with `new Date` or `Date.UTC`
        var string = '' + config._i,
            i, parsedInput, tokens, token, skipped,
            stringLength = string.length,
            totalParsedInputLength = 0;

        tokens = expandFormat(config._f, config._locale).match(formattingTokens) || [];

        for (i = 0; i < tokens.length; i++) {
            token = tokens[i];
            parsedInput = (string.match(getParseRegexForToken(token, config)) || [])[0];
            // console.log('token', token, 'parsedInput', parsedInput,
            //         'regex', getParseRegexForToken(token, config));
            if (parsedInput) {
                skipped = string.substr(0, string.indexOf(parsedInput));
                if (skipped.length > 0) {
                    getParsingFlags(config).unusedInput.push(skipped);
                }
                string = string.slice(string.indexOf(parsedInput) + parsedInput.length);
                totalParsedInputLength += parsedInput.length;
            }
            // don't parse if it's not a known token
            if (formatTokenFunctions[token]) {
                if (parsedInput) {
                    getParsingFlags(config).empty = false;
                }
                else {
                    getParsingFlags(config).unusedTokens.push(token);
                }
                addTimeToArrayFromToken(token, parsedInput, config);
            }
            else if (config._strict && !parsedInput) {
                getParsingFlags(config).unusedTokens.push(token);
            }
        }

        // add remaining unparsed input length to the string
        getParsingFlags(config).charsLeftOver = stringLength - totalParsedInputLength;
        if (string.length > 0) {
            getParsingFlags(config).unusedInput.push(string);
        }

        // clear _12h flag if hour is <= 12
        if (config._a[HOUR] <= 12 &&
            getParsingFlags(config).bigHour === true &&
            config._a[HOUR] > 0) {
            getParsingFlags(config).bigHour = undefined;
        }

        getParsingFlags(config).parsedDateParts = config._a.slice(0);
        getParsingFlags(config).meridiem = config._meridiem;
        // handle meridiem
        config._a[HOUR] = meridiemFixWrap(config._locale, config._a[HOUR], config._meridiem);

        configFromArray(config);
        checkOverflow(config);
    }


    function meridiemFixWrap (locale, hour, meridiem) {
        var isPm;

        if (meridiem == null) {
            // nothing to do
            return hour;
        }
        if (locale.meridiemHour != null) {
            return locale.meridiemHour(hour, meridiem);
        } else if (locale.isPM != null) {
            // Fallback
            isPm = locale.isPM(meridiem);
            if (isPm && hour < 12) {
                hour += 12;
            }
            if (!isPm && hour === 12) {
                hour = 0;
            }
            return hour;
        } else {
            // this is not supposed to happen
            return hour;
        }
    }

    // date from string and array of format strings
    function configFromStringAndArray(config) {
        var tempConfig,
            bestMoment,

            scoreToBeat,
            i,
            currentScore;

        if (config._f.length === 0) {
            getParsingFlags(config).invalidFormat = true;
            config._d = new Date(NaN);
            return;
        }

        for (i = 0; i < config._f.length; i++) {
            currentScore = 0;
            tempConfig = copyConfig({}, config);
            if (config._useUTC != null) {
                tempConfig._useUTC = config._useUTC;
            }
            tempConfig._f = config._f[i];
            configFromStringAndFormat(tempConfig);

            if (!isValid(tempConfig)) {
                continue;
            }

            // if there is any input that was not parsed add a penalty for that format
            currentScore += getParsingFlags(tempConfig).charsLeftOver;

            //or tokens
            currentScore += getParsingFlags(tempConfig).unusedTokens.length * 10;

            getParsingFlags(tempConfig).score = currentScore;

            if (scoreToBeat == null || currentScore < scoreToBeat) {
                scoreToBeat = currentScore;
                bestMoment = tempConfig;
            }
        }

        extend(config, bestMoment || tempConfig);
    }

    function configFromObject(config) {
        if (config._d) {
            return;
        }

        var i = normalizeObjectUnits(config._i);
        config._a = map([i.year, i.month, i.day || i.date, i.hour, i.minute, i.second, i.millisecond], function (obj) {
            return obj && parseInt(obj, 10);
        });

        configFromArray(config);
    }

    function createFromConfig (config) {
        var res = new Moment(checkOverflow(prepareConfig(config)));
        if (res._nextDay) {
            // Adding is smart enough around DST
            res.add(1, 'd');
            res._nextDay = undefined;
        }

        return res;
    }

    function prepareConfig (config) {
        var input = config._i,
            format = config._f;

        config._locale = config._locale || getLocale(config._l);

        if (input === null || (format === undefined && input === '')) {
            return createInvalid({nullInput: true});
        }

        if (typeof input === 'string') {
            config._i = input = config._locale.preparse(input);
        }

        if (isMoment(input)) {
            return new Moment(checkOverflow(input));
        } else if (isDate(input)) {
            config._d = input;
        } else if (isArray(format)) {
            configFromStringAndArray(config);
        } else if (format) {
            configFromStringAndFormat(config);
        }  else {
            configFromInput(config);
        }

        if (!isValid(config)) {
            config._d = null;
        }

        return config;
    }

    function configFromInput(config) {
        var input = config._i;
        if (isUndefined(input)) {
            config._d = new Date(hooks.now());
        } else if (isDate(input)) {
            config._d = new Date(input.valueOf());
        } else if (typeof input === 'string') {
            configFromString(config);
        } else if (isArray(input)) {
            config._a = map(input.slice(0), function (obj) {
                return parseInt(obj, 10);
            });
            configFromArray(config);
        } else if (isObject(input)) {
            configFromObject(config);
        } else if (isNumber(input)) {
            // from milliseconds
            config._d = new Date(input);
        } else {
            hooks.createFromInputFallback(config);
        }
    }

    function createLocalOrUTC (input, format, locale, strict, isUTC) {
        var c = {};

        if (locale === true || locale === false) {
            strict = locale;
            locale = undefined;
        }

        if ((isObject(input) && isObjectEmpty(input)) ||
                (isArray(input) && input.length === 0)) {
            input = undefined;
        }
        // object construction must be done this way.
        // https://github.com/moment/moment/issues/1423
        c._isAMomentObject = true;
        c._useUTC = c._isUTC = isUTC;
        c._l = locale;
        c._i = input;
        c._f = format;
        c._strict = strict;

        return createFromConfig(c);
    }

    function createLocal (input, format, locale, strict) {
        return createLocalOrUTC(input, format, locale, strict, false);
    }

    var prototypeMin = deprecate(
        'moment().min is deprecated, use moment.max instead. http://momentjs.com/guides/#/warnings/min-max/',
        function () {
            var other = createLocal.apply(null, arguments);
            if (this.isValid() && other.isValid()) {
                return other < this ? this : other;
            } else {
                return createInvalid();
            }
        }
    );

    var prototypeMax = deprecate(
        'moment().max is deprecated, use moment.min instead. http://momentjs.com/guides/#/warnings/min-max/',
        function () {
            var other = createLocal.apply(null, arguments);
            if (this.isValid() && other.isValid()) {
                return other > this ? this : other;
            } else {
                return createInvalid();
            }
        }
    );

    // Pick a moment m from moments so that m[fn](other) is true for all
    // other. This relies on the function fn to be transitive.
    //
    // moments should either be an array of moment objects or an array, whose
    // first element is an array of moment objects.
    function pickBy(fn, moments) {
        var res, i;
        if (moments.length === 1 && isArray(moments[0])) {
            moments = moments[0];
        }
        if (!moments.length) {
            return createLocal();
        }
        res = moments[0];
        for (i = 1; i < moments.length; ++i) {
            if (!moments[i].isValid() || moments[i][fn](res)) {
                res = moments[i];
            }
        }
        return res;
    }

    // TODO: Use [].sort instead?
    function min () {
        var args = [].slice.call(arguments, 0);

        return pickBy('isBefore', args);
    }

    function max () {
        var args = [].slice.call(arguments, 0);

        return pickBy('isAfter', args);
    }

    var now = function () {
        return Date.now ? Date.now() : +(new Date());
    };

    var ordering = ['year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond'];

    function isDurationValid(m) {
        for (var key in m) {
            if (!(indexOf.call(ordering, key) !== -1 && (m[key] == null || !isNaN(m[key])))) {
                return false;
            }
        }

        var unitHasDecimal = false;
        for (var i = 0; i < ordering.length; ++i) {
            if (m[ordering[i]]) {
                if (unitHasDecimal) {
                    return false; // only allow non-integers for smallest unit
                }
                if (parseFloat(m[ordering[i]]) !== toInt(m[ordering[i]])) {
                    unitHasDecimal = true;
                }
            }
        }

        return true;
    }

    function isValid$1() {
        return this._isValid;
    }

    function createInvalid$1() {
        return createDuration(NaN);
    }

    function Duration (duration) {
        var normalizedInput = normalizeObjectUnits(duration),
            years = normalizedInput.year || 0,
            quarters = normalizedInput.quarter || 0,
            months = normalizedInput.month || 0,
            weeks = normalizedInput.week || normalizedInput.isoWeek || 0,
            days = normalizedInput.day || 0,
            hours = normalizedInput.hour || 0,
            minutes = normalizedInput.minute || 0,
            seconds = normalizedInput.second || 0,
            milliseconds = normalizedInput.millisecond || 0;

        this._isValid = isDurationValid(normalizedInput);

        // representation for dateAddRemove
        this._milliseconds = +milliseconds +
            seconds * 1e3 + // 1000
            minutes * 6e4 + // 1000 * 60
            hours * 1000 * 60 * 60; //using 1000 * 60 * 60 instead of 36e5 to avoid floating point rounding errors https://github.com/moment/moment/issues/2978
        // Because of dateAddRemove treats 24 hours as different from a
        // day when working around DST, we need to store them separately
        this._days = +days +
            weeks * 7;
        // It is impossible to translate months into days without knowing
        // which months you are are talking about, so we have to store
        // it separately.
        this._months = +months +
            quarters * 3 +
            years * 12;

        this._data = {};

        this._locale = getLocale();

        this._bubble();
    }

    function isDuration (obj) {
        return obj instanceof Duration;
    }

    function absRound (number) {
        if (number < 0) {
            return Math.round(-1 * number) * -1;
        } else {
            return Math.round(number);
        }
    }

    // FORMATTING

    function offset (token, separator) {
        addFormatToken(token, 0, 0, function () {
            var offset = this.utcOffset();
            var sign = '+';
            if (offset < 0) {
                offset = -offset;
                sign = '-';
            }
            return sign + zeroFill(~~(offset / 60), 2) + separator + zeroFill(~~(offset) % 60, 2);
        });
    }

    offset('Z', ':');
    offset('ZZ', '');

    // PARSING

    addRegexToken('Z',  matchShortOffset);
    addRegexToken('ZZ', matchShortOffset);
    addParseToken(['Z', 'ZZ'], function (input, array, config) {
        config._useUTC = true;
        config._tzm = offsetFromString(matchShortOffset, input);
    });

    // HELPERS

    // timezone chunker
    // '+10:00' > ['10',  '00']
    // '-1530'  > ['-15', '30']
    var chunkOffset = /([\+\-]|\d\d)/gi;

    function offsetFromString(matcher, string) {
        var matches = (string || '').match(matcher);

        if (matches === null) {
            return null;
        }

        var chunk   = matches[matches.length - 1] || [];
        var parts   = (chunk + '').match(chunkOffset) || ['-', 0, 0];
        var minutes = +(parts[1] * 60) + toInt(parts[2]);

        return minutes === 0 ?
          0 :
          parts[0] === '+' ? minutes : -minutes;
    }

    // Return a moment from input, that is local/utc/zone equivalent to model.
    function cloneWithOffset(input, model) {
        var res, diff;
        if (model._isUTC) {
            res = model.clone();
            diff = (isMoment(input) || isDate(input) ? input.valueOf() : createLocal(input).valueOf()) - res.valueOf();
            // Use low-level api, because this fn is low-level api.
            res._d.setTime(res._d.valueOf() + diff);
            hooks.updateOffset(res, false);
            return res;
        } else {
            return createLocal(input).local();
        }
    }

    function getDateOffset (m) {
        // On Firefox.24 Date#getTimezoneOffset returns a floating point.
        // https://github.com/moment/moment/pull/1871
        return -Math.round(m._d.getTimezoneOffset() / 15) * 15;
    }

    // HOOKS

    // This function will be called whenever a moment is mutated.
    // It is intended to keep the offset in sync with the timezone.
    hooks.updateOffset = function () {};

    // MOMENTS

    // keepLocalTime = true means only change the timezone, without
    // affecting the local hour. So 5:31:26 +0300 --[utcOffset(2, true)]-->
    // 5:31:26 +0200 It is possible that 5:31:26 doesn't exist with offset
    // +0200, so we adjust the time as needed, to be valid.
    //
    // Keeping the time actually adds/subtracts (one hour)
    // from the actual represented time. That is why we call updateOffset
    // a second time. In case it wants us to change the offset again
    // _changeInProgress == true case, then we have to adjust, because
    // there is no such time in the given timezone.
    function getSetOffset (input, keepLocalTime, keepMinutes) {
        var offset = this._offset || 0,
            localAdjust;
        if (!this.isValid()) {
            return input != null ? this : NaN;
        }
        if (input != null) {
            if (typeof input === 'string') {
                input = offsetFromString(matchShortOffset, input);
                if (input === null) {
                    return this;
                }
            } else if (Math.abs(input) < 16 && !keepMinutes) {
                input = input * 60;
            }
            if (!this._isUTC && keepLocalTime) {
                localAdjust = getDateOffset(this);
            }
            this._offset = input;
            this._isUTC = true;
            if (localAdjust != null) {
                this.add(localAdjust, 'm');
            }
            if (offset !== input) {
                if (!keepLocalTime || this._changeInProgress) {
                    addSubtract(this, createDuration(input - offset, 'm'), 1, false);
                } else if (!this._changeInProgress) {
                    this._changeInProgress = true;
                    hooks.updateOffset(this, true);
                    this._changeInProgress = null;
                }
            }
            return this;
        } else {
            return this._isUTC ? offset : getDateOffset(this);
        }
    }

    function getSetZone (input, keepLocalTime) {
        if (input != null) {
            if (typeof input !== 'string') {
                input = -input;
            }

            this.utcOffset(input, keepLocalTime);

            return this;
        } else {
            return -this.utcOffset();
        }
    }

    function setOffsetToUTC (keepLocalTime) {
        return this.utcOffset(0, keepLocalTime);
    }

    function setOffsetToLocal (keepLocalTime) {
        if (this._isUTC) {
            this.utcOffset(0, keepLocalTime);
            this._isUTC = false;

            if (keepLocalTime) {
                this.subtract(getDateOffset(this), 'm');
            }
        }
        return this;
    }

    function setOffsetToParsedOffset () {
        if (this._tzm != null) {
            this.utcOffset(this._tzm, false, true);
        } else if (typeof this._i === 'string') {
            var tZone = offsetFromString(matchOffset, this._i);
            if (tZone != null) {
                this.utcOffset(tZone);
            }
            else {
                this.utcOffset(0, true);
            }
        }
        return this;
    }

    function hasAlignedHourOffset (input) {
        if (!this.isValid()) {
            return false;
        }
        input = input ? createLocal(input).utcOffset() : 0;

        return (this.utcOffset() - input) % 60 === 0;
    }

    function isDaylightSavingTime () {
        return (
            this.utcOffset() > this.clone().month(0).utcOffset() ||
            this.utcOffset() > this.clone().month(5).utcOffset()
        );
    }

    function isDaylightSavingTimeShifted () {
        if (!isUndefined(this._isDSTShifted)) {
            return this._isDSTShifted;
        }

        var c = {};

        copyConfig(c, this);
        c = prepareConfig(c);

        if (c._a) {
            var other = c._isUTC ? createUTC(c._a) : createLocal(c._a);
            this._isDSTShifted = this.isValid() &&
                compareArrays(c._a, other.toArray()) > 0;
        } else {
            this._isDSTShifted = false;
        }

        return this._isDSTShifted;
    }

    function isLocal () {
        return this.isValid() ? !this._isUTC : false;
    }

    function isUtcOffset () {
        return this.isValid() ? this._isUTC : false;
    }

    function isUtc () {
        return this.isValid() ? this._isUTC && this._offset === 0 : false;
    }

    // ASP.NET json date format regex
    var aspNetRegex = /^(\-|\+)?(?:(\d*)[. ])?(\d+)\:(\d+)(?:\:(\d+)(\.\d*)?)?$/;

    // from http://docs.closure-library.googlecode.com/git/closure_goog_date_date.js.source.html
    // somewhat more in line with 4.4.3.2 2004 spec, but allows decimal anywhere
    // and further modified to allow for strings containing both week and day
    var isoRegex = /^(-|\+)?P(?:([-+]?[0-9,.]*)Y)?(?:([-+]?[0-9,.]*)M)?(?:([-+]?[0-9,.]*)W)?(?:([-+]?[0-9,.]*)D)?(?:T(?:([-+]?[0-9,.]*)H)?(?:([-+]?[0-9,.]*)M)?(?:([-+]?[0-9,.]*)S)?)?$/;

    function createDuration (input, key) {
        var duration = input,
            // matching against regexp is expensive, do it on demand
            match = null,
            sign,
            ret,
            diffRes;

        if (isDuration(input)) {
            duration = {
                ms : input._milliseconds,
                d  : input._days,
                M  : input._months
            };
        } else if (isNumber(input)) {
            duration = {};
            if (key) {
                duration[key] = input;
            } else {
                duration.milliseconds = input;
            }
        } else if (!!(match = aspNetRegex.exec(input))) {
            sign = (match[1] === '-') ? -1 : 1;
            duration = {
                y  : 0,
                d  : toInt(match[DATE])                         * sign,
                h  : toInt(match[HOUR])                         * sign,
                m  : toInt(match[MINUTE])                       * sign,
                s  : toInt(match[SECOND])                       * sign,
                ms : toInt(absRound(match[MILLISECOND] * 1000)) * sign // the millisecond decimal point is included in the match
            };
        } else if (!!(match = isoRegex.exec(input))) {
            sign = (match[1] === '-') ? -1 : 1;
            duration = {
                y : parseIso(match[2], sign),
                M : parseIso(match[3], sign),
                w : parseIso(match[4], sign),
                d : parseIso(match[5], sign),
                h : parseIso(match[6], sign),
                m : parseIso(match[7], sign),
                s : parseIso(match[8], sign)
            };
        } else if (duration == null) {// checks for null or undefined
            duration = {};
        } else if (typeof duration === 'object' && ('from' in duration || 'to' in duration)) {
            diffRes = momentsDifference(createLocal(duration.from), createLocal(duration.to));

            duration = {};
            duration.ms = diffRes.milliseconds;
            duration.M = diffRes.months;
        }

        ret = new Duration(duration);

        if (isDuration(input) && hasOwnProp(input, '_locale')) {
            ret._locale = input._locale;
        }

        return ret;
    }

    createDuration.fn = Duration.prototype;
    createDuration.invalid = createInvalid$1;

    function parseIso (inp, sign) {
        // We'd normally use ~~inp for this, but unfortunately it also
        // converts floats to ints.
        // inp may be undefined, so careful calling replace on it.
        var res = inp && parseFloat(inp.replace(',', '.'));
        // apply sign while we're at it
        return (isNaN(res) ? 0 : res) * sign;
    }

    function positiveMomentsDifference(base, other) {
        var res = {};

        res.months = other.month() - base.month() +
            (other.year() - base.year()) * 12;
        if (base.clone().add(res.months, 'M').isAfter(other)) {
            --res.months;
        }

        res.milliseconds = +other - +(base.clone().add(res.months, 'M'));

        return res;
    }

    function momentsDifference(base, other) {
        var res;
        if (!(base.isValid() && other.isValid())) {
            return {milliseconds: 0, months: 0};
        }

        other = cloneWithOffset(other, base);
        if (base.isBefore(other)) {
            res = positiveMomentsDifference(base, other);
        } else {
            res = positiveMomentsDifference(other, base);
            res.milliseconds = -res.milliseconds;
            res.months = -res.months;
        }

        return res;
    }

    // TODO: remove 'name' arg after deprecation is removed
    function createAdder(direction, name) {
        return function (val, period) {
            var dur, tmp;
            //invert the arguments, but complain about it
            if (period !== null && !isNaN(+period)) {
                deprecateSimple(name, 'moment().' + name  + '(period, number) is deprecated. Please use moment().' + name + '(number, period). ' +
                'See http://momentjs.com/guides/#/warnings/add-inverted-param/ for more info.');
                tmp = val; val = period; period = tmp;
            }

            val = typeof val === 'string' ? +val : val;
            dur = createDuration(val, period);
            addSubtract(this, dur, direction);
            return this;
        };
    }

    function addSubtract (mom, duration, isAdding, updateOffset) {
        var milliseconds = duration._milliseconds,
            days = absRound(duration._days),
            months = absRound(duration._months);

        if (!mom.isValid()) {
            // No op
            return;
        }

        updateOffset = updateOffset == null ? true : updateOffset;

        if (months) {
            setMonth(mom, get(mom, 'Month') + months * isAdding);
        }
        if (days) {
            set$1(mom, 'Date', get(mom, 'Date') + days * isAdding);
        }
        if (milliseconds) {
            mom._d.setTime(mom._d.valueOf() + milliseconds * isAdding);
        }
        if (updateOffset) {
            hooks.updateOffset(mom, days || months);
        }
    }

    var add      = createAdder(1, 'add');
    var subtract = createAdder(-1, 'subtract');

    function getCalendarFormat(myMoment, now) {
        var diff = myMoment.diff(now, 'days', true);
        return diff < -6 ? 'sameElse' :
                diff < -1 ? 'lastWeek' :
                diff < 0 ? 'lastDay' :
                diff < 1 ? 'sameDay' :
                diff < 2 ? 'nextDay' :
                diff < 7 ? 'nextWeek' : 'sameElse';
    }

    function calendar$1 (time, formats) {
        // We want to compare the start of today, vs this.
        // Getting start-of-today depends on whether we're local/utc/offset or not.
        var now = time || createLocal(),
            sod = cloneWithOffset(now, this).startOf('day'),
            format = hooks.calendarFormat(this, sod) || 'sameElse';

        var output = formats && (isFunction(formats[format]) ? formats[format].call(this, now) : formats[format]);

        return this.format(output || this.localeData().calendar(format, this, createLocal(now)));
    }

    function clone () {
        return new Moment(this);
    }

    function isAfter (input, units) {
        var localInput = isMoment(input) ? input : createLocal(input);
        if (!(this.isValid() && localInput.isValid())) {
            return false;
        }
        units = normalizeUnits(units) || 'millisecond';
        if (units === 'millisecond') {
            return this.valueOf() > localInput.valueOf();
        } else {
            return localInput.valueOf() < this.clone().startOf(units).valueOf();
        }
    }

    function isBefore (input, units) {
        var localInput = isMoment(input) ? input : createLocal(input);
        if (!(this.isValid() && localInput.isValid())) {
            return false;
        }
        units = normalizeUnits(units) || 'millisecond';
        if (units === 'millisecond') {
            return this.valueOf() < localInput.valueOf();
        } else {
            return this.clone().endOf(units).valueOf() < localInput.valueOf();
        }
    }

    function isBetween (from, to, units, inclusivity) {
        var localFrom = isMoment(from) ? from : createLocal(from),
            localTo = isMoment(to) ? to : createLocal(to);
        if (!(this.isValid() && localFrom.isValid() && localTo.isValid())) {
            return false;
        }
        inclusivity = inclusivity || '()';
        return (inclusivity[0] === '(' ? this.isAfter(localFrom, units) : !this.isBefore(localFrom, units)) &&
            (inclusivity[1] === ')' ? this.isBefore(localTo, units) : !this.isAfter(localTo, units));
    }

    function isSame (input, units) {
        var localInput = isMoment(input) ? input : createLocal(input),
            inputMs;
        if (!(this.isValid() && localInput.isValid())) {
            return false;
        }
        units = normalizeUnits(units) || 'millisecond';
        if (units === 'millisecond') {
            return this.valueOf() === localInput.valueOf();
        } else {
            inputMs = localInput.valueOf();
            return this.clone().startOf(units).valueOf() <= inputMs && inputMs <= this.clone().endOf(units).valueOf();
        }
    }

    function isSameOrAfter (input, units) {
        return this.isSame(input, units) || this.isAfter(input, units);
    }

    function isSameOrBefore (input, units) {
        return this.isSame(input, units) || this.isBefore(input, units);
    }

    function diff (input, units, asFloat) {
        var that,
            zoneDelta,
            output;

        if (!this.isValid()) {
            return NaN;
        }

        that = cloneWithOffset(input, this);

        if (!that.isValid()) {
            return NaN;
        }

        zoneDelta = (that.utcOffset() - this.utcOffset()) * 6e4;

        units = normalizeUnits(units);

        switch (units) {
            case 'year': output = monthDiff(this, that) / 12; break;
            case 'month': output = monthDiff(this, that); break;
            case 'quarter': output = monthDiff(this, that) / 3; break;
            case 'second': output = (this - that) / 1e3; break; // 1000
            case 'minute': output = (this - that) / 6e4; break; // 1000 * 60
            case 'hour': output = (this - that) / 36e5; break; // 1000 * 60 * 60
            case 'day': output = (this - that - zoneDelta) / 864e5; break; // 1000 * 60 * 60 * 24, negate dst
            case 'week': output = (this - that - zoneDelta) / 6048e5; break; // 1000 * 60 * 60 * 24 * 7, negate dst
            default: output = this - that;
        }

        return asFloat ? output : absFloor(output);
    }

    function monthDiff (a, b) {
        // difference in months
        var wholeMonthDiff = ((b.year() - a.year()) * 12) + (b.month() - a.month()),
            // b is in (anchor - 1 month, anchor + 1 month)
            anchor = a.clone().add(wholeMonthDiff, 'months'),
            anchor2, adjust;

        if (b - anchor < 0) {
            anchor2 = a.clone().add(wholeMonthDiff - 1, 'months');
            // linear across the month
            adjust = (b - anchor) / (anchor - anchor2);
        } else {
            anchor2 = a.clone().add(wholeMonthDiff + 1, 'months');
            // linear across the month
            adjust = (b - anchor) / (anchor2 - anchor);
        }

        //check for negative zero, return zero if negative zero
        return -(wholeMonthDiff + adjust) || 0;
    }

    hooks.defaultFormat = 'YYYY-MM-DDTHH:mm:ssZ';
    hooks.defaultFormatUtc = 'YYYY-MM-DDTHH:mm:ss[Z]';

    function toString () {
        return this.clone().locale('en').format('ddd MMM DD YYYY HH:mm:ss [GMT]ZZ');
    }

    function toISOString(keepOffset) {
        if (!this.isValid()) {
            return null;
        }
        var utc = keepOffset !== true;
        var m = utc ? this.clone().utc() : this;
        if (m.year() < 0 || m.year() > 9999) {
            return formatMoment(m, utc ? 'YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]' : 'YYYYYY-MM-DD[T]HH:mm:ss.SSSZ');
        }
        if (isFunction(Date.prototype.toISOString)) {
            // native implementation is ~50x faster, use it when we can
            if (utc) {
                return this.toDate().toISOString();
            } else {
                return new Date(this.valueOf() + this.utcOffset() * 60 * 1000).toISOString().replace('Z', formatMoment(m, 'Z'));
            }
        }
        return formatMoment(m, utc ? 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]' : 'YYYY-MM-DD[T]HH:mm:ss.SSSZ');
    }

    /**
     * Return a human readable representation of a moment that can
     * also be evaluated to get a new moment which is the same
     *
     * @link https://nodejs.org/dist/latest/docs/api/util.html#util_custom_inspect_function_on_objects
     */
    function inspect () {
        if (!this.isValid()) {
            return 'moment.invalid(/* ' + this._i + ' */)';
        }
        var func = 'moment';
        var zone = '';
        if (!this.isLocal()) {
            func = this.utcOffset() === 0 ? 'moment.utc' : 'moment.parseZone';
            zone = 'Z';
        }
        var prefix = '[' + func + '("]';
        var year = (0 <= this.year() && this.year() <= 9999) ? 'YYYY' : 'YYYYYY';
        var datetime = '-MM-DD[T]HH:mm:ss.SSS';
        var suffix = zone + '[")]';

        return this.format(prefix + year + datetime + suffix);
    }

    function format (inputString) {
        if (!inputString) {
            inputString = this.isUtc() ? hooks.defaultFormatUtc : hooks.defaultFormat;
        }
        var output = formatMoment(this, inputString);
        return this.localeData().postformat(output);
    }

    function from (time, withoutSuffix) {
        if (this.isValid() &&
                ((isMoment(time) && time.isValid()) ||
                 createLocal(time).isValid())) {
            return createDuration({to: this, from: time}).locale(this.locale()).humanize(!withoutSuffix);
        } else {
            return this.localeData().invalidDate();
        }
    }

    function fromNow (withoutSuffix) {
        return this.from(createLocal(), withoutSuffix);
    }

    function to (time, withoutSuffix) {
        if (this.isValid() &&
                ((isMoment(time) && time.isValid()) ||
                 createLocal(time).isValid())) {
            return createDuration({from: this, to: time}).locale(this.locale()).humanize(!withoutSuffix);
        } else {
            return this.localeData().invalidDate();
        }
    }

    function toNow (withoutSuffix) {
        return this.to(createLocal(), withoutSuffix);
    }

    // If passed a locale key, it will set the locale for this
    // instance.  Otherwise, it will return the locale configuration
    // variables for this instance.
    function locale (key) {
        var newLocaleData;

        if (key === undefined) {
            return this._locale._abbr;
        } else {
            newLocaleData = getLocale(key);
            if (newLocaleData != null) {
                this._locale = newLocaleData;
            }
            return this;
        }
    }

    var lang = deprecate(
        'moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change languages.',
        function (key) {
            if (key === undefined) {
                return this.localeData();
            } else {
                return this.locale(key);
            }
        }
    );

    function localeData () {
        return this._locale;
    }

    var MS_PER_SECOND = 1000;
    var MS_PER_MINUTE = 60 * MS_PER_SECOND;
    var MS_PER_HOUR = 60 * MS_PER_MINUTE;
    var MS_PER_400_YEARS = (365 * 400 + 97) * 24 * MS_PER_HOUR;

    // actual modulo - handles negative numbers (for dates before 1970):
    function mod$1(dividend, divisor) {
        return (dividend % divisor + divisor) % divisor;
    }

    function localStartOfDate(y, m, d) {
        // the date constructor remaps years 0-99 to 1900-1999
        if (y < 100 && y >= 0) {
            // preserve leap years using a full 400 year cycle, then reset
            return new Date(y + 400, m, d) - MS_PER_400_YEARS;
        } else {
            return new Date(y, m, d).valueOf();
        }
    }

    function utcStartOfDate(y, m, d) {
        // Date.UTC remaps years 0-99 to 1900-1999
        if (y < 100 && y >= 0) {
            // preserve leap years using a full 400 year cycle, then reset
            return Date.UTC(y + 400, m, d) - MS_PER_400_YEARS;
        } else {
            return Date.UTC(y, m, d);
        }
    }

    function startOf (units) {
        var time;
        units = normalizeUnits(units);
        if (units === undefined || units === 'millisecond' || !this.isValid()) {
            return this;
        }

        var startOfDate = this._isUTC ? utcStartOfDate : localStartOfDate;

        switch (units) {
            case 'year':
                time = startOfDate(this.year(), 0, 1);
                break;
            case 'quarter':
                time = startOfDate(this.year(), this.month() - this.month() % 3, 1);
                break;
            case 'month':
                time = startOfDate(this.year(), this.month(), 1);
                break;
            case 'week':
                time = startOfDate(this.year(), this.month(), this.date() - this.weekday());
                break;
            case 'isoWeek':
                time = startOfDate(this.year(), this.month(), this.date() - (this.isoWeekday() - 1));
                break;
            case 'day':
            case 'date':
                time = startOfDate(this.year(), this.month(), this.date());
                break;
            case 'hour':
                time = this._d.valueOf();
                time -= mod$1(time + (this._isUTC ? 0 : this.utcOffset() * MS_PER_MINUTE), MS_PER_HOUR);
                break;
            case 'minute':
                time = this._d.valueOf();
                time -= mod$1(time, MS_PER_MINUTE);
                break;
            case 'second':
                time = this._d.valueOf();
                time -= mod$1(time, MS_PER_SECOND);
                break;
        }

        this._d.setTime(time);
        hooks.updateOffset(this, true);
        return this;
    }

    function endOf (units) {
        var time;
        units = normalizeUnits(units);
        if (units === undefined || units === 'millisecond' || !this.isValid()) {
            return this;
        }

        var startOfDate = this._isUTC ? utcStartOfDate : localStartOfDate;

        switch (units) {
            case 'year':
                time = startOfDate(this.year() + 1, 0, 1) - 1;
                break;
            case 'quarter':
                time = startOfDate(this.year(), this.month() - this.month() % 3 + 3, 1) - 1;
                break;
            case 'month':
                time = startOfDate(this.year(), this.month() + 1, 1) - 1;
                break;
            case 'week':
                time = startOfDate(this.year(), this.month(), this.date() - this.weekday() + 7) - 1;
                break;
            case 'isoWeek':
                time = startOfDate(this.year(), this.month(), this.date() - (this.isoWeekday() - 1) + 7) - 1;
                break;
            case 'day':
            case 'date':
                time = startOfDate(this.year(), this.month(), this.date() + 1) - 1;
                break;
            case 'hour':
                time = this._d.valueOf();
                time += MS_PER_HOUR - mod$1(time + (this._isUTC ? 0 : this.utcOffset() * MS_PER_MINUTE), MS_PER_HOUR) - 1;
                break;
            case 'minute':
                time = this._d.valueOf();
                time += MS_PER_MINUTE - mod$1(time, MS_PER_MINUTE) - 1;
                break;
            case 'second':
                time = this._d.valueOf();
                time += MS_PER_SECOND - mod$1(time, MS_PER_SECOND) - 1;
                break;
        }

        this._d.setTime(time);
        hooks.updateOffset(this, true);
        return this;
    }

    function valueOf () {
        return this._d.valueOf() - ((this._offset || 0) * 60000);
    }

    function unix () {
        return Math.floor(this.valueOf() / 1000);
    }

    function toDate () {
        return new Date(this.valueOf());
    }

    function toArray () {
        var m = this;
        return [m.year(), m.month(), m.date(), m.hour(), m.minute(), m.second(), m.millisecond()];
    }

    function toObject () {
        var m = this;
        return {
            years: m.year(),
            months: m.month(),
            date: m.date(),
            hours: m.hours(),
            minutes: m.minutes(),
            seconds: m.seconds(),
            milliseconds: m.milliseconds()
        };
    }

    function toJSON () {
        // new Date(NaN).toJSON() === null
        return this.isValid() ? this.toISOString() : null;
    }

    function isValid$2 () {
        return isValid(this);
    }

    function parsingFlags () {
        return extend({}, getParsingFlags(this));
    }

    function invalidAt () {
        return getParsingFlags(this).overflow;
    }

    function creationData() {
        return {
            input: this._i,
            format: this._f,
            locale: this._locale,
            isUTC: this._isUTC,
            strict: this._strict
        };
    }

    // FORMATTING

    addFormatToken(0, ['gg', 2], 0, function () {
        return this.weekYear() % 100;
    });

    addFormatToken(0, ['GG', 2], 0, function () {
        return this.isoWeekYear() % 100;
    });

    function addWeekYearFormatToken (token, getter) {
        addFormatToken(0, [token, token.length], 0, getter);
    }

    addWeekYearFormatToken('gggg',     'weekYear');
    addWeekYearFormatToken('ggggg',    'weekYear');
    addWeekYearFormatToken('GGGG',  'isoWeekYear');
    addWeekYearFormatToken('GGGGG', 'isoWeekYear');

    // ALIASES

    addUnitAlias('weekYear', 'gg');
    addUnitAlias('isoWeekYear', 'GG');

    // PRIORITY

    addUnitPriority('weekYear', 1);
    addUnitPriority('isoWeekYear', 1);


    // PARSING

    addRegexToken('G',      matchSigned);
    addRegexToken('g',      matchSigned);
    addRegexToken('GG',     match1to2, match2);
    addRegexToken('gg',     match1to2, match2);
    addRegexToken('GGGG',   match1to4, match4);
    addRegexToken('gggg',   match1to4, match4);
    addRegexToken('GGGGG',  match1to6, match6);
    addRegexToken('ggggg',  match1to6, match6);

    addWeekParseToken(['gggg', 'ggggg', 'GGGG', 'GGGGG'], function (input, week, config, token) {
        week[token.substr(0, 2)] = toInt(input);
    });

    addWeekParseToken(['gg', 'GG'], function (input, week, config, token) {
        week[token] = hooks.parseTwoDigitYear(input);
    });

    // MOMENTS

    function getSetWeekYear (input) {
        return getSetWeekYearHelper.call(this,
                input,
                this.week(),
                this.weekday(),
                this.localeData()._week.dow,
                this.localeData()._week.doy);
    }

    function getSetISOWeekYear (input) {
        return getSetWeekYearHelper.call(this,
                input, this.isoWeek(), this.isoWeekday(), 1, 4);
    }

    function getISOWeeksInYear () {
        return weeksInYear(this.year(), 1, 4);
    }

    function getWeeksInYear () {
        var weekInfo = this.localeData()._week;
        return weeksInYear(this.year(), weekInfo.dow, weekInfo.doy);
    }

    function getSetWeekYearHelper(input, week, weekday, dow, doy) {
        var weeksTarget;
        if (input == null) {
            return weekOfYear(this, dow, doy).year;
        } else {
            weeksTarget = weeksInYear(input, dow, doy);
            if (week > weeksTarget) {
                week = weeksTarget;
            }
            return setWeekAll.call(this, input, week, weekday, dow, doy);
        }
    }

    function setWeekAll(weekYear, week, weekday, dow, doy) {
        var dayOfYearData = dayOfYearFromWeeks(weekYear, week, weekday, dow, doy),
            date = createUTCDate(dayOfYearData.year, 0, dayOfYearData.dayOfYear);

        this.year(date.getUTCFullYear());
        this.month(date.getUTCMonth());
        this.date(date.getUTCDate());
        return this;
    }

    // FORMATTING

    addFormatToken('Q', 0, 'Qo', 'quarter');

    // ALIASES

    addUnitAlias('quarter', 'Q');

    // PRIORITY

    addUnitPriority('quarter', 7);

    // PARSING

    addRegexToken('Q', match1);
    addParseToken('Q', function (input, array) {
        array[MONTH] = (toInt(input) - 1) * 3;
    });

    // MOMENTS

    function getSetQuarter (input) {
        return input == null ? Math.ceil((this.month() + 1) / 3) : this.month((input - 1) * 3 + this.month() % 3);
    }

    // FORMATTING

    addFormatToken('D', ['DD', 2], 'Do', 'date');

    // ALIASES

    addUnitAlias('date', 'D');

    // PRIORITY
    addUnitPriority('date', 9);

    // PARSING

    addRegexToken('D',  match1to2);
    addRegexToken('DD', match1to2, match2);
    addRegexToken('Do', function (isStrict, locale) {
        // TODO: Remove "ordinalParse" fallback in next major release.
        return isStrict ?
          (locale._dayOfMonthOrdinalParse || locale._ordinalParse) :
          locale._dayOfMonthOrdinalParseLenient;
    });

    addParseToken(['D', 'DD'], DATE);
    addParseToken('Do', function (input, array) {
        array[DATE] = toInt(input.match(match1to2)[0]);
    });

    // MOMENTS

    var getSetDayOfMonth = makeGetSet('Date', true);

    // FORMATTING

    addFormatToken('DDD', ['DDDD', 3], 'DDDo', 'dayOfYear');

    // ALIASES

    addUnitAlias('dayOfYear', 'DDD');

    // PRIORITY
    addUnitPriority('dayOfYear', 4);

    // PARSING

    addRegexToken('DDD',  match1to3);
    addRegexToken('DDDD', match3);
    addParseToken(['DDD', 'DDDD'], function (input, array, config) {
        config._dayOfYear = toInt(input);
    });

    // HELPERS

    // MOMENTS

    function getSetDayOfYear (input) {
        var dayOfYear = Math.round((this.clone().startOf('day') - this.clone().startOf('year')) / 864e5) + 1;
        return input == null ? dayOfYear : this.add((input - dayOfYear), 'd');
    }

    // FORMATTING

    addFormatToken('m', ['mm', 2], 0, 'minute');

    // ALIASES

    addUnitAlias('minute', 'm');

    // PRIORITY

    addUnitPriority('minute', 14);

    // PARSING

    addRegexToken('m',  match1to2);
    addRegexToken('mm', match1to2, match2);
    addParseToken(['m', 'mm'], MINUTE);

    // MOMENTS

    var getSetMinute = makeGetSet('Minutes', false);

    // FORMATTING

    addFormatToken('s', ['ss', 2], 0, 'second');

    // ALIASES

    addUnitAlias('second', 's');

    // PRIORITY

    addUnitPriority('second', 15);

    // PARSING

    addRegexToken('s',  match1to2);
    addRegexToken('ss', match1to2, match2);
    addParseToken(['s', 'ss'], SECOND);

    // MOMENTS

    var getSetSecond = makeGetSet('Seconds', false);

    // FORMATTING

    addFormatToken('S', 0, 0, function () {
        return ~~(this.millisecond() / 100);
    });

    addFormatToken(0, ['SS', 2], 0, function () {
        return ~~(this.millisecond() / 10);
    });

    addFormatToken(0, ['SSS', 3], 0, 'millisecond');
    addFormatToken(0, ['SSSS', 4], 0, function () {
        return this.millisecond() * 10;
    });
    addFormatToken(0, ['SSSSS', 5], 0, function () {
        return this.millisecond() * 100;
    });
    addFormatToken(0, ['SSSSSS', 6], 0, function () {
        return this.millisecond() * 1000;
    });
    addFormatToken(0, ['SSSSSSS', 7], 0, function () {
        return this.millisecond() * 10000;
    });
    addFormatToken(0, ['SSSSSSSS', 8], 0, function () {
        return this.millisecond() * 100000;
    });
    addFormatToken(0, ['SSSSSSSSS', 9], 0, function () {
        return this.millisecond() * 1000000;
    });


    // ALIASES

    addUnitAlias('millisecond', 'ms');

    // PRIORITY

    addUnitPriority('millisecond', 16);

    // PARSING

    addRegexToken('S',    match1to3, match1);
    addRegexToken('SS',   match1to3, match2);
    addRegexToken('SSS',  match1to3, match3);

    var token;
    for (token = 'SSSS'; token.length <= 9; token += 'S') {
        addRegexToken(token, matchUnsigned);
    }

    function parseMs(input, array) {
        array[MILLISECOND] = toInt(('0.' + input) * 1000);
    }

    for (token = 'S'; token.length <= 9; token += 'S') {
        addParseToken(token, parseMs);
    }
    // MOMENTS

    var getSetMillisecond = makeGetSet('Milliseconds', false);

    // FORMATTING

    addFormatToken('z',  0, 0, 'zoneAbbr');
    addFormatToken('zz', 0, 0, 'zoneName');

    // MOMENTS

    function getZoneAbbr () {
        return this._isUTC ? 'UTC' : '';
    }

    function getZoneName () {
        return this._isUTC ? 'Coordinated Universal Time' : '';
    }

    var proto = Moment.prototype;

    proto.add               = add;
    proto.calendar          = calendar$1;
    proto.clone             = clone;
    proto.diff              = diff;
    proto.endOf             = endOf;
    proto.format            = format;
    proto.from              = from;
    proto.fromNow           = fromNow;
    proto.to                = to;
    proto.toNow             = toNow;
    proto.get               = stringGet;
    proto.invalidAt         = invalidAt;
    proto.isAfter           = isAfter;
    proto.isBefore          = isBefore;
    proto.isBetween         = isBetween;
    proto.isSame            = isSame;
    proto.isSameOrAfter     = isSameOrAfter;
    proto.isSameOrBefore    = isSameOrBefore;
    proto.isValid           = isValid$2;
    proto.lang              = lang;
    proto.locale            = locale;
    proto.localeData        = localeData;
    proto.max               = prototypeMax;
    proto.min               = prototypeMin;
    proto.parsingFlags      = parsingFlags;
    proto.set               = stringSet;
    proto.startOf           = startOf;
    proto.subtract          = subtract;
    proto.toArray           = toArray;
    proto.toObject          = toObject;
    proto.toDate            = toDate;
    proto.toISOString       = toISOString;
    proto.inspect           = inspect;
    proto.toJSON            = toJSON;
    proto.toString          = toString;
    proto.unix              = unix;
    proto.valueOf           = valueOf;
    proto.creationData      = creationData;
    proto.year       = getSetYear;
    proto.isLeapYear = getIsLeapYear;
    proto.weekYear    = getSetWeekYear;
    proto.isoWeekYear = getSetISOWeekYear;
    proto.quarter = proto.quarters = getSetQuarter;
    proto.month       = getSetMonth;
    proto.daysInMonth = getDaysInMonth;
    proto.week           = proto.weeks        = getSetWeek;
    proto.isoWeek        = proto.isoWeeks     = getSetISOWeek;
    proto.weeksInYear    = getWeeksInYear;
    proto.isoWeeksInYear = getISOWeeksInYear;
    proto.date       = getSetDayOfMonth;
    proto.day        = proto.days             = getSetDayOfWeek;
    proto.weekday    = getSetLocaleDayOfWeek;
    proto.isoWeekday = getSetISODayOfWeek;
    proto.dayOfYear  = getSetDayOfYear;
    proto.hour = proto.hours = getSetHour;
    proto.minute = proto.minutes = getSetMinute;
    proto.second = proto.seconds = getSetSecond;
    proto.millisecond = proto.milliseconds = getSetMillisecond;
    proto.utcOffset            = getSetOffset;
    proto.utc                  = setOffsetToUTC;
    proto.local                = setOffsetToLocal;
    proto.parseZone            = setOffsetToParsedOffset;
    proto.hasAlignedHourOffset = hasAlignedHourOffset;
    proto.isDST                = isDaylightSavingTime;
    proto.isLocal              = isLocal;
    proto.isUtcOffset          = isUtcOffset;
    proto.isUtc                = isUtc;
    proto.isUTC                = isUtc;
    proto.zoneAbbr = getZoneAbbr;
    proto.zoneName = getZoneName;
    proto.dates  = deprecate('dates accessor is deprecated. Use date instead.', getSetDayOfMonth);
    proto.months = deprecate('months accessor is deprecated. Use month instead', getSetMonth);
    proto.years  = deprecate('years accessor is deprecated. Use year instead', getSetYear);
    proto.zone   = deprecate('moment().zone is deprecated, use moment().utcOffset instead. http://momentjs.com/guides/#/warnings/zone/', getSetZone);
    proto.isDSTShifted = deprecate('isDSTShifted is deprecated. See http://momentjs.com/guides/#/warnings/dst-shifted/ for more information', isDaylightSavingTimeShifted);

    function createUnix (input) {
        return createLocal(input * 1000);
    }

    function createInZone () {
        return createLocal.apply(null, arguments).parseZone();
    }

    function preParsePostFormat (string) {
        return string;
    }

    var proto$1 = Locale.prototype;

    proto$1.calendar        = calendar;
    proto$1.longDateFormat  = longDateFormat;
    proto$1.invalidDate     = invalidDate;
    proto$1.ordinal         = ordinal;
    proto$1.preparse        = preParsePostFormat;
    proto$1.postformat      = preParsePostFormat;
    proto$1.relativeTime    = relativeTime;
    proto$1.pastFuture      = pastFuture;
    proto$1.set             = set;

    proto$1.months            =        localeMonths;
    proto$1.monthsShort       =        localeMonthsShort;
    proto$1.monthsParse       =        localeMonthsParse;
    proto$1.monthsRegex       = monthsRegex;
    proto$1.monthsShortRegex  = monthsShortRegex;
    proto$1.week = localeWeek;
    proto$1.firstDayOfYear = localeFirstDayOfYear;
    proto$1.firstDayOfWeek = localeFirstDayOfWeek;

    proto$1.weekdays       =        localeWeekdays;
    proto$1.weekdaysMin    =        localeWeekdaysMin;
    proto$1.weekdaysShort  =        localeWeekdaysShort;
    proto$1.weekdaysParse  =        localeWeekdaysParse;

    proto$1.weekdaysRegex       =        weekdaysRegex;
    proto$1.weekdaysShortRegex  =        weekdaysShortRegex;
    proto$1.weekdaysMinRegex    =        weekdaysMinRegex;

    proto$1.isPM = localeIsPM;
    proto$1.meridiem = localeMeridiem;

    function get$1 (format, index, field, setter) {
        var locale = getLocale();
        var utc = createUTC().set(setter, index);
        return locale[field](utc, format);
    }

    function listMonthsImpl (format, index, field) {
        if (isNumber(format)) {
            index = format;
            format = undefined;
        }

        format = format || '';

        if (index != null) {
            return get$1(format, index, field, 'month');
        }

        var i;
        var out = [];
        for (i = 0; i < 12; i++) {
            out[i] = get$1(format, i, field, 'month');
        }
        return out;
    }

    // ()
    // (5)
    // (fmt, 5)
    // (fmt)
    // (true)
    // (true, 5)
    // (true, fmt, 5)
    // (true, fmt)
    function listWeekdaysImpl (localeSorted, format, index, field) {
        if (typeof localeSorted === 'boolean') {
            if (isNumber(format)) {
                index = format;
                format = undefined;
            }

            format = format || '';
        } else {
            format = localeSorted;
            index = format;
            localeSorted = false;

            if (isNumber(format)) {
                index = format;
                format = undefined;
            }

            format = format || '';
        }

        var locale = getLocale(),
            shift = localeSorted ? locale._week.dow : 0;

        if (index != null) {
            return get$1(format, (index + shift) % 7, field, 'day');
        }

        var i;
        var out = [];
        for (i = 0; i < 7; i++) {
            out[i] = get$1(format, (i + shift) % 7, field, 'day');
        }
        return out;
    }

    function listMonths (format, index) {
        return listMonthsImpl(format, index, 'months');
    }

    function listMonthsShort (format, index) {
        return listMonthsImpl(format, index, 'monthsShort');
    }

    function listWeekdays (localeSorted, format, index) {
        return listWeekdaysImpl(localeSorted, format, index, 'weekdays');
    }

    function listWeekdaysShort (localeSorted, format, index) {
        return listWeekdaysImpl(localeSorted, format, index, 'weekdaysShort');
    }

    function listWeekdaysMin (localeSorted, format, index) {
        return listWeekdaysImpl(localeSorted, format, index, 'weekdaysMin');
    }

    getSetGlobalLocale('en', {
        dayOfMonthOrdinalParse: /\d{1,2}(th|st|nd|rd)/,
        ordinal : function (number) {
            var b = number % 10,
                output = (toInt(number % 100 / 10) === 1) ? 'th' :
                (b === 1) ? 'st' :
                (b === 2) ? 'nd' :
                (b === 3) ? 'rd' : 'th';
            return number + output;
        }
    });

    // Side effect imports

    hooks.lang = deprecate('moment.lang is deprecated. Use moment.locale instead.', getSetGlobalLocale);
    hooks.langData = deprecate('moment.langData is deprecated. Use moment.localeData instead.', getLocale);

    var mathAbs = Math.abs;

    function abs () {
        var data           = this._data;

        this._milliseconds = mathAbs(this._milliseconds);
        this._days         = mathAbs(this._days);
        this._months       = mathAbs(this._months);

        data.milliseconds  = mathAbs(data.milliseconds);
        data.seconds       = mathAbs(data.seconds);
        data.minutes       = mathAbs(data.minutes);
        data.hours         = mathAbs(data.hours);
        data.months        = mathAbs(data.months);
        data.years         = mathAbs(data.years);

        return this;
    }

    function addSubtract$1 (duration, input, value, direction) {
        var other = createDuration(input, value);

        duration._milliseconds += direction * other._milliseconds;
        duration._days         += direction * other._days;
        duration._months       += direction * other._months;

        return duration._bubble();
    }

    // supports only 2.0-style add(1, 's') or add(duration)
    function add$1 (input, value) {
        return addSubtract$1(this, input, value, 1);
    }

    // supports only 2.0-style subtract(1, 's') or subtract(duration)
    function subtract$1 (input, value) {
        return addSubtract$1(this, input, value, -1);
    }

    function absCeil (number) {
        if (number < 0) {
            return Math.floor(number);
        } else {
            return Math.ceil(number);
        }
    }

    function bubble () {
        var milliseconds = this._milliseconds;
        var days         = this._days;
        var months       = this._months;
        var data         = this._data;
        var seconds, minutes, hours, years, monthsFromDays;

        // if we have a mix of positive and negative values, bubble down first
        // check: https://github.com/moment/moment/issues/2166
        if (!((milliseconds >= 0 && days >= 0 && months >= 0) ||
                (milliseconds <= 0 && days <= 0 && months <= 0))) {
            milliseconds += absCeil(monthsToDays(months) + days) * 864e5;
            days = 0;
            months = 0;
        }

        // The following code bubbles up values, see the tests for
        // examples of what that means.
        data.milliseconds = milliseconds % 1000;

        seconds           = absFloor(milliseconds / 1000);
        data.seconds      = seconds % 60;

        minutes           = absFloor(seconds / 60);
        data.minutes      = minutes % 60;

        hours             = absFloor(minutes / 60);
        data.hours        = hours % 24;

        days += absFloor(hours / 24);

        // convert days to months
        monthsFromDays = absFloor(daysToMonths(days));
        months += monthsFromDays;
        days -= absCeil(monthsToDays(monthsFromDays));

        // 12 months -> 1 year
        years = absFloor(months / 12);
        months %= 12;

        data.days   = days;
        data.months = months;
        data.years  = years;

        return this;
    }

    function daysToMonths (days) {
        // 400 years have 146097 days (taking into account leap year rules)
        // 400 years have 12 months === 4800
        return days * 4800 / 146097;
    }

    function monthsToDays (months) {
        // the reverse of daysToMonths
        return months * 146097 / 4800;
    }

    function as (units) {
        if (!this.isValid()) {
            return NaN;
        }
        var days;
        var months;
        var milliseconds = this._milliseconds;

        units = normalizeUnits(units);

        if (units === 'month' || units === 'quarter' || units === 'year') {
            days = this._days + milliseconds / 864e5;
            months = this._months + daysToMonths(days);
            switch (units) {
                case 'month':   return months;
                case 'quarter': return months / 3;
                case 'year':    return months / 12;
            }
        } else {
            // handle milliseconds separately because of floating point math errors (issue #1867)
            days = this._days + Math.round(monthsToDays(this._months));
            switch (units) {
                case 'week'   : return days / 7     + milliseconds / 6048e5;
                case 'day'    : return days         + milliseconds / 864e5;
                case 'hour'   : return days * 24    + milliseconds / 36e5;
                case 'minute' : return days * 1440  + milliseconds / 6e4;
                case 'second' : return days * 86400 + milliseconds / 1000;
                // Math.floor prevents floating point math errors here
                case 'millisecond': return Math.floor(days * 864e5) + milliseconds;
                default: throw new Error('Unknown unit ' + units);
            }
        }
    }

    // TODO: Use this.as('ms')?
    function valueOf$1 () {
        if (!this.isValid()) {
            return NaN;
        }
        return (
            this._milliseconds +
            this._days * 864e5 +
            (this._months % 12) * 2592e6 +
            toInt(this._months / 12) * 31536e6
        );
    }

    function makeAs (alias) {
        return function () {
            return this.as(alias);
        };
    }

    var asMilliseconds = makeAs('ms');
    var asSeconds      = makeAs('s');
    var asMinutes      = makeAs('m');
    var asHours        = makeAs('h');
    var asDays         = makeAs('d');
    var asWeeks        = makeAs('w');
    var asMonths       = makeAs('M');
    var asQuarters     = makeAs('Q');
    var asYears        = makeAs('y');

    function clone$1 () {
        return createDuration(this);
    }

    function get$2 (units) {
        units = normalizeUnits(units);
        return this.isValid() ? this[units + 's']() : NaN;
    }

    function makeGetter(name) {
        return function () {
            return this.isValid() ? this._data[name] : NaN;
        };
    }

    var milliseconds = makeGetter('milliseconds');
    var seconds      = makeGetter('seconds');
    var minutes      = makeGetter('minutes');
    var hours        = makeGetter('hours');
    var days         = makeGetter('days');
    var months       = makeGetter('months');
    var years        = makeGetter('years');

    function weeks () {
        return absFloor(this.days() / 7);
    }

    var round = Math.round;
    var thresholds = {
        ss: 44,         // a few seconds to seconds
        s : 45,         // seconds to minute
        m : 45,         // minutes to hour
        h : 22,         // hours to day
        d : 26,         // days to month
        M : 11          // months to year
    };

    // helper function for moment.fn.from, moment.fn.fromNow, and moment.duration.fn.humanize
    function substituteTimeAgo(string, number, withoutSuffix, isFuture, locale) {
        return locale.relativeTime(number || 1, !!withoutSuffix, string, isFuture);
    }

    function relativeTime$1 (posNegDuration, withoutSuffix, locale) {
        var duration = createDuration(posNegDuration).abs();
        var seconds  = round(duration.as('s'));
        var minutes  = round(duration.as('m'));
        var hours    = round(duration.as('h'));
        var days     = round(duration.as('d'));
        var months   = round(duration.as('M'));
        var years    = round(duration.as('y'));

        var a = seconds <= thresholds.ss && ['s', seconds]  ||
                seconds < thresholds.s   && ['ss', seconds] ||
                minutes <= 1             && ['m']           ||
                minutes < thresholds.m   && ['mm', minutes] ||
                hours   <= 1             && ['h']           ||
                hours   < thresholds.h   && ['hh', hours]   ||
                days    <= 1             && ['d']           ||
                days    < thresholds.d   && ['dd', days]    ||
                months  <= 1             && ['M']           ||
                months  < thresholds.M   && ['MM', months]  ||
                years   <= 1             && ['y']           || ['yy', years];

        a[2] = withoutSuffix;
        a[3] = +posNegDuration > 0;
        a[4] = locale;
        return substituteTimeAgo.apply(null, a);
    }

    // This function allows you to set the rounding function for relative time strings
    function getSetRelativeTimeRounding (roundingFunction) {
        if (roundingFunction === undefined) {
            return round;
        }
        if (typeof(roundingFunction) === 'function') {
            round = roundingFunction;
            return true;
        }
        return false;
    }

    // This function allows you to set a threshold for relative time strings
    function getSetRelativeTimeThreshold (threshold, limit) {
        if (thresholds[threshold] === undefined) {
            return false;
        }
        if (limit === undefined) {
            return thresholds[threshold];
        }
        thresholds[threshold] = limit;
        if (threshold === 's') {
            thresholds.ss = limit - 1;
        }
        return true;
    }

    function humanize (withSuffix) {
        if (!this.isValid()) {
            return this.localeData().invalidDate();
        }

        var locale = this.localeData();
        var output = relativeTime$1(this, !withSuffix, locale);

        if (withSuffix) {
            output = locale.pastFuture(+this, output);
        }

        return locale.postformat(output);
    }

    var abs$1 = Math.abs;

    function sign(x) {
        return ((x > 0) - (x < 0)) || +x;
    }

    function toISOString$1() {
        // for ISO strings we do not use the normal bubbling rules:
        //  * milliseconds bubble up until they become hours
        //  * days do not bubble at all
        //  * months bubble up until they become years
        // This is because there is no context-free conversion between hours and days
        // (think of clock changes)
        // and also not between days and months (28-31 days per month)
        if (!this.isValid()) {
            return this.localeData().invalidDate();
        }

        var seconds = abs$1(this._milliseconds) / 1000;
        var days         = abs$1(this._days);
        var months       = abs$1(this._months);
        var minutes, hours, years;

        // 3600 seconds -> 60 minutes -> 1 hour
        minutes           = absFloor(seconds / 60);
        hours             = absFloor(minutes / 60);
        seconds %= 60;
        minutes %= 60;

        // 12 months -> 1 year
        years  = absFloor(months / 12);
        months %= 12;


        // inspired by https://github.com/dordille/moment-isoduration/blob/master/moment.isoduration.js
        var Y = years;
        var M = months;
        var D = days;
        var h = hours;
        var m = minutes;
        var s = seconds ? seconds.toFixed(3).replace(/\.?0+$/, '') : '';
        var total = this.asSeconds();

        if (!total) {
            // this is the same as C#'s (Noda) and python (isodate)...
            // but not other JS (goog.date)
            return 'P0D';
        }

        var totalSign = total < 0 ? '-' : '';
        var ymSign = sign(this._months) !== sign(total) ? '-' : '';
        var daysSign = sign(this._days) !== sign(total) ? '-' : '';
        var hmsSign = sign(this._milliseconds) !== sign(total) ? '-' : '';

        return totalSign + 'P' +
            (Y ? ymSign + Y + 'Y' : '') +
            (M ? ymSign + M + 'M' : '') +
            (D ? daysSign + D + 'D' : '') +
            ((h || m || s) ? 'T' : '') +
            (h ? hmsSign + h + 'H' : '') +
            (m ? hmsSign + m + 'M' : '') +
            (s ? hmsSign + s + 'S' : '');
    }

    var proto$2 = Duration.prototype;

    proto$2.isValid        = isValid$1;
    proto$2.abs            = abs;
    proto$2.add            = add$1;
    proto$2.subtract       = subtract$1;
    proto$2.as             = as;
    proto$2.asMilliseconds = asMilliseconds;
    proto$2.asSeconds      = asSeconds;
    proto$2.asMinutes      = asMinutes;
    proto$2.asHours        = asHours;
    proto$2.asDays         = asDays;
    proto$2.asWeeks        = asWeeks;
    proto$2.asMonths       = asMonths;
    proto$2.asQuarters     = asQuarters;
    proto$2.asYears        = asYears;
    proto$2.valueOf        = valueOf$1;
    proto$2._bubble        = bubble;
    proto$2.clone          = clone$1;
    proto$2.get            = get$2;
    proto$2.milliseconds   = milliseconds;
    proto$2.seconds        = seconds;
    proto$2.minutes        = minutes;
    proto$2.hours          = hours;
    proto$2.days           = days;
    proto$2.weeks          = weeks;
    proto$2.months         = months;
    proto$2.years          = years;
    proto$2.humanize       = humanize;
    proto$2.toISOString    = toISOString$1;
    proto$2.toString       = toISOString$1;
    proto$2.toJSON         = toISOString$1;
    proto$2.locale         = locale;
    proto$2.localeData     = localeData;

    proto$2.toIsoString = deprecate('toIsoString() is deprecated. Please use toISOString() instead (notice the capitals)', toISOString$1);
    proto$2.lang = lang;

    // Side effect imports

    // FORMATTING

    addFormatToken('X', 0, 0, 'unix');
    addFormatToken('x', 0, 0, 'valueOf');

    // PARSING

    addRegexToken('x', matchSigned);
    addRegexToken('X', matchTimestamp);
    addParseToken('X', function (input, array, config) {
        config._d = new Date(parseFloat(input, 10) * 1000);
    });
    addParseToken('x', function (input, array, config) {
        config._d = new Date(toInt(input));
    });

    // Side effect imports


    hooks.version = '2.24.0';

    setHookCallback(createLocal);

    hooks.fn                    = proto;
    hooks.min                   = min;
    hooks.max                   = max;
    hooks.now                   = now;
    hooks.utc                   = createUTC;
    hooks.unix                  = createUnix;
    hooks.months                = listMonths;
    hooks.isDate                = isDate;
    hooks.locale                = getSetGlobalLocale;
    hooks.invalid               = createInvalid;
    hooks.duration              = createDuration;
    hooks.isMoment              = isMoment;
    hooks.weekdays              = listWeekdays;
    hooks.parseZone             = createInZone;
    hooks.localeData            = getLocale;
    hooks.isDuration            = isDuration;
    hooks.monthsShort           = listMonthsShort;
    hooks.weekdaysMin           = listWeekdaysMin;
    hooks.defineLocale          = defineLocale;
    hooks.updateLocale          = updateLocale;
    hooks.locales               = listLocales;
    hooks.weekdaysShort         = listWeekdaysShort;
    hooks.normalizeUnits        = normalizeUnits;
    hooks.relativeTimeRounding  = getSetRelativeTimeRounding;
    hooks.relativeTimeThreshold = getSetRelativeTimeThreshold;
    hooks.calendarFormat        = getCalendarFormat;
    hooks.prototype             = proto;

    // currently HTML5 input type only supports 24-hour formats
    hooks.HTML5_FMT = {
        DATETIME_LOCAL: 'YYYY-MM-DDTHH:mm',             // <input type="datetime-local" />
        DATETIME_LOCAL_SECONDS: 'YYYY-MM-DDTHH:mm:ss',  // <input type="datetime-local" step="1" />
        DATETIME_LOCAL_MS: 'YYYY-MM-DDTHH:mm:ss.SSS',   // <input type="datetime-local" step="0.001" />
        DATE: 'YYYY-MM-DD',                             // <input type="date" />
        TIME: 'HH:mm',                                  // <input type="time" />
        TIME_SECONDS: 'HH:mm:ss',                       // <input type="time" step="1" />
        TIME_MS: 'HH:mm:ss.SSS',                        // <input type="time" step="0.001" />
        WEEK: 'GGGG-[W]WW',                             // <input type="week" />
        MONTH: 'YYYY-MM'                                // <input type="month" />
    };

    return hooks;

})));
;
/* flatpickr v4.6.3,, @license MIT */
!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define(t):(e=e||self).flatpickr=t()}(this,function(){"use strict";var e=function(){return(e=Object.assign||function(e){for(var t,n=1,a=arguments.length;n<a;n++)for(var i in t=arguments[n])Object.prototype.hasOwnProperty.call(t,i)&&(e[i]=t[i]);return e}).apply(this,arguments)},t=["onChange","onClose","onDayCreate","onDestroy","onKeyDown","onMonthChange","onOpen","onParseConfig","onReady","onValueUpdate","onYearChange","onPreCalendarPosition"],n={_disable:[],_enable:[],allowInput:!1,altFormat:"F j, Y",altInput:!1,altInputClass:"form-control input",animate:"object"==typeof window&&-1===window.navigator.userAgent.indexOf("MSIE"),ariaDateFormat:"F j, Y",clickOpens:!0,closeOnSelect:!0,conjunction:", ",dateFormat:"Y-m-d",defaultHour:12,defaultMinute:0,defaultSeconds:0,disable:[],disableMobile:!1,enable:[],enableSeconds:!1,enableTime:!1,errorHandler:function(e){return"undefined"!=typeof console&&console.warn(e)},getWeek:function(e){var t=new Date(e.getTime());t.setHours(0,0,0,0),t.setDate(t.getDate()+3-(t.getDay()+6)%7);var n=new Date(t.getFullYear(),0,4);return 1+Math.round(((t.getTime()-n.getTime())/864e5-3+(n.getDay()+6)%7)/7)},hourIncrement:1,ignoredFocusElements:[],inline:!1,locale:"default",minuteIncrement:5,mode:"single",monthSelectorType:"dropdown",nextArrow:"<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M13.207 8.472l-7.854 7.854-0.707-0.707 7.146-7.146-7.146-7.148 0.707-0.707 7.854 7.854z' /></svg>",noCalendar:!1,now:new Date,onChange:[],onClose:[],onDayCreate:[],onDestroy:[],onKeyDown:[],onMonthChange:[],onOpen:[],onParseConfig:[],onReady:[],onValueUpdate:[],onYearChange:[],onPreCalendarPosition:[],plugins:[],position:"auto",positionElement:void 0,prevArrow:"<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M5.207 8.471l7.146 7.147-0.707 0.707-7.853-7.854 7.854-7.853 0.707 0.707-7.147 7.146z' /></svg>",shorthandCurrentMonth:!1,showMonths:1,static:!1,time_24hr:!1,weekNumbers:!1,wrap:!1},a={weekdays:{shorthand:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],longhand:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]},months:{shorthand:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],longhand:["January","February","March","April","May","June","July","August","September","October","November","December"]},daysInMonth:[31,28,31,30,31,30,31,31,30,31,30,31],firstDayOfWeek:0,ordinal:function(e){var t=e%100;if(t>3&&t<21)return"th";switch(t%10){case 1:return"st";case 2:return"nd";case 3:return"rd";default:return"th"}},rangeSeparator:" to ",weekAbbreviation:"Wk",scrollTitle:"Scroll to increment",toggleTitle:"Click to toggle",amPM:["AM","PM"],yearAriaLabel:"Year",hourAriaLabel:"Hour",minuteAriaLabel:"Minute",time_24hr:!1},i=function(e){return("0"+e).slice(-2)},o=function(e){return!0===e?1:0};function r(e,t,n){var a;return void 0===n&&(n=!1),function(){var i=this,o=arguments;null!==a&&clearTimeout(a),a=window.setTimeout(function(){a=null,n||e.apply(i,o)},t),n&&!a&&e.apply(i,o)}}var l=function(e){return e instanceof Array?e:[e]};function c(e,t,n){if(!0===n)return e.classList.add(t);e.classList.remove(t)}function d(e,t,n){var a=window.document.createElement(e);return t=t||"",n=n||"",a.className=t,void 0!==n&&(a.textContent=n),a}function s(e){for(;e.firstChild;)e.removeChild(e.firstChild)}function u(e,t){var n=d("div","numInputWrapper"),a=d("input","numInput "+e),i=d("span","arrowUp"),o=d("span","arrowDown");if(-1===navigator.userAgent.indexOf("MSIE 9.0")?a.type="number":(a.type="text",a.pattern="\\d*"),void 0!==t)for(var r in t)a.setAttribute(r,t[r]);return n.appendChild(a),n.appendChild(i),n.appendChild(o),n}var f=function(){},m=function(e,t,n){return n.months[t?"shorthand":"longhand"][e]},g={D:f,F:function(e,t,n){e.setMonth(n.months.longhand.indexOf(t))},G:function(e,t){e.setHours(parseFloat(t))},H:function(e,t){e.setHours(parseFloat(t))},J:function(e,t){e.setDate(parseFloat(t))},K:function(e,t,n){e.setHours(e.getHours()%12+12*o(new RegExp(n.amPM[1],"i").test(t)))},M:function(e,t,n){e.setMonth(n.months.shorthand.indexOf(t))},S:function(e,t){e.setSeconds(parseFloat(t))},U:function(e,t){return new Date(1e3*parseFloat(t))},W:function(e,t,n){var a=parseInt(t),i=new Date(e.getFullYear(),0,2+7*(a-1),0,0,0,0);return i.setDate(i.getDate()-i.getDay()+n.firstDayOfWeek),i},Y:function(e,t){e.setFullYear(parseFloat(t))},Z:function(e,t){return new Date(t)},d:function(e,t){e.setDate(parseFloat(t))},h:function(e,t){e.setHours(parseFloat(t))},i:function(e,t){e.setMinutes(parseFloat(t))},j:function(e,t){e.setDate(parseFloat(t))},l:f,m:function(e,t){e.setMonth(parseFloat(t)-1)},n:function(e,t){e.setMonth(parseFloat(t)-1)},s:function(e,t){e.setSeconds(parseFloat(t))},u:function(e,t){return new Date(parseFloat(t))},w:f,y:function(e,t){e.setFullYear(2e3+parseFloat(t))}},p={D:"(\\w+)",F:"(\\w+)",G:"(\\d\\d|\\d)",H:"(\\d\\d|\\d)",J:"(\\d\\d|\\d)\\w+",K:"",M:"(\\w+)",S:"(\\d\\d|\\d)",U:"(.+)",W:"(\\d\\d|\\d)",Y:"(\\d{4})",Z:"(.+)",d:"(\\d\\d|\\d)",h:"(\\d\\d|\\d)",i:"(\\d\\d|\\d)",j:"(\\d\\d|\\d)",l:"(\\w+)",m:"(\\d\\d|\\d)",n:"(\\d\\d|\\d)",s:"(\\d\\d|\\d)",u:"(.+)",w:"(\\d\\d|\\d)",y:"(\\d{2})"},h={Z:function(e){return e.toISOString()},D:function(e,t,n){return t.weekdays.shorthand[h.w(e,t,n)]},F:function(e,t,n){return m(h.n(e,t,n)-1,!1,t)},G:function(e,t,n){return i(h.h(e,t,n))},H:function(e){return i(e.getHours())},J:function(e,t){return void 0!==t.ordinal?e.getDate()+t.ordinal(e.getDate()):e.getDate()},K:function(e,t){return t.amPM[o(e.getHours()>11)]},M:function(e,t){return m(e.getMonth(),!0,t)},S:function(e){return i(e.getSeconds())},U:function(e){return e.getTime()/1e3},W:function(e,t,n){return n.getWeek(e)},Y:function(e){return e.getFullYear()},d:function(e){return i(e.getDate())},h:function(e){return e.getHours()%12?e.getHours()%12:12},i:function(e){return i(e.getMinutes())},j:function(e){return e.getDate()},l:function(e,t){return t.weekdays.longhand[e.getDay()]},m:function(e){return i(e.getMonth()+1)},n:function(e){return e.getMonth()+1},s:function(e){return e.getSeconds()},u:function(e){return e.getTime()},w:function(e){return e.getDay()},y:function(e){return String(e.getFullYear()).substring(2)}},v=function(e){var t=e.config,i=void 0===t?n:t,o=e.l10n,r=void 0===o?a:o;return function(e,t,n){var a=n||r;return void 0!==i.formatDate?i.formatDate(e,t,a):t.split("").map(function(t,n,o){return h[t]&&"\\"!==o[n-1]?h[t](e,a,i):"\\"!==t?t:""}).join("")}},D=function(e){var t=e.config,i=void 0===t?n:t,o=e.l10n,r=void 0===o?a:o;return function(e,t,a,o){if(0===e||e){var l,c=o||r,d=e;if(e instanceof Date)l=new Date(e.getTime());else if("string"!=typeof e&&void 0!==e.toFixed)l=new Date(e);else if("string"==typeof e){var s=t||(i||n).dateFormat,u=String(e).trim();if("today"===u)l=new Date,a=!0;else if(/Z$/.test(u)||/GMT$/.test(u))l=new Date(e);else if(i&&i.parseDate)l=i.parseDate(e,s);else{l=i&&i.noCalendar?new Date((new Date).setHours(0,0,0,0)):new Date((new Date).getFullYear(),0,1,0,0,0,0);for(var f=void 0,m=[],h=0,v=0,D="";h<s.length;h++){var w=s[h],b="\\"===w,C="\\"===s[h-1]||b;if(p[w]&&!C){D+=p[w];var M=new RegExp(D).exec(e);M&&(f=!0)&&m["Y"!==w?"push":"unshift"]({fn:g[w],val:M[++v]})}else b||(D+=".");m.forEach(function(e){var t=e.fn,n=e.val;return l=t(l,n,c)||l})}l=f?l:void 0}}if(l instanceof Date&&!isNaN(l.getTime()))return!0===a&&l.setHours(0,0,0,0),l;i.errorHandler(new Error("Invalid date provided: "+d))}}};function w(e,t,n){return void 0===n&&(n=!0),!1!==n?new Date(e.getTime()).setHours(0,0,0,0)-new Date(t.getTime()).setHours(0,0,0,0):e.getTime()-t.getTime()}var b=function(e,t,n){return e>Math.min(t,n)&&e<Math.max(t,n)},C={DAY:864e5};"function"!=typeof Object.assign&&(Object.assign=function(e){for(var t=[],n=1;n<arguments.length;n++)t[n-1]=arguments[n];if(!e)throw TypeError("Cannot convert undefined or null to object");for(var a=function(t){t&&Object.keys(t).forEach(function(n){return e[n]=t[n]})},i=0,o=t;i<o.length;i++){a(o[i])}return e});var M=300;function y(f,g){var h={config:e({},n,E.defaultConfig),l10n:a};function y(e){return e.bind(h)}function x(){var e=h.config;!1===e.weekNumbers&&1===e.showMonths||!0!==e.noCalendar&&window.requestAnimationFrame(function(){if(void 0!==h.calendarContainer&&(h.calendarContainer.style.visibility="hidden",h.calendarContainer.style.display="block"),void 0!==h.daysContainer){var t=(h.days.offsetWidth+1)*e.showMonths;h.daysContainer.style.width=t+"px",h.calendarContainer.style.width=t+(void 0!==h.weekWrapper?h.weekWrapper.offsetWidth:0)+"px",h.calendarContainer.style.removeProperty("visibility"),h.calendarContainer.style.removeProperty("display")}})}function T(e){0===h.selectedDates.length&&ie(),void 0!==e&&"blur"!==e.type&&function(e){e.preventDefault();var t="keydown"===e.type,n=e.target;void 0!==h.amPM&&e.target===h.amPM&&(h.amPM.textContent=h.l10n.amPM[o(h.amPM.textContent===h.l10n.amPM[0])]);var a=parseFloat(n.getAttribute("min")),r=parseFloat(n.getAttribute("max")),l=parseFloat(n.getAttribute("step")),c=parseInt(n.value,10),d=e.delta||(t?38===e.which?1:-1:0),s=c+l*d;if(void 0!==n.value&&2===n.value.length){var u=n===h.hourElement,f=n===h.minuteElement;s<a?(s=r+s+o(!u)+(o(u)&&o(!h.amPM)),f&&j(void 0,-1,h.hourElement)):s>r&&(s=n===h.hourElement?s-r-o(!h.amPM):a,f&&j(void 0,1,h.hourElement)),h.amPM&&u&&(1===l?s+c===23:Math.abs(s-c)>l)&&(h.amPM.textContent=h.l10n.amPM[o(h.amPM.textContent===h.l10n.amPM[0])]),n.value=i(s)}}(e);var t=h._input.value;k(),we(),h._input.value!==t&&h._debouncedChange()}function k(){if(void 0!==h.hourElement&&void 0!==h.minuteElement){var e,t,n=(parseInt(h.hourElement.value.slice(-2),10)||0)%24,a=(parseInt(h.minuteElement.value,10)||0)%60,i=void 0!==h.secondElement?(parseInt(h.secondElement.value,10)||0)%60:0;void 0!==h.amPM&&(e=n,t=h.amPM.textContent,n=e%12+12*o(t===h.l10n.amPM[1]));var r=void 0!==h.config.minTime||h.config.minDate&&h.minDateHasTime&&h.latestSelectedDateObj&&0===w(h.latestSelectedDateObj,h.config.minDate,!0);if(void 0!==h.config.maxTime||h.config.maxDate&&h.maxDateHasTime&&h.latestSelectedDateObj&&0===w(h.latestSelectedDateObj,h.config.maxDate,!0)){var l=void 0!==h.config.maxTime?h.config.maxTime:h.config.maxDate;(n=Math.min(n,l.getHours()))===l.getHours()&&(a=Math.min(a,l.getMinutes())),a===l.getMinutes()&&(i=Math.min(i,l.getSeconds()))}if(r){var c=void 0!==h.config.minTime?h.config.minTime:h.config.minDate;(n=Math.max(n,c.getHours()))===c.getHours()&&(a=Math.max(a,c.getMinutes())),a===c.getMinutes()&&(i=Math.max(i,c.getSeconds()))}O(n,a,i)}}function I(e){var t=e||h.latestSelectedDateObj;t&&O(t.getHours(),t.getMinutes(),t.getSeconds())}function S(){var e=h.config.defaultHour,t=h.config.defaultMinute,n=h.config.defaultSeconds;if(void 0!==h.config.minDate){var a=h.config.minDate.getHours(),i=h.config.minDate.getMinutes();(e=Math.max(e,a))===a&&(t=Math.max(i,t)),e===a&&t===i&&(n=h.config.minDate.getSeconds())}if(void 0!==h.config.maxDate){var o=h.config.maxDate.getHours(),r=h.config.maxDate.getMinutes();(e=Math.min(e,o))===o&&(t=Math.min(r,t)),e===o&&t===r&&(n=h.config.maxDate.getSeconds())}O(e,t,n)}function O(e,t,n){void 0!==h.latestSelectedDateObj&&h.latestSelectedDateObj.setHours(e%24,t,n||0,0),h.hourElement&&h.minuteElement&&!h.isMobile&&(h.hourElement.value=i(h.config.time_24hr?e:(12+e)%12+12*o(e%12==0)),h.minuteElement.value=i(t),void 0!==h.amPM&&(h.amPM.textContent=h.l10n.amPM[o(e>=12)]),void 0!==h.secondElement&&(h.secondElement.value=i(n)))}function _(e){var t=parseInt(e.target.value)+(e.delta||0);(t/1e3>1||"Enter"===e.key&&!/[^\d]/.test(t.toString()))&&Q(t)}function F(e,t,n,a){return t instanceof Array?t.forEach(function(t){return F(e,t,n,a)}):e instanceof Array?e.forEach(function(e){return F(e,t,n,a)}):(e.addEventListener(t,n,a),void h._handlers.push({element:e,event:t,handler:n,options:a}))}function N(e){return function(t){1===t.which&&e(t)}}function Y(){ge("onChange")}function A(e,t){var n=void 0!==e?h.parseDate(e):h.latestSelectedDateObj||(h.config.minDate&&h.config.minDate>h.now?h.config.minDate:h.config.maxDate&&h.config.maxDate<h.now?h.config.maxDate:h.now),a=h.currentYear,i=h.currentMonth;try{void 0!==n&&(h.currentYear=n.getFullYear(),h.currentMonth=n.getMonth())}catch(e){e.message="Invalid date supplied: "+n,h.config.errorHandler(e)}t&&h.currentYear!==a&&(ge("onYearChange"),K()),!t||h.currentYear===a&&h.currentMonth===i||ge("onMonthChange"),h.redraw()}function P(e){~e.target.className.indexOf("arrow")&&j(e,e.target.classList.contains("arrowUp")?1:-1)}function j(e,t,n){var a=e&&e.target,i=n||a&&a.parentNode&&a.parentNode.firstChild,o=pe("increment");o.delta=t,i&&i.dispatchEvent(o)}function H(e,t,n,a){var i=X(t,!0),o=d("span","flatpickr-day "+e,t.getDate().toString());return o.dateObj=t,o.$i=a,o.setAttribute("aria-label",h.formatDate(t,h.config.ariaDateFormat)),-1===e.indexOf("hidden")&&0===w(t,h.now)&&(h.todayDateElem=o,o.classList.add("today"),o.setAttribute("aria-current","date")),i?(o.tabIndex=-1,he(t)&&(o.classList.add("selected"),h.selectedDateElem=o,"range"===h.config.mode&&(c(o,"startRange",h.selectedDates[0]&&0===w(t,h.selectedDates[0],!0)),c(o,"endRange",h.selectedDates[1]&&0===w(t,h.selectedDates[1],!0)),"nextMonthDay"===e&&o.classList.add("inRange")))):o.classList.add("flatpickr-disabled"),"range"===h.config.mode&&function(e){return!("range"!==h.config.mode||h.selectedDates.length<2)&&w(e,h.selectedDates[0])>=0&&w(e,h.selectedDates[1])<=0}(t)&&!he(t)&&o.classList.add("inRange"),h.weekNumbers&&1===h.config.showMonths&&"prevMonthDay"!==e&&n%7==1&&h.weekNumbers.insertAdjacentHTML("beforeend","<span class='flatpickr-day'>"+h.config.getWeek(t)+"</span>"),ge("onDayCreate",o),o}function L(e){e.focus(),"range"===h.config.mode&&ne(e)}function W(e){for(var t=e>0?0:h.config.showMonths-1,n=e>0?h.config.showMonths:-1,a=t;a!=n;a+=e)for(var i=h.daysContainer.children[a],o=e>0?0:i.children.length-1,r=e>0?i.children.length:-1,l=o;l!=r;l+=e){var c=i.children[l];if(-1===c.className.indexOf("hidden")&&X(c.dateObj))return c}}function R(e,t){var n=ee(document.activeElement||document.body),a=void 0!==e?e:n?document.activeElement:void 0!==h.selectedDateElem&&ee(h.selectedDateElem)?h.selectedDateElem:void 0!==h.todayDateElem&&ee(h.todayDateElem)?h.todayDateElem:W(t>0?1:-1);return void 0===a?h._input.focus():n?void function(e,t){for(var n=-1===e.className.indexOf("Month")?e.dateObj.getMonth():h.currentMonth,a=t>0?h.config.showMonths:-1,i=t>0?1:-1,o=n-h.currentMonth;o!=a;o+=i)for(var r=h.daysContainer.children[o],l=n-h.currentMonth===o?e.$i+t:t<0?r.children.length-1:0,c=r.children.length,d=l;d>=0&&d<c&&d!=(t>0?c:-1);d+=i){var s=r.children[d];if(-1===s.className.indexOf("hidden")&&X(s.dateObj)&&Math.abs(e.$i-d)>=Math.abs(t))return L(s)}h.changeMonth(i),R(W(i),0)}(a,t):L(a)}function B(e,t){for(var n=(new Date(e,t,1).getDay()-h.l10n.firstDayOfWeek+7)%7,a=h.utils.getDaysInMonth((t-1+12)%12),i=h.utils.getDaysInMonth(t),o=window.document.createDocumentFragment(),r=h.config.showMonths>1,l=r?"prevMonthDay hidden":"prevMonthDay",c=r?"nextMonthDay hidden":"nextMonthDay",s=a+1-n,u=0;s<=a;s++,u++)o.appendChild(H(l,new Date(e,t-1,s),s,u));for(s=1;s<=i;s++,u++)o.appendChild(H("",new Date(e,t,s),s,u));for(var f=i+1;f<=42-n&&(1===h.config.showMonths||u%7!=0);f++,u++)o.appendChild(H(c,new Date(e,t+1,f%i),f,u));var m=d("div","dayContainer");return m.appendChild(o),m}function J(){if(void 0!==h.daysContainer){s(h.daysContainer),h.weekNumbers&&s(h.weekNumbers);for(var e=document.createDocumentFragment(),t=0;t<h.config.showMonths;t++){var n=new Date(h.currentYear,h.currentMonth,1);n.setMonth(h.currentMonth+t),e.appendChild(B(n.getFullYear(),n.getMonth()))}h.daysContainer.appendChild(e),h.days=h.daysContainer.firstChild,"range"===h.config.mode&&1===h.selectedDates.length&&ne()}}function K(){if(!(h.config.showMonths>1||"dropdown"!==h.config.monthSelectorType)){var e=function(e){return!(void 0!==h.config.minDate&&h.currentYear===h.config.minDate.getFullYear()&&e<h.config.minDate.getMonth())&&!(void 0!==h.config.maxDate&&h.currentYear===h.config.maxDate.getFullYear()&&e>h.config.maxDate.getMonth())};h.monthsDropdownContainer.tabIndex=-1,h.monthsDropdownContainer.innerHTML="";for(var t=0;t<12;t++)if(e(t)){var n=d("option","flatpickr-monthDropdown-month");n.value=new Date(h.currentYear,t).getMonth().toString(),n.textContent=m(t,h.config.shorthandCurrentMonth,h.l10n),n.tabIndex=-1,h.currentMonth===t&&(n.selected=!0),h.monthsDropdownContainer.appendChild(n)}}}function U(){var e,t=d("div","flatpickr-month"),n=window.document.createDocumentFragment();h.config.showMonths>1||"static"===h.config.monthSelectorType?e=d("span","cur-month"):(h.monthsDropdownContainer=d("select","flatpickr-monthDropdown-months"),F(h.monthsDropdownContainer,"change",function(e){var t=e.target,n=parseInt(t.value,10);h.changeMonth(n-h.currentMonth),ge("onMonthChange")}),K(),e=h.monthsDropdownContainer);var a=u("cur-year",{tabindex:"-1"}),i=a.getElementsByTagName("input")[0];i.setAttribute("aria-label",h.l10n.yearAriaLabel),h.config.minDate&&i.setAttribute("min",h.config.minDate.getFullYear().toString()),h.config.maxDate&&(i.setAttribute("max",h.config.maxDate.getFullYear().toString()),i.disabled=!!h.config.minDate&&h.config.minDate.getFullYear()===h.config.maxDate.getFullYear());var o=d("div","flatpickr-current-month");return o.appendChild(e),o.appendChild(a),n.appendChild(o),t.appendChild(n),{container:t,yearElement:i,monthElement:e}}function q(){s(h.monthNav),h.monthNav.appendChild(h.prevMonthNav),h.config.showMonths&&(h.yearElements=[],h.monthElements=[]);for(var e=h.config.showMonths;e--;){var t=U();h.yearElements.push(t.yearElement),h.monthElements.push(t.monthElement),h.monthNav.appendChild(t.container)}h.monthNav.appendChild(h.nextMonthNav)}function $(){h.weekdayContainer?s(h.weekdayContainer):h.weekdayContainer=d("div","flatpickr-weekdays");for(var e=h.config.showMonths;e--;){var t=d("div","flatpickr-weekdaycontainer");h.weekdayContainer.appendChild(t)}return z(),h.weekdayContainer}function z(){if(h.weekdayContainer){var e=h.l10n.firstDayOfWeek,t=h.l10n.weekdays.shorthand.slice();e>0&&e<t.length&&(t=t.splice(e,t.length).concat(t.splice(0,e)));for(var n=h.config.showMonths;n--;)h.weekdayContainer.children[n].innerHTML="\n      <span class='flatpickr-weekday'>\n        "+t.join("</span><span class='flatpickr-weekday'>")+"\n      </span>\n      "}}function G(e,t){void 0===t&&(t=!0);var n=t?e:e-h.currentMonth;n<0&&!0===h._hidePrevMonthArrow||n>0&&!0===h._hideNextMonthArrow||(h.currentMonth+=n,(h.currentMonth<0||h.currentMonth>11)&&(h.currentYear+=h.currentMonth>11?1:-1,h.currentMonth=(h.currentMonth+12)%12,ge("onYearChange"),K()),J(),ge("onMonthChange"),ve())}function V(e){return!(!h.config.appendTo||!h.config.appendTo.contains(e))||h.calendarContainer.contains(e)}function Z(e){if(h.isOpen&&!h.config.inline){var t="function"==typeof(r=e).composedPath?r.composedPath()[0]:r.target,n=V(t),a=t===h.input||t===h.altInput||h.element.contains(t)||e.path&&e.path.indexOf&&(~e.path.indexOf(h.input)||~e.path.indexOf(h.altInput)),i="blur"===e.type?a&&e.relatedTarget&&!V(e.relatedTarget):!a&&!n&&!V(e.relatedTarget),o=!h.config.ignoredFocusElements.some(function(e){return e.contains(t)});i&&o&&(void 0!==h.timeContainer&&void 0!==h.minuteElement&&void 0!==h.hourElement&&T(),h.close(),"range"===h.config.mode&&1===h.selectedDates.length&&(h.clear(!1),h.redraw()))}var r}function Q(e){if(!(!e||h.config.minDate&&e<h.config.minDate.getFullYear()||h.config.maxDate&&e>h.config.maxDate.getFullYear())){var t=e,n=h.currentYear!==t;h.currentYear=t||h.currentYear,h.config.maxDate&&h.currentYear===h.config.maxDate.getFullYear()?h.currentMonth=Math.min(h.config.maxDate.getMonth(),h.currentMonth):h.config.minDate&&h.currentYear===h.config.minDate.getFullYear()&&(h.currentMonth=Math.max(h.config.minDate.getMonth(),h.currentMonth)),n&&(h.redraw(),ge("onYearChange"),K())}}function X(e,t){void 0===t&&(t=!0);var n=h.parseDate(e,void 0,t);if(h.config.minDate&&n&&w(n,h.config.minDate,void 0!==t?t:!h.minDateHasTime)<0||h.config.maxDate&&n&&w(n,h.config.maxDate,void 0!==t?t:!h.maxDateHasTime)>0)return!1;if(0===h.config.enable.length&&0===h.config.disable.length)return!0;if(void 0===n)return!1;for(var a=h.config.enable.length>0,i=a?h.config.enable:h.config.disable,o=0,r=void 0;o<i.length;o++){if("function"==typeof(r=i[o])&&r(n))return a;if(r instanceof Date&&void 0!==n&&r.getTime()===n.getTime())return a;if("string"==typeof r&&void 0!==n){var l=h.parseDate(r,void 0,!0);return l&&l.getTime()===n.getTime()?a:!a}if("object"==typeof r&&void 0!==n&&r.from&&r.to&&n.getTime()>=r.from.getTime()&&n.getTime()<=r.to.getTime())return a}return!a}function ee(e){return void 0!==h.daysContainer&&(-1===e.className.indexOf("hidden")&&h.daysContainer.contains(e))}function te(e){var t=e.target===h._input,n=h.config.allowInput,a=h.isOpen&&(!n||!t),i=h.config.inline&&t&&!n;if(13===e.keyCode&&t){if(n)return h.setDate(h._input.value,!0,e.target===h.altInput?h.config.altFormat:h.config.dateFormat),e.target.blur();h.open()}else if(V(e.target)||a||i){var o=!!h.timeContainer&&h.timeContainer.contains(e.target);switch(e.keyCode){case 13:o?(e.preventDefault(),T(),de()):se(e);break;case 27:e.preventDefault(),de();break;case 8:case 46:t&&!h.config.allowInput&&(e.preventDefault(),h.clear());break;case 37:case 39:if(o||t)h.hourElement&&h.hourElement.focus();else if(e.preventDefault(),void 0!==h.daysContainer&&(!1===n||document.activeElement&&ee(document.activeElement))){var r=39===e.keyCode?1:-1;e.ctrlKey?(e.stopPropagation(),G(r),R(W(1),0)):R(void 0,r)}break;case 38:case 40:e.preventDefault();var l=40===e.keyCode?1:-1;h.daysContainer&&void 0!==e.target.$i||e.target===h.input||e.target===h.altInput?e.ctrlKey?(e.stopPropagation(),Q(h.currentYear-l),R(W(1),0)):o||R(void 0,7*l):e.target===h.currentYearElement?Q(h.currentYear-l):h.config.enableTime&&(!o&&h.hourElement&&h.hourElement.focus(),T(e),h._debouncedChange());break;case 9:if(o){var c=[h.hourElement,h.minuteElement,h.secondElement,h.amPM].concat(h.pluginElements).filter(function(e){return e}),d=c.indexOf(e.target);if(-1!==d){var s=c[d+(e.shiftKey?-1:1)];e.preventDefault(),(s||h._input).focus()}}else!h.config.noCalendar&&h.daysContainer&&h.daysContainer.contains(e.target)&&e.shiftKey&&(e.preventDefault(),h._input.focus())}}if(void 0!==h.amPM&&e.target===h.amPM)switch(e.key){case h.l10n.amPM[0].charAt(0):case h.l10n.amPM[0].charAt(0).toLowerCase():h.amPM.textContent=h.l10n.amPM[0],k(),we();break;case h.l10n.amPM[1].charAt(0):case h.l10n.amPM[1].charAt(0).toLowerCase():h.amPM.textContent=h.l10n.amPM[1],k(),we()}(t||V(e.target))&&ge("onKeyDown",e)}function ne(e){if(1===h.selectedDates.length&&(!e||e.classList.contains("flatpickr-day")&&!e.classList.contains("flatpickr-disabled"))){for(var t=e?e.dateObj.getTime():h.days.firstElementChild.dateObj.getTime(),n=h.parseDate(h.selectedDates[0],void 0,!0).getTime(),a=Math.min(t,h.selectedDates[0].getTime()),i=Math.max(t,h.selectedDates[0].getTime()),o=!1,r=0,l=0,c=a;c<i;c+=C.DAY)X(new Date(c),!0)||(o=o||c>a&&c<i,c<n&&(!r||c>r)?r=c:c>n&&(!l||c<l)&&(l=c));for(var d=0;d<h.config.showMonths;d++)for(var s=h.daysContainer.children[d],u=function(a,i){var c=s.children[a],d=c.dateObj.getTime(),u=r>0&&d<r||l>0&&d>l;return u?(c.classList.add("notAllowed"),["inRange","startRange","endRange"].forEach(function(e){c.classList.remove(e)}),"continue"):o&&!u?"continue":(["startRange","inRange","endRange","notAllowed"].forEach(function(e){c.classList.remove(e)}),void(void 0!==e&&(e.classList.add(t<=h.selectedDates[0].getTime()?"startRange":"endRange"),n<t&&d===n?c.classList.add("startRange"):n>t&&d===n&&c.classList.add("endRange"),d>=r&&(0===l||d<=l)&&b(d,n,t)&&c.classList.add("inRange"))))},f=0,m=s.children.length;f<m;f++)u(f)}}function ae(){!h.isOpen||h.config.static||h.config.inline||le()}function ie(){h.setDate(void 0!==h.config.minDate?new Date(h.config.minDate.getTime()):new Date,!0),S(),we()}function oe(e){return function(t){var n=h.config["_"+e+"Date"]=h.parseDate(t,h.config.dateFormat),a=h.config["_"+("min"===e?"max":"min")+"Date"];void 0!==n&&(h["min"===e?"minDateHasTime":"maxDateHasTime"]=n.getHours()>0||n.getMinutes()>0||n.getSeconds()>0),h.selectedDates&&(h.selectedDates=h.selectedDates.filter(function(e){return X(e)}),h.selectedDates.length||"min"!==e||I(n),we()),h.daysContainer&&(ce(),void 0!==n?h.currentYearElement[e]=n.getFullYear().toString():h.currentYearElement.removeAttribute(e),h.currentYearElement.disabled=!!a&&void 0!==n&&a.getFullYear()===n.getFullYear())}}function re(){"object"!=typeof h.config.locale&&void 0===E.l10ns[h.config.locale]&&h.config.errorHandler(new Error("flatpickr: invalid locale "+h.config.locale)),h.l10n=e({},E.l10ns.default,"object"==typeof h.config.locale?h.config.locale:"default"!==h.config.locale?E.l10ns[h.config.locale]:void 0),p.K="("+h.l10n.amPM[0]+"|"+h.l10n.amPM[1]+"|"+h.l10n.amPM[0].toLowerCase()+"|"+h.l10n.amPM[1].toLowerCase()+")",void 0===e({},g,JSON.parse(JSON.stringify(f.dataset||{}))).time_24hr&&void 0===E.defaultConfig.time_24hr&&(h.config.time_24hr=h.l10n.time_24hr),h.formatDate=v(h),h.parseDate=D({config:h.config,l10n:h.l10n})}function le(e){if(void 0!==h.calendarContainer){ge("onPreCalendarPosition");var t=e||h._positionElement,n=Array.prototype.reduce.call(h.calendarContainer.children,function(e,t){return e+t.offsetHeight},0),a=h.calendarContainer.offsetWidth,i=h.config.position.split(" "),o=i[0],r=i.length>1?i[1]:null,l=t.getBoundingClientRect(),d=window.innerHeight-l.bottom,s="above"===o||"below"!==o&&d<n&&l.top>n,u=window.pageYOffset+l.top+(s?-n-2:t.offsetHeight+2);if(c(h.calendarContainer,"arrowTop",!s),c(h.calendarContainer,"arrowBottom",s),!h.config.inline){var f=window.pageXOffset+l.left-(null!=r&&"center"===r?(a-l.width)/2:0),m=window.document.body.offsetWidth-(window.pageXOffset+l.right),g=f+a>window.document.body.offsetWidth,p=m+a>window.document.body.offsetWidth;if(c(h.calendarContainer,"rightMost",g),!h.config.static)if(h.calendarContainer.style.top=u+"px",g)if(p){var v=document.styleSheets[0];if(void 0===v)return;var D=window.document.body.offsetWidth,w=Math.max(0,D/2-a/2),b=v.cssRules.length,C="{left:"+l.left+"px;right:auto;}";c(h.calendarContainer,"rightMost",!1),c(h.calendarContainer,"centerMost",!0),v.insertRule(".flatpickr-calendar.centerMost:before,.flatpickr-calendar.centerMost:after"+C,b),h.calendarContainer.style.left=w+"px",h.calendarContainer.style.right="auto"}else h.calendarContainer.style.left="auto",h.calendarContainer.style.right=m+"px";else h.calendarContainer.style.left=f+"px",h.calendarContainer.style.right="auto"}}}function ce(){h.config.noCalendar||h.isMobile||(ve(),J())}function de(){h._input.focus(),-1!==window.navigator.userAgent.indexOf("MSIE")||void 0!==navigator.msMaxTouchPoints?setTimeout(h.close,0):h.close()}function se(e){e.preventDefault(),e.stopPropagation();var t=function e(t,n){return n(t)?t:t.parentNode?e(t.parentNode,n):void 0}(e.target,function(e){return e.classList&&e.classList.contains("flatpickr-day")&&!e.classList.contains("flatpickr-disabled")&&!e.classList.contains("notAllowed")});if(void 0!==t){var n=t,a=h.latestSelectedDateObj=new Date(n.dateObj.getTime()),i=(a.getMonth()<h.currentMonth||a.getMonth()>h.currentMonth+h.config.showMonths-1)&&"range"!==h.config.mode;if(h.selectedDateElem=n,"single"===h.config.mode)h.selectedDates=[a];else if("multiple"===h.config.mode){var o=he(a);o?h.selectedDates.splice(parseInt(o),1):h.selectedDates.push(a)}else"range"===h.config.mode&&(2===h.selectedDates.length&&h.clear(!1,!1),h.latestSelectedDateObj=a,h.selectedDates.push(a),0!==w(a,h.selectedDates[0],!0)&&h.selectedDates.sort(function(e,t){return e.getTime()-t.getTime()}));if(k(),i){var r=h.currentYear!==a.getFullYear();h.currentYear=a.getFullYear(),h.currentMonth=a.getMonth(),r&&(ge("onYearChange"),K()),ge("onMonthChange")}if(ve(),J(),we(),h.config.enableTime&&setTimeout(function(){return h.showTimeInput=!0},50),i||"range"===h.config.mode||1!==h.config.showMonths?void 0!==h.selectedDateElem&&void 0===h.hourElement&&h.selectedDateElem&&h.selectedDateElem.focus():L(n),void 0!==h.hourElement&&void 0!==h.hourElement&&h.hourElement.focus(),h.config.closeOnSelect){var l="single"===h.config.mode&&!h.config.enableTime,c="range"===h.config.mode&&2===h.selectedDates.length&&!h.config.enableTime;(l||c)&&de()}Y()}}h.parseDate=D({config:h.config,l10n:h.l10n}),h._handlers=[],h.pluginElements=[],h.loadedPlugins=[],h._bind=F,h._setHoursFromDate=I,h._positionCalendar=le,h.changeMonth=G,h.changeYear=Q,h.clear=function(e,t){void 0===e&&(e=!0);void 0===t&&(t=!0);h.input.value="",void 0!==h.altInput&&(h.altInput.value="");void 0!==h.mobileInput&&(h.mobileInput.value="");h.selectedDates=[],h.latestSelectedDateObj=void 0,!0===t&&(h.currentYear=h._initialDate.getFullYear(),h.currentMonth=h._initialDate.getMonth());h.showTimeInput=!1,!0===h.config.enableTime&&S();h.redraw(),e&&ge("onChange")},h.close=function(){h.isOpen=!1,h.isMobile||(void 0!==h.calendarContainer&&h.calendarContainer.classList.remove("open"),void 0!==h._input&&h._input.classList.remove("active"));ge("onClose")},h._createElement=d,h.destroy=function(){void 0!==h.config&&ge("onDestroy");for(var e=h._handlers.length;e--;){var t=h._handlers[e];t.element.removeEventListener(t.event,t.handler,t.options)}if(h._handlers=[],h.mobileInput)h.mobileInput.parentNode&&h.mobileInput.parentNode.removeChild(h.mobileInput),h.mobileInput=void 0;else if(h.calendarContainer&&h.calendarContainer.parentNode)if(h.config.static&&h.calendarContainer.parentNode){var n=h.calendarContainer.parentNode;if(n.lastChild&&n.removeChild(n.lastChild),n.parentNode){for(;n.firstChild;)n.parentNode.insertBefore(n.firstChild,n);n.parentNode.removeChild(n)}}else h.calendarContainer.parentNode.removeChild(h.calendarContainer);h.altInput&&(h.input.type="text",h.altInput.parentNode&&h.altInput.parentNode.removeChild(h.altInput),delete h.altInput);h.input&&(h.input.type=h.input._type,h.input.classList.remove("flatpickr-input"),h.input.removeAttribute("readonly"),h.input.value="");["_showTimeInput","latestSelectedDateObj","_hideNextMonthArrow","_hidePrevMonthArrow","__hideNextMonthArrow","__hidePrevMonthArrow","isMobile","isOpen","selectedDateElem","minDateHasTime","maxDateHasTime","days","daysContainer","_input","_positionElement","innerContainer","rContainer","monthNav","todayDateElem","calendarContainer","weekdayContainer","prevMonthNav","nextMonthNav","monthsDropdownContainer","currentMonthElement","currentYearElement","navigationCurrentMonth","selectedDateElem","config"].forEach(function(e){try{delete h[e]}catch(e){}})},h.isEnabled=X,h.jumpToDate=A,h.open=function(e,t){void 0===t&&(t=h._positionElement);if(!0===h.isMobile)return e&&(e.preventDefault(),e.target&&e.target.blur()),void 0!==h.mobileInput&&(h.mobileInput.focus(),h.mobileInput.click()),void ge("onOpen");if(h._input.disabled||h.config.inline)return;var n=h.isOpen;h.isOpen=!0,n||(h.calendarContainer.classList.add("open"),h._input.classList.add("active"),ge("onOpen"),le(t));!0===h.config.enableTime&&!0===h.config.noCalendar&&(0===h.selectedDates.length&&ie(),!1!==h.config.allowInput||void 0!==e&&h.timeContainer.contains(e.relatedTarget)||setTimeout(function(){return h.hourElement.select()},50))},h.redraw=ce,h.set=function(e,n){if(null!==e&&"object"==typeof e)for(var a in Object.assign(h.config,e),e)void 0!==ue[a]&&ue[a].forEach(function(e){return e()});else h.config[e]=n,void 0!==ue[e]?ue[e].forEach(function(e){return e()}):t.indexOf(e)>-1&&(h.config[e]=l(n));h.redraw(),we(!1)},h.setDate=function(e,t,n){void 0===t&&(t=!1);void 0===n&&(n=h.config.dateFormat);if(0!==e&&!e||e instanceof Array&&0===e.length)return h.clear(t);fe(e,n),h.showTimeInput=h.selectedDates.length>0,h.latestSelectedDateObj=h.selectedDates[h.selectedDates.length-1],h.redraw(),A(),I(),0===h.selectedDates.length&&h.clear(!1);we(t),t&&ge("onChange")},h.toggle=function(e){if(!0===h.isOpen)return h.close();h.open(e)};var ue={locale:[re,z],showMonths:[q,x,$],minDate:[A],maxDate:[A]};function fe(e,t){var n=[];if(e instanceof Array)n=e.map(function(e){return h.parseDate(e,t)});else if(e instanceof Date||"number"==typeof e)n=[h.parseDate(e,t)];else if("string"==typeof e)switch(h.config.mode){case"single":case"time":n=[h.parseDate(e,t)];break;case"multiple":n=e.split(h.config.conjunction).map(function(e){return h.parseDate(e,t)});break;case"range":n=e.split(h.l10n.rangeSeparator).map(function(e){return h.parseDate(e,t)})}else h.config.errorHandler(new Error("Invalid date supplied: "+JSON.stringify(e)));h.selectedDates=n.filter(function(e){return e instanceof Date&&X(e,!1)}),"range"===h.config.mode&&h.selectedDates.sort(function(e,t){return e.getTime()-t.getTime()})}function me(e){return e.slice().map(function(e){return"string"==typeof e||"number"==typeof e||e instanceof Date?h.parseDate(e,void 0,!0):e&&"object"==typeof e&&e.from&&e.to?{from:h.parseDate(e.from,void 0),to:h.parseDate(e.to,void 0)}:e}).filter(function(e){return e})}function ge(e,t){if(void 0!==h.config){var n=h.config[e];if(void 0!==n&&n.length>0)for(var a=0;n[a]&&a<n.length;a++)n[a](h.selectedDates,h.input.value,h,t);"onChange"===e&&(h.input.dispatchEvent(pe("change")),h.input.dispatchEvent(pe("input")))}}function pe(e){var t=document.createEvent("Event");return t.initEvent(e,!0,!0),t}function he(e){for(var t=0;t<h.selectedDates.length;t++)if(0===w(h.selectedDates[t],e))return""+t;return!1}function ve(){h.config.noCalendar||h.isMobile||!h.monthNav||(h.yearElements.forEach(function(e,t){var n=new Date(h.currentYear,h.currentMonth,1);n.setMonth(h.currentMonth+t),h.config.showMonths>1||"static"===h.config.monthSelectorType?h.monthElements[t].textContent=m(n.getMonth(),h.config.shorthandCurrentMonth,h.l10n)+" ":h.monthsDropdownContainer.value=n.getMonth().toString(),e.value=n.getFullYear().toString()}),h._hidePrevMonthArrow=void 0!==h.config.minDate&&(h.currentYear===h.config.minDate.getFullYear()?h.currentMonth<=h.config.minDate.getMonth():h.currentYear<h.config.minDate.getFullYear()),h._hideNextMonthArrow=void 0!==h.config.maxDate&&(h.currentYear===h.config.maxDate.getFullYear()?h.currentMonth+1>h.config.maxDate.getMonth():h.currentYear>h.config.maxDate.getFullYear()))}function De(e){return h.selectedDates.map(function(t){return h.formatDate(t,e)}).filter(function(e,t,n){return"range"!==h.config.mode||h.config.enableTime||n.indexOf(e)===t}).join("range"!==h.config.mode?h.config.conjunction:h.l10n.rangeSeparator)}function we(e){void 0===e&&(e=!0),void 0!==h.mobileInput&&h.mobileFormatStr&&(h.mobileInput.value=void 0!==h.latestSelectedDateObj?h.formatDate(h.latestSelectedDateObj,h.mobileFormatStr):""),h.input.value=De(h.config.dateFormat),void 0!==h.altInput&&(h.altInput.value=De(h.config.altFormat)),!1!==e&&ge("onValueUpdate")}function be(e){var t=h.prevMonthNav.contains(e.target),n=h.nextMonthNav.contains(e.target);t||n?G(t?-1:1):h.yearElements.indexOf(e.target)>=0?e.target.select():e.target.classList.contains("arrowUp")?h.changeYear(h.currentYear+1):e.target.classList.contains("arrowDown")&&h.changeYear(h.currentYear-1)}return function(){h.element=h.input=f,h.isOpen=!1,function(){var a=["wrap","weekNumbers","allowInput","clickOpens","time_24hr","enableTime","noCalendar","altInput","shorthandCurrentMonth","inline","static","enableSeconds","disableMobile"],i=e({},g,JSON.parse(JSON.stringify(f.dataset||{}))),o={};h.config.parseDate=i.parseDate,h.config.formatDate=i.formatDate,Object.defineProperty(h.config,"enable",{get:function(){return h.config._enable},set:function(e){h.config._enable=me(e)}}),Object.defineProperty(h.config,"disable",{get:function(){return h.config._disable},set:function(e){h.config._disable=me(e)}});var r="time"===i.mode;if(!i.dateFormat&&(i.enableTime||r)){var c=E.defaultConfig.dateFormat||n.dateFormat;o.dateFormat=i.noCalendar||r?"H:i"+(i.enableSeconds?":S":""):c+" H:i"+(i.enableSeconds?":S":"")}if(i.altInput&&(i.enableTime||r)&&!i.altFormat){var d=E.defaultConfig.altFormat||n.altFormat;o.altFormat=i.noCalendar||r?"h:i"+(i.enableSeconds?":S K":" K"):d+" h:i"+(i.enableSeconds?":S":"")+" K"}i.altInputClass||(h.config.altInputClass=h.input.className+" "+h.config.altInputClass),Object.defineProperty(h.config,"minDate",{get:function(){return h.config._minDate},set:oe("min")}),Object.defineProperty(h.config,"maxDate",{get:function(){return h.config._maxDate},set:oe("max")});var s=function(e){return function(t){h.config["min"===e?"_minTime":"_maxTime"]=h.parseDate(t,"H:i:S")}};Object.defineProperty(h.config,"minTime",{get:function(){return h.config._minTime},set:s("min")}),Object.defineProperty(h.config,"maxTime",{get:function(){return h.config._maxTime},set:s("max")}),"time"===i.mode&&(h.config.noCalendar=!0,h.config.enableTime=!0),Object.assign(h.config,o,i);for(var u=0;u<a.length;u++)h.config[a[u]]=!0===h.config[a[u]]||"true"===h.config[a[u]];t.filter(function(e){return void 0!==h.config[e]}).forEach(function(e){h.config[e]=l(h.config[e]||[]).map(y)}),h.isMobile=!h.config.disableMobile&&!h.config.inline&&"single"===h.config.mode&&!h.config.disable.length&&!h.config.enable.length&&!h.config.weekNumbers&&/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);for(var u=0;u<h.config.plugins.length;u++){var m=h.config.plugins[u](h)||{};for(var p in m)t.indexOf(p)>-1?h.config[p]=l(m[p]).map(y).concat(h.config[p]):void 0===i[p]&&(h.config[p]=m[p])}ge("onParseConfig")}(),re(),h.input=h.config.wrap?f.querySelector("[data-input]"):f,h.input?(h.input._type=h.input.type,h.input.type="text",h.input.classList.add("flatpickr-input"),h._input=h.input,h.config.altInput&&(h.altInput=d(h.input.nodeName,h.config.altInputClass),h._input=h.altInput,h.altInput.placeholder=h.input.placeholder,h.altInput.disabled=h.input.disabled,h.altInput.required=h.input.required,h.altInput.tabIndex=h.input.tabIndex,h.altInput.type="text",h.input.setAttribute("type","hidden"),!h.config.static&&h.input.parentNode&&h.input.parentNode.insertBefore(h.altInput,h.input.nextSibling)),h.config.allowInput||h._input.setAttribute("readonly","readonly"),h._positionElement=h.config.positionElement||h._input):h.config.errorHandler(new Error("Invalid input element specified")),function(){h.selectedDates=[],h.now=h.parseDate(h.config.now)||new Date;var e=h.config.defaultDate||("INPUT"!==h.input.nodeName&&"TEXTAREA"!==h.input.nodeName||!h.input.placeholder||h.input.value!==h.input.placeholder?h.input.value:null);e&&fe(e,h.config.dateFormat),h._initialDate=h.selectedDates.length>0?h.selectedDates[0]:h.config.minDate&&h.config.minDate.getTime()>h.now.getTime()?h.config.minDate:h.config.maxDate&&h.config.maxDate.getTime()<h.now.getTime()?h.config.maxDate:h.now,h.currentYear=h._initialDate.getFullYear(),h.currentMonth=h._initialDate.getMonth(),h.selectedDates.length>0&&(h.latestSelectedDateObj=h.selectedDates[0]),void 0!==h.config.minTime&&(h.config.minTime=h.parseDate(h.config.minTime,"H:i")),void 0!==h.config.maxTime&&(h.config.maxTime=h.parseDate(h.config.maxTime,"H:i")),h.minDateHasTime=!!h.config.minDate&&(h.config.minDate.getHours()>0||h.config.minDate.getMinutes()>0||h.config.minDate.getSeconds()>0),h.maxDateHasTime=!!h.config.maxDate&&(h.config.maxDate.getHours()>0||h.config.maxDate.getMinutes()>0||h.config.maxDate.getSeconds()>0),Object.defineProperty(h,"showTimeInput",{get:function(){return h._showTimeInput},set:function(e){h._showTimeInput=e,h.calendarContainer&&c(h.calendarContainer,"showTimeInput",e),h.isOpen&&le()}})}(),h.utils={getDaysInMonth:function(e,t){return void 0===e&&(e=h.currentMonth),void 0===t&&(t=h.currentYear),1===e&&(t%4==0&&t%100!=0||t%400==0)?29:h.l10n.daysInMonth[e]}},h.isMobile||function(){var e=window.document.createDocumentFragment();if(h.calendarContainer=d("div","flatpickr-calendar"),h.calendarContainer.tabIndex=-1,!h.config.noCalendar){if(e.appendChild((h.monthNav=d("div","flatpickr-months"),h.yearElements=[],h.monthElements=[],h.prevMonthNav=d("span","flatpickr-prev-month"),h.prevMonthNav.innerHTML=h.config.prevArrow,h.nextMonthNav=d("span","flatpickr-next-month"),h.nextMonthNav.innerHTML=h.config.nextArrow,q(),Object.defineProperty(h,"_hidePrevMonthArrow",{get:function(){return h.__hidePrevMonthArrow},set:function(e){h.__hidePrevMonthArrow!==e&&(c(h.prevMonthNav,"flatpickr-disabled",e),h.__hidePrevMonthArrow=e)}}),Object.defineProperty(h,"_hideNextMonthArrow",{get:function(){return h.__hideNextMonthArrow},set:function(e){h.__hideNextMonthArrow!==e&&(c(h.nextMonthNav,"flatpickr-disabled",e),h.__hideNextMonthArrow=e)}}),h.currentYearElement=h.yearElements[0],ve(),h.monthNav)),h.innerContainer=d("div","flatpickr-innerContainer"),h.config.weekNumbers){var t=function(){h.calendarContainer.classList.add("hasWeeks");var e=d("div","flatpickr-weekwrapper");e.appendChild(d("span","flatpickr-weekday",h.l10n.weekAbbreviation));var t=d("div","flatpickr-weeks");return e.appendChild(t),{weekWrapper:e,weekNumbers:t}}(),n=t.weekWrapper,a=t.weekNumbers;h.innerContainer.appendChild(n),h.weekNumbers=a,h.weekWrapper=n}h.rContainer=d("div","flatpickr-rContainer"),h.rContainer.appendChild($()),h.daysContainer||(h.daysContainer=d("div","flatpickr-days"),h.daysContainer.tabIndex=-1),J(),h.rContainer.appendChild(h.daysContainer),h.innerContainer.appendChild(h.rContainer),e.appendChild(h.innerContainer)}h.config.enableTime&&e.appendChild(function(){h.calendarContainer.classList.add("hasTime"),h.config.noCalendar&&h.calendarContainer.classList.add("noCalendar"),h.timeContainer=d("div","flatpickr-time"),h.timeContainer.tabIndex=-1;var e=d("span","flatpickr-time-separator",":"),t=u("flatpickr-hour",{"aria-label":h.l10n.hourAriaLabel});h.hourElement=t.getElementsByTagName("input")[0];var n=u("flatpickr-minute",{"aria-label":h.l10n.minuteAriaLabel});if(h.minuteElement=n.getElementsByTagName("input")[0],h.hourElement.tabIndex=h.minuteElement.tabIndex=-1,h.hourElement.value=i(h.latestSelectedDateObj?h.latestSelectedDateObj.getHours():h.config.time_24hr?h.config.defaultHour:function(e){switch(e%24){case 0:case 12:return 12;default:return e%12}}(h.config.defaultHour)),h.minuteElement.value=i(h.latestSelectedDateObj?h.latestSelectedDateObj.getMinutes():h.config.defaultMinute),h.hourElement.setAttribute("step",h.config.hourIncrement.toString()),h.minuteElement.setAttribute("step",h.config.minuteIncrement.toString()),h.hourElement.setAttribute("min",h.config.time_24hr?"0":"1"),h.hourElement.setAttribute("max",h.config.time_24hr?"23":"12"),h.minuteElement.setAttribute("min","0"),h.minuteElement.setAttribute("max","59"),h.timeContainer.appendChild(t),h.timeContainer.appendChild(e),h.timeContainer.appendChild(n),h.config.time_24hr&&h.timeContainer.classList.add("time24hr"),h.config.enableSeconds){h.timeContainer.classList.add("hasSeconds");var a=u("flatpickr-second");h.secondElement=a.getElementsByTagName("input")[0],h.secondElement.value=i(h.latestSelectedDateObj?h.latestSelectedDateObj.getSeconds():h.config.defaultSeconds),h.secondElement.setAttribute("step",h.minuteElement.getAttribute("step")),h.secondElement.setAttribute("min","0"),h.secondElement.setAttribute("max","59"),h.timeContainer.appendChild(d("span","flatpickr-time-separator",":")),h.timeContainer.appendChild(a)}return h.config.time_24hr||(h.amPM=d("span","flatpickr-am-pm",h.l10n.amPM[o((h.latestSelectedDateObj?h.hourElement.value:h.config.defaultHour)>11)]),h.amPM.title=h.l10n.toggleTitle,h.amPM.tabIndex=-1,h.timeContainer.appendChild(h.amPM)),h.timeContainer}()),c(h.calendarContainer,"rangeMode","range"===h.config.mode),c(h.calendarContainer,"animate",!0===h.config.animate),c(h.calendarContainer,"multiMonth",h.config.showMonths>1),h.calendarContainer.appendChild(e);var r=void 0!==h.config.appendTo&&void 0!==h.config.appendTo.nodeType;if((h.config.inline||h.config.static)&&(h.calendarContainer.classList.add(h.config.inline?"inline":"static"),h.config.inline&&(!r&&h.element.parentNode?h.element.parentNode.insertBefore(h.calendarContainer,h._input.nextSibling):void 0!==h.config.appendTo&&h.config.appendTo.appendChild(h.calendarContainer)),h.config.static)){var l=d("div","flatpickr-wrapper");h.element.parentNode&&h.element.parentNode.insertBefore(l,h.element),l.appendChild(h.element),h.altInput&&l.appendChild(h.altInput),l.appendChild(h.calendarContainer)}h.config.static||h.config.inline||(void 0!==h.config.appendTo?h.config.appendTo:window.document.body).appendChild(h.calendarContainer)}(),function(){if(h.config.wrap&&["open","close","toggle","clear"].forEach(function(e){Array.prototype.forEach.call(h.element.querySelectorAll("[data-"+e+"]"),function(t){return F(t,"click",h[e])})}),h.isMobile)!function(){var e=h.config.enableTime?h.config.noCalendar?"time":"datetime-local":"date";h.mobileInput=d("input",h.input.className+" flatpickr-mobile"),h.mobileInput.step=h.input.getAttribute("step")||"any",h.mobileInput.tabIndex=1,h.mobileInput.type=e,h.mobileInput.disabled=h.input.disabled,h.mobileInput.required=h.input.required,h.mobileInput.placeholder=h.input.placeholder,h.mobileFormatStr="datetime-local"===e?"Y-m-d\\TH:i:S":"date"===e?"Y-m-d":"H:i:S",h.selectedDates.length>0&&(h.mobileInput.defaultValue=h.mobileInput.value=h.formatDate(h.selectedDates[0],h.mobileFormatStr)),h.config.minDate&&(h.mobileInput.min=h.formatDate(h.config.minDate,"Y-m-d")),h.config.maxDate&&(h.mobileInput.max=h.formatDate(h.config.maxDate,"Y-m-d")),h.input.type="hidden",void 0!==h.altInput&&(h.altInput.type="hidden");try{h.input.parentNode&&h.input.parentNode.insertBefore(h.mobileInput,h.input.nextSibling)}catch(e){}F(h.mobileInput,"change",function(e){h.setDate(e.target.value,!1,h.mobileFormatStr),ge("onChange"),ge("onClose")})}();else{var e=r(ae,50);h._debouncedChange=r(Y,M),h.daysContainer&&!/iPhone|iPad|iPod/i.test(navigator.userAgent)&&F(h.daysContainer,"mouseover",function(e){"range"===h.config.mode&&ne(e.target)}),F(window.document.body,"keydown",te),h.config.inline||h.config.static||F(window,"resize",e),void 0!==window.ontouchstart?F(window.document,"touchstart",Z):F(window.document,"mousedown",N(Z)),F(window.document,"focus",Z,{capture:!0}),!0===h.config.clickOpens&&(F(h._input,"focus",h.open),F(h._input,"mousedown",N(h.open))),void 0!==h.daysContainer&&(F(h.monthNav,"mousedown",N(be)),F(h.monthNav,["keyup","increment"],_),F(h.daysContainer,"mousedown",N(se))),void 0!==h.timeContainer&&void 0!==h.minuteElement&&void 0!==h.hourElement&&(F(h.timeContainer,["increment"],T),F(h.timeContainer,"blur",T,{capture:!0}),F(h.timeContainer,"mousedown",N(P)),F([h.hourElement,h.minuteElement],["focus","click"],function(e){return e.target.select()}),void 0!==h.secondElement&&F(h.secondElement,"focus",function(){return h.secondElement&&h.secondElement.select()}),void 0!==h.amPM&&F(h.amPM,"mousedown",N(function(e){T(e),Y()})))}}(),(h.selectedDates.length||h.config.noCalendar)&&(h.config.enableTime&&I(h.config.noCalendar?h.latestSelectedDateObj||h.config.minDate:void 0),we(!1)),x(),h.showTimeInput=h.selectedDates.length>0||h.config.noCalendar;var a=/^((?!chrome|android).)*safari/i.test(navigator.userAgent);!h.isMobile&&a&&le(),ge("onReady")}(),h}function x(e,t){for(var n=Array.prototype.slice.call(e).filter(function(e){return e instanceof HTMLElement}),a=[],i=0;i<n.length;i++){var o=n[i];try{if(null!==o.getAttribute("data-fp-omit"))continue;void 0!==o._flatpickr&&(o._flatpickr.destroy(),o._flatpickr=void 0),o._flatpickr=y(o,t||{}),a.push(o._flatpickr)}catch(e){console.error(e)}}return 1===a.length?a[0]:a}"undefined"!=typeof HTMLElement&&"undefined"!=typeof HTMLCollection&&"undefined"!=typeof NodeList&&(HTMLCollection.prototype.flatpickr=NodeList.prototype.flatpickr=function(e){return x(this,e)},HTMLElement.prototype.flatpickr=function(e){return x([this],e)});var E=function(e,t){return"string"==typeof e?x(window.document.querySelectorAll(e),t):e instanceof Node?x([e],t):x(e,t)};return E.defaultConfig={},E.l10ns={en:e({},a),default:e({},a)},E.localize=function(t){E.l10ns.default=e({},E.l10ns.default,t)},E.setDefaults=function(t){E.defaultConfig=e({},E.defaultConfig,t)},E.parseDate=D({}),E.formatDate=v({}),E.compareDates=w,"undefined"!=typeof jQuery&&void 0!==jQuery.fn&&(jQuery.fn.flatpickr=function(e){return x(this,e)}),Date.prototype.fp_incr=function(e){return new Date(this.getFullYear(),this.getMonth(),this.getDate()+("string"==typeof e?parseInt(e,10):e))},"undefined"!=typeof window&&(window.flatpickr=E),E});;
/*
 * jQuery Iframe Transport Plugin
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, require */

(function (factory) {
  'use strict';
  if (typeof define === 'function' && define.amd) {
    // Register as an anonymous AMD module:
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS:
    factory(require('jquery'));
  } else {
    // Browser globals:
    factory(window.jQuery);
  }
})(function ($) {
  'use strict';

  // Helper variable to create unique names for the transport iframes:
  var counter = 0,
    jsonAPI = $,
    jsonParse = 'parseJSON';

  if ('JSON' in window && 'parse' in JSON) {
    jsonAPI = JSON;
    jsonParse = 'parse';
  }

  // The iframe transport accepts four additional options:
  // options.fileInput: a jQuery collection of file input fields
  // options.paramName: the parameter name for the file form data,
  //  overrides the name property of the file input field(s),
  //  can be a string or an array of strings.
  // options.formData: an array of objects with name and value properties,
  //  equivalent to the return data of .serializeArray(), e.g.:
  //  [{name: 'a', value: 1}, {name: 'b', value: 2}]
  // options.initialIframeSrc: the URL of the initial iframe src,
  //  by default set to "javascript:false;"
  $.ajaxTransport('iframe', function (options) {
    if (options.async) {
      // javascript:false as initial iframe src
      // prevents warning popups on HTTPS in IE6:
      // eslint-disable-next-line no-script-url
      var initialIframeSrc = options.initialIframeSrc || 'javascript:false;',
        form,
        iframe,
        addParamChar;
      return {
        send: function (_, completeCallback) {
          form = $('<form style="display:none;"></form>');
          form.attr('accept-charset', options.formAcceptCharset);
          addParamChar = /\?/.test(options.url) ? '&' : '?';
          // XDomainRequest only supports GET and POST:
          if (options.type === 'DELETE') {
            options.url = options.url + addParamChar + '_method=DELETE';
            options.type = 'POST';
          } else if (options.type === 'PUT') {
            options.url = options.url + addParamChar + '_method=PUT';
            options.type = 'POST';
          } else if (options.type === 'PATCH') {
            options.url = options.url + addParamChar + '_method=PATCH';
            options.type = 'POST';
          }
          // IE versions below IE8 cannot set the name property of
          // elements that have already been added to the DOM,
          // so we set the name along with the iframe HTML markup:
          counter += 1;
          iframe = $(
            '<iframe src="' +
              initialIframeSrc +
              '" name="iframe-transport-' +
              counter +
              '"></iframe>'
          ).on('load', function () {
            var fileInputClones,
              paramNames = $.isArray(options.paramName)
                ? options.paramName
                : [options.paramName];
            iframe.off('load').on('load', function () {
              var response;
              // Wrap in a try/catch block to catch exceptions thrown
              // when trying to access cross-domain iframe contents:
              try {
                response = iframe.contents();
                // Google Chrome and Firefox do not throw an
                // exception when calling iframe.contents() on
                // cross-domain requests, so we unify the response:
                if (!response.length || !response[0].firstChild) {
                  throw new Error();
                }
              } catch (e) {
                response = undefined;
              }
              // The complete callback returns the
              // iframe content document as response object:
              completeCallback(200, 'success', { iframe: response });
              // Fix for IE endless progress bar activity bug
              // (happens on form submits to iframe targets):
              $('<iframe src="' + initialIframeSrc + '"></iframe>').appendTo(
                form
              );
              window.setTimeout(function () {
                // Removing the form in a setTimeout call
                // allows Chrome's developer tools to display
                // the response result
                form.remove();
              }, 0);
            });
            form
              .prop('target', iframe.prop('name'))
              .prop('action', options.url)
              .prop('method', options.type);
            if (options.formData) {
              $.each(options.formData, function (index, field) {
                $('<input type="hidden"/>')
                  .prop('name', field.name)
                  .val(field.value)
                  .appendTo(form);
              });
            }
            if (
              options.fileInput &&
              options.fileInput.length &&
              options.type === 'POST'
            ) {
              fileInputClones = options.fileInput.clone();
              // Insert a clone for each file input field:
              options.fileInput.after(function (index) {
                return fileInputClones[index];
              });
              if (options.paramName) {
                options.fileInput.each(function (index) {
                  $(this).prop('name', paramNames[index] || options.paramName);
                });
              }
              // Appending the file input fields to the hidden form
              // removes them from their original location:
              form
                .append(options.fileInput)
                .prop('enctype', 'multipart/form-data')
                // enctype must be set as encoding for IE:
                .prop('encoding', 'multipart/form-data');
              // Remove the HTML5 form attribute from the input(s):
              options.fileInput.removeAttr('form');
            }
            window.setTimeout(function () {
              // Submitting the form in a setTimeout call fixes an issue with
              // Safari 13 not triggering the iframe load event after resetting
              // the load event handler, see also:
              // https://github.com/blueimp/jQuery-File-Upload/issues/3633
              form.submit();
              // Insert the file input fields at their original location
              // by replacing the clones with the originals:
              if (fileInputClones && fileInputClones.length) {
                options.fileInput.each(function (index, input) {
                  var clone = $(fileInputClones[index]);
                  // Restore the original name and form properties:
                  $(input)
                    .prop('name', clone.prop('name'))
                    .attr('form', clone.attr('form'));
                  clone.replaceWith(input);
                });
              }
            }, 0);
          });
          form.append(iframe).appendTo(document.body);
        },
        abort: function () {
          if (iframe) {
            // javascript:false as iframe src aborts the request
            // and prevents warning popups on HTTPS in IE6.
            iframe.off('load').prop('src', initialIframeSrc);
          }
          if (form) {
            form.remove();
          }
        }
      };
    }
  });

  // The iframe transport returns the iframe content document as response.
  // The following adds converters from iframe to text, json, html, xml
  // and script.
  // Please note that the Content-Type for JSON responses has to be text/plain
  // or text/html, if the browser doesn't include application/json in the
  // Accept header, else IE will show a download dialog.
  // The Content-Type for XML responses on the other hand has to be always
  // application/xml or text/xml, so IE properly parses the XML response.
  // See also
  // https://github.com/blueimp/jQuery-File-Upload/wiki/Setup#content-type-negotiation
  $.ajaxSetup({
    converters: {
      'iframe text': function (iframe) {
        return iframe && $(iframe[0].body).text();
      },
      'iframe json': function (iframe) {
        return iframe && jsonAPI[jsonParse]($(iframe[0].body).text());
      },
      'iframe html': function (iframe) {
        return iframe && $(iframe[0].body).html();
      },
      'iframe xml': function (iframe) {
        var xmlDoc = iframe && iframe[0];
        return xmlDoc && $.isXMLDoc(xmlDoc)
          ? xmlDoc
          : $.parseXML(
              (xmlDoc.XMLDocument && xmlDoc.XMLDocument.xml) ||
                $(xmlDoc.body).html()
            );
      },
      'iframe script': function (iframe) {
        return iframe && $.globalEval($(iframe[0].body).text());
      }
    }
  });
});
;
/*
 * jQuery File Upload Plugin
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, require */
/* eslint-disable new-cap */

(function (factory) {
  'use strict';
  if (typeof define === 'function' && define.amd) {
    // Register as an anonymous AMD module:
    define(['jquery', 'jquery-ui/ui/widget'], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS:
    factory(require('jquery'), require('./vendor/jquery.ui.widget'));
  } else {
    // Browser globals:
    factory(window.jQuery);
  }
})(function ($) {
  'use strict';

  // Detect file input support, based on
  // https://viljamis.com/2012/file-upload-support-on-mobile/
  $.support.fileInput = !(
    new RegExp(
      // Handle devices which give false positives for the feature detection:
      '(Android (1\\.[0156]|2\\.[01]))' +
        '|(Windows Phone (OS 7|8\\.0))|(XBLWP)|(ZuneWP)|(WPDesktop)' +
        '|(w(eb)?OSBrowser)|(webOS)' +
        '|(Kindle/(1\\.0|2\\.[05]|3\\.0))'
    ).test(window.navigator.userAgent) ||
    // Feature detection for all other devices:
    $('<input type="file"/>').prop('disabled')
  );

  // The FileReader API is not actually used, but works as feature detection,
  // as some Safari versions (5?) support XHR file uploads via the FormData API,
  // but not non-multipart XHR file uploads.
  // window.XMLHttpRequestUpload is not available on IE10, so we check for
  // window.ProgressEvent instead to detect XHR2 file upload capability:
  $.support.xhrFileUpload = !!(window.ProgressEvent && window.FileReader);
  $.support.xhrFormDataFileUpload = !!window.FormData;

  // Detect support for Blob slicing (required for chunked uploads):
  $.support.blobSlice =
    window.Blob &&
    (Blob.prototype.slice ||
      Blob.prototype.webkitSlice ||
      Blob.prototype.mozSlice);

  /**
   * Helper function to create drag handlers for dragover/dragenter/dragleave
   *
   * @param {string} type Event type
   * @returns {Function} Drag handler
   */
  function getDragHandler(type) {
    var isDragOver = type === 'dragover';
    return function (e) {
      e.dataTransfer = e.originalEvent && e.originalEvent.dataTransfer;
      var dataTransfer = e.dataTransfer;
      if (
        dataTransfer &&
        $.inArray('Files', dataTransfer.types) !== -1 &&
        this._trigger(type, $.Event(type, { delegatedEvent: e })) !== false
      ) {
        e.preventDefault();
        if (isDragOver) {
          dataTransfer.dropEffect = 'copy';
        }
      }
    };
  }

  // The fileupload widget listens for change events on file input fields defined
  // via fileInput setting and paste or drop events of the given dropZone.
  // In addition to the default jQuery Widget methods, the fileupload widget
  // exposes the "add" and "send" methods, to add or directly send files using
  // the fileupload API.
  // By default, files added via file input selection, paste, drag & drop or
  // "add" method are uploaded immediately, but it is possible to override
  // the "add" callback option to queue file uploads.
  $.widget('blueimp.fileupload', {
    options: {
      // The drop target element(s), by the default the complete document.
      // Set to null to disable drag & drop support:
      dropZone: $(document),
      // The paste target element(s), by the default undefined.
      // Set to a DOM node or jQuery object to enable file pasting:
      pasteZone: undefined,
      // The file input field(s), that are listened to for change events.
      // If undefined, it is set to the file input fields inside
      // of the widget element on plugin initialization.
      // Set to null to disable the change listener.
      fileInput: undefined,
      // By default, the file input field is replaced with a clone after
      // each input field change event. This is required for iframe transport
      // queues and allows change events to be fired for the same file
      // selection, but can be disabled by setting the following option to false:
      replaceFileInput: true,
      // The parameter name for the file form data (the request argument name).
      // If undefined or empty, the name property of the file input field is
      // used, or "files[]" if the file input name property is also empty,
      // can be a string or an array of strings:
      paramName: undefined,
      // By default, each file of a selection is uploaded using an individual
      // request for XHR type uploads. Set to false to upload file
      // selections in one request each:
      singleFileUploads: true,
      // To limit the number of files uploaded with one XHR request,
      // set the following option to an integer greater than 0:
      limitMultiFileUploads: undefined,
      // The following option limits the number of files uploaded with one
      // XHR request to keep the request size under or equal to the defined
      // limit in bytes:
      limitMultiFileUploadSize: undefined,
      // Multipart file uploads add a number of bytes to each uploaded file,
      // therefore the following option adds an overhead for each file used
      // in the limitMultiFileUploadSize configuration:
      limitMultiFileUploadSizeOverhead: 512,
      // Set the following option to true to issue all file upload requests
      // in a sequential order:
      sequentialUploads: false,
      // To limit the number of concurrent uploads,
      // set the following option to an integer greater than 0:
      limitConcurrentUploads: undefined,
      // Set the following option to true to force iframe transport uploads:
      forceIframeTransport: false,
      // Set the following option to the location of a redirect url on the
      // origin server, for cross-domain iframe transport uploads:
      redirect: undefined,
      // The parameter name for the redirect url, sent as part of the form
      // data and set to 'redirect' if this option is empty:
      redirectParamName: undefined,
      // Set the following option to the location of a postMessage window,
      // to enable postMessage transport uploads:
      postMessage: undefined,
      // By default, XHR file uploads are sent as multipart/form-data.
      // The iframe transport is always using multipart/form-data.
      // Set to false to enable non-multipart XHR uploads:
      multipart: true,
      // To upload large files in smaller chunks, set the following option
      // to a preferred maximum chunk size. If set to 0, null or undefined,
      // or the browser does not support the required Blob API, files will
      // be uploaded as a whole.
      maxChunkSize: undefined,
      // When a non-multipart upload or a chunked multipart upload has been
      // aborted, this option can be used to resume the upload by setting
      // it to the size of the already uploaded bytes. This option is most
      // useful when modifying the options object inside of the "add" or
      // "send" callbacks, as the options are cloned for each file upload.
      uploadedBytes: undefined,
      // By default, failed (abort or error) file uploads are removed from the
      // global progress calculation. Set the following option to false to
      // prevent recalculating the global progress data:
      recalculateProgress: true,
      // Interval in milliseconds to calculate and trigger progress events:
      progressInterval: 100,
      // Interval in milliseconds to calculate progress bitrate:
      bitrateInterval: 500,
      // By default, uploads are started automatically when adding files:
      autoUpload: true,
      // By default, duplicate file names are expected to be handled on
      // the server-side. If this is not possible (e.g. when uploading
      // files directly to Amazon S3), the following option can be set to
      // an empty object or an object mapping existing filenames, e.g.:
      // { "image.jpg": true, "image (1).jpg": true }
      // If it is set, all files will be uploaded with unique filenames,
      // adding increasing number suffixes if necessary, e.g.:
      // "image (2).jpg"
      uniqueFilenames: undefined,

      // Error and info messages:
      messages: {
        uploadedBytes: 'Uploaded bytes exceed file size'
      },

      // Translation function, gets the message key to be translated
      // and an object with context specific data as arguments:
      i18n: function (message, context) {
        // eslint-disable-next-line no-param-reassign
        message = this.messages[message] || message.toString();
        if (context) {
          $.each(context, function (key, value) {
            // eslint-disable-next-line no-param-reassign
            message = message.replace('{' + key + '}', value);
          });
        }
        return message;
      },

      // Additional form data to be sent along with the file uploads can be set
      // using this option, which accepts an array of objects with name and
      // value properties, a function returning such an array, a FormData
      // object (for XHR file uploads), or a simple object.
      // The form of the first fileInput is given as parameter to the function:
      formData: function (form) {
        return form.serializeArray();
      },

      // The add callback is invoked as soon as files are added to the fileupload
      // widget (via file input selection, drag & drop, paste or add API call).
      // If the singleFileUploads option is enabled, this callback will be
      // called once for each file in the selection for XHR file uploads, else
      // once for each file selection.
      //
      // The upload starts when the submit method is invoked on the data parameter.
      // The data object contains a files property holding the added files
      // and allows you to override plugin options as well as define ajax settings.
      //
      // Listeners for this callback can also be bound the following way:
      // .on('fileuploadadd', func);
      //
      // data.submit() returns a Promise object and allows to attach additional
      // handlers using jQuery's Deferred callbacks:
      // data.submit().done(func).fail(func).always(func);
      add: function (e, data) {
        if (e.isDefaultPrevented()) {
          return false;
        }
        if (
          data.autoUpload ||
          (data.autoUpload !== false &&
            $(this).fileupload('option', 'autoUpload'))
        ) {
          data.process().done(function () {
            data.submit();
          });
        }
      },

      // Other callbacks:

      // Callback for the submit event of each file upload:
      // submit: function (e, data) {}, // .on('fileuploadsubmit', func);

      // Callback for the start of each file upload request:
      // send: function (e, data) {}, // .on('fileuploadsend', func);

      // Callback for successful uploads:
      // done: function (e, data) {}, // .on('fileuploaddone', func);

      // Callback for failed (abort or error) uploads:
      // fail: function (e, data) {}, // .on('fileuploadfail', func);

      // Callback for completed (success, abort or error) requests:
      // always: function (e, data) {}, // .on('fileuploadalways', func);

      // Callback for upload progress events:
      // progress: function (e, data) {}, // .on('fileuploadprogress', func);

      // Callback for global upload progress events:
      // progressall: function (e, data) {}, // .on('fileuploadprogressall', func);

      // Callback for uploads start, equivalent to the global ajaxStart event:
      // start: function (e) {}, // .on('fileuploadstart', func);

      // Callback for uploads stop, equivalent to the global ajaxStop event:
      // stop: function (e) {}, // .on('fileuploadstop', func);

      // Callback for change events of the fileInput(s):
      // change: function (e, data) {}, // .on('fileuploadchange', func);

      // Callback for paste events to the pasteZone(s):
      // paste: function (e, data) {}, // .on('fileuploadpaste', func);

      // Callback for drop events of the dropZone(s):
      // drop: function (e, data) {}, // .on('fileuploaddrop', func);

      // Callback for dragover events of the dropZone(s):
      // dragover: function (e) {}, // .on('fileuploaddragover', func);

      // Callback before the start of each chunk upload request (before form data initialization):
      // chunkbeforesend: function (e, data) {}, // .on('fileuploadchunkbeforesend', func);

      // Callback for the start of each chunk upload request:
      // chunksend: function (e, data) {}, // .on('fileuploadchunksend', func);

      // Callback for successful chunk uploads:
      // chunkdone: function (e, data) {}, // .on('fileuploadchunkdone', func);

      // Callback for failed (abort or error) chunk uploads:
      // chunkfail: function (e, data) {}, // .on('fileuploadchunkfail', func);

      // Callback for completed (success, abort or error) chunk upload requests:
      // chunkalways: function (e, data) {}, // .on('fileuploadchunkalways', func);

      // The plugin options are used as settings object for the ajax calls.
      // The following are jQuery ajax settings required for the file uploads:
      processData: false,
      contentType: false,
      cache: false,
      timeout: 0
    },

    // A list of options that require reinitializing event listeners and/or
    // special initialization code:
    _specialOptions: [
      'fileInput',
      'dropZone',
      'pasteZone',
      'multipart',
      'forceIframeTransport'
    ],

    _blobSlice:
      $.support.blobSlice &&
      function () {
        var slice = this.slice || this.webkitSlice || this.mozSlice;
        return slice.apply(this, arguments);
      },

    _BitrateTimer: function () {
      this.timestamp = Date.now ? Date.now() : new Date().getTime();
      this.loaded = 0;
      this.bitrate = 0;
      this.getBitrate = function (now, loaded, interval) {
        var timeDiff = now - this.timestamp;
        if (!this.bitrate || !interval || timeDiff > interval) {
          this.bitrate = (loaded - this.loaded) * (1000 / timeDiff) * 8;
          this.loaded = loaded;
          this.timestamp = now;
        }
        return this.bitrate;
      };
    },

    _isXHRUpload: function (options) {
      return (
        !options.forceIframeTransport &&
        ((!options.multipart && $.support.xhrFileUpload) ||
          $.support.xhrFormDataFileUpload)
      );
    },

    _getFormData: function (options) {
      var formData;
      if ($.type(options.formData) === 'function') {
        return options.formData(options.form);
      }
      if ($.isArray(options.formData)) {
        return options.formData;
      }
      if ($.type(options.formData) === 'object') {
        formData = [];
        $.each(options.formData, function (name, value) {
          formData.push({ name: name, value: value });
        });
        return formData;
      }
      return [];
    },

    _getTotal: function (files) {
      var total = 0;
      $.each(files, function (index, file) {
        total += file.size || 1;
      });
      return total;
    },

    _initProgressObject: function (obj) {
      var progress = {
        loaded: 0,
        total: 0,
        bitrate: 0
      };
      if (obj._progress) {
        $.extend(obj._progress, progress);
      } else {
        obj._progress = progress;
      }
    },

    _initResponseObject: function (obj) {
      var prop;
      if (obj._response) {
        for (prop in obj._response) {
          if (Object.prototype.hasOwnProperty.call(obj._response, prop)) {
            delete obj._response[prop];
          }
        }
      } else {
        obj._response = {};
      }
    },

    _onProgress: function (e, data) {
      if (e.lengthComputable) {
        var now = Date.now ? Date.now() : new Date().getTime(),
          loaded;
        if (
          data._time &&
          data.progressInterval &&
          now - data._time < data.progressInterval &&
          e.loaded !== e.total
        ) {
          return;
        }
        data._time = now;
        loaded =
          Math.floor(
            (e.loaded / e.total) * (data.chunkSize || data._progress.total)
          ) + (data.uploadedBytes || 0);
        // Add the difference from the previously loaded state
        // to the global loaded counter:
        this._progress.loaded += loaded - data._progress.loaded;
        this._progress.bitrate = this._bitrateTimer.getBitrate(
          now,
          this._progress.loaded,
          data.bitrateInterval
        );
        data._progress.loaded = data.loaded = loaded;
        data._progress.bitrate = data.bitrate = data._bitrateTimer.getBitrate(
          now,
          loaded,
          data.bitrateInterval
        );
        // Trigger a custom progress event with a total data property set
        // to the file size(s) of the current upload and a loaded data
        // property calculated accordingly:
        this._trigger(
          'progress',
          $.Event('progress', { delegatedEvent: e }),
          data
        );
        // Trigger a global progress event for all current file uploads,
        // including ajax calls queued for sequential file uploads:
        this._trigger(
          'progressall',
          $.Event('progressall', { delegatedEvent: e }),
          this._progress
        );
      }
    },

    _initProgressListener: function (options) {
      var that = this,
        xhr = options.xhr ? options.xhr() : $.ajaxSettings.xhr();
      // Accesss to the native XHR object is required to add event listeners
      // for the upload progress event:
      if (xhr.upload) {
        $(xhr.upload).on('progress', function (e) {
          var oe = e.originalEvent;
          // Make sure the progress event properties get copied over:
          e.lengthComputable = oe.lengthComputable;
          e.loaded = oe.loaded;
          e.total = oe.total;
          that._onProgress(e, options);
        });
        options.xhr = function () {
          return xhr;
        };
      }
    },

    _deinitProgressListener: function (options) {
      var xhr = options.xhr ? options.xhr() : $.ajaxSettings.xhr();
      if (xhr.upload) {
        $(xhr.upload).off('progress');
      }
    },

    _isInstanceOf: function (type, obj) {
      // Cross-frame instanceof check
      return Object.prototype.toString.call(obj) === '[object ' + type + ']';
    },

    _getUniqueFilename: function (name, map) {
      // eslint-disable-next-line no-param-reassign
      name = String(name);
      if (map[name]) {
        // eslint-disable-next-line no-param-reassign
        name = name.replace(/(?: \(([\d]+)\))?(\.[^.]+)?$/, function (
          _,
          p1,
          p2
        ) {
          var index = p1 ? Number(p1) + 1 : 1;
          var ext = p2 || '';
          return ' (' + index + ')' + ext;
        });
        return this._getUniqueFilename(name, map);
      }
      map[name] = true;
      return name;
    },

    _initXHRData: function (options) {
      var that = this,
        formData,
        file = options.files[0],
        // Ignore non-multipart setting if not supported:
        multipart = options.multipart || !$.support.xhrFileUpload,
        paramName =
          $.type(options.paramName) === 'array'
            ? options.paramName[0]
            : options.paramName;
      options.headers = $.extend({}, options.headers);
      if (options.contentRange) {
        options.headers['Content-Range'] = options.contentRange;
      }
      if (!multipart || options.blob || !this._isInstanceOf('File', file)) {
        options.headers['Content-Disposition'] =
          'attachment; filename="' +
          encodeURI(file.uploadName || file.name) +
          '"';
      }
      if (!multipart) {
        options.contentType = file.type || 'application/octet-stream';
        options.data = options.blob || file;
      } else if ($.support.xhrFormDataFileUpload) {
        if (options.postMessage) {
          // window.postMessage does not allow sending FormData
          // objects, so we just add the File/Blob objects to
          // the formData array and let the postMessage window
          // create the FormData object out of this array:
          formData = this._getFormData(options);
          if (options.blob) {
            formData.push({
              name: paramName,
              value: options.blob
            });
          } else {
            $.each(options.files, function (index, file) {
              formData.push({
                name:
                  ($.type(options.paramName) === 'array' &&
                    options.paramName[index]) ||
                  paramName,
                value: file
              });
            });
          }
        } else {
          if (that._isInstanceOf('FormData', options.formData)) {
            formData = options.formData;
          } else {
            formData = new FormData();
            $.each(this._getFormData(options), function (index, field) {
              formData.append(field.name, field.value);
            });
          }
          if (options.blob) {
            formData.append(
              paramName,
              options.blob,
              file.uploadName || file.name
            );
          } else {
            $.each(options.files, function (index, file) {
              // This check allows the tests to run with
              // dummy objects:
              if (
                that._isInstanceOf('File', file) ||
                that._isInstanceOf('Blob', file)
              ) {
                var fileName = file.uploadName || file.name;
                if (options.uniqueFilenames) {
                  fileName = that._getUniqueFilename(
                    fileName,
                    options.uniqueFilenames
                  );
                }
                formData.append(
                  ($.type(options.paramName) === 'array' &&
                    options.paramName[index]) ||
                    paramName,
                  file,
                  fileName
                );
              }
            });
          }
        }
        options.data = formData;
      }
      // Blob reference is not needed anymore, free memory:
      options.blob = null;
    },

    _initIframeSettings: function (options) {
      var targetHost = $('<a></a>').prop('href', options.url).prop('host');
      // Setting the dataType to iframe enables the iframe transport:
      options.dataType = 'iframe ' + (options.dataType || '');
      // The iframe transport accepts a serialized array as form data:
      options.formData = this._getFormData(options);
      // Add redirect url to form data on cross-domain uploads:
      if (options.redirect && targetHost && targetHost !== location.host) {
        options.formData.push({
          name: options.redirectParamName || 'redirect',
          value: options.redirect
        });
      }
    },

    _initDataSettings: function (options) {
      if (this._isXHRUpload(options)) {
        if (!this._chunkedUpload(options, true)) {
          if (!options.data) {
            this._initXHRData(options);
          }
          this._initProgressListener(options);
        }
        if (options.postMessage) {
          // Setting the dataType to postmessage enables the
          // postMessage transport:
          options.dataType = 'postmessage ' + (options.dataType || '');
        }
      } else {
        this._initIframeSettings(options);
      }
    },

    _getParamName: function (options) {
      var fileInput = $(options.fileInput),
        paramName = options.paramName;
      if (!paramName) {
        paramName = [];
        fileInput.each(function () {
          var input = $(this),
            name = input.prop('name') || 'files[]',
            i = (input.prop('files') || [1]).length;
          while (i) {
            paramName.push(name);
            i -= 1;
          }
        });
        if (!paramName.length) {
          paramName = [fileInput.prop('name') || 'files[]'];
        }
      } else if (!$.isArray(paramName)) {
        paramName = [paramName];
      }
      return paramName;
    },

    _initFormSettings: function (options) {
      // Retrieve missing options from the input field and the
      // associated form, if available:
      if (!options.form || !options.form.length) {
        options.form = $(options.fileInput.prop('form'));
        // If the given file input doesn't have an associated form,
        // use the default widget file input's form:
        if (!options.form.length) {
          options.form = $(this.options.fileInput.prop('form'));
        }
      }
      options.paramName = this._getParamName(options);
      if (!options.url) {
        options.url = options.form.prop('action') || location.href;
      }
      // The HTTP request method must be "POST" or "PUT":
      options.type = (
        options.type ||
        ($.type(options.form.prop('method')) === 'string' &&
          options.form.prop('method')) ||
        ''
      ).toUpperCase();
      if (
        options.type !== 'POST' &&
        options.type !== 'PUT' &&
        options.type !== 'PATCH'
      ) {
        options.type = 'POST';
      }
      if (!options.formAcceptCharset) {
        options.formAcceptCharset = options.form.attr('accept-charset');
      }
    },

    _getAJAXSettings: function (data) {
      var options = $.extend({}, this.options, data);
      this._initFormSettings(options);
      this._initDataSettings(options);
      return options;
    },

    // jQuery 1.6 doesn't provide .state(),
    // while jQuery 1.8+ removed .isRejected() and .isResolved():
    _getDeferredState: function (deferred) {
      if (deferred.state) {
        return deferred.state();
      }
      if (deferred.isResolved()) {
        return 'resolved';
      }
      if (deferred.isRejected()) {
        return 'rejected';
      }
      return 'pending';
    },

    // Maps jqXHR callbacks to the equivalent
    // methods of the given Promise object:
    _enhancePromise: function (promise) {
      promise.success = promise.done;
      promise.error = promise.fail;
      promise.complete = promise.always;
      return promise;
    },

    // Creates and returns a Promise object enhanced with
    // the jqXHR methods abort, success, error and complete:
    _getXHRPromise: function (resolveOrReject, context, args) {
      var dfd = $.Deferred(),
        promise = dfd.promise();
      // eslint-disable-next-line no-param-reassign
      context = context || this.options.context || promise;
      if (resolveOrReject === true) {
        dfd.resolveWith(context, args);
      } else if (resolveOrReject === false) {
        dfd.rejectWith(context, args);
      }
      promise.abort = dfd.promise;
      return this._enhancePromise(promise);
    },

    // Adds convenience methods to the data callback argument:
    _addConvenienceMethods: function (e, data) {
      var that = this,
        getPromise = function (args) {
          return $.Deferred().resolveWith(that, args).promise();
        };
      data.process = function (resolveFunc, rejectFunc) {
        if (resolveFunc || rejectFunc) {
          data._processQueue = this._processQueue = (
            this._processQueue || getPromise([this])
          )
            .then(function () {
              if (data.errorThrown) {
                return $.Deferred().rejectWith(that, [data]).promise();
              }
              return getPromise(arguments);
            })
            .then(resolveFunc, rejectFunc);
        }
        return this._processQueue || getPromise([this]);
      };
      data.submit = function () {
        if (this.state() !== 'pending') {
          data.jqXHR = this.jqXHR =
            that._trigger(
              'submit',
              $.Event('submit', { delegatedEvent: e }),
              this
            ) !== false && that._onSend(e, this);
        }
        return this.jqXHR || that._getXHRPromise();
      };
      data.abort = function () {
        if (this.jqXHR) {
          return this.jqXHR.abort();
        }
        this.errorThrown = 'abort';
        that._trigger('fail', null, this);
        return that._getXHRPromise(false);
      };
      data.state = function () {
        if (this.jqXHR) {
          return that._getDeferredState(this.jqXHR);
        }
        if (this._processQueue) {
          return that._getDeferredState(this._processQueue);
        }
      };
      data.processing = function () {
        return (
          !this.jqXHR &&
          this._processQueue &&
          that._getDeferredState(this._processQueue) === 'pending'
        );
      };
      data.progress = function () {
        return this._progress;
      };
      data.response = function () {
        return this._response;
      };
    },

    // Parses the Range header from the server response
    // and returns the uploaded bytes:
    _getUploadedBytes: function (jqXHR) {
      var range = jqXHR.getResponseHeader('Range'),
        parts = range && range.split('-'),
        upperBytesPos = parts && parts.length > 1 && parseInt(parts[1], 10);
      return upperBytesPos && upperBytesPos + 1;
    },

    // Uploads a file in multiple, sequential requests
    // by splitting the file up in multiple blob chunks.
    // If the second parameter is true, only tests if the file
    // should be uploaded in chunks, but does not invoke any
    // upload requests:
    _chunkedUpload: function (options, testOnly) {
      options.uploadedBytes = options.uploadedBytes || 0;
      var that = this,
        file = options.files[0],
        fs = file.size,
        ub = options.uploadedBytes,
        mcs = options.maxChunkSize || fs,
        slice = this._blobSlice,
        dfd = $.Deferred(),
        promise = dfd.promise(),
        jqXHR,
        upload;
      if (
        !(
          this._isXHRUpload(options) &&
          slice &&
          (ub || ($.type(mcs) === 'function' ? mcs(options) : mcs) < fs)
        ) ||
        options.data
      ) {
        return false;
      }
      if (testOnly) {
        return true;
      }
      if (ub >= fs) {
        file.error = options.i18n('uploadedBytes');
        return this._getXHRPromise(false, options.context, [
          null,
          'error',
          file.error
        ]);
      }
      // The chunk upload method:
      upload = function () {
        // Clone the options object for each chunk upload:
        var o = $.extend({}, options),
          currentLoaded = o._progress.loaded;
        o.blob = slice.call(
          file,
          ub,
          ub + ($.type(mcs) === 'function' ? mcs(o) : mcs),
          file.type
        );
        // Store the current chunk size, as the blob itself
        // will be dereferenced after data processing:
        o.chunkSize = o.blob.size;
        // Expose the chunk bytes position range:
        o.contentRange =
          'bytes ' + ub + '-' + (ub + o.chunkSize - 1) + '/' + fs;
        // Trigger chunkbeforesend to allow form data to be updated for this chunk
        that._trigger('chunkbeforesend', null, o);
        // Process the upload data (the blob and potential form data):
        that._initXHRData(o);
        // Add progress listeners for this chunk upload:
        that._initProgressListener(o);
        jqXHR = (
          (that._trigger('chunksend', null, o) !== false && $.ajax(o)) ||
          that._getXHRPromise(false, o.context)
        )
          .done(function (result, textStatus, jqXHR) {
            ub = that._getUploadedBytes(jqXHR) || ub + o.chunkSize;
            // Create a progress event if no final progress event
            // with loaded equaling total has been triggered
            // for this chunk:
            if (currentLoaded + o.chunkSize - o._progress.loaded) {
              that._onProgress(
                $.Event('progress', {
                  lengthComputable: true,
                  loaded: ub - o.uploadedBytes,
                  total: ub - o.uploadedBytes
                }),
                o
              );
            }
            options.uploadedBytes = o.uploadedBytes = ub;
            o.result = result;
            o.textStatus = textStatus;
            o.jqXHR = jqXHR;
            that._trigger('chunkdone', null, o);
            that._trigger('chunkalways', null, o);
            if (ub < fs) {
              // File upload not yet complete,
              // continue with the next chunk:
              upload();
            } else {
              dfd.resolveWith(o.context, [result, textStatus, jqXHR]);
            }
          })
          .fail(function (jqXHR, textStatus, errorThrown) {
            o.jqXHR = jqXHR;
            o.textStatus = textStatus;
            o.errorThrown = errorThrown;
            that._trigger('chunkfail', null, o);
            that._trigger('chunkalways', null, o);
            dfd.rejectWith(o.context, [jqXHR, textStatus, errorThrown]);
          })
          .always(function () {
            that._deinitProgressListener(o);
          });
      };
      this._enhancePromise(promise);
      promise.abort = function () {
        return jqXHR.abort();
      };
      upload();
      return promise;
    },

    _beforeSend: function (e, data) {
      if (this._active === 0) {
        // the start callback is triggered when an upload starts
        // and no other uploads are currently running,
        // equivalent to the global ajaxStart event:
        this._trigger('start');
        // Set timer for global bitrate progress calculation:
        this._bitrateTimer = new this._BitrateTimer();
        // Reset the global progress values:
        this._progress.loaded = this._progress.total = 0;
        this._progress.bitrate = 0;
      }
      // Make sure the container objects for the .response() and
      // .progress() methods on the data object are available
      // and reset to their initial state:
      this._initResponseObject(data);
      this._initProgressObject(data);
      data._progress.loaded = data.loaded = data.uploadedBytes || 0;
      data._progress.total = data.total = this._getTotal(data.files) || 1;
      data._progress.bitrate = data.bitrate = 0;
      this._active += 1;
      // Initialize the global progress values:
      this._progress.loaded += data.loaded;
      this._progress.total += data.total;
    },

    _onDone: function (result, textStatus, jqXHR, options) {
      var total = options._progress.total,
        response = options._response;
      if (options._progress.loaded < total) {
        // Create a progress event if no final progress event
        // with loaded equaling total has been triggered:
        this._onProgress(
          $.Event('progress', {
            lengthComputable: true,
            loaded: total,
            total: total
          }),
          options
        );
      }
      response.result = options.result = result;
      response.textStatus = options.textStatus = textStatus;
      response.jqXHR = options.jqXHR = jqXHR;
      this._trigger('done', null, options);
    },

    _onFail: function (jqXHR, textStatus, errorThrown, options) {
      var response = options._response;
      if (options.recalculateProgress) {
        // Remove the failed (error or abort) file upload from
        // the global progress calculation:
        this._progress.loaded -= options._progress.loaded;
        this._progress.total -= options._progress.total;
      }
      response.jqXHR = options.jqXHR = jqXHR;
      response.textStatus = options.textStatus = textStatus;
      response.errorThrown = options.errorThrown = errorThrown;
      this._trigger('fail', null, options);
    },

    _onAlways: function (jqXHRorResult, textStatus, jqXHRorError, options) {
      // jqXHRorResult, textStatus and jqXHRorError are added to the
      // options object via done and fail callbacks
      this._trigger('always', null, options);
    },

    _onSend: function (e, data) {
      if (!data.submit) {
        this._addConvenienceMethods(e, data);
      }
      var that = this,
        jqXHR,
        aborted,
        slot,
        pipe,
        options = that._getAJAXSettings(data),
        send = function () {
          that._sending += 1;
          // Set timer for bitrate progress calculation:
          options._bitrateTimer = new that._BitrateTimer();
          jqXHR =
            jqXHR ||
            (
              ((aborted ||
                that._trigger(
                  'send',
                  $.Event('send', { delegatedEvent: e }),
                  options
                ) === false) &&
                that._getXHRPromise(false, options.context, aborted)) ||
              that._chunkedUpload(options) ||
              $.ajax(options)
            )
              .done(function (result, textStatus, jqXHR) {
                that._onDone(result, textStatus, jqXHR, options);
              })
              .fail(function (jqXHR, textStatus, errorThrown) {
                that._onFail(jqXHR, textStatus, errorThrown, options);
              })
              .always(function (jqXHRorResult, textStatus, jqXHRorError) {
                that._deinitProgressListener(options);
                that._onAlways(
                  jqXHRorResult,
                  textStatus,
                  jqXHRorError,
                  options
                );
                that._sending -= 1;
                that._active -= 1;
                if (
                  options.limitConcurrentUploads &&
                  options.limitConcurrentUploads > that._sending
                ) {
                  // Start the next queued upload,
                  // that has not been aborted:
                  var nextSlot = that._slots.shift();
                  while (nextSlot) {
                    if (that._getDeferredState(nextSlot) === 'pending') {
                      nextSlot.resolve();
                      break;
                    }
                    nextSlot = that._slots.shift();
                  }
                }
                if (that._active === 0) {
                  // The stop callback is triggered when all uploads have
                  // been completed, equivalent to the global ajaxStop event:
                  that._trigger('stop');
                }
              });
          return jqXHR;
        };
      this._beforeSend(e, options);
      if (
        this.options.sequentialUploads ||
        (this.options.limitConcurrentUploads &&
          this.options.limitConcurrentUploads <= this._sending)
      ) {
        if (this.options.limitConcurrentUploads > 1) {
          slot = $.Deferred();
          this._slots.push(slot);
          pipe = slot.then(send);
        } else {
          this._sequence = this._sequence.then(send, send);
          pipe = this._sequence;
        }
        // Return the piped Promise object, enhanced with an abort method,
        // which is delegated to the jqXHR object of the current upload,
        // and jqXHR callbacks mapped to the equivalent Promise methods:
        pipe.abort = function () {
          aborted = [undefined, 'abort', 'abort'];
          if (!jqXHR) {
            if (slot) {
              slot.rejectWith(options.context, aborted);
            }
            return send();
          }
          return jqXHR.abort();
        };
        return this._enhancePromise(pipe);
      }
      return send();
    },

    _onAdd: function (e, data) {
      var that = this,
        result = true,
        options = $.extend({}, this.options, data),
        files = data.files,
        filesLength = files.length,
        limit = options.limitMultiFileUploads,
        limitSize = options.limitMultiFileUploadSize,
        overhead = options.limitMultiFileUploadSizeOverhead,
        batchSize = 0,
        paramName = this._getParamName(options),
        paramNameSet,
        paramNameSlice,
        fileSet,
        i,
        j = 0;
      if (!filesLength) {
        return false;
      }
      if (limitSize && files[0].size === undefined) {
        limitSize = undefined;
      }
      if (
        !(options.singleFileUploads || limit || limitSize) ||
        !this._isXHRUpload(options)
      ) {
        fileSet = [files];
        paramNameSet = [paramName];
      } else if (!(options.singleFileUploads || limitSize) && limit) {
        fileSet = [];
        paramNameSet = [];
        for (i = 0; i < filesLength; i += limit) {
          fileSet.push(files.slice(i, i + limit));
          paramNameSlice = paramName.slice(i, i + limit);
          if (!paramNameSlice.length) {
            paramNameSlice = paramName;
          }
          paramNameSet.push(paramNameSlice);
        }
      } else if (!options.singleFileUploads && limitSize) {
        fileSet = [];
        paramNameSet = [];
        for (i = 0; i < filesLength; i = i + 1) {
          batchSize += files[i].size + overhead;
          if (
            i + 1 === filesLength ||
            batchSize + files[i + 1].size + overhead > limitSize ||
            (limit && i + 1 - j >= limit)
          ) {
            fileSet.push(files.slice(j, i + 1));
            paramNameSlice = paramName.slice(j, i + 1);
            if (!paramNameSlice.length) {
              paramNameSlice = paramName;
            }
            paramNameSet.push(paramNameSlice);
            j = i + 1;
            batchSize = 0;
          }
        }
      } else {
        paramNameSet = paramName;
      }
      data.originalFiles = files;
      $.each(fileSet || files, function (index, element) {
        var newData = $.extend({}, data);
        newData.files = fileSet ? element : [element];
        newData.paramName = paramNameSet[index];
        that._initResponseObject(newData);
        that._initProgressObject(newData);
        that._addConvenienceMethods(e, newData);
        result = that._trigger(
          'add',
          $.Event('add', { delegatedEvent: e }),
          newData
        );
        return result;
      });
      return result;
    },

    _replaceFileInput: function (data) {
      var input = data.fileInput,
        inputClone = input.clone(true),
        restoreFocus = input.is(document.activeElement);
      // Add a reference for the new cloned file input to the data argument:
      data.fileInputClone = inputClone;
      $('<form></form>').append(inputClone)[0].reset();
      // Detaching allows to insert the fileInput on another form
      // without loosing the file input value:
      input.after(inputClone).detach();
      // If the fileInput had focus before it was detached,
      // restore focus to the inputClone.
      if (restoreFocus) {
        inputClone.focus();
      }
      // Avoid memory leaks with the detached file input:
      $.cleanData(input.off('remove'));
      // Replace the original file input element in the fileInput
      // elements set with the clone, which has been copied including
      // event handlers:
      this.options.fileInput = this.options.fileInput.map(function (i, el) {
        if (el === input[0]) {
          return inputClone[0];
        }
        return el;
      });
      // If the widget has been initialized on the file input itself,
      // override this.element with the file input clone:
      if (input[0] === this.element[0]) {
        this.element = inputClone;
      }
    },

    _handleFileTreeEntry: function (entry, path) {
      var that = this,
        dfd = $.Deferred(),
        entries = [],
        dirReader,
        errorHandler = function (e) {
          if (e && !e.entry) {
            e.entry = entry;
          }
          // Since $.when returns immediately if one
          // Deferred is rejected, we use resolve instead.
          // This allows valid files and invalid items
          // to be returned together in one set:
          dfd.resolve([e]);
        },
        successHandler = function (entries) {
          that
            ._handleFileTreeEntries(entries, path + entry.name + '/')
            .done(function (files) {
              dfd.resolve(files);
            })
            .fail(errorHandler);
        },
        readEntries = function () {
          dirReader.readEntries(function (results) {
            if (!results.length) {
              successHandler(entries);
            } else {
              entries = entries.concat(results);
              readEntries();
            }
          }, errorHandler);
        };
      // eslint-disable-next-line no-param-reassign
      path = path || '';
      if (entry.isFile) {
        if (entry._file) {
          // Workaround for Chrome bug #149735
          entry._file.relativePath = path;
          dfd.resolve(entry._file);
        } else {
          entry.file(function (file) {
            file.relativePath = path;
            dfd.resolve(file);
          }, errorHandler);
        }
      } else if (entry.isDirectory) {
        dirReader = entry.createReader();
        readEntries();
      } else {
        // Return an empty list for file system items
        // other than files or directories:
        dfd.resolve([]);
      }
      return dfd.promise();
    },

    _handleFileTreeEntries: function (entries, path) {
      var that = this;
      return $.when
        .apply(
          $,
          $.map(entries, function (entry) {
            return that._handleFileTreeEntry(entry, path);
          })
        )
        .then(function () {
          return Array.prototype.concat.apply([], arguments);
        });
    },

    _getDroppedFiles: function (dataTransfer) {
      // eslint-disable-next-line no-param-reassign
      dataTransfer = dataTransfer || {};
      var items = dataTransfer.items;
      if (
        items &&
        items.length &&
        (items[0].webkitGetAsEntry || items[0].getAsEntry)
      ) {
        return this._handleFileTreeEntries(
          $.map(items, function (item) {
            var entry;
            if (item.webkitGetAsEntry) {
              entry = item.webkitGetAsEntry();
              if (entry) {
                // Workaround for Chrome bug #149735:
                entry._file = item.getAsFile();
              }
              return entry;
            }
            return item.getAsEntry();
          })
        );
      }
      return $.Deferred().resolve($.makeArray(dataTransfer.files)).promise();
    },

    _getSingleFileInputFiles: function (fileInput) {
      // eslint-disable-next-line no-param-reassign
      fileInput = $(fileInput);
      var entries =
          fileInput.prop('webkitEntries') || fileInput.prop('entries'),
        files,
        value;
      if (entries && entries.length) {
        return this._handleFileTreeEntries(entries);
      }
      files = $.makeArray(fileInput.prop('files'));
      if (!files.length) {
        value = fileInput.prop('value');
        if (!value) {
          return $.Deferred().resolve([]).promise();
        }
        // If the files property is not available, the browser does not
        // support the File API and we add a pseudo File object with
        // the input value as name with path information removed:
        files = [{ name: value.replace(/^.*\\/, '') }];
      } else if (files[0].name === undefined && files[0].fileName) {
        // File normalization for Safari 4 and Firefox 3:
        $.each(files, function (index, file) {
          file.name = file.fileName;
          file.size = file.fileSize;
        });
      }
      return $.Deferred().resolve(files).promise();
    },

    _getFileInputFiles: function (fileInput) {
      if (!(fileInput instanceof $) || fileInput.length === 1) {
        return this._getSingleFileInputFiles(fileInput);
      }
      return $.when
        .apply($, $.map(fileInput, this._getSingleFileInputFiles))
        .then(function () {
          return Array.prototype.concat.apply([], arguments);
        });
    },

    _onChange: function (e) {
      var that = this,
        data = {
          fileInput: $(e.target),
          form: $(e.target.form)
        };
      this._getFileInputFiles(data.fileInput).always(function (files) {
        data.files = files;
        if (that.options.replaceFileInput) {
          that._replaceFileInput(data);
        }
        if (
          that._trigger(
            'change',
            $.Event('change', { delegatedEvent: e }),
            data
          ) !== false
        ) {
          that._onAdd(e, data);
        }
      });
    },

    _onPaste: function (e) {
      var items =
          e.originalEvent &&
          e.originalEvent.clipboardData &&
          e.originalEvent.clipboardData.items,
        data = { files: [] };
      if (items && items.length) {
        $.each(items, function (index, item) {
          var file = item.getAsFile && item.getAsFile();
          if (file) {
            data.files.push(file);
          }
        });
        if (
          this._trigger(
            'paste',
            $.Event('paste', { delegatedEvent: e }),
            data
          ) !== false
        ) {
          this._onAdd(e, data);
        }
      }
    },

    _onDrop: function (e) {
      e.dataTransfer = e.originalEvent && e.originalEvent.dataTransfer;
      var that = this,
        dataTransfer = e.dataTransfer,
        data = {};
      if (dataTransfer && dataTransfer.files && dataTransfer.files.length) {
        e.preventDefault();
        this._getDroppedFiles(dataTransfer).always(function (files) {
          data.files = files;
          if (
            that._trigger(
              'drop',
              $.Event('drop', { delegatedEvent: e }),
              data
            ) !== false
          ) {
            that._onAdd(e, data);
          }
        });
      }
    },

    _onDragOver: getDragHandler('dragover'),

    _onDragEnter: getDragHandler('dragenter'),

    _onDragLeave: getDragHandler('dragleave'),

    _initEventHandlers: function () {
      if (this._isXHRUpload(this.options)) {
        this._on(this.options.dropZone, {
          dragover: this._onDragOver,
          drop: this._onDrop,
          // event.preventDefault() on dragenter is required for IE10+:
          dragenter: this._onDragEnter,
          // dragleave is not required, but added for completeness:
          dragleave: this._onDragLeave
        });
        this._on(this.options.pasteZone, {
          paste: this._onPaste
        });
      }
      if ($.support.fileInput) {
        this._on(this.options.fileInput, {
          change: this._onChange
        });
      }
    },

    _destroyEventHandlers: function () {
      this._off(this.options.dropZone, 'dragenter dragleave dragover drop');
      this._off(this.options.pasteZone, 'paste');
      this._off(this.options.fileInput, 'change');
    },

    _destroy: function () {
      this._destroyEventHandlers();
    },

    _setOption: function (key, value) {
      var reinit = $.inArray(key, this._specialOptions) !== -1;
      if (reinit) {
        this._destroyEventHandlers();
      }
      this._super(key, value);
      if (reinit) {
        this._initSpecialOptions();
        this._initEventHandlers();
      }
    },

    _initSpecialOptions: function () {
      var options = this.options;
      if (options.fileInput === undefined) {
        options.fileInput = this.element.is('input[type="file"]')
          ? this.element
          : this.element.find('input[type="file"]');
      } else if (!(options.fileInput instanceof $)) {
        options.fileInput = $(options.fileInput);
      }
      if (!(options.dropZone instanceof $)) {
        options.dropZone = $(options.dropZone);
      }
      if (!(options.pasteZone instanceof $)) {
        options.pasteZone = $(options.pasteZone);
      }
    },

    _getRegExp: function (str) {
      var parts = str.split('/'),
        modifiers = parts.pop();
      parts.shift();
      return new RegExp(parts.join('/'), modifiers);
    },

    _isRegExpOption: function (key, value) {
      return (
        key !== 'url' &&
        $.type(value) === 'string' &&
        /^\/.*\/[igm]{0,3}$/.test(value)
      );
    },

    _initDataAttributes: function () {
      var that = this,
        options = this.options,
        data = this.element.data();
      // Initialize options set via HTML5 data-attributes:
      $.each(this.element[0].attributes, function (index, attr) {
        var key = attr.name.toLowerCase(),
          value;
        if (/^data-/.test(key)) {
          // Convert hyphen-ated key to camelCase:
          key = key.slice(5).replace(/-[a-z]/g, function (str) {
            return str.charAt(1).toUpperCase();
          });
          value = data[key];
          if (that._isRegExpOption(key, value)) {
            value = that._getRegExp(value);
          }
          options[key] = value;
        }
      });
    },

    _create: function () {
      this._initDataAttributes();
      this._initSpecialOptions();
      this._slots = [];
      this._sequence = this._getXHRPromise(true);
      this._sending = this._active = 0;
      this._initProgressObject(this);
      this._initEventHandlers();
    },

    // This method is exposed to the widget API and allows to query
    // the number of active uploads:
    active: function () {
      return this._active;
    },

    // This method is exposed to the widget API and allows to query
    // the widget upload progress.
    // It returns an object with loaded, total and bitrate properties
    // for the running uploads:
    progress: function () {
      return this._progress;
    },

    // This method is exposed to the widget API and allows adding files
    // using the fileupload API. The data parameter accepts an object which
    // must have a files property and can contain additional options:
    // .fileupload('add', {files: filesList});
    add: function (data) {
      var that = this;
      if (!data || this.options.disabled) {
        return;
      }
      if (data.fileInput && !data.files) {
        this._getFileInputFiles(data.fileInput).always(function (files) {
          data.files = files;
          that._onAdd(null, data);
        });
      } else {
        data.files = $.makeArray(data.files);
        this._onAdd(null, data);
      }
    },

    // This method is exposed to the widget API and allows sending files
    // using the fileupload API. The data parameter accepts an object which
    // must have a files or fileInput property and can contain additional options:
    // .fileupload('send', {files: filesList});
    // The method returns a Promise object for the file upload call.
    send: function (data) {
      if (data && !this.options.disabled) {
        if (data.fileInput && !data.files) {
          var that = this,
            dfd = $.Deferred(),
            promise = dfd.promise(),
            jqXHR,
            aborted;
          promise.abort = function () {
            aborted = true;
            if (jqXHR) {
              return jqXHR.abort();
            }
            dfd.reject(null, 'abort', 'abort');
            return promise;
          };
          this._getFileInputFiles(data.fileInput).always(function (files) {
            if (aborted) {
              return;
            }
            if (!files.length) {
              dfd.reject();
              return;
            }
            data.files = files;
            jqXHR = that._onSend(null, data);
            jqXHR.then(
              function (result, textStatus, jqXHR) {
                dfd.resolve(result, textStatus, jqXHR);
              },
              function (jqXHR, textStatus, errorThrown) {
                dfd.reject(jqXHR, textStatus, errorThrown);
              }
            );
          });
          return this._enhancePromise(promise);
        }
        data.files = $.makeArray(data.files);
        if (data.files.length) {
          return this._onSend(null, data);
        }
      }
      return this._getXHRPromise(false, data && data.context);
    }
  });
});
;
/*!
 * colpick Color Picker
 * https://github.com/mrgrain/colpick
 *
 * Copyright 2013, 2015 Moritz Kornher, Jose Vargas, Stefan Petre
 * Released under the MIT license and GPLv2 license
 * https://github.com/mrgrain/colpick/blob/master/LICENSE
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    var colpick = function () {
        var
            tpl = '<div class="colpick"><div class="colpick_color"><div class="colpick_color_overlay1"><div class="colpick_color_overlay2"><div class="colpick_selector_outer"><div class="colpick_selector_inner"></div></div></div></div></div><div class="colpick_hue"><div class="colpick_hue_arrs"><div class="colpick_hue_larr"></div><div class="colpick_hue_rarr"></div></div></div><div class="colpick_new_color"></div><div class="colpick_current_color"></div><div class="colpick_hex_field"><div class="colpick_field_letter">#</div><input type="text" maxlength="6" size="6" /></div><div class="colpick_rgb_r colpick_field"><div class="colpick_field_letter">R</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_rgb_g colpick_field"><div class="colpick_field_letter">G</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_rgb_b colpick_field"><div class="colpick_field_letter">B</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_hsb_h colpick_field"><div class="colpick_field_letter">H</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_hsb_s colpick_field"><div class="colpick_field_letter">S</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_hsb_b colpick_field"><div class="colpick_field_letter">B</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_submit"></div></div>',
            defaults = {
                showEvent: 'click',
                onShow: function () {
                },
                onBeforeShow: function () {
                },
                onHide: function () {
                },
                onChange: function () {
                },
                onSubmit: function () {
                },
                colorScheme: 'light',
                color: 'auto',
                livePreview: true,
                flat: false,
                layout: 'full',
                submit: 1,
                submitText: 'OK',
                height: 156,
                polyfill: false,
                styles: false
            },
        //Fill the inputs of the plugin
            fillRGBFields = function (hsb, cal) {
                var rgb = hsbToRgb(hsb);
                $(cal).data('colpick').fields
                    .eq(1).val(rgb.r).end()
                    .eq(2).val(rgb.g).end()
                    .eq(3).val(rgb.b).end();
            },
            fillHSBFields = function (hsb, cal) {
                $(cal).data('colpick').fields
                    .eq(4).val(Math.round(hsb.h)).end()
                    .eq(5).val(Math.round(hsb.s)).end()
                    .eq(6).val(Math.round(hsb.b)).end();
            },
            fillHexFields = function (hsb, cal) {
                $(cal).data('colpick').fields.eq(0).val(hsbToHex(hsb));
            },
        //Set the round selector position
            setSelector = function (hsb, cal) {
                $(cal).data('colpick').selector.css('backgroundColor', '#' + hsbToHex({h: hsb.h, s: 100, b: 100}));
                $(cal).data('colpick').selectorIndic.css({
                    left: parseInt($(cal).data('colpick').height * hsb.s / 100, 10),
                    top: parseInt($(cal).data('colpick').height * (100 - hsb.b) / 100, 10)
                });
            },
        //Set the hue selector position
            setHue = function (hsb, cal) {
                $(cal).data('colpick').hue.css('top', parseInt($(cal).data('colpick').height - $(cal).data('colpick').height * hsb.h / 360, 10));
            },
        //Set current and new colors
            setCurrentColor = function (hsb, cal) {
                $(cal).data('colpick').currentColor.css('backgroundColor', '#' + hsbToHex(hsb));
            },
            setNewColor = function (hsb, cal) {
                $(cal).data('colpick').newColor.css('backgroundColor', '#' + hsbToHex(hsb));
            },
        //Called when the new color is changed
            change = function () {
                var cal = $(this).parent().parent(), col;
                if (this.parentNode.className.indexOf('_hex') > 0) {
                    cal.data('colpick').color = col = hexToHsb(fixHex(this.value));
                    fillRGBFields(col, cal.get(0));
                    fillHSBFields(col, cal.get(0));
                } else if (this.parentNode.className.indexOf('_hsb') > 0) {
                    cal.data('colpick').color = col = fixHSB({
                        h: parseInt(cal.data('colpick').fields.eq(4).val(), 10),
                        s: parseInt(cal.data('colpick').fields.eq(5).val(), 10),
                        b: parseInt(cal.data('colpick').fields.eq(6).val(), 10)
                    });
                    fillRGBFields(col, cal.get(0));
                    fillHexFields(col, cal.get(0));
                } else {
                    cal.data('colpick').color = col = rgbToHsb(fixRGB({
                        r: parseInt(cal.data('colpick').fields.eq(1).val(), 10),
                        g: parseInt(cal.data('colpick').fields.eq(2).val(), 10),
                        b: parseInt(cal.data('colpick').fields.eq(3).val(), 10)
                    }));
                    fillHexFields(col, cal.get(0));
                    fillHSBFields(col, cal.get(0));
                }
                setSelector(col, cal.get(0));
                setHue(col, cal.get(0));
                setNewColor(col, cal.get(0));
                cal.data('colpick').onChange.apply(cal.parent(), [col, hsbToHex(col), hsbToRgb(col), cal.data('colpick').el, 0]);
            },
        //Change style on blur and on focus of inputs
            blur = function () {
                $(this).parent().removeClass('colpick_focus');
            },
            focus = function () {
                $(this).parent().parent().data('colpick').fields.parent().removeClass('colpick_focus');
                $(this).parent().addClass('colpick_focus');
            },
        //Increment/decrement arrows functions
            downIncrement = function (ev) {
                ev.preventDefault ? ev.preventDefault() : ev.returnValue = false;
                var field = $(this).parent().find('input').focus();
                var current = {
                    el: $(this).parent().addClass('colpick_slider'),
                    max: this.parentNode.className.indexOf('_hsb_h') > 0 ? 360 : (this.parentNode.className.indexOf('_hsb') > 0 ? 100 : 255),
                    y: ev.pageY,
                    field: field,
                    val: parseInt(field.val(), 10),
                    preview: $(this).parent().parent().data('colpick').livePreview
                };
                $(document).mouseup(current, upIncrement);
                $(document).mousemove(current, moveIncrement);
            },
            moveIncrement = function (ev) {
                ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val - ev.pageY + ev.data.y, 10))));
                if (ev.data.preview) {
                    change.apply(ev.data.field.get(0), [true]);
                }
                return false;
            },
            upIncrement = function (ev) {
                change.apply(ev.data.field.get(0), [true]);
                ev.data.el.removeClass('colpick_slider').find('input').focus();
                $(document).off('mouseup', upIncrement);
                $(document).off('mousemove', moveIncrement);
                return false;
            },
        //Hue slider functions
            downHue = function (ev) {
                ev.preventDefault ? ev.preventDefault() : ev.returnValue = false;
                var current = {
                    cal: $(this).parent(),
                    y: $(this).offset().top
                };
                $(document).on('mouseup touchend', current, upHue);
                $(document).on('mousemove touchmove', current, moveHue);

                var pageY = ((ev.type == 'touchstart') ? ev.originalEvent.changedTouches[0].pageY : ev.pageY );
                change.apply(
                    current.cal.data('colpick')
                        .fields.eq(4).val(parseInt(360 * (current.cal.data('colpick').height - (pageY - current.y)) / current.cal.data('colpick').height, 10))
                        .get(0),
                    [current.cal.data('colpick').livePreview]
                );
                return false;
            },
            moveHue = function (ev) {
                var pageY = ((ev.type == 'touchmove') ? ev.originalEvent.changedTouches[0].pageY : ev.pageY );
                change.apply(
                    ev.data.cal.data('colpick')
                        .fields.eq(4).val(parseInt(360 * (ev.data.cal.data('colpick').height - Math.max(0, Math.min(ev.data.cal.data('colpick').height, (pageY - ev.data.y)))) / ev.data.cal.data('colpick').height, 10))
                        .get(0),
                    [ev.data.preview]
                );
                return false;
            },
            upHue = function (ev) {
                fillRGBFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                fillHexFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                $(document).off('mouseup touchend', upHue);
                $(document).off('mousemove touchmove', moveHue);
                return false;
            },
        //Color selector functions
            downSelector = function (ev) {
                ev.preventDefault ? ev.preventDefault() : ev.returnValue = false;
                var current = {
                    cal: $(this).parent(),
                    pos: $(this).offset()
                };
                current.preview = current.cal.data('colpick').livePreview;

                $(document).on('mouseup touchend', current, upSelector);
                $(document).on('mousemove touchmove', current, moveSelector);

                var pageX, pageY;
                if (ev.type == 'touchstart') {
                    pageX = ev.originalEvent.changedTouches[0].pageX;
                    pageY = ev.originalEvent.changedTouches[0].pageY;
                } else {
                    pageX = ev.pageX;
                    pageY = ev.pageY;
                }

                change.apply(
                    current.cal.data('colpick').fields
                        .eq(6).val(parseInt(100 * (current.cal.data('colpick').height - (pageY - current.pos.top)) / current.cal.data('colpick').height, 10)).end()
                        .eq(5).val(parseInt(100 * (pageX - current.pos.left) / current.cal.data('colpick').height, 10))
                        .get(0),
                    [current.preview]
                );
                return false;
            },
            moveSelector = function (ev) {
                var pageX, pageY;
                if (ev.type == 'touchmove') {
                    pageX = ev.originalEvent.changedTouches[0].pageX;
                    pageY = ev.originalEvent.changedTouches[0].pageY;
                } else {
                    pageX = ev.pageX;
                    pageY = ev.pageY;
                }

                change.apply(
                    ev.data.cal.data('colpick').fields
                        .eq(6).val(parseInt(100 * (ev.data.cal.data('colpick').height - Math.max(0, Math.min(ev.data.cal.data('colpick').height, (pageY - ev.data.pos.top)))) / ev.data.cal.data('colpick').height, 10)).end()
                        .eq(5).val(parseInt(100 * (Math.max(0, Math.min(ev.data.cal.data('colpick').height, (pageX - ev.data.pos.left)))) / ev.data.cal.data('colpick').height, 10))
                        .get(0),
                    [ev.data.preview]
                );
                return false;
            },
            upSelector = function (ev) {
                fillRGBFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                fillHexFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                $(document).off('mouseup touchend', upSelector);
                $(document).off('mousemove touchmove', moveSelector);
                return false;
            },
        //Submit button
            clickSubmit = function () {
                var cal = $(this).parent();
                var col = cal.data('colpick').color;
                cal.data('colpick').origColor = col;
                setCurrentColor(col, cal.get(0));
                cal.data('colpick').onSubmit(col, hsbToHex(col), hsbToRgb(col), cal.data('colpick').el);
            },
        //Show/hide the color picker
            show = function (ev) {
                if (ev) {
                    // Prevent the trigger of any direct parent
                    ev.stopPropagation();
                }
                var cal = $('#' + $(this).data('colpickId'));
                if (ev && !cal.data('colpick').polyfill) {
                    ev.preventDefault();
                }
                cal.data('colpick').onBeforeShow.apply(this, [cal.get(0)]);
                var pos = $(this).offset();
                var top = pos.top + this.offsetHeight;
                var left = pos.left;
                var viewPort = getViewport();
                var calW = cal.width();
                if (left + calW > viewPort.l + viewPort.w) {
                    left -= calW;
                }
                cal.css({left: left + 'px', top: top + 'px'});
                if (cal.data('colpick').onShow.apply(this, [cal.get(0)]) != false) {
                    cal.show();
                }
                //Hide when user clicks outside
                $('html').mousedown({cal: cal}, hide);
                cal.mousedown(function (ev) {
                    ev.stopPropagation();
                })
            },
            hide = function (ev) {
                var cal = $('#' + $(this).data('colpickId'));
                if (ev) {
                    cal = ev.data.cal;
                }
                if (cal.data('colpick').onHide.apply(this, [cal.get(0)]) != false) {
                    cal.hide();
                }
                $('html').off('mousedown', hide);
            },
            getViewport = function () {
                var m = document.compatMode == 'CSS1Compat';
                return {
                    l: window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
                    w: window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth)
                };
            },
        //Fix the values if the user enters a negative or high value
            fixHSB = function (hsb) {
                return {
                    h: Math.min(360, Math.max(0, hsb.h)),
                    s: Math.min(100, Math.max(0, hsb.s)),
                    b: Math.min(100, Math.max(0, hsb.b))
                };
            },
            fixRGB = function (rgb) {
                return {
                    r: Math.min(255, Math.max(0, rgb.r)),
                    g: Math.min(255, Math.max(0, rgb.g)),
                    b: Math.min(255, Math.max(0, rgb.b))
                };
            },
            fixHex = function (hex) {
                var len = 6 - hex.length;
                if (len == 3) {
                    var e = [];
                    for (var j = 0; j < len; j++) {
                        e.push(hex[j]);
                        e.push(hex[j]);
                    }
                    hex = e.join('');
                } else {
                    if (len > 0) {
                        var o = [];
                        for (var i = 0; i < len; i++) {
                            o.push('0');
                        }
                        o.push(hex);
                        hex = o.join('');
                    }
                }
                return hex;
            },
            restoreOriginal = function () {
                var cal = $(this).parent();
                var col = cal.data('colpick').origColor;
                cal.data('colpick').color = col;
                fillRGBFields(col, cal.get(0));
                fillHexFields(col, cal.get(0));
                fillHSBFields(col, cal.get(0));
                setSelector(col, cal.get(0));
                setHue(col, cal.get(0));
                setNewColor(col, cal.get(0));
            };
        return {
            init: function (opt) {
                opt = $.extend({}, defaults, opt || {});
                //Set color
                if (opt.color === 'auto') {
                } else if (typeof opt.color == 'string') {
                    opt.color = hexToHsb(opt.color);
                } else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
                    opt.color = rgbToHsb(opt.color);
                } else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
                    opt.color = fixHSB(opt.color);
                } else {
                    return this;
                }

                //For each selected DOM element
                return this.each(function () {
                    //If the element does not have an ID
                    if (!$(this).data('colpickId')) {
                        var options = $.extend({}, opt);
                        //Color
                        if (opt.color === 'auto') {
                            options.color = $(this).val() ? hexToHsb($(this).val()) : {h: 0, s: 0, b: 0};
                        }
                        options.origColor = options.color;

                        //Polyfill
                        if (typeof opt.polyfill == 'function') {
                            options.polyfill = opt.polyfill(this);
                        }
                        if (options.polyfill && $(this).is('input') && this.type === "color") {
                            return;
                        }

                        //Generate and assign a random ID
                        var id = 'collorpicker_' + parseInt(Math.random() * 1000);
                        $(this).data('colpickId', id);
                        //Set the tpl's ID and get the HTML
                        var cal = $(tpl).attr('id', id);
                        //Add class according to layout
                        cal.addClass('colpick_' + options.layout + (options.submit ? '' : ' colpick_' + options.layout + '_ns'));
                        //Add class if the color scheme is not default
                        if (options.colorScheme != 'light') {
                            cal.addClass('colpick_' + options.colorScheme);
                        }
                        //Setup submit button
                        cal.find('div.colpick_submit').html(options.submitText).click(clickSubmit);
                        //Setup input fields
                        options.fields = cal.find('input').change(change).blur(blur).focus(focus);
                        cal.find('div.colpick_field_arrs').mousedown(downIncrement).end().find('div.colpick_current_color').click(restoreOriginal);
                        //Setup hue selector
                        options.selector = cal.find('div.colpick_color').on('mousedown touchstart', downSelector);
                        options.selectorIndic = options.selector.find('div.colpick_selector_outer');
                        //Store parts of the plugin
                        options.el = this;
                        options.hue = cal.find('div.colpick_hue_arrs');
                        var huebar = options.hue.parent();
                        //Paint the hue bar
                        var UA = navigator.userAgent.toLowerCase();
                        var isIE = navigator.appName === 'Microsoft Internet Explorer';
                        var IEver = isIE ? parseFloat(UA.match(/msie ([0-9]*[\.0-9]+)/)[1]) : 0;
                        var ngIE = ( isIE && IEver < 10 );
                        var stops = ['#ff0000', '#ff0080', '#ff00ff', '#8000ff', '#0000ff', '#0080ff', '#00ffff', '#00ff80', '#00ff00', '#80ff00', '#ffff00', '#ff8000', '#ff0000'];
                        if (ngIE) {
                            var i, div;
                            for (i = 0; i <= 11; i++) {
                                div = $('<div></div>').attr('style', 'height:8.333333%; filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=' + stops[i] + ', endColorstr=' + stops[i + 1] + '); -ms-filter: "progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=' + stops[i] + ', endColorstr=' + stops[i + 1] + ')";');
                                huebar.append(div);
                            }
                        } else {
                            var stopList = stops.join(',');
                            huebar.attr('style', 'background:-webkit-linear-gradient(top,' + stopList + '); background: -o-linear-gradient(top,' + stopList + '); background: -ms-linear-gradient(top,' + stopList + '); background:-moz-linear-gradient(top,' + stopList + '); -webkit-linear-gradient(top,' + stopList + '); background:linear-gradient(to bottom,' + stopList + '); ');
                        }
                        cal.find('div.colpick_hue').on('mousedown touchstart', downHue);
                        options.newColor = cal.find('div.colpick_new_color');
                        options.currentColor = cal.find('div.colpick_current_color');
                        //Store options and fill with default color
                        cal.data('colpick', options);
                        fillRGBFields(options.color, cal.get(0));
                        fillHSBFields(options.color, cal.get(0));
                        fillHexFields(options.color, cal.get(0));
                        setHue(options.color, cal.get(0));
                        setSelector(options.color, cal.get(0));
                        setCurrentColor(options.color, cal.get(0));
                        setNewColor(options.color, cal.get(0));
                        //Append to body if flat=false, else show in place
                        if (options.flat) {
                            cal.appendTo(options.appendTo || this).show();
                            cal.css(options.styles || {
                                position: 'relative',
                                display: 'block'
                            });
                        } else {
                            cal.appendTo(options.appendTo || document.body);
                            $(this).on(options.showEvent, show);
                            cal.css(options.styles || {
                                position: 'absolute'
                            });
                        }
                    }
                });
            },
            //Shows the picker
            showPicker: function () {
                return this.each(function () {
                    if ($(this).data('colpickId')) {
                        show.apply(this);
                    }
                });
            },
            //Hides the picker
            hidePicker: function () {
                return this.each(function () {
                    if ($(this).data('colpickId')) {
                        hide.apply(this);
                    }
                });
            },
            //Sets a color as new and current (default)
            setColor: function (col, setCurrent) {
                setCurrent = (typeof setCurrent === "undefined") ? 1 : setCurrent;
                if (typeof col == 'string') {
                    col = hexToHsb(col);
                } else if (col.r != undefined && col.g != undefined && col.b != undefined) {
                    col = rgbToHsb(col);
                } else if (col.h != undefined && col.s != undefined && col.b != undefined) {
                    col = fixHSB(col);
                } else {
                    return this;
                }
                return this.each(function () {
                    if ($(this).data('colpickId')) {
                        var cal = $('#' + $(this).data('colpickId'));
                        cal.data('colpick').color = col;
                        cal.data('colpick').origColor = col;
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                        setHue(col, cal.get(0));
                        setSelector(col, cal.get(0));

                        setNewColor(col, cal.get(0));
                        cal.data('colpick').onChange.apply(cal.parent(), [col, hsbToHex(col), hsbToRgb(col), cal.data('colpick').el, 1]);
                        if (setCurrent) {
                            setCurrentColor(col, cal.get(0));
                        }
                    }
                });
            },
            destroy: function () {
                $('#' + $(this).data('colpickId')).remove();
            }
        };
    }();
    //Color space conversions
    var hexToRgb = function (hex) {
        hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
        return {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
    };
    var hexToHsb = function (hex) {
        return rgbToHsb(hexToRgb(hex));
    };
    var rgbToHsb = function (rgb) {
        var hsb = {h: 0, s: 0, b: 0};
        var min = Math.min(rgb.r, rgb.g, rgb.b);
        var max = Math.max(rgb.r, rgb.g, rgb.b);
        var delta = max - min;
        hsb.b = max;
        hsb.s = max != 0 ? 255 * delta / max : 0;
        if (hsb.s != 0) {
            if (rgb.r == max) hsb.h = (rgb.g - rgb.b) / delta;
            else if (rgb.g == max) hsb.h = 2 + (rgb.b - rgb.r) / delta;
            else hsb.h = 4 + (rgb.r - rgb.g) / delta;
        } else hsb.h = -1;
        hsb.h *= 60;
        if (hsb.h < 0) hsb.h += 360;
        hsb.s *= 100 / 255;
        hsb.b *= 100 / 255;
        return hsb;
    };
    var hsbToRgb = function (hsb) {
        var rgb = {};
        var h = hsb.h;
        var s = hsb.s * 255 / 100;
        var v = hsb.b * 255 / 100;
        if (s == 0) {
            rgb.r = rgb.g = rgb.b = v;
        } else {
            var t1 = v;
            var t2 = (255 - s) * v / 255;
            var t3 = (t1 - t2) * (h % 60) / 60;
            if (h == 360) h = 0;
            if (h < 60) {
                rgb.r = t1;
                rgb.b = t2;
                rgb.g = t2 + t3
            }
            else if (h < 120) {
                rgb.g = t1;
                rgb.b = t2;
                rgb.r = t1 - t3
            }
            else if (h < 180) {
                rgb.g = t1;
                rgb.r = t2;
                rgb.b = t2 + t3
            }
            else if (h < 240) {
                rgb.b = t1;
                rgb.r = t2;
                rgb.g = t1 - t3
            }
            else if (h < 300) {
                rgb.b = t1;
                rgb.g = t2;
                rgb.r = t2 + t3
            }
            else if (h < 360) {
                rgb.r = t1;
                rgb.g = t2;
                rgb.b = t1 - t3
            }
            else {
                rgb.r = 0;
                rgb.g = 0;
                rgb.b = 0
            }
        }
        return {r: Math.round(rgb.r), g: Math.round(rgb.g), b: Math.round(rgb.b)};
    };
    var rgbToHex = function (rgb) {
        var hex = [
            rgb.r.toString(16),
            rgb.g.toString(16),
            rgb.b.toString(16)
        ];
        $.each(hex, function (nr, val) {
            if (val.length == 1) {
                hex[nr] = '0' + val;
            }
        });
        return hex.join('');
    };
    var hsbToHex = function (hsb) {
        return rgbToHex(hsbToRgb(hsb));
    };
    $.fn.extend({
        colpick: colpick.init,
        colpickHide: colpick.hidePicker,
        colpickShow: colpick.showPicker,
        colpickSetColor: colpick.setColor,
        colpickDestroy: colpick.destroy
    });
    $.extend({
        colpick: {
            rgbToHex: rgbToHex,
            rgbToHsb: rgbToHsb,
            hsbToHex: hsbToHex,
            hsbToRgb: hsbToRgb,
            hexToHsb: hexToHsb,
            hexToRgb: hexToRgb
        }
    });
}));
;
(function(factory,undefined){if(typeof define==="function"&&define.amd){define(["jquery"],factory)}else if(typeof module==="object"&&typeof module.exports==="object"){module.exports=factory(require("jquery"))}else{factory(jQuery)}})(function($,undefined){if($.fn.resizableSafe)return;$.fn.resizableSafe=function fnResizable(options){var defaultOptions={handleSelector:null,resizeWidth:true,resizeHeight:true,resizeWidthFrom:"right",resizeHeightFrom:"bottom",onDragStart:null,onDragEnd:null,onDrag:null,touchActionNone:true,instanceId:null};if(typeof options=="object")defaultOptions=$.extend(defaultOptions,options);return this.each(function(){var opt=$.extend({},defaultOptions);if(!opt.instanceId)opt.instanceId="rsz_"+(new Date).getTime();var startPos,startTransition;var $el=$(this);var $handle;if(options==="destroy"){opt=$el.data("resizable");if(!opt)return;$handle=getHandle(opt.handleSelector,$el);$handle.off("mousedown."+opt.instanceId+" touchstart."+opt.instanceId);if(opt.touchActionNone)$handle.css("touch-action","");$el.removeClass("resizable");return}$el.data("resizable",opt);$handle=getHandle(opt.handleSelector,$el);if(opt.touchActionNone)$handle.css("touch-action","none");$el.addClass("resizable");$handle.on("mousedown."+opt.instanceId+" touchstart."+opt.instanceId,startDragging);function noop(e){e.stopPropagation();e.preventDefault()}function startDragging(e){if(e.preventDefault){e.preventDefault()}startPos=getMousePos(e);startPos.width=parseInt($el.width(),10);startPos.height=parseInt($el.height(),10);startTransition=$el.css("transition");$el.css("transition","none");if(opt.onDragStart){if(opt.onDragStart(e,$el,opt)===false)return}$(document).on("mousemove."+opt.instanceId,doDrag);$(document).on("mouseup."+opt.instanceId,stopDragging);if(window.Touch||navigator.maxTouchPoints){$(document).on("touchmove."+opt.instanceId,doDrag);$(document).on("touchend."+opt.instanceId,stopDragging)}$(document).on("selectstart."+opt.instanceId,noop);$("iframe").css("pointer-events","none")}function doDrag(e){var pos=getMousePos(e),newWidth,newHeight;if(opt.resizeWidthFrom==="left")newWidth=startPos.width-pos.x+startPos.x;else newWidth=startPos.width+pos.x-startPos.x;if(opt.resizeHeightFrom==="top")newHeight=startPos.height-pos.y+startPos.y;else newHeight=startPos.height+pos.y-startPos.y;if(!opt.onDrag||opt.onDrag(e,$el,newWidth,newHeight,opt)!==false){if(opt.resizeHeight)$el.height(newHeight);if(opt.resizeWidth)$el.width(newWidth)}}function stopDragging(e){e.stopPropagation();e.preventDefault();$(document).off("mousemove."+opt.instanceId);$(document).off("mouseup."+opt.instanceId);if(window.Touch||navigator.maxTouchPoints){$(document).off("touchmove."+opt.instanceId);$(document).off("touchend."+opt.instanceId)}$(document).off("selectstart."+opt.instanceId,noop);$el.css("transition",startTransition);$("iframe").css("pointer-events","auto");if(opt.onDragEnd)opt.onDragEnd(e,$el,opt);return false}function getMousePos(e){var pos={x:0,y:0,width:0,height:0};if(typeof e.clientX==="number"){pos.x=e.clientX;pos.y=e.clientY}else if(e.originalEvent.touches){pos.x=e.originalEvent.touches[0].clientX;pos.y=e.originalEvent.touches[0].clientY}else return null;return pos}function getHandle(selector,$el){if(selector&&selector.trim()[0]===">"){selector=selector.trim().replace(/^>\s*/,"");return $el.find(selector)}return selector?$el.parent().find(selector):$el}})};if(!$.fn.resizable)$.fn.resizable=$.fn.resizableSafe});