/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Developers need to apply a snippet like this for each 
 * class they want to use:
 *
 * MetaModelsFE.addClassHook('someclass', function(element, key){ 
 *    alert("the class " + key + " has been applied to " + element)
 * });
 * 
 * jQuery.MetaModels.addClassHook('submitonclick', function(el, helper) {
 *           // Remove old events
 *           helper.unbindEvent({
 *               object: el,
 *               type: 'click'
 *           });
 *           
 *           // Add new event
 *           helper.bindEvent({
 *               object: el,
 *               type: 'click',
 *               func: function(event) {
 *                   // Stop event
 *                   event.preventDefault();
 *                   event.stopPropagation();
 *                   
 *                   // Do something
 *                   
 *                   return false;
 *               }
 *           });
 *       }); 
 */

// Check if we have mootools
if (typeof mootools !== 'undefined') {

    (function($) {
        var MetaModels = new Class({

            Implements: Options,

            /**
             * Options
             */
            options: {},

            /**
             * List for additions functions
             */
            classhooks: {},

            /**
             * Init
             */
            initialize: function(options) {
                this.setOptions(options);

                this.addClassHook('submitonchange', this.applySubmitOnChange);
                this.addClassHook('submitonclick', this.applySubmitOnClick);
            },

            /**
             * Hook functions, for adding/remove new functions
             */
            addClassHook: function(name, callback) {
                this.classhooks[name] = callback;
            },

            removeClassHook: function(name) {
                delete(this.classhooks[name]);
            },

            /**
             * apply all class handlers to their elements.
             */
            applyClassHooks: function() {
                Object.each(this.classhooks, function(value, key) {
                    $$('.' + key).each(function(el, key) {
                        value(el);
                    });
                });
            },

            /**
             * Local helper for form elements to submit the form.
             */

            applySubmitOnChange: function(el) {
                $(el).addEvent('change', function() {
                    $(el).getParent('form').submit()
                });
            },

            applySubmitOnClick: function(el) {
                $(el).addEvent('click', function() {
                    $(el).getParent('form').submit()
                });
            },

        });

        var MetaModelsFE = new MetaModels();

        // apply all class hooks on domready
        window.addEvent('domready', function() {
            MetaModelsFE.applyClassHooks();
        });

    })(mootools);

}
// Check if we have jQuery
else if (typeof jQuery !== 'undefined') {

    (function($) {
        $.MetaModels = {

            // VARS ------------------------------------------------------------
            /**
             * Options
             */
            options: {},

            /**
             * List for additions functions
             */
            classhooks: {},

            /**
             * List for additions events
             */
            events: [],

            // CORE ------------------------------------------------------------
            /**
             * Init
             */
            init: function(opts) {
                // Set the given params
                this.options = $.extend(this.options, opts);

                this.addClassHook('submitonchange', this.applySubmitOnChange);
                this.addClassHook('submitonclick', this.applySubmitOnClick);
            },

            // DEFAULT ---------------------------------------------------------
            /**
             * Local helper for form elements to submit the form.
             */
            applySubmitOnChange: function(el, helper) {
                helper.bindEvent({
                    object: el,
                    type: 'change',
                    func: function(event) {
                        $(this).parents('form:first')[0].submit()
                    }
                });
            },

            applySubmitOnClick: function(el, helper) {
                helper.bindEvent({
                    object: el,
                    type: 'click',
                    func: function(event) {
                        $(this).parents('form:first')[0].submit()
                    }
                });
            },

            // API ------------------------------------------------------------
            /**
             * Hook functions, for adding/remove new functions
             */
            addClassHook: function(name, callback) {
                this.classhooks[name] = callback;
            },

            removeClassHook: function(name) {
                delete(this.classhooks[name]);
            },

            /**
             * apply all class handlers to their elements.
             */
            applyClassHooks: function() {
                var self = this;

                $.each(this.classhooks, function(key, value) {
                    $('.' + key).each(function(key1, el) {
                        value(el, self);
                    });
                });
            },

            // EVENTS ----------------------------------------------------------
            /**
             * Bind given event
             * 
             * @param {object} objEvent
             */
            bindEvent: function(objEvent) {
                this.events.push(objEvent);                
                $(objEvent.object).bind(objEvent.type, objEvent.func);
            },

            /**
             * Unbind given event
             * 
             * @param {object} objEvent
             * @param {boolean} blnNotRemove
             */
            unbindEvent: function(objEvent, blnNotRemove) {
                var intIndex = null;

                $(objEvent.object).unbind(objEvent.type);

                if (blnNotRemove !== true) {
                    for (var i = 0; i < this.events.length; i++) {
                        if (objEvent.object === this.events[i].object && objEvent.type === this.events[i].type) {
                            intIndex = i;
                            break;
                        }
                    }
                    if (intIndex !== null) {
                        this.events.splice(intIndex, 1);
                    }
                }
            },

            /**
             * Unbind events for this class
             */
            unbindEvents: function() {
                var arrStore = [];

                for (var i = 0; i < this.events.length; i++) {
                    if (this.onLoadWindowScroll === this.events[i]) {
                        arrStore.push(this.onLoadWindowScroll);
                        continue;
                    }

                    this.unbindEvent(this.events[i], true);
                }

                this.events = arrStore;
            },
        }

        $(document).ready(function() {
            $.MetaModels.init();
            $.MetaModels.applyClassHooks();
        });

    })(jQuery);
}