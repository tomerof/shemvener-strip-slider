jQuery(document).on('ready', function() {
    const track = document.querySelector('.shemvener-slider-track');
    if (!track) return;

    let isDown = false;
    let startX;
    let scrollLeft;

    // This is the "Gatekeeper" variable
    let isUserInteracting = false;

    // --- MOUSE DRAG EVENTS ---
    track.addEventListener('mousedown', (e) => {
        isDown = true;
        isUserInteracting = true; // Block auto-scroll immediately
        track.classList.add('is-dragging');
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    });

    track.addEventListener('mouseup', () => {
        isDown = false;
        // Small delay before resuming auto-scroll so it doesn't jump instantly
        setTimeout(() => { isUserInteracting = false; }, 1000);
        track.classList.remove('is-dragging');
    });

    track.addEventListener('mouseleave', () => {
        isDown = false;
        isUserInteracting = false;
        track.classList.remove('is-dragging');
    });

    track.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 2;
        track.scrollLeft = scrollLeft - walk;
    });

    // --- PAUSE ON HOVER ---
    track.addEventListener('mouseenter', () => isUserInteracting = true);
    track.addEventListener('mouseleave', () => isUserInteracting = false);

    // --- AUTO-SCROLL LOGIC ---
    setInterval(() => {
        // Only scroll if the user IS NOT dragging and IS NOT hovering
        if (track && !isUserInteracting) {
            const maxScroll = track.scrollWidth - track.clientWidth;

            // RTL: If we are at the end (scrollLeft is negative and matches max)
            if (Math.abs(track.scrollLeft) >= (maxScroll - 10)) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: -250, behavior: 'smooth' });
            }
        }
    }, 3000);
});