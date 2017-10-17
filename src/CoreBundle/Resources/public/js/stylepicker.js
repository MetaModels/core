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

var MetaModelsPicker=new Class({initialize:function(a,f){var b=this;this.checkboxes=a.getElements("input");var f=$(parent.document.getElementById(f));if(f==null){alert("Parent-Field not found! [E11]");return}if(f.get("tag")!="input"){f=f.getElements("input");if(f==null||f.length<1){alert("Parent-Field not found! [E12]");return}this.parentField=$(f[f.length-1])}else{this.parentField=f}a.getElements(".item").each(function(g){g.addEvent("click",function(h){b.clickItem(h,g)})});var e=this.parentField.get("value").trim().split(" ");for(var d=0;d<e.length;d++){for(var c=0;c<this.checkboxes.length;c++){if(e[d]==$(this.checkboxes[c]).get("value")){this.checkboxes[c].checked=true}}}},clickItem:function(f,b){if(f.target.get("tag")=="img"&&f.target.get("rel").length>0){this.showImage(f,f.target);return}var c=b.getElement("input");if(f==null||f.target.get("tag")!="input"){c.checked=!c.checked}var d=c.get("value");var a=this.parentField.get("value").trim().split(" ");if(c.checked){if(!a.contains(d)){a.push(d)}}else{a.erase(d)}this.parentField.set("value",a.join(" "))},showImage:function(c,b){var a=new Asset.image(b.get("rel"),{onLoad:function(){var d={x:a.get("width"),y:a.get("height")};a.setStyles({height:0,width:0,position:"absolute",left:b.getPosition().x+"px",top:b.getPosition().y+"px"});a.set("morph",{duration:400,transition:"quint:in:out"});a.addEvent("click",function(){a.set("morph",{onComplete:function(){a.destroy()}}).morph({height:0,width:0})});a.inject(document.body);a.morph({height:d.y,width:d.x})}})}});