// ============================================
// PagadianStay - Main JavaScript
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ---- Navbar scroll effect ----
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.style.boxShadow = window.scrollY > 30
                ? '0 4px 30px rgba(0,0,0,0.35)'
                : '0 2px 20px rgba(0,0,0,0.25)';
        });
    }

    // ---- Hotel search filter ----
    const searchInput = document.getElementById('hotelSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('.hotel-card-wrapper').forEach(card => {
                const name = card.dataset.name?.toLowerCase() || '';
                const location = card.dataset.location?.toLowerCase() || '';
                card.style.display = (name.includes(query) || location.includes(query) || query === '') ? '' : 'none';
            });
        });
    }

    // ---- Price range filter ----
    const priceFilter = document.getElementById('priceFilter');
    if (priceFilter) {
        priceFilter.addEventListener('change', function () {
            filterRooms();
        });
    }

    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', function () {
            filterRooms();
        });
    }

    function filterRooms() {
        const priceVal = priceFilter ? priceFilter.value : 'all';
        const typeVal  = typeFilter ? typeFilter.value : 'all';

        document.querySelectorAll('.room-card-wrapper').forEach(card => {
            const price = parseFloat(card.dataset.price || 0);
            const type  = card.dataset.type?.toLowerCase() || '';
            let show = true;

            if (priceVal !== 'all') {
                const [min, max] = priceVal.split('-').map(Number);
                if (max) { show = show && (price >= min && price <= max); }
                else      { show = show && (price >= min); }
            }

            if (typeVal !== 'all') {
                show = show && type.includes(typeVal.toLowerCase());
            }

            card.style.display = show ? '' : 'none';
        });

        updateNoResultsMsg();
    }

    function updateNoResultsMsg() {
        const noResultsEl = document.getElementById('noRoomsMsg');
        if (!noResultsEl) return;
        const visible = document.querySelectorAll('.room-card-wrapper[style=""]').length
                      + document.querySelectorAll('.room-card-wrapper:not([style])').length;
        noResultsEl.style.display = visible === 0 ? '' : 'none';
    }

    // ---- Booking form: dynamic price calculation ----
    const checkIn   = document.getElementById('check_in');
    const checkOut  = document.getElementById('check_out');
    const pricePerNight = document.getElementById('pricePerNight');
    const totalDaysEl   = document.getElementById('totalDays');
    const totalPriceEl  = document.getElementById('totalPrice');
    const totalHiddenEl = document.getElementById('total_price');

    function calcPrice() {
        if (!checkIn || !checkOut || !pricePerNight) return;
        const d1 = new Date(checkIn.value);
        const d2 = new Date(checkOut.value);
        const nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        const price  = parseFloat(pricePerNight.value || 0);

        if (nights > 0 && price > 0) {
            const total = nights * price;
            if (totalDaysEl)   totalDaysEl.textContent   = nights + (nights === 1 ? ' night' : ' nights');
            if (totalPriceEl)  totalPriceEl.textContent  = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2});
            if (totalHiddenEl) totalHiddenEl.value        = total.toFixed(2);
        } else {
            if (totalDaysEl)   totalDaysEl.textContent   = '—';
            if (totalPriceEl)  totalPriceEl.textContent  = '—';
            if (totalHiddenEl) totalHiddenEl.value        = '';
        }
    }

    if (checkIn)  checkIn.addEventListener('change', calcPrice);
    if (checkOut) checkOut.addEventListener('change', calcPrice);
    calcPrice();

    // ---- Set min date for check-in to today ----
    const today = new Date().toISOString().split('T')[0];
    if (checkIn) {
        checkIn.min = today;
        checkIn.addEventListener('change', function () {
            if (checkOut) {
                checkOut.min = this.value;
                if (checkOut.value && checkOut.value <= this.value) {
                    checkOut.value = '';
                }
            }
        });
    }
    if (checkOut) checkOut.min = today;

    // ---- Booking form validation ----
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function (e) {
            let valid = true;

            // Name validation
            const name = document.getElementById('full_name');
            if (name && name.value.trim().length < 3) {
                name.classList.add('is-invalid');
                valid = false;
            } else if (name) { name.classList.remove('is-invalid'); }

            // Email validation
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email.value.trim())) {
                email.classList.add('is-invalid');
                valid = false;
            } else if (email) { email.classList.remove('is-invalid'); }

            // Phone validation
            const phone = document.getElementById('phone');
            const phoneRegex = /^[0-9+\-\s]{7,15}$/;
            if (phone && !phoneRegex.test(phone.value.trim())) {
                phone.classList.add('is-invalid');
                valid = false;
            } else if (phone) { phone.classList.remove('is-invalid'); }

            // Date validation
            if (checkIn && checkOut) {
                if (!checkIn.value) {
                    checkIn.classList.add('is-invalid');
                    valid = false;
                } else { checkIn.classList.remove('is-invalid'); }

                if (!checkOut.value) {
                    checkOut.classList.add('is-invalid');
                    valid = false;
                } else { checkOut.classList.remove('is-invalid'); }

                if (checkIn.value && checkOut.value && checkOut.value <= checkIn.value) {
                    checkOut.classList.add('is-invalid');
                    showAlert('Check-out date must be after check-in date.', 'danger');
                    valid = false;
                }
            }

            if (!valid) {
                e.preventDefault();
                showAlert('Please fix the errors in the form before submitting.', 'danger');
            }
        });
    }

    function showAlert(msg, type = 'danger') {
        let alertBox = document.getElementById('formAlertBox');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'formAlertBox';
            const form = document.getElementById('bookingForm');
            if (form) form.prepend(alertBox);
        }
        alertBox.className = `alert alert-${type} alert-dismissible fade show`;
        alertBox.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ---- Auto-dismiss alerts ----
    document.querySelectorAll('.auto-dismiss').forEach(el => {
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.5s ease';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

    // ---- Confirm delete ----
    document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ---- Image preview on upload ----
    document.querySelectorAll('.img-upload-input').forEach(input => {
        input.addEventListener('change', function () {
            const preview = document.getElementById(this.dataset.preview);
            if (!preview) return;
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(file);
            }
        });
    });

    // ---- Fade in up animations ----
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.stagger-1, .stagger-2, .stagger-3').forEach(el => {
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        el.style.transform = 'translateY(20px)';
        observer.observe(el);
    });

});
