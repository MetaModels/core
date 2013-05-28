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
if(typeof mootools!=="undefined"){(function(c){var b=new Class({Implements:Options,options:{},classhooks:{},initialize:function(d){this.setOptions(d);this.addClassHook("submitonchange",this.applySubmitOnChange);this.addClassHook("submitonclick",this.applySubmitOnClick)},addClassHook:function(d,e){this.classhooks[d]=e},removeClassHook:function(d){delete (this.classhooks[d])},applyClassHooks:function(){Object.each(this.classhooks,function(e,d){$$("."+d).each(function(g,f){e(g)})})},applySubmitOnChange:function(d){c(d).addEvent("change",function(){c(d).getParent("form").submit()})},applySubmitOnClick:function(d){c(d).addEvent("click",function(){c(d).getParent("form").submit()})}});var a=new b();window.addEvent("domready",function(){a.applyClassHooks()})})(mootools)}else{if(typeof jQuery!=="undefined"){(function(a){a.MetaModels={options:{},classhooks:{},events:[],init:function(b){this.options=a.extend(this.options,b);this.addClassHook("submitonchange",this.applySubmitOnChange);this.addClassHook("submitonclick",this.applySubmitOnClick)},applySubmitOnChange:function(b,c){c.bindEvent({object:b,type:"change",func:function(d){a(this).parents("form:first")[0].submit()}})},applySubmitOnClick:function(b,c){c.bindEvent({object:b,type:"click",func:function(d){a(this).parents("form:first")[0].submit()}})},addClassHook:function(b,c){this.classhooks[b]=c},removeClassHook:function(b){delete (this.classhooks[b])},applyClassHooks:function(){var b=this;a.each(this.classhooks,function(c,d){a("."+c).each(function(f,e){d(e,b)})})},bindEvent:function(b){this.events.push(b);a(b.object).bind(b.type,b.func)},unbindEvent:function(b,e){var c=null;a(b.object).unbind(b.type);if(e!==true){for(var d=0;d<this.events.length;d++){if(b.object===this.events[d].object&&b.type===this.events[d].type){c=d;break}}if(c!==null){this.events.splice(c,1)}}},unbindEvents:function(){var c=[];for(var b=0;b<this.events.length;b++){if(this.onLoadWindowScroll===this.events[b]){c.push(this.onLoadWindowScroll);continue}this.unbindEvent(this.events[b],true)}this.events=c}};a(document).ready(function(){a.MetaModels.init();a.MetaModels.applyClassHooks()})})(jQuery)}};