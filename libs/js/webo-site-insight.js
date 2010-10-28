function initJS() {

    var titleExpandCollapse = _('.wsi-group-title');
    for(var i = 0; i < titleExpandCollapse.length; i++) {
        addEvent(titleExpandCollapse[i], 'click', function(e){
			parentGroup = this.parentNode;
			if(parentGroup.className.search('wsi-group-expanded') != -1) {
				parentGroup.className = parentGroup.className.replace(' wsi-group-expanded','') + ' wsi-group-collapsed';
			}
			else if(parentGroup.className.search('wsi-group-collapsed') != -1) {
				parentGroup.className = parentGroup.className.replace(' wsi-group-collapsed','') + ' wsi-group-expanded';
			}
            noDefaultAction(e);
        });
    }

    var buttonActivateWidget = _('.wsi-widgets-short .wsi-widget');
    for(i = 0; i < buttonActivateWidget.length; i++) {
        addEvent(buttonActivateWidget[i], 'click', function(e){
            /* click at active widget does nothing */
            if(this.className.search('wsi-widget-active') != -1) {
                return;
            }

            widgetName = this.className.replace('wsi-widget ','');

            /* deactivating old active widgets */
            deactivateWidgets = _('.wsi-widget');
            for(j = 0; j < deactivateWidgets.length; j++) {
                deactivateWidgets[j].className = deactivateWidgets[j].className.replace(' wsi-widget-active','');
            }

            /* activating new widget */
            this.className += ' wsi-widget-active';
            widgetContent = _('.wsi-group-expanded .wsi-widgets-detailed' + ' .' + widgetName);
            if(widgetContent.length) {
                widgetContent[0].className += ' wsi-widget-active';
            }

            /* do we need to collapse? */
            if(this.parentNode.parentNode.className.search('wsi-group-collapsed') != -1) {
				this.parentNode.parentNode.className = this.parentNode.parentNode.className.replace(' wsi-group-collapsed','') + ' wsi-group-expanded';
			}
        });
    }

}

function setCookie (id, status) {
    _.doc.cookie='b'+id+'wp='+status+';expires='+(new Date(new Date().getTime()+30000000000)).toGMTString()+';path=/';
}

function getCookie (id) {
    var cookie = _.doc.cookie,
	key = 'b' + id + 'wp=',
	item = cookie.indexOf(key),
	length = key.length;
    if (item != -1) {
	    return cookie.substr(item + length, 1).charAt(0) === '1';
    } else {
	    return 0;
    }
}

function noDefaultAction(e) {
    e = e || _.win.event;
    if (e.preventDefault) {
        e.preventDefault();
    } else {
        e.returnValue = false;
    }
    return false;
}

function noPropogation(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    } else {
        e.cancelBubble = true;
    }
    return false;
}

function addEvent(element, type, handler) {
  // assign each event handler a unique ID
  if (!handler.$$guid) handler.$$guid = addEvent.guid++;
  // create a hash table of event types for the element
  if (!element.events) element.events = {};
  // create a hash table of event handlers for each element/event pair
  var handlers = element.events[type];
  if (!handlers) {
    handlers = element.events[type] = {};
    // store the existing event handler (if there is one)
    if (element["on" + type]) {
      handlers[0] = element["on" + type];
    }
  }
  // store the event handler in the hash table
  handlers[handler.$$guid] = handler;
  // assign a global event handler to do all the work
  element["on" + type] = handleEvent;
};
// a counter used to create unique IDs
addEvent.guid = 1;

function removeEvent(element, type, handler) {
  // delete the event handler from the hash table
  if (element.events && element.events[type]) {
    delete element.events[type][handler.$$guid];
  }
};

function handleEvent(event) {
  // grab the event object (IE uses a global event object)
  event = event || window.event;
  // get a reference to the hash table of event handlers
  var handlers = this.events[event.type];
  // execute each event handler
  for (var i in handlers) {
    this.$$handleEvent = handlers[i];
    this.$$handleEvent(event);
  }
};

_.ready(initJS);