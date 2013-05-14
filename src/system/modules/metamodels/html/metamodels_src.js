/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
 */

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