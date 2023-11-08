const scrollGroups = document.querySelectorAll('.scroll-group');

scrollGroups.forEach(scrollGroup => {
    const btnL = scrollGroup.getElementsByClassName('btn-left')[0];
    const btnR = scrollGroup.getElementsByClassName('btn-right')[0];

    const scroll = scrollGroup.getElementsByClassName('scroll')[0];

    btnL.addEventListener('click', () => {
        scroll.scrollLeft += -964;
    });

    btnR.addEventListener('click', () => {
        scroll.scrollLeft += 964;
    });
})