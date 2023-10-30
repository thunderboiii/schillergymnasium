

function init () {

	// Kurse: Anzeige bei 'andere Eingabe machen...' ändern:
	var elems = document.getElementsByClassName('häufigkeit'); console.log(elems);
	for (i = 0; i < elems.length; i++)	{
		var elem = elems[i];
		elem.addEventListener('change', function(index) { return function() { reagieren(index); } }(i) );
	}
	
	// Klassenleitung: Anzeige bei 'Ja'/'Nein' ändern:
	var klassenein = document.getElementById('klassenein');
	var klasseja   = document.getElementById('klasseja');

	klassenein.addEventListener('change', einausblenden);
	  klasseja.addEventListener('change', einausblenden);	

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