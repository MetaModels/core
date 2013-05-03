/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  The MetaModels team.
 * @license    LGPL
 * @filesource
 */

var Stylepicker4ward=new Class({initialize:function(a,f){this.checkboxes=a.getElements("input");this.seperators=[];var f=$(parent.document.getElementById(f));if(f==null){alert("Parent-Field not found! [E11]");return}if(f.get("tag")!="input"){f=f.getElements("input");if(f==null||f.length<1){alert("Parent-Field not found! [E12]");return}this.parentField=$(f[f.length-1])}else{this.parentField=f}a.getElements(".item").each(function(g){g.addEvent("click",this.clickItem.bindWithEvent(this,[g]))}.bind(this));var d=this.parentField.get("value").trim().split(/ /);var e=this.parentField.get("value").trim();for(var c=0;c<d.length;c++){this.seperators[d[c]]=e[e.indexOf(d[c])+d[c].length];for(var b=0;b<this.checkboxes.length;b++){if(d[c]==$(this.checkboxes[b]).get("value")){this.checkboxes[b].checked=true}}}},clickItem:function(h,c){if(h.target.get("tag")=="img"&&h.target.get("rel").length>0){this.showImage(h,h.target);return}var f=c.getElement("input");if(h==null||h.target.get("tag")!="input"){f.checked=!f.checked}var g=f.get("value");var b=this.parentField.get("value").trim().split(/ /);if(f.checked){if(!b.contains(g)){b.push(g)}}else{b.erase(g)}var d="";for(var a=0;a<b.length;a++){if(d.length>0){d+=" "}d+=b[a]}this.parentField.set("value",d)},showImage:function(c,b){var a=new Asset.image(b.get("rel"),{onLoad:function(){var d={x:a.get("width"),y:a.get("height")};a.setStyles({height:0,width:0,position:"absolute",left:b.getPosition().x+"px",top:b.getPosition().y+"px"});a.set("morph",{duration:400,transition:"quint:in:out"});a.addEvent("click",function(){a.set("morph",{onComplete:function(){a.destroy()}}).morph({height:0,width:0})});a.inject(document.body);a.morph({height:d.y,width:d.x})}})}});