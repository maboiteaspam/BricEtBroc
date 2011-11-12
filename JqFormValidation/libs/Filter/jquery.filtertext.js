/**
 * 
 */
function filtertextClass( element, options ){

    this.stripHTMLTags = function( newValue, newChar ){
        return newValue.replace(/<.*?>/g, '');
    };

    this.trim = function( newValue, newChar ){
        return $.trim(newValue);
    };

    this.doGetCaretPosition = function(  ){
        var ctrl = this.element;
        var CaretPos = 0;	// IE Support
        if (document.selection){
            ctrl.focus ();
            var Sel = document.selection.createRange ();
            Sel.moveStart ('character', -ctrl.value.length);
            CaretPos = Sel.text.length;
        }
        // Firefox support
        else if (ctrl.selectionStart || ctrl.selectionStart == '0')
        CaretPos = ctrl.selectionStart;
        return (CaretPos);
    };
    
    this.liveTrim = function( newChar ){
        var doCancel    = false;

        if( this.trim(newChar) == "" ){
            var caretPos = this.doGetCaretPosition();
            if( caretPos == 0 ){
                doCancel = true;
            }else if( caretPos == this.element.value.length ){
                if( this.element.value.substr(-1, 1) == " " ){
                    doCancel = true;
                }
            }
        }

        return doCancel;
    };
    
    this.liveStripHTMLTags = function( newChar ){
        var doCancel    = false;
        var newValue    = this.element.value+""+newChar;
        if( this.stripHTMLTags(newValue) != newValue ){
            doCancel = true;
        }
        return doCancel;
    };
    
    this.liveRemoveHTMLTags = function(  newChar){
        var doCancel    = false;
        var corrected_text = this.stripHTMLTags(this.element.value);
        if( corrected_text != this.element.value ){
            this.element.value = corrected_text;
            doCancel = true;
        }
        return doCancel;
    };
    
    this.liveNoChars = function( newChar ){
        var doCancel    = false;
        var newValue    = newChar;
        var noChars     = this.options.nochars;
        if( noChars.indexOf(newChar)>-1 ){
            doCancel = true;
        }
        return doCancel;
    };
    
    this.liveCallback = function( newChar ){
        var doCancel    = false;
        var newValue    = this.element.value+""+newChar;
        var callback    = this.options.callback;
        if( callback.call(this, newValue, newChar) == false ){
            doCancel = true;
        }
        return doCancel;
    };

    this.init = function( ){
        if( this.options.trim ){
            $(this.element).keypress(
                function(event){
                    var filter      = $(this).data('filtertext');
                    var doCancel    = filter.liveTrim( String.fromCharCode(event.keyCode) );
                    if( doCancel ){
                        event.stopPropagation();
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            );
        }
        if( this.options.nohtml ){
            $(this.element).keypress(
                function(event){
                    var filter      = $(this).data('filtertext');
                    var doCancel    = filter.liveStripHTMLTags( String.fromCharCode(event.keyCode) );
                    if( doCancel ){
                        event.stopPropagation();
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            );
            $(this.element).keyup(
                function (event){
                    var filter      = $(this).data('filtertext');
                    var doCancel    = filter.liveRemoveHTMLTags( String.fromCharCode(event.keyCode) );
                    if( doCancel ){
                        event.stopPropagation();
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            );
        }
        if( this.options.nochars ){
            $(this.element).keypress(
                function(event){
                    var filter      = $(this).data('filtertext');
                    var doCancel    = filter.liveNoChars( String.fromCharCode(event.keyCode) );
                    if( doCancel ){
                        event.stopPropagation();
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            );
        }
        if( this.options.callback ){
            $(this.element).keypress(
                function(event){
                    var filter      = $(this).data('filtertext');
                    var doCancel    = filter.liveCallback( String.fromCharCode(event.keyCode) );
                    if( doCancel ){
                        event.stopPropagation();
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            );
        }
    };
    
    this.element = element;
    this.options = options;
    
    
    this.init();
}




( function($) {
    $.extend($.fn, {
        filtertext: function( options ) {

            // check if a validator for this form was already created
            var filter = $.data(this[0], 'filtertext');
            if ( ! filter ) {
                if ( $(this).is('form') ) {
                    filter = new Array();
                    var form = $(this);
                    $.each(options, function(element_name, filters) {
                        var el = form.find("input[name="+element_name+"]");
                        if( el.length == 0 ){
                            el = form.find("textarea[name="+element_name+"]");
                        }
                        
                        if( el.length > 0 ){
                            el.filtertext( filters );
                            filter.push( el );
                        }
                    });
                } else {
                    filter = new filtertextClass( this[0], options );
                    jQuery.data(this[0], 'filtertext', filter);
                }
            }

            return filter;
        }
    });
})(jQuery);
