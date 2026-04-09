jQuery(document).on('ready', function() {
    const track = document.querySelector('.shemvener-slider-track');
    if (!track) return;

    let isDown = false;
    let startX;
    let scrollLeft;
    let moved = false;

    // This is the "Gatekeeper" variable
    let isUserInteracting = false;

    // --- MOUSE DRAG EVENTS ---
    track.addEventListener('mousedown', (e) => {
        isDown = true;
        moved = false;
        isUserInteracting = true; // Block auto-scroll immediately
        track.classList.add('is-dragging');
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    });

    // Prevent navigation if a drag happened
    track.addEventListener('click', (e) => {
        if (moved) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // Prevent native dragging for links/images inside the track
    track.addEventListener('dragstart', (e) => {
        e.preventDefault();
    });

    track.addEventListener('mouseup', () => {
        isDown = false;
        track.classList.remove('is-dragging');
        // Small delay before resuming auto-scroll so it doesn't jump instantly
        setTimeout(updateInteracting, 1000);
    });


    track.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        
        // If movement exceeds a small threshold, mark it as a drag
        if (Math.abs(x - startX) > 5) {
            moved = true;
        }

        const walk = (x - startX) * 2;
        track.scrollLeft = scrollLeft - walk;
    });

    // --- TOUCH DRAG EVENTS ---
    track.addEventListener('touchstart', (e) => {
        isDown = true;
        moved = false;
        isUserInteracting = true;
        track.classList.add('is-dragging');
        startX = e.touches[0].pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    }, { passive: true });

    track.addEventListener('touchmove', (e) => {
        if (!isDown) return;
        const x = e.touches[0].pageX - track.offsetLeft;
        if (Math.abs(x - startX) > 5) {
            moved = true;
        }
    }, { passive: true });

    track.addEventListener('touchend', () => {
        isDown = false;
        track.classList.remove('is-dragging');
        setTimeout(updateInteracting, 1000);
    }, { passive: true });

    track.addEventListener('touchcancel', () => {
        isDown = false;
        track.classList.remove('is-dragging');
        setTimeout(updateInteracting, 1000);
    }, { passive: true });

    // --- PAUSE ON INTERACTION ---
    const updateInteracting = () => {
        isUserInteracting = isDown || track.matches(':hover') || track.contains(document.activeElement);
    };

    track.addEventListener('mouseenter', updateInteracting);
    track.addEventListener('mouseleave', () => {
        isDown = false;
        track.classList.remove('is-dragging');
        updateInteracting();
    });

    track.addEventListener('focusin', updateInteracting);
    track.addEventListener('focusout', () => setTimeout(updateInteracting, 50));

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