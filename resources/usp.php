<?php // User Submitted Posts > JavaScript

	header("Cache-Control: must-revalidate");
	$offset = 60*60*24*60;
	$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";
	header($ExpStr);
	header('Content-Type: application/x-javascript');
	define('WP_USE_THEMES', false);
	require('../../../../wp-blog-header.php');
	$usp_options = get_option('usp_options');
?>
/* 
	User Submitted Posts > JavaScript
	@ http://perishablepress.com/user-submitted-posts/
*/

jQuery(document).ready(function($) {

	// captcha check
	$('#user-submitted-post').click(function() {
		var usp_captcha = $('#user-submitted-captcha').val();
		var usp_response = /<?php echo $usp_options['usp_response']; ?>/i;
		if (usp_captcha.match(usp_response)) {
			$('.usp-input-error-captcha').remove();
			return true;
		} else {
			$('.usp-input-error-captcha').remove();
			$('.usp-captcha').append('<div class="usp-input-error usp-input-error-captcha"><span>Incorrect response. Please try again.</span></div>');
			return false;
		}
	});

	// remember input values
	function remember(selector){
		$(selector).each(function(){
			var name = $(this).attr('name');
			if($.cookie(name)){ $(this).val($.cookie(name)); }
			$(this).change(function(){$.cookie(name, $(this).val(), { path: '/', expires: 365 });});
		});
	}
	remember('[name=user-submitted-name]');
	remember('[name=user-submitted-email]');
	//added
	remember('[name=user-submitted-bio]');
	remember('[name=user-submitted-url]');
	//end added
	remember('[name=user-submitted-title]');
	remember('[name=user-submitted-tags]');
	remember('[name=user-submitted-captcha]');
	remember('[name=user-submitted-category]');
	remember('[name=user-submitted-content]');
	
	// get url params
	function getUrlVars(){
	    var vars = [], hash;
	    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	    for(var i = 0; i < hashes.length; i++){
		   hash = hashes[i].split('=');
		   vars.push(hash[0]);
		   vars[hash[0]] = hash[1];
	    }
	    return vars;
	}
	// forget input values
	var qs = getUrlVars();
	if (qs == 'success'){
		$.removeCookie('user-submitted-name', { path: '/' });
		//added 
		$.removeCookie('user-submitted-email', { path: '/' });
		$.removeCookie('user-submitted-bio', { path: '/' });
		//end added
		$.removeCookie('user-submitted-url', { path: '/' });
		$.removeCookie('user-submitted-title', { path: '/' });
		$.removeCookie('user-submitted-tags', { path: '/' });
		$.removeCookie('user-submitted-captcha', { path: '/' });
		$.removeCookie('user-submitted-category', { path: '/' });
		$.removeCookie('user-submitted-content', { path: '/' });
	}

	// parsely
	$('#usp_form').parsley({ errors: { errorsWrapper: '<div class="usp-input-error"></div>', errorElem: '<span></span>' } });

	// add another image
	var x = parseInt($('#usp-image-limit').val());
	var n = parseInt($('#usp-image-count').val());
	if (x == 1) {
		$('#usp_add-another').hide();
	}
	$('#usp_add-another').click(function(event) {
		event.preventDefault();
		n++;
		var $this = $(this);
		var $new = $this.parent().find('input:visible:last').clone().val('');
		$('#usp-image-count').val(n);
		if (n < x) {
			$this.before($new.fadeIn(300));
		} else if (n = x) {
			$this.before($new.fadeIn(300));
			$this.hide();
		} else {
			$this.hide();
		}
	});
});

