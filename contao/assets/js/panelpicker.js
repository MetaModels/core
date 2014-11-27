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

var MetaModelsPicker=new Class({initialize:function(cont,parentField){this.checkboxes=cont.getElements("input");this.seperators=[];var parentField=$(parent.document.getElementById(parentField));if(parentField==null){alert("Parent-Field not found! [E11]");return}if(parentField.get("tag")!="input"){parentField=parentField.getElements("input");if(parentField==null||parentField.length<1){alert("Parent-Field not found! [E12]");return}this.parentField=$(parentField[parentField.length-1])}else{this.parentField=parentField}cont.getElements(".item").each(function(el){el.addEvent("click",function(e){this.clickItem.apply(this,[e,el])}.bind(this))}.bind(this));var classes=this.parentField.get("value").trim().split(/,|;/);var parentString=this.parentField.get("value").trim();for(var i=0;i<classes.length;i++){this.seperators[classes[i]]=parentString[parentString.indexOf(classes[i])+classes[i].length];for(var j=0;j<this.checkboxes.length;j++){if(classes[i]==$(this.checkboxes[j]).get("value")){this.checkboxes[j].checked=true}}}},clickItem:function(e,el){if(e.target.get("tag")=="img"&&e.target.get("rel").length>0){this.showImage(e,e.target);return}var inp=el.getElement("input");if(e==null||e.target.get("tag")!="input"){inp.checked=!inp.checked}var classname=inp.get("value");var classes=this.parentField.get("value").trim().split(/,|;/);if(inp.checked){if(!classes.contains(classname)){classes.push(classname)}}else{classes.erase(classname)}var strClasses="";for(var i=0;i<classes.length;i++){if(strClasses.length>0){strClasses+=(this.seperators[classes[i-1]]==";")?";":","}strClasses+=classes[i]}this.parentField.set("value",strClasses)},showImage:function(ev,el){var img=new Asset.image(el.get("rel"),{onLoad:function(){var size={x:img.get("width"),y:img.get("height")};img.setStyles({height:0,width:0,position:"absolute",left:el.getPosition().x+"px",top:el.getPosition().y+"px"});img.set("morph",{duration:400,transition:"quint:in:out"});img.addEvent("click",function(){img.set("morph",{onComplete:function(){img.destroy()}}).morph({height:0,width:0})});img.inject(document.body);img.morph({height:size.y,width:size.x})}})}});