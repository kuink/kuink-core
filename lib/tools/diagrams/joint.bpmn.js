(function(global){	// BEGIN CLOSURE

var Joint = global.Joint,
     Element = Joint.dia.Element,
     point = Joint.point;

/**
 * @name Joint.dia.bpmn
 * @namespace Holds functionality related to BPMN diagrams.
 */
var bpmn = Joint.dia.bpmn = {};

/**
 * Predefined arrow. You are free to use this arrow as the option parameter to joint method.
 * @name arrow
 * @memberOf Joint.bpmn
 * @example
 * var arrow = Joint.dia.bpmn.arrow;
 * s1.joint(s2, (arrow.label = "anEvent", arrow));
 */
bpmn.arrow = {
    startArrow: {type: "none"},
    endArrow: {type: "basic", size: 5},
    attrs: {"stroke-dasharray": "none"}
};

/**
 * Finite state machine state.
 * @name State.create
 * @methodOf Joint.dia.bpmn
 * @param {Object} properties
 * @param {Object} properties.position Position of the State (e.g. {x: 50, y: 100}).
 * @param {Number} [properties.radius] Radius of the circle of the state.
 * @param {String} [properties.label] The name of the state.
 * @param {Number} [properties.labelOffsetX] Offset in x-axis of the label from the state circle origin.
 * @param {Number} [properties.labelOffsetY] Offset in y-axis of the label from the state circle origin.
 * @param {Object} [properties.attrs] SVG attributes of the appearance of the state.
 * @example
var s1 = Joint.dia.bpmn.State.create({
  position: {x: 120, y: 70},
  label: "state 1",
  radius: 40,
  attrs: {
    stroke: "blue",
    fill: "yellow"
  }
});
 */
bpmn.Activity = Element.extend({
    object: "Ativity",
    module: "bpmn",
    init: function(properties){
	// options
	var p = Joint.DeepSupplement(this.properties, properties, {
            position: point(0,0),
            //radius: 30,
            	radius: 8,
            	width: 100,
            	height: 75,
            //label: 'hhhh',
            labelOffsetX: 30/2,
            labelOffsetY: 30/2 + 8,
            //attrs: { fill: '45-#999999-#eeeeee', stroke:'#777777', glow: {color: "#0000dd", width: 20} }
            //attrs: { fill: '45-#0066ff-#00ddff', stroke:'#0099ff', 'stroke-width': 2 },
            attrs: { fill: '45-#99ff00-#ddff00', stroke:'#0099ff', 'stroke-width': 2 },
            shadow: true
        });
	// wrapper
	var rect = this.paper.rect(p.position.x, p.position.y, p.width, p.height, p.radius);
	rect.attr(p.attrs).mouseup( function(){ report( p.name, p.name, p.label ); } );

	//rect.attr().mouseup( function(){ report( p.name, p.name, p.label ); } );
	
	//rect.glow({color: "#0000dd", width: 20});
	
	this.setWrapper( rect );
	//this.setWrapper(this.paper.rect(p.position.x, p.position.y, p.width, p.height, p.radius).attr(p.attrs));
	//this.setWrapper(this.paper.circle(p.position.x, p.position.y, p.radius).attr(p.attrs));
	// inner
	this.addInner(this.getLabelElement());
    },
    getLabelElement: function(){
	var
	p = this.properties,
	bb = this.wrapper.getBBox(),
	t = this.paper.text(bb.x, bb.y, p.label),
	tbb = t.getBBox();
	t.translate(bb.x - tbb.x + p.labelOffsetX,
		    bb.y - tbb.y + p.labelOffsetY);
	return t;
    },
    zoom: function(){
     this.inner[0].remove();
     this.inner[0] = this.getLabelElement();
   	 //this.inner[0].scale.apply(this.inner[0], arguments);
    }
    
});

/**
 * Finite state machine start state.
 * @name StartState.create
 * @methodOf Joint.dia.bpmn
 * @param {Object} properties
 * @param {Object} properties.position Position of the start state (e.g. {x: 50, y: 100}).
 * @param {Number} [properties.radius] Radius of the circle of the start state.
 * @param {Object} [properties.attrs] SVG attributes of the appearance of the start state.
 * @example
var s0 = Joint.dia.bpmn.StartState.create({
  position: {x: 120, y: 70},
  radius: 15,
  attrs: {
    stroke: "blue",
    fill: "yellow"
  }
});
 */
bpmn.StartEvent = Element.extend({
     object: "StartEvent",
     module: "bpmn",
     init: function(properties){
	 // options
         var p = Joint.DeepSupplement(this.properties, properties, {
             position: point(0,0),
             radius: 10,
             attrs: { fill: 'black' }
         });
	 // wrapper
	 this.setWrapper(this.paper.circle(p.position.x, p.position.y, p.radius).attr(p.attrs));
     },
     zoom: function(){
    	 this.inner[0].scale.apply(this.inner[0], arguments);
     }

});

/**
 * Finite state machine end state.
 * @name EndEvent.create
 * @methodOf Joint.dia.bpmn
 * @param {Object} properties
 * @param {Object} properties.position Position of the end state (e.g. {x: 50, y: 100}).
 * @param {Number} [properties.radius] Radius of the circle of the end state.
 * @param {Number} [properties.innerRadius] Radius of the inner circle of the end state.
 * @param {Object} [properties.attrs] SVG attributes of the appearance of the end state.
 * @param {Object} [properties.innerAttrs] SVG attributes of the appearance of the inner circle of the end state.
 * @example
var s0 = Joint.dia.bpmn.EndEvent.create({
  position: {x: 120, y: 70},
  radius: 15,
  innerRadius: 8,
  attrs: {
    stroke: "blue",
    fill: "yellow"
  },
  innerAttrs: {
    fill: "red"
  }
});
 */
bpmn.EndEvent = Element.extend({
     object: "EndEvent",
     module: "bpmn",
     init: function(properties){
	 // options
	 var p = Joint.DeepSupplement(this.properties, properties, {
             position: point(0,0),
             radius: 10,
             innerRadius: (properties.radius && (properties.radius / 2)) || 5,
             attrs: { fill: 'white' },
             innerAttrs: { fill: 'black' }
         });
	 // wrapper
	 this.setWrapper(this.paper.circle(p.position.x, p.position.y, p.radius).attr(p.attrs));
	 // inner
	 this.addInner(this.paper.circle(p.position.x, p.position.y, p.innerRadius).attr(p.innerAttrs));
     },
     zoom: function(){
    	 this.inner[0].scale.apply(this.inner[0], arguments);
     }
});

})(this);	// END CLOSURE


