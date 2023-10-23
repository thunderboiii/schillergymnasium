const images = document.getElementsByClassName("fachschaftImage");
const imgContainer = document.getElementById("fachschaft-gallery");

const imageLength = images.length;

if(length % 3 === 0) {
    imgContainer.style.width = "calc((2 * 1rem) + (3* 145px))";
} else if (length % 4 === 0) {
    imgContainer.style.width = "calc((3 * 1rem) + (4* 145px))";
} else if (length % 5 === 0) {
    imgContainer.style.width = "calc((4 * 1rem) + (5* 145px)) !important";
} else {
    return; 
}