
/**
 * Return integer values of top, left, width, height
 */
 
jQuery.fn.itop = function() {
    return parseInt(this.css('top'));
};

jQuery.fn.ileft = function() {
    return parseInt(this.css('left'));
};

jQuery.fn.iwidth = function() {
    return parseInt(this.css('width'));
};

jQuery.fn.iheight = function() {
    return parseInt(this.css('height'));
};


/**
 * Set visibility of jQuery object to 'hidden' or 'visible'
 */
jQuery.fn.showv = function() {
    this.css('visibility', 'visible');
	return this;
};

jQuery.fn.hidev = function() {
    this.css('visibility', 'hidden');
	return this;
};


/**
 * Set checked status of checkboxes globablly
 */
jQuery.fn.check = function(mode) {
    var mode = mode || 'on'; // if mode is undefined, use 'on' as default
    return this.each(function() {
        switch(mode) {
        case 'on':
            this.checked = true;
            break;
        case 'off':
            this.checked = false;
            break;
        case 'toggle':
            this.checked = !this.checked;
            break;
        }
    });
};


/**
 * Swap image src
 */
jQuery.fn.imgSwap = function(from, to) {
    this.src(this.src().replace(from,to));
    return this;
};


/**
 * Default method for AJAX requests 
 */
$.ajaxSetup( { type: "POST", data:"dummy=dummy" } );



