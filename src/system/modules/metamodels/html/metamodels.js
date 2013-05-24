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
 * @author     Patrick Kahl <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
if(typeof mootools!=="undefined"){(function(c){var b=new Class({Implements:Options,options:{},classhooks:{},initialize:function(d){this.setOptions(d);this.addClassHook("submitonchange",this.applySubmitOnChange);this.addClassHook("submitonclick",this.applySubmitOnClick)},addClassHook:function(d,e){this.classhooks[d]=e},removeClassHook:function(d){delete (this.classhooks[d])},applyClassHooks:function(){Object.each(this.classhooks,function(e,d){$$("."+d).each(function(g,f){e(g)})})},applySubmitOnChange:function(d){c(d).addEvent("change",function(){c(d).getParent("form").submit()})},applySubmitOnClick:function(d){c(d).addEvent("click",function(){c(d).getParent("form").submit()})}});var a=new b();window.addEvent("domready",function(){a.applyClassHooks()})})(mootools)}else{if(typeof jQuery!=="undefined"){(function(a){a.MetaModels={options:{},classhooks:{},init:function(b){this.options=a.extend(this.options,b);this.addClassHook("submitonchange",this.applySubmitOnChange);this.addClassHook("submitonclick",this.applySubmitOnClick)},addClassHook:function(b,c){this.classhooks[b]=c},removeClassHook:function(b){delete (this.classhooks[b])},applyClassHooks:function(){a.each(this.classhooks,function(b,c){a("input."+b).each(function(e,d){c(d)})})},applySubmitOnChange:function(b){a(b).bind("change",function(){a(b).parents("form:first")[0].submit()})},applySubmitOnClick:function(b){a(b).bind("click",function(){a(b).parents("form:first")[0].submit()})}};a(document).ready(function(){a.MetaModels.init();a.MetaModels.applyClassHooks()})})(jQuery)}};