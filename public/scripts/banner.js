var slideIndex = 0;
carousel();

function carousel() {
    var i;
    var x = document.getElementsByClassName("background");

    for (i = 0; i < x.length; i++ ) {
        x[i].style.opacity = "0";
    }
    slideIndex++;

    if(slideIndex > x.length) {slideIndex = 1}
    x[slideIndex-1].style.opacity = "1";

    setTimeout(carousel, 5000);
}