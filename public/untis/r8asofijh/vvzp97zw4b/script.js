// globale Variablen:
var iframe_ids = [];
var timeouts = {};
var timing = {
				wait_before: 800,
				ms_per_move: 20,
				wait_after:  2400,
				min_time_overall: 5000
			 };

function init() {
	
	cleartimeouts();
	
	var srcElements = document.querySelectorAll('[data-src]');
	
	var el;// src_href, src_html;
	//var xmlhttp = [];
	
	for (var i = 0; i < srcElements.length; i++) {
		
		el = srcElements[i];
		
		refresh_iframe(el);
				
	}
	
	window.onresize = init;
	
}

function refresh_iframe (el) {
	
	var src_href = el.dataset.src;
	if (el.dataset.next !== undefined) src_href = el.dataset.folder + '/' + el.dataset.next;

	var xmlhttp = new XMLHttpRequest();

	xmlhttp.onreadystatechange = function(elt, xml) {
		
		return function () {
				
				if (xml.readyState==4 && xml.status==200) {
				
				// DOM from response-String:
				var response = new DOMParser().parseFromString(xml.responseText, 'text/html');
				
				// manipulate DOM: evaluate and delete meta-refresh-tag
				var refr_tag = response.querySelector('meta[http-equiv="refresh"]');
					// evaluate:
				var next			= refr_tag.content.split('=')[1];
				var path			= elt.dataset.src.split('/');
				var current 		= path.pop(); // .slice(-1)[0]; if one doesn't want the array to be changed
				var folder			= path.join('/');
				elt.dataset.next	= next;
				elt.dataset.current	= current;
				elt.dataset.folder	= folder;
					// remove:
				refr_tag.parentElement.removeChild(refr_tag);
				
				// response-DOM back to string:
				response = new XMLSerializer().serializeToString(response);
				
				// prepare iframe-event-handling:
				// if (!elt.onload) elt.onload = autoscroll; => if nicht nötig, weil nicht 'addEventListener' (dann käme jedes Mal ein weiterer hinzu), sondern 'on_event = ...' => überschreibt jedes Mal => bleibt unterm Strich 1 Listener
				elt.onload = autoscroll;

				// insert into iframe:
				elt.contentWindow.document.open();
				elt.contentWindow.document.write(response);
				elt.contentWindow.document.close();
				
			}
			
		}
		
	}(el, xmlhttp);

	xmlhttp.open('GET', src_href);
	
	xmlhttp.send(); // .send(params);
	
}

function autoscroll (event) {
	
	var iframe = event.target;
	var id = iframe.id;
	if (iframe_ids.indexOf(id) === -1)	iframe_ids.push(id);
	if (!(id in timeouts))				timeouts[id] = [];
	cleartimeouts(id);
	
	if (iframe.contentDocument) {
	
		var container_height	= iframe.contentDocument.body.clientHeight;
		var content_height		= iframe.contentDocument.body.scrollHeight;
		
		// Muss gescrollt werden?
		var overflow = content_height - container_height;
		var rest = overflow;
		
		// Scrollen:
		while (rest > 0) {
			
			rest = rest - 1;
			let scroll = overflow - rest;
			
			timeouts[id].push(setTimeout( () => iframe.contentWindow.scrollTo(0,scroll), timing.wait_before + scroll*(timing.ms_per_move+iframe_ids.indexOf(id)*4)));
			
		}
		
		// Danach: Refreshen
		var time_until_refresh = timing.wait_before + overflow*(timing.ms_per_move+iframe_ids.indexOf(id)*4) + timing.wait_after;
		if (time_until_refresh < timing.min_time_overall) time_until_refresh = timing.min_time_overall;
		timeouts[id].push(setTimeout( () => refresh_iframe(iframe), time_until_refresh));
		
	}

}


function cleartimeouts(id = true) {
	if (id === true) var arr = iframe_ids;
	else			 var arr = [id];
	
	arr.forEach( id => {
		while (timeouts[id].length > 0) {
			clearTimeout(timeouts[id].pop());
		}
	});
}

// Response-Text als eigenes DOM-Array interpretieren:
// alt:
function htmlToElements(html) {
	var template = document.createElement('template');
	html = html.trim();
	template.innerHTML = html;
	return template.content;
}
// NEU:
function html2DOM(html_string) {
	return new DOMParser().parseFromString(html_string, 'text/html');
}
function DOM2html(doc) {
	return new XMLSerializer().serializeToString(doc);
}
