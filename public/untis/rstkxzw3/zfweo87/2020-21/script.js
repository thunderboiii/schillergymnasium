

function init () {

	// Kurse: Anzeige bei 'andere Eingabe machen...' ändern:
	document.addEventListener('change', function (event) {

		var el = event.target;

		if (el.tagName === 'SELECT') { console.log(el);
			
			// nach ggf. existierenden Geschwister-Elementen "normal..."/"anders..."/"nicht..." suchen:
			var no = null; var an = null; var ni = null;
			var sibling = el.nextElementSibling;
			while (sibling) { console.log(sibling);
				if (sibling.matches('[id^="normal"]')) no = sibling;
				if (sibling.matches('[id^="anders"]')) an = sibling;
				if (sibling.matches('[id^="nicht"]' )) ni = sibling;
				sibling = sibling.nextElementSibling;
			}
			/* funktioniert leider nicht, weil Ebene nicht beschränkbar:
			var no = el.parentNode.children.querySelector('[id^="normal"]');
			var an = el.parentNode.children.querySelector('[id^="anders"]');
			var ni = el.parentNode.children.querySelector('[id^="nicht"]' ); */
			
			// nur, wenn es überhaupt etwas anzuzeigen gibt für "andere Eingabe" / "nicht":
			if (an || ni) {
				if (el.value === 'a') { // "andere Eingabe"
					if (no) no.style.display = 'none';
					if (an) an.style.display = 'inline-block';
					if (ni) ni.style.display = 'none';
				}
				else if (el.value === 'n') { // "nicht"
					if (no) no.style.display = 'none';
					if (an) an.style.display = 'none';
					if (ni) ni.style.display = 'inline-block';
				}
				else { // normal
					if (no) no.style.display = 'inline-block';
					if (an) an.style.display = 'none';
					if (ni) ni.style.display = 'none';
				}
			}
		}
	});
	
	// Elemente ein-/ausblenden abh. von Radio-Buttons:
	
	/* alt: einzeln: (spezifisch)

	// Klassenleitung: Anzeige bei 'Ja'/'Nein' ändern:
	var klassenein = document.getElementById('klassenein');
	var klasseja   = document.getElementById('klasseja');

	if (klassenein) klassenein.addEventListener('change', einausblenden);
	if (klasseja)	klasseja.  addEventListener('change', einausblenden);
	*/
	
	// neu (generisch):
	var toggles = document.querySelectorAll('[data-toggle]');
	[...toggles].forEach( (elt) => { elt.addEventListener('change', einausblenden); });
	
	var formular = document.getElementById('formular');
	if (formular) formular.addEventListener('submit', check_formular);

}

function check_formular (event) {
	var formular = event.target;
	if (formular) {
		var necessary_grouped = groupBy(formular.querySelectorAll('[data-necessary]'), 'name'); console.log(necessary_grouped);
		var ok = true;
		var not_ok = [];
		necessary_grouped.forEach ( (group) => {
		    var group_ok = false;
		    group.forEach ( (necessary) => {
		        if (necessary.checked) group_ok = true;
		    });
		    if (!group_ok) {
		        ok = false;
		        //group.forEach ( (item) => { not_ok.push(item); } );
		        not_ok.push(...group);
		    }
		});
		if (!ok) {
		    event.preventDefault();
		    // alert('nicht alles angeklickt');
		    console.log('nicht ok:', not_ok);
		    highlight(not_ok);
			// und Fenster zum ersten der nicht-ok-Elemente scrollen, und zwar zum Rahmen, in dem das liegt:
			var rahmen = containing('.rahmen', not_ok[0]);
			if (rahmen) window.scrollTo(0, offset(rahmen).y);
			console.log('scrollTo: ',	   offset(rahmen).y);
		}
	}
}

function groupBy(collection, property) {
    var i = 0, val, index,
        values = [], result = [];
    for (; i < collection.length; i++) {
        val = collection[i][property];
        index = values.indexOf(val);
        if (index > -1)
            result[index].push(collection[i]);
        else {
            values.push(val);
            result.push([collection[i]]);
        }
    }
    return result;
}

function highlight(list) {
    [...list].forEach( (item) => {
        var visible_element = containing('label', item);
        if (visible_element) {
			var i = 0;
			if (visible_element.classList.contains('highlighted')) {
				visible_element.classList.remove('highlighted'); i++;
			}
            setTimeout( () => { visible_element.classList.add	('highlighted'); }, i*160 + 1); i++;
			setTimeout( () => { visible_element.classList.remove('highlighted'); }, i*160 + 1); i++;
			setTimeout( () => { visible_element.classList.add	('highlighted'); }, i*160 + 1);
            item.onchange = () => { dehighlight (item) };
        }
    });
}

function dehighlight(item) {
    //var item = event.target;
    if (item) { console.log('item, das de_highlighten auslöst:', item);
        var list = document.querySelectorAll('[data-necessary][name='+item.name+']');
        [...list].forEach ((list_item) => {
            var visible_element = containing('label', list_item);
            if (visible_element) {
                visible_element.classList.remove('highlighted');
            }
        });
    }
}

function containing(selector, el) {
    while (!el.matches(selector) && el.tagName !== 'HTML' && el !== null)  el = el.parentElement;
    if (el.matches(selector)) return el;
}

function reagieren(nr) {
		var x = document.getElementsByClassName('häufigkeit')[nr].value;
		if (x == 'a')		{	document.getElementById('normal'+nr).style.display = 'none';
								document.getElementById('anders'+nr).style.display = 'inline-block';
								document.getElementById('nicht' +nr).style.display = 'none';			}
		else if (x == 'n')	{ 	document.getElementById('normal'+nr).style.display = 'none';
								document.getElementById('anders'+nr).style.display = 'none';
								document.getElementById('nicht' +nr).style.display = 'inline-block';	}
		else				{ 	document.getElementById('normal'+nr).style.display = 'inline-block';
								document.getElementById('anders'+nr).style.display = 'none';
								document.getElementById('nicht' +nr).style.display = 'none';		}	
}

function einausblenden(event) {
	var elt = event.target;
	if (elt) {		
		var new_status = (elt.dataset.toggle === 'on') ? 'block' : 'none';
		var name = elt.name;
		if (name) {
			var dependancies = document.querySelectorAll('[data-dependancy='+name+']');
			[...dependancies].forEach( (dependancy) => { dependancy.style.display = new_status; } );
		}
	}
}


function offset(el) {
	var rect = el.getBoundingClientRect(),
	scrollLeft = window.scrollX || window.pageXOffset || document.documentElement.scrollLeft,
	scrollTop  = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
	return { x: rect.left + scrollLeft, y: rect.top + scrollTop }
}
