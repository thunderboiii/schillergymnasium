

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
	
	// Klassenleitung: Anzeige bei 'Ja'/'Nein' ändern:
	var klassenein = document.getElementById('klassenein');
	var klasseja   = document.getElementById('klasseja');

	if (klassenein) klassenein.addEventListener('change', einausblenden);
	if (klasseja)	klasseja.addEventListener(	'change', einausblenden);	

}

function reagieren(nr) {
		var x = document.getElementsByClassName('häufigkeit')[nr].value;
		if (x == 'a')		{	document.getElementById('normal'+nr).style.display = 'none';
								document.getElementById('anders'+nr).style.display = 'inline-block';
								document.getElementById('nicht'+nr).style.display = 'none';			}
		else if (x == 'n')	{ 	document.getElementById('normal'+nr).style.display = 'none';
								document.getElementById('anders'+nr).style.display = 'none';
								document.getElementById('nicht'+nr).style.display = 'inline-block';	}
		else				{ 	document.getElementById('normal'+nr).style.display = 'inline-block';
								document.getElementById('anders'+nr).style.display = 'none';
								document.getElementById('nicht'+nr).style.display = 'none';		}	
}



function einausblenden() {
	var y = document.getElementById('klassenein').checked;
	var z = document.getElementById('klasseja').checked;
//						alert ('y = ' + y + ', z = ' + z);
	if (z == true)				{	document.getElementById('klassenleitung').style.display = 'block';	}
	else						{ 	document.getElementById('klassenleitung').style.display = 'none';	}
}