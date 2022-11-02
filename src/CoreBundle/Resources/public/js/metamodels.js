
/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
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
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Developers need to apply a snippet like this for each
 * class they want to use:
 *
 *      window.MetaModelsFE.addClassHook('someclass', function(element, key){
 *        alert("the class " + key + " has been applied to " + element)
 *      });
 *
 *      window.MetaModelsFE.addClassHook('submitonclick', function(el, helper) {
 *           // Remove old events
 *           helper.unbindEvents({
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

this.__MetaModels = (
    function() {
        function __MetaModels(opts) {
            /**
             * List for additions functions.
             */
            this.classhooks = {};

            /**
             * List for additions events.
             */
            this.events = [];

            this.addClassHook('submitonchange', this.applySubmitOnChange);
            this.addClassHook('submitonclick', this.applySubmitOnClick);
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
            for (let x in this.classhooks) {
                if (this.classhooks.hasOwnProperty(x)) {
                    document.querySelectorAll('.' + x).forEach(hook => this.classhooks[x](hook, this));
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
            objEvent.object.addEventListener(objEvent.type, objEvent.func);
        };

        /**
         * Unbind given event.
         *
         * @param {{type: string, object: Object, func: Function}} objEvent
         * @param {boolean} blnNotRemove
         */
        __MetaModels.prototype.unbindEvent = function(objEvent, blnNotRemove) {
            let intIndex = null;

            objEvent.object.removeEventListener(objEvent.type, objEvent.func);

            if (blnNotRemove !== true) {
                for (let i = 0; i < this.events.length; i++) {
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
         * Unbind events.
         *
         * @param {{type?: string, object?: Object}} opts
         */
        __MetaModels.prototype.unbindEvents = function(opts) {
            let filtered = this.events;
            if (opts) {
                filtered = filtered.filter(event => (!opts.object || (event.object === opts.object)) && (!opts.type || (opts.type === event.type)));
            }

            for (var i = 0; i < filtered.length; i++) {
                this.unbindEvent(filtered[i]);
            }
        };

        return __MetaModels;
    }());

window.MetaModelsFE = new __MetaModels();

window.addEventListener('DOMContentLoaded', function(e) {
    window.MetaModelsFE.applyClassHooks();
});