function report(c_id, euid, nodeValue) {
    var form = document.createElement("form", "report_form");
    form.id = "report_form";
    form.method = "post";
    form.action = "index.php?mode=post_comment";

    var reply_place = document.createElement("div");
    reply_place.id = "overlay";
    var inner_div = document.createElement("div"), button_close = document.createElement("button");
    button_close.id = "upprev_close";
    button_close.innerHTML = "x";
    button_close.onclick = function () {
        var element = document.getElementById('overlay');
        element.parentNode.removeChild(element);
    };
    inner_div.appendChild(button_close);

    var legend = document.createElement("legend");
    legend.innerHTML = "Why do you want to report this?";
    form.appendChild(legend);

    var hidden1 = document.createElement("input");
    hidden1.type = "text";
    hidden1.id = "euid";
    hidden1.name = "euid";
    hidden1.value = euid;
    form.appendChild(hidden1);
    
    var input1 = document.createElement("input");
    input1.type = "text";
    input1.id = "label";
    input1.value = nodeValue;
    input1.name = "label";
    form.appendChild(input1);

    var submit_btn = document.createElement("input", "the_submit");
    submit_btn.type = "button";
    submit_btn.className = "submit";
    submit_btn.value = "Report";
    form.appendChild(submit_btn);

    submit_btn.onclick = function () {
    	var elements = Joint.dia.registeredElements();
		for( var element in elements) {
	    	//console.log( elements[element].properties.name );
	    		
			if (elements[element].properties.name == document.getElementById('euid').value) {
				elements[element].properties.label = document.getElementById('label').value;
				elements[element].zoom( 1 );
				//console.log( elements[element] );
		        //alert (document.getElementById('euid').value + '::' + document.getElementById('label').value );
				//console.log( element.euid );
			}
		}
        return (false);
    }

    inner_div.appendChild(form);
    reply_place.appendChild(inner_div);

    var attach_to = document.getElementById("wrapper"), parentDiv = attach_to.parentNode;
    parentDiv.insertBefore(reply_place, attach_to);
}


/*
remove joint

function removeJoint(j) { 
   j.freeJoint(j.startObject()); 
   j.freeJoint(j.endObject()); 
   j.clean(["connection", "startCap", "endCap", "handleStart", 
"handleEnd", "label"]); 
   dia.unregisterJoint(j); 
} 

*/

/*

Events in joints
https://groups.google.com/forum/?fromgroups=#!topic/jointjs/VkNJx_Co7rU

*/