// SLIDESHOW
function wrapConsecutiveSlides() {
    const slideElements = document.querySelectorAll('.slide');
    let consecutiveSlides = [];

    slideElements.forEach((slide, index) => {
        if (index === 0 || slide.previousElementSibling !== slideElements[index - 1]) {
            // Start a new group of consecutive slides
            consecutiveSlides = [slide];
        } else {
            // Continue the group of consecutive slides
            consecutiveSlides.push(slide);
        }

        if (index === slideElements.length - 1 || slide.nextElementSibling !== slideElements[index + 1]) {
            // Wrap consecutive slides in a div when reaching the end of the group
            if (consecutiveSlides.length >= 2) {
                const wrapperDiv = document.createElement('div');
                wrapperDiv.classList.add('slide-wrapper');

                // Insert the wrapper before the first element of the group
                consecutiveSlides[0].parentNode.insertBefore(wrapperDiv, consecutiveSlides[0]);

                // Move consecutive slides into the wrapper
                consecutiveSlides.forEach(slide => wrapperDiv.appendChild(slide));
            }
        }
    });
}

function startAutomaticSlideshow() {
    const slideWrappers = document.querySelectorAll('.slide-wrapper');

    slideWrappers.forEach(wrapper => {
        const slides = wrapper.querySelectorAll('.slide');
        let currentSlideIndex = 0;

        function showNextSlide() {
            slides.forEach(slide => slide.style.display = 'none');
            slides[currentSlideIndex].style.display = 'block';
            currentSlideIndex = (currentSlideIndex + 1) % slides.length;
        }

        // Initially hide all slides except the first one
        slides.forEach((slide, index) => {
            slide.style.display = index === 0 ? 'block' : 'none';
        });

        // Set up automatic slideshow with a 2-second interval
        setInterval(showNextSlide, 2000);
    });
}

// GALLERY

function wrapConsecutiveGallery() {
    const galleryElements = document.querySelectorAll('.gallery');
    let consecutiveGallery = [];

    galleryElements.forEach((gallery, index) => {
        if (index === 0 || gallery.previousElementSibling !== galleryElements[index - 1]) {
            // Start a new group of consecutive slides
            consecutiveGallery = [gallery];
        } else {
            // Continue the group of consecutive slides
            consecutiveGallery.push(gallery);
        }

        if (index === galleryElements.length - 1 || gallery.nextElementSibling !== galleryElements[index + 1]) {
            // Wrap consecutive slides in a div when reaching the end of the group
            if (consecutiveGallery.length >= 2) {
                const wrapperDiv = document.createElement('div');
                wrapperDiv.classList.add('gallery-wrapper');

                // Insert the wrapper before the first element of the group
                consecutiveGallery[0].parentNode.insertBefore(wrapperDiv, consecutiveGallery[0]);

                // Move consecutive slides into the wrapper
                consecutiveGallery.forEach(slide => wrapperDiv.appendChild(slide));
            }
        }
    });
}

// Call the function to wrap consecutive slides
wrapConsecutiveSlides();

// Call the function to start the automatic slideshow
startAutomaticSlideshow();

wrapConsecutiveGallery();