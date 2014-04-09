(function(list){
// JSmile for jQuery - Andrea Giammarchi [webreflection.blogspot.com] - V0.4
jQuery.fn.extend({
	smile: (function(){
		function smile(show){
			show = !arguments.length || !!show;
			this.each(function(i, firstChild){
				jQuery.each(firstChild.childNodes, function(i, firstChild){
					switch(firstChild.nodeType){
						case	1:
							show ? jQuery(firstChild).smile(show) : smile.remove(firstChild);
							break;
						case	3:
							if(show && !smile.nodeName[firstChild.parentNode.nodeName.toLowerCase()])
								smile.add(firstChild);
							break;
					}
				});
			});
			return	this;
		};
		smile.add	= function(firstChild){
			var	parentNode	= firstChild.parentNode,
				nodeValue	= firstChild.nodeValue,
				i           = 0,
				length		= 0,
				img;
			nodeValue.replace(
				smile.RegExp,
				function(createTextNode, pos){
					if((++length <= smile.max||smile.max == 0) && list[createTextNode]){
                        if(smile.list[createTextNode])
                            img = smile.list[createTextNode].cloneNode(true);
                        else {
    						img             = new Image();
    						img.className   = smile.className;
    						img.src         = URL+"view/default/img/smile/" + list[createTextNode];
                            img.alt         = img.title = createTextNode;
                            smile.list[createTextNode]  = img;
                        };
						jQuery(firstChild).before(document.createTextNode(nodeValue.substring(i, pos))).before(img);
						i   = pos + createTextNode.length;
					}
				}
			);
			if(i)
				jQuery(firstChild).before(document.createTextNode(nodeValue.substring(i))).remove();
		};
		smile.remove	= function(firstChild){
			jQuery(firstChild).find("img." + smile.className).each(function(i, firstChild){
				jQuery(firstChild).replaceWith(document.createTextNode(firstChild.alt || firstChild.title));
			});
		};
        smile.max       = 0;
        smile.list      = {};
        smile.className = "jsmile";
        smile.nodeName  = {"code":1,"noscript":1,"pre":1,"script":1,"style":1};
        smile.RegExp    = /:sorry:|:despise:|:rage:|:laugh:|:happy:|:shy:|:sleep:|:grin:|:scared:|:pleasant:|:cry:|:puzzled:|:bother:|:strive:|:amative:|:angry:|:pain:|:sad:|:faint:|:impatient:/g;
        return  smile;
    })()
});
})({":sorry:":"mantou/sorry.png",":despise:":"mantou/despise.png",":rage:":"mantou/rage.png",":laugh:":"mantou/laugh.png",":happy:":"mantou/happy.png",":shy:":"mantou/shy.png",":sleep:":"mantou/sleep.png",":grin:":"mantou/grin.png",":scared:":"mantou/scared.png",":pleasant:":"mantou/pleasant.png",":cry:":"mantou/cry.png",":puzzled:":"mantou/puzzled.png",":bother:":"mantou/bother.png",":strive:":"mantou/strive.png",":amative:":"mantou/amative.png",":angry:":"mantou/angry.png",":pain:":"mantou/pain.png",":sad:":"mantou/sad.png",":faint:":"mantou/faint.png",":impatient:":"mantou/impatient.png"});
