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

var MetaModels=new Class({Implements:Options,options:{},classhooks:{},initialize:function(a){this.setOptions(a);this.addClassHook("submitonchange",this.applySubmitOnChange);this.addClassHook("submitonclick",this.applySubmitOnClick)},addClassHook:function(a,b){this.classhooks[a]=b},removeClassHook:function(a){delete (this.classhooks[a])},applyClassHooks:function(){Object.each(this.classhooks,function(b,a){$$("."+a).each(function(d,c){b(d)})})},applySubmitOnChange:function(a){$(a).addEvent("change",function(){$(a).getParent("form").submit()})},applySubmitOnClick:function(a){$(a).addEvent("click",function(){$(a).getParent("form").submit()})},});var MetaModelsFE=new MetaModels();window.addEvent("domready",function(){MetaModelsFE.applyClassHooks()});