/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as anonymous module.
		define(['jquery'], factory);
	} else {
		// Browser globals.
		factory(jQuery);
	}
}(function ($) {
	var pluses = /\+/g;
	function raw(s) {
		return s;
	}
	function decoded(s) {
		return decodeURIComponent(s.replace(pluses, ' '));
	}
	function converted(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}
		try {
			return config.json ? JSON.parse(s) : s;
		} catch(er) {}
	}
	var config = $.cookie = function (key, value, options) {
		// write
		if (value !== undefined) {
			options = $.extend({}, config.defaults, options);
			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}
			value = config.json ? JSON.stringify(value) : String(value);
			return (document.cookie = [
				config.raw ? key : encodeURIComponent(key),
				'=',
				config.raw ? value : encodeURIComponent(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}
		// read
		var decode = config.raw ? raw : decoded;
		var cookies = document.cookie.split('; ');
		var result = key ? undefined : {};
		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = decode(parts.join('='));
			if (key && key === name) {
				result = converted(cookie);
				break;
			}
			if (!key) {
				result[name] = converted(cookie);
			}
		}
		return result;
	};
	config.defaults = {};
	$.removeCookie = function (key, options) {
		if ($.cookie(key) !== undefined) {
			// Must not alter options, thus extending a fresh object...
			$.cookie(key, '', $.extend({}, options, { expires: -1 }));
			return true;
		}
		return false;
	};
}));

/* Parsley dist/parsley.min.js build version 1.1.17-dev http://parsleyjs.org */
!function(d){var h=function(a){this.messages={defaultMessage:"This value seems to be invalid.",type:{email:"This value should be a valid email.",url:"This value should be a valid url.",urlstrict:"This value should be a valid url.",number:"This value should be a valid number.",digits:"This value should be digits.",dateIso:"This value should be a valid date (YYYY-MM-DD).",alphanum:"This value should be alphanumeric.",phone:"This value should be a valid phone number."},notnull:"This value should not be null.",
notblank:"This value should not be blank.",required:"This value is required.",regexp:"This value seems to be invalid.",min:"This value should be greater than or equal to %s.",max:"This value should be lower than or equal to %s.",range:"This value should be between %s and %s.",minlength:"This value is too short. It should have %s characters or more.",maxlength:"This value is too long. It should have %s characters or less.",rangelength:"This value length is invalid. It should be between %s and %s characters long.",
mincheck:"You must select at least %s choices.",maxcheck:"You must select %s choices or less.",rangecheck:"You must select between %s and %s choices.",equalto:"This value should be the same."};this.init(a)};h.prototype={constructor:h,validators:{notnull:function(a){return 0<a.length},notblank:function(a){return"string"===typeof a&&""!==a.replace(/^\s+/g,"").replace(/\s+$/g,"")},required:function(a){if("object"===typeof a){for(var b in a)if(this.required(a[b]))return!0;return!1}return this.notnull(a)&&
this.notblank(a)},type:function(a,b){var c;switch(b){case "number":c=/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/;break;case "digits":c=/^\d+$/;break;case "alphanum":c=/^\w+$/;break;case "email":c=/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i;
break;case "url":a=/(https?|s?ftp|git)/i.test(a)?a:"http://"+a;case "urlstrict":c=/^(https?|s?ftp|git):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
break;case "dateIso":c=/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/;break;case "phone":c=/^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/;break;default:return!1}return""!==a?c.test(a):!1},regexp:function(a,b,c){return RegExp(b,c.options.regexpFlag||"").test(a)},minlength:function(a,b){return a.length>=b},maxlength:function(a,b){return a.length<=b},rangelength:function(a,b){return this.minlength(a,b[0])&&this.maxlength(a,b[1])},
min:function(a,b){return Number(a)>=b},max:function(a,b){return Number(a)<=b},range:function(a,b){return a>=b[0]&&a<=b[1]},equalto:function(a,b,c){c.options.validateIfUnchanged=!0;return a===d(b).val()},remote:function(a,b,c){var f={},g={};f[c.$element.attr("name")]=a;"undefined"!==typeof c.options.remoteDatatype&&(g={dataType:c.options.remoteDatatype});var m=function(a,b){"undefined"!==typeof b&&("undefined"!==typeof c.Validator.messages.remote&&b!==c.Validator.messages.remote)&&d(c.ulError+" .remote").remove();
c.updtConstraint({name:"remote",valid:a},b);c.manageValidationResult()},n=function(a){if("object"===typeof a)return a;try{a=d.parseJSON(a)}catch(b){}return a},e=function(a){return"object"===typeof a&&null!==a?"undefined"!==typeof a.error?a.error:"undefined"!==typeof a.message?a.message:null:null};d.ajax(d.extend({},{url:b,data:f,type:c.options.remoteMethod||"GET",success:function(a){a=n(a);m(1===a||!0===a||"object"===typeof a&&null!==a&&"undefined"!==typeof a.success,e(a))},error:function(a){a=n(a);
m(!1,e(a))}},g));return null},mincheck:function(a,b){return this.minlength(a,b)},maxcheck:function(a,b){return this.maxlength(a,b)},rangecheck:function(a,b){return this.rangelength(a,b)}},init:function(a){var b=a.validators;a=a.messages;for(var c in b)this.addValidator(c,b[c]);for(c in a)this.addMessage(c,a[c])},formatMesssage:function(a,b){if("object"===typeof b){for(var c in b)a=this.formatMesssage(a,b[c]);return a}return"string"===typeof a?a.replace(/%s/i,b):""},addValidator:function(a,b){this.validators[a]=
b},addMessage:function(a,b,c){if("undefined"!==typeof c&&!0===c)this.messages.type[a]=b;else if("type"===a)for(var d in b)this.messages.type[d]=b[d];else this.messages[a]=b}};var j=function(a,b,c){this.options=b;this.Validator=new h(b);if("ParsleyFieldMultiple"===c)return this;this.init(a,c||"ParsleyField")};j.prototype={constructor:j,init:function(a,b){this.type=b;this.valid=!0;this.element=a;this.validatedOnce=!1;this.$element=d(a);this.val=this.$element.val();this.isRequired=!1;this.constraints=
{};"undefined"===typeof this.isRadioOrCheckbox&&(this.isRadioOrCheckbox=!1,this.hash=this.generateHash(),this.errorClassHandler=this.options.errors.classHandler(a,this.isRadioOrCheckbox)||this.$element);this.ulErrorManagement();this.bindHtml5Constraints();this.addConstraints();this.hasConstraints()&&this.bindValidationEvents()},setParent:function(a){this.$parent=d(a)},getParent:function(){return this.$parent},bindHtml5Constraints:function(){if(this.$element.hasClass("required")||this.$element.prop("required"))this.options.required=
!0;"undefined"!==typeof this.$element.attr("type")&&RegExp(this.$element.attr("type"),"i").test("email url number range")&&(this.options.type=this.$element.attr("type"),RegExp(this.options.type,"i").test("number range")&&(this.options.type="number","undefined"!==typeof this.$element.attr("min")&&this.$element.attr("min").length&&(this.options.min=this.$element.attr("min")),"undefined"!==typeof this.$element.attr("max")&&this.$element.attr("max").length&&(this.options.max=this.$element.attr("max"))));
"string"===typeof this.$element.attr("pattern")&&this.$element.attr("pattern").length&&(this.options.regexp=this.$element.attr("pattern"))},addConstraints:function(){for(var a in this.options){var b={};b[a]=this.options[a];this.addConstraint(b,!0)}},addConstraint:function(a,b){for(var c in a)c=c.toLowerCase(),"function"===typeof this.Validator.validators[c]&&(this.constraints[c]={name:c,requirements:a[c],valid:null},"required"===c&&(this.isRequired=!0),this.addCustomConstraintMessage(c));"undefined"===
typeof b&&this.bindValidationEvents()},updateConstraint:function(a,b){for(var c in a)this.updtConstraint({name:c,requirements:a[c],valid:null},b)},updtConstraint:function(a,b){this.constraints[a.name]=d.extend(!0,this.constraints[a.name],a);"string"===typeof b&&(this.Validator.messages[a.name]=b);this.bindValidationEvents()},removeConstraint:function(a){a=a.toLowerCase();delete this.constraints[a];"required"===a&&(this.isRequired=!1);this.hasConstraints()?this.bindValidationEvents():"ParsleyForm"===
typeof this.getParent()?this.getParent().removeItem(this.$element):this.destroy()},addCustomConstraintMessage:function(a){var b=a+("type"===a&&"undefined"!==typeof this.options[a]?this.options[a].charAt(0).toUpperCase()+this.options[a].substr(1):"")+"Message";"undefined"!==typeof this.options[b]&&this.Validator.addMessage("type"===a?this.options[a]:a,this.options[b],"type"===a)},bindValidationEvents:function(){this.valid=null;this.$element.addClass("parsley-validated");this.$element.off("."+this.type);
this.options.remote&&!/change/i.test(this.options.trigger)&&(this.options.trigger=!this.options.trigger?"change":" change");var a=(!this.options.trigger?"":this.options.trigger)+(/key/i.test(this.options.trigger)?"":" keyup");this.$element.is("select")&&(a+=/change/i.test(a)?"":" change");a=a.replace(/^\s+/g,"").replace(/\s+$/g,"");this.$element.on((a+" ").split(" ").join("."+this.type+" "),!1,d.proxy(this.eventValidation,this))},generateHash:function(){return"parsley-"+(Math.random()+"").substring(2)},
getHash:function(){return this.hash},getVal:function(){return this.$element.data("value")||this.$element.val()},eventValidation:function(a){var b=this.getVal();if("keyup"===a.type&&!/keyup/i.test(this.options.trigger)&&!this.validatedOnce||"change"===a.type&&!/change/i.test(this.options.trigger)&&!this.validatedOnce||!this.isRadioOrCheckbox&&b.length<this.options.validationMinlength&&!this.validatedOnce)return!0;this.validate()},isValid:function(){return this.validate(!1)},hasConstraints:function(){for(var a in this.constraints)return!0;
return!1},validate:function(a){var b=this.getVal(),c=null;if(!this.hasConstraints())return null;if(this.options.listeners.onFieldValidate(this.element,this)||""===b&&!this.isRequired)return this.reset(),null;if(!this.needsValidation(b))return this.valid;c=this.applyValidators();("undefined"!==typeof a?a:this.options.showErrors)&&this.manageValidationResult();return c},needsValidation:function(a){if(!this.options.validateIfUnchanged&&null!==this.valid&&this.val===a&&this.validatedOnce)return!1;this.val=
a;return this.validatedOnce=!0},applyValidators:function(){var a=null,b;for(b in this.constraints){var c=this.Validator.validators[this.constraints[b].name](this.val,this.constraints[b].requirements,this);!1===c?(a=!1,this.constraints[b].valid=a,this.options.listeners.onFieldError(this.element,this.constraints,this)):!0===c&&(this.constraints[b].valid=!0,a=!1!==a,this.options.listeners.onFieldSuccess(this.element,this.constraints,this))}return a},manageValidationResult:function(){var a=null,b;for(b in this.constraints)!1===
this.constraints[b].valid?(this.manageError(this.constraints[b]),a=!1):!0===this.constraints[b].valid&&(this.removeError(this.constraints[b].name),a=!1!==a);this.valid=a;return!0===this.valid?(this.removeErrors(),this.errorClassHandler.removeClass(this.options.errorClass).addClass(this.options.successClass),!0):!1===this.valid?(this.errorClassHandler.removeClass(this.options.successClass).addClass(this.options.errorClass),!1):a},ulErrorManagement:function(){this.ulError="#"+this.hash;this.ulTemplate=
d(this.options.errors.errorsWrapper).attr("id",this.hash).addClass("parsley-error-list")},removeError:function(a){a=this.ulError+" ."+a;var b=this;this.options.animate?d(a).fadeOut(this.options.animateDuration,function(){d(this).remove();b.ulError&&0===d(b.ulError).children().length&&b.removeErrors()}):d(a).remove();this.ulError&&0===d(this.ulError).children().length&&this.removeErrors()},addError:function(a){for(var b in a){var c=d(this.options.errors.errorElem).addClass(b);d(this.ulError).append(this.options.animate?
d(c).html(a[b]).hide().fadeIn(this.options.animateDuration):d(c).html(a[b]))}},removeErrors:function(){this.options.animate?d(this.ulError).fadeOut(this.options.animateDuration,function(){d(this).remove()}):d(this.ulError).remove()},reset:function(){this.valid=null;this.removeErrors();this.validatedOnce=!1;this.errorClassHandler.removeClass(this.options.successClass).removeClass(this.options.errorClass);for(var a in this.constraints)this.constraints[a].valid=null;return this},manageError:function(a){d(this.ulError).length||
this.manageErrorContainer();if(!("required"===a.name&&null!==this.getVal()&&0<this.getVal().length)&&(!this.isRequired||!("required"!==a.name&&(null===this.getVal()||0===this.getVal().length)))){var b=a.name,c=!1!==this.options.errorMessage?"custom-error-message":b,f={};a=!1!==this.options.errorMessage?this.options.errorMessage:"type"===a.name?this.Validator.messages[b][a.requirements]:"undefined"===typeof this.Validator.messages[b]?this.Validator.messages.defaultMessage:this.Validator.formatMesssage(this.Validator.messages[b],
a.requirements);d(this.ulError+" ."+c).length||(f[c]=a,this.addError(f))}},manageErrorContainer:function(){var a=this.options.errorContainer||this.options.errors.container(this.element,this.isRadioOrCheckbox),b=this.options.animate?this.ulTemplate.show():this.ulTemplate;"undefined"!==typeof a?d(a).append(b):!this.isRadioOrCheckbox?this.$element.after(b):this.$element.parent().after(b)},addListener:function(a){for(var b in a)this.options.listeners[b]=a[b]},destroy:function(){this.$element.removeClass("parsley-validated");
this.reset().$element.off("."+this.type).removeData(this.type)}};var l=function(a,b,c){this.initMultiple(a,b);this.inherit(a,b);this.Validator=new h(b);this.init(a,c||"ParsleyFieldMultiple")};l.prototype={constructor:l,initMultiple:function(a,b){this.element=a;this.$element=d(a);this.group=b.group||!1;this.hash=this.getName();this.siblings=this.group?'[data-group="'+this.group+'"]':'input[name="'+this.$element.attr("name")+'"]';this.isRadioOrCheckbox=!0;this.isRadio=this.$element.is("input[type=radio]");
this.isCheckbox=this.$element.is("input[type=checkbox]");this.errorClassHandler=b.errors.classHandler(a,this.isRadioOrCheckbox)||this.$element.parent()},inherit:function(a,b){var c=new j(a,b,"ParsleyFieldMultiple"),d;for(d in c)"undefined"===typeof this[d]&&(this[d]=c[d])},getName:function(){if(this.group)return"parsley-"+this.group;if("undefined"===typeof this.$element.attr("name"))throw"A radio / checkbox input must have a data-group attribute or a name to be Parsley validated !";return"parsley-"+
this.$element.attr("name").replace(/(:|\.|\[|\])/g,"")},getVal:function(){if(this.isRadio)return d(this.siblings+":checked").val()||"";if(this.isCheckbox){var a=[];d(this.siblings+":checked").each(function(){a.push(d(this).val())});return a}},bindValidationEvents:function(){this.valid=null;this.$element.addClass("parsley-validated");this.$element.off("."+this.type);var a=this,b=(!this.options.trigger?"":this.options.trigger)+(/change/i.test(this.options.trigger)?"":" change"),b=b.replace(/^\s+/g,
"").replace(/\s+$/g,"");d(this.siblings).each(function(){d(this).on(b.split(" ").join("."+a.type+" "),!1,d.proxy(a.eventValidation,a))})}};var k=function(a,b,c){this.init(a,b,c||"parsleyForm")};k.prototype={constructor:k,init:function(a,b,c){this.type=c;this.items=[];this.$element=d(a);this.options=b;var f=this;this.$element.find(b.inputs).each(function(){f.addItem(this)});this.$element.on("submit."+this.type,!1,d.proxy(this.validate,this))},addListener:function(a){for(var b in a)if(/Field/.test(b))for(var c=
0;c<this.items.length;c++)this.items[c].addListener(a);else this.options.listeners[b]=a[b]},addItem:function(a){if(d(a).is(this.options.excluded))return!1;a=d(a).parsley(this.options);a.setParent(this);this.items.push(a)},removeItem:function(a){a=d(a).parsley();for(var b=0;b<this.items.length;b++)if(this.items[b].hash===a.hash)return this.items[b].destroy(),this.items.splice(b,1),!0;return!1},validate:function(a){var b=!0;this.focusedField=!1;for(var c=0;c<this.items.length;c++)if("undefined"!==typeof this.items[c]&&
!1===this.items[c].validate()&&(b=!1,!this.focusedField&&"first"===this.options.focus||"last"===this.options.focus))this.focusedField=this.items[c].$element;this.focusedField&&!b&&this.focusedField.focus();this.options.listeners.onFormSubmit(b,a,this);return b},isValid:function(){for(var a=0;a<this.items.length;a++)if(!1===this.items[a].isValid())return!1;return!0},removeErrors:function(){for(var a=0;a<this.items.length;a++)this.items[a].parsley("reset")},destroy:function(){for(var a=0;a<this.items.length;a++)this.items[a].destroy();
this.$element.off("."+this.type).removeData(this.type)},reset:function(){for(var a=0;a<this.items.length;a++)this.items[a].reset()}};d.fn.parsley=function(a,b){function c(c,g){var e=d(c).data(g);if(!e){switch(g){case "parsleyForm":e=new k(c,f,"parsleyForm");break;case "parsleyField":e=new j(c,f,"parsleyField");break;case "parsleyFieldMultiple":e=new l(c,f,"parsleyFieldMultiple");break;default:return}d(c).data(g,e)}return"string"===typeof a&&"function"===typeof e[a]?(e=e[a](b),"undefined"!==typeof e?
e:d(c)):e}var f=d.extend(!0,{},d.fn.parsley.defaults,"undefined"!==typeof window.ParsleyConfig?window.ParsleyConfig:{},a,this.data()),g=null;d(this).is("form")||!0===d(this).data("bind")?g=c(d(this),"parsleyForm"):d(this).is(f.inputs)&&!d(this).is(f.excluded)&&(g=c(d(this),!d(this).is("input[type=radio], input[type=checkbox]")?"parsleyField":"parsleyFieldMultiple"));return"function"===typeof b?b():g};d.fn.parsley.Constructor=k;d.fn.parsley.defaults={inputs:"input, textarea, select",excluded:"input[type=hidden], input[type=file], :disabled",
trigger:!1,animate:!0,animateDuration:300,focus:"first",validationMinlength:3,successClass:"parsley-success",errorClass:"parsley-error",errorMessage:!1,validators:{},showErrors:!0,messages:{},validateIfUnchanged:!1,errors:{classHandler:function(){},container:function(){},errorsWrapper:"<ul></ul>",errorElem:"<li></li>"},listeners:{onFieldValidate:function(){return!1},onFormSubmit:function(){},onFieldError:function(){},onFieldSuccess:function(){}}};d(window).on("load",function(){d('[data-validate="parsley"]').each(function(){d(this).parsley()})})}(window.jQuery||
window.Zepto);
