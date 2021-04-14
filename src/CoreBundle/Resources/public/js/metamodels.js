
/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Developers need to apply a snippet like this for each
 * class they want to use:
 *
 *   window.addEventListener('DOMContentLoaded', function(e){
 *
 *      var __metaMoodels = new __MetaModels().init();
 *
 *      __metaMoodels.addClassHook('someclass', function(element, key){
 *        alert("the class " + key + " has been applied to " + element)
 *      });
 *
 *      __metaMoodels.addClassHook('submitonclick', function(el, helper) {
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
 *   });
 */

(
    function() {
        this.__MetaModels = function() {
            // VARS.
            /**
             * Options.
             */
            this.options = {};

            /**
             * List for additions functions.
             */
            this.classhooks = {};

            /**
             * List for additions events.
             */
            this.events = [];

            // Define option defaults
            let defaults = {
                selector: '',
            };

            if (arguments[0] && typeof arguments[0] === 'object') {
                this.options = extendDefaults(defaults, arguments[0]);
            }

            this.selector = this.options.selector;
        };

        // CORE.
        /**
         * Init.
         */
        __MetaModels.prototype.init = function(opts) {
            // Set the given params
            this.options = Object(this.options, opts);

            this.addClassHook('submitonchange', this.applySubmitOnChange);
            this.addClassHook('submitonclick', this.applySubmitOnClick);

            this.applyClassHooks();
        };

        // DEFAULT.
        /**
         * Local helper for form elements to submit the form.
         */
        __MetaModels.prototype.applySubmitOnChange = function(el, helper) {
            helper.bindEvent({
                                 object: el,
                                 type  : 'change',
                                 func  : function(event) {
                                     el.closest('form').submit();
                                 },
                             });
        };

        __MetaModels.prototype.applySubmitOnClick = function(el, helper) {
            helper.bindEvent({
                                 object: el,
                                 type  : 'click',
                                 func  : function(event) {
                                     el.closest('form').submit();
                                 },
                             });
        };

        // API.
        /**
         * Hook functions, for adding/remove new functions.
         */
        __MetaModels.prototype.addClassHook = function(name, callback) {
            this.classhooks[name] = callback;
        };

        __MetaModels.prototype.removeClassHook = function(name) {
            delete (this.classhooks[name]);
        };

        /**
         * Apply all class handlers to their elements.
         */
        __MetaModels.prototype.applyClassHooks = function() {
            var self = this;
            for (let x in this.classhooks) {
                let __hooks = document.querySelectorAll('.' + x);
                for (let y in __hooks) {
                    this.classhooks[x](__hooks[y], self);
                }
            }
        };

        // EVENTS.
        /**
         * Bind given event.
         *
         * @param {object} objEvent
         */
        __MetaModels.prototype.bindEvent = function(objEvent) {
            this.events.push(objEvent);
            let __el = objEvent.object;

            try {
                __el.addEventListener(objEvent.type, objEvent.func);
            } catch (e) {
                // console.log(e);
            }
        };

        /**
         * Unbind given event.
         *
         * @param {object} objEvent
         * @param {boolean} blnNotRemove
         */
        __MetaModels.prototype.unbindEvent = function(objEvent, blnNotRemove) {
            let intIndex = null;

            let __el = objEvent.object;

            __el.unbind(objEvent.type);

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
        };

        /**
         * Unbind events for this class.
         */
        __MetaModels.prototype.unbindEvents = function() {
            let arrStore = [];

            for (var i = 0; i < this.events.length; i++) {
                if (this.onLoadWindowScroll === this.events[i]) {
                    arrStore.push(this.onLoadWindowScroll);
                    continue;
                }

                this.unbindEvent(this.events[i], true);
            }

            this.events = arrStore;
        };
    }());

window.addEventListener('DOMContentLoaded', function(e) {
    new __MetaModels().init();
});
