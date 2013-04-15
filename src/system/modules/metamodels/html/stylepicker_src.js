var Stylepicker4ward = new Class(
{
	
    initialize: function(cont,parentField)
    {
		this.checkboxes = cont.getElements('input');
		this.seperators = [];
		// find parent class-field
		var parentField = $(parent.document.getElementById(parentField));
		if(parentField == null)
		{
			alert('Parent-Field not found! [E11]');
			return;
		}
		if(parentField.get('tag') != 'input')
		{
			parentField = parentField.getElements('input');
			if(parentField == null || parentField.length < 1)
			{
				alert('Parent-Field not found! [E12]');
				return;
			}
			this.parentField = $(parentField[parentField.length-1]);
		}
		else
		{
			this.parentField = parentField;
		}

		// set click-events
    	cont.getElements('.item').each(function(el){
    		el.addEvent('click',this.clickItem.bindWithEvent(this,[el]));
    	}.bind(this));
    	
    	// check checkboxes if a classname is set
    	var classes = this.parentField.get('value').trim().split(/,|;/);
		var parentString = this.parentField.get('value').trim();
    	for(var i=0;i<classes.length;i++)
    	{
		this.seperators[classes[i]] = parentString[parentString.indexOf(classes[i]) + classes[i].length];
    		for(var j=0;j<this.checkboxes.length;j++)
    		{
    			if(classes[i] == $(this.checkboxes[j]).get('value'))
    				this.checkboxes[j].checked = true;
    		}
    	}
	},
	
	clickItem: function(e,el)
	{
		if(e.target.get('tag') == 'img' && e.target.get('rel').length > 0)
		{
			this.showImage(e,e.target);
			return;
		}
		
		var inp = el.getElement('input');
		if(e == null || e.target.get('tag') != 'input')
		{
			inp.checked = !inp.checked;
		}
		
		// update parent-field
		var classname = inp.get('value');
		var classes = this.parentField.get('value').trim().split(/,|;/);
		if(inp.checked)
		{
			// add classname
			if(!classes.contains(classname))
				classes.push(classname)
			
		}
		else
		{
			// remove classname
			classes.erase(classname);
		}
		var strClasses = "";
		for (var i =0; i < classes.length; i++){
			if (strClasses.length > 0)
				strClasses += (this.seperators[classes[i-1]] == ';') ? ';': ',';
			strClasses += classes[i];
		}
		this.parentField.set('value',strClasses);
	},
	
	showImage: function(ev,el)
	{
		var img = new Asset.image(el.get('rel'),{
			onLoad: function()
			{
				var size = {x:img.get('width'),y:img.get('height')};
				img.setStyles({
					'height':0,
					'width':0,
					'position':'absolute',
					'left':el.getPosition().x+'px',
					'top':el.getPosition().y+'px'
				});
				img.set('morph',{duration:400,transition:'quint:in:out'});
				img.addEvent('click',function(){
					img.set('morph',{onComplete:function(){img.destroy();}}).morph({height:0,width:0});
				});
				img.inject(document.body);
				img.morph({height:size.y,width:size.x});
			}
		});
	}
	
});

