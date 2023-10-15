const images = document.getElementsByClassName("fachschaftImage");
const imgContainer = document.getElementById("fachschaft-gallery");

const imageLength = images.length;

if(length % 3 === 0) {
    imgContainer.classList.add("3");
} else if (length % 4 === 0) {

} else if (length % 5 === 0) {

} else {
    return; 
}