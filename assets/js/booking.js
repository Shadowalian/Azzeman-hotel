// Booking Modals JavaScript
// Rebuilt to match chatbot pattern - simple and reliable

let currentModal = null;

// Get base path - same pattern as chatbot
function getBookingBasePath() {
    // Try global override first
    if (typeof window !== 'undefined' && typeof window.APP_BASE_PATH === 'string') {
        return window.APP_BASE_PATH;
    }
    // Try to get from chatbot button (if it exists) - they share the same base path
    const chatbotBtn = document.getElementById('chatbotToggleBtn');
    if (chatbotBtn && chatbotBtn.dataset.basePath) {
        return chatbotBtn.dataset.basePath;
    }
    
    // Fallback: calculate from pathname (same as chatbot)
    const pathname = window.location.pathname;
    if (pathname.includes('/azzemanhotel_PHP')) {
        return '/azzemanhotel_PHP';
    } else {
        const pathParts = pathname.split('/').filter(p => p && p !== 'index.php');
        if (pathParts.length > 0) {
            const lastPart = pathParts[pathParts.length - 1];
            if (lastPart.includes('.')) {
                pathParts.pop();
            }
        }
        return pathParts.length > 0 ? '/' + pathParts.join('/') : '';
    }
}

function normalizeBasePath(rawPath) {
    if (!rawPath) return '';
    let base = rawPath.trim();
    if (base === '.') base = '';
    base = base.replace(/^https?:\/\/[^/]+/i, '');
    base = base.replace(/\/+/g, '/');
    base = base.replace(/\/?public$/i, '');
    base = base.replace(/\/$/, '');
    if (base && !base.startsWith('/')) {
        base = '/' + base;
    }
    return base;
}

// Open room booking modal
function openBookingModal() {
    closeCurrentModal();
    currentModal = createBookingModal('room');
    document.body.appendChild(currentModal);
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        currentModal.classList.add('modal-open');
    }, 10);
}

// Open spa booking modal
function openSpaBookingModal() {
    closeCurrentModal();
    currentModal = createBookingModal('spa');
    document.body.appendChild(currentModal);
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        currentModal.classList.add('modal-open');
    }, 10);
}

// Open meeting booking modal
function openMeetingBookingModal() {
    closeCurrentModal();
    currentModal = createBookingModal('meeting');
    document.body.appendChild(currentModal);
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        currentModal.classList.add('modal-open');
    }, 10);
}

// Close current modal
function closeCurrentModal() {
    if (currentModal) {
        currentModal.classList.remove('modal-open');
        setTimeout(() => {
            if (currentModal && currentModal.parentNode) {
                currentModal.remove();
            }
            currentModal = null;
            document.body.style.overflow = '';
        }, 300);
    }
}

// Cached config data
let roomsData = [];
let currenciesData = [];
let spaServicesData = [];
let meetingVenuesData = [];

// Fetch configurations from API
async function fetchBootstrapConfig() {
    if (roomsData.length > 0 && currenciesData.length > 0) {
        return {
            rooms: roomsData,
            currencies: currenciesData,
            spaServices: spaServicesData,
            meetingVenues: meetingVenuesData,
        };
    }
    let basePath = normalizeBasePath(getBookingBasePath());
    try {
        const response = await fetch((basePath || '') + '/api/bootstrap');
        if (response.ok) {
            const data = await response.json();
            if (data) {
                if (data.rooms) roomsData = data.rooms;
                if (data.currencies) currenciesData = data.currencies;
                if (data.spaServices) spaServicesData = data.spaServices;
                if (data.meetingVenues) meetingVenuesData = data.meetingVenues;
            }
        }
    } catch (e) {
        console.error('Failed to fetch config:', e);
    }
    return {
        rooms: roomsData,
        currencies: currenciesData,
        spaServices: spaServicesData,
        meetingVenues: meetingVenuesData,
    };
}

// Global state for selected rooms inside the booking modal
let selectedRooms = [{ adults: 1, children: 0 }];

// Initialize room pricing calculator dynamic estimation
async function initRoomPricingCalculator(modal) {
    const form = modal.querySelector('form');
    const roomSelect = form.querySelector('select[name="roomType"]');
    const currencySelect = form.querySelector('select[name="currency"]');
    const checkInInput = form.querySelector('input[name="checkInDate"]');
    const checkOutInput = form.querySelector('input[name="checkOutDate"]');
    const roomsContainer = form.querySelector('#rooms-guest-allocation-container');
    const addRoomBtn = form.querySelector('#add-room-row-btn');
    const summaryContainer = form.querySelector('#room-pricing-summary') || form.querySelector('#catalog-pricing-summary');
    const submitBtn = form.querySelector('#submit-btn-room');
    const submitBtnText = submitBtn ? submitBtn.querySelector('.submit-text') : null;
    
    if (!roomSelect || !checkInInput || !checkOutInput || !summaryContainer) return;
    
    const config = await fetchBootstrapConfig();
    const rooms = config.rooms;
    const currencies = config.currencies;

    // Populate currency selector
    if (currencySelect) {
        currencySelect.innerHTML = '';
        currencies.forEach(curr => {
            const opt = document.createElement('option');
            opt.value = curr.code;
            opt.textContent = `${curr.name} (${curr.code} - ${curr.symbol})`;
            if (curr.is_default) {
                opt.selected = true;
            }
            currencySelect.appendChild(opt);
        });
    }

    // Populate room type dropdown from API data
    const defaultOpt = roomSelect.querySelector('option[value=""]');
    roomSelect.innerHTML = '';
    if (defaultOpt) roomSelect.appendChild(defaultOpt);
    rooms.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.type;
        opt.textContent = r.type;
        roomSelect.appendChild(opt);
    });

    const roomImagePreview = form.querySelector('#room-type-image-preview');
    const roomImageEl = roomImagePreview ? roomImagePreview.querySelector('.room-type-image-preview__img') : null;
    const roomImagePrevBtn = roomImagePreview ? roomImagePreview.querySelector('.room-type-image-preview__nav--prev') : null;
    const roomImageNextBtn = roomImagePreview ? roomImagePreview.querySelector('.room-type-image-preview__nav--next') : null;
    const roomImageDots = roomImagePreview ? roomImagePreview.querySelector('.room-type-image-preview__dots') : null;
    let roomImageUrls = [];
    let roomImageIndex = 0;

    function resolveRoomImageUrl(imagePath) {
        if (!imagePath) return '';
        if (/^https?:\/\//i.test(imagePath) || imagePath.startsWith('data:')) return imagePath;
        let basePath = normalizeBasePath(getBookingBasePath());
        const clean = String(imagePath).replace(/^\/+/, '');
        const assetPath = clean.startsWith('assets/') ? clean : `assets/${clean}`;
        return `${basePath}/${assetPath}`.replace(/([^:]\/)\/+/g, '$1');
    }

    function renderRoomImageSlide() {
        if (!roomImagePreview || !roomImageEl) return;
        if (!roomImageUrls.length) {
            roomImageEl.removeAttribute('src');
            roomImageEl.alt = '';
            roomImagePreview.classList.add('hidden');
            return;
        }
        const url = roomImageUrls[roomImageIndex];
        roomImageEl.src = url;
        roomImageEl.alt = `${roomSelect.value || 'Room'} image ${roomImageIndex + 1}`;
        roomImagePreview.classList.remove('hidden');

        const multi = roomImageUrls.length > 1;
        roomImagePrevBtn?.classList.toggle('hidden', !multi);
        roomImageNextBtn?.classList.toggle('hidden', !multi);

        if (roomImageDots) {
            roomImageDots.innerHTML = '';
            if (multi) {
                roomImageUrls.forEach((_, i) => {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'room-type-image-preview__dot' + (i === roomImageIndex ? ' is-active' : '');
                    dot.setAttribute('aria-label', `Show image ${i + 1}`);
                    dot.addEventListener('click', () => {
                        roomImageIndex = i;
                        renderRoomImageSlide();
                    });
                    roomImageDots.appendChild(dot);
                });
            }
        }
    }

    function updateRoomTypeImage() {
        if (!roomImagePreview || !roomImageEl) return;
        const selected = rooms.find(r => r.type === roomSelect.value);
        const paths = [];
        if (selected) {
            if (Array.isArray(selected.images) && selected.images.length) {
                selected.images.forEach(p => { if (p) paths.push(p); });
            } else if (selected.image_path) {
                paths.push(selected.image_path);
            }
        }
        roomImageUrls = paths.map(resolveRoomImageUrl).filter(Boolean);
        roomImageIndex = 0;
        renderRoomImageSlide();
    }

    roomImagePrevBtn?.addEventListener('click', () => {
        if (!roomImageUrls.length) return;
        roomImageIndex = (roomImageIndex - 1 + roomImageUrls.length) % roomImageUrls.length;
        renderRoomImageSlide();
    });
    roomImageNextBtn?.addEventListener('click', () => {
        if (!roomImageUrls.length) return;
        roomImageIndex = (roomImageIndex + 1) % roomImageUrls.length;
        renderRoomImageSlide();
    });

    roomSelect.addEventListener('change', updateRoomTypeImage);
    updateRoomTypeImage();
    
    function formatPrice(val, curr) {
        const found = currencies.find(c => c.code === curr);
        const symbol = found ? found.symbol : '';
        return `${symbol}${val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${curr}`;
    }

    function calculateTotalPrice(roomConfig, roomsList, selectedCurrency) {
        const cp = roomConfig.currency_prices || {};
        const rates = cp[selectedCurrency] || {
            adult: parseFloat(roomConfig.price_per_adult || roomConfig.price_per_night || 100.00),
            additional_adult: parseFloat(roomConfig.price_additional_adult || 50.00),
            child: parseFloat(roomConfig.price_per_child || 40.00)
        };
        
        const pAdult = parseFloat(rates.adult);
        const pAdd = parseFloat(rates.additional_adult);
        const pChild = parseFloat(rates.child);
        
        let totalPerNight = 0;
        roomsList.forEach(rm => {
            if (rm.adults > 0) {
                totalPerNight += pAdult + (rm.adults - 1) * pAdd + (rm.children * pChild);
            } else {
                totalPerNight += rm.children * pChild;
            }
        });
        return totalPerNight;
    }
    
    function renderRoomsList() {
        if (!roomsContainer) return;
        roomsContainer.innerHTML = '';
        
        const selectedType = roomSelect.value;
        const roomConfig = rooms.find(r => r.type === selectedType);
        const maxG = roomConfig ? (parseInt(roomConfig.max_guests, 10) || 2) : 2;
        
        selectedRooms.forEach((room, i) => {
            const row = document.createElement('div');
            row.className = 'room-guest-row';
            row.dataset.index = i;
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.gap = '1rem';
            row.style.backgroundColor = '#fafaf9';
            row.style.padding = '0.75rem 1rem';
            row.style.borderRadius = '0.375rem';
            row.style.border = '1px solid #e5e7eb';
            
            row.innerHTML = `
                <div style="font-weight: bold; font-size: 0.875rem; color: #374151; min-width: 5rem;">ROOM ${i + 1}</div>
                <div style="display: flex; align-items: center; gap: 1.25rem; flex: 1; justify-content: flex-end;">
                    <!-- Adults Stepper -->
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                        <span style="font-size: 0.7rem; color: #6b7280; text-transform: uppercase; font-weight: 500;">adults</span>
                        <div style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 9999px; background: white; overflow: hidden; height: 2rem;">
                            <button type="button" class="dec-adult-btn" style="width: 1.75rem; height: 100%; border: none; background: none; font-size: 1.1rem; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; outline: none;">&minus;</button>
                            <span class="adults-val" style="min-width: 1.25rem; text-align: center; font-weight: bold; font-size: 0.85rem; color: #111827;">${room.adults}</span>
                            <button type="button" class="inc-adult-btn" style="width: 1.75rem; height: 100%; border: none; background: none; font-size: 1.1rem; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; outline: none;">+</button>
                        </div>
                    </div>
                    <!-- Children Stepper -->
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                        <span style="font-size: 0.7rem; color: #6b7280; text-transform: uppercase; font-weight: 500;">children</span>
                        <div style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 9999px; background: white; overflow: hidden; height: 2rem;">
                            <button type="button" class="dec-child-btn" style="width: 1.75rem; height: 100%; border: none; background: none; font-size: 1.1rem; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; outline: none;">&minus;</button>
                            <span class="children-val" style="min-width: 1.25rem; text-align: center; font-weight: bold; font-size: 0.85rem; color: #111827;">${room.children}</span>
                            <button type="button" class="inc-child-btn" style="width: 1.75rem; height: 100%; border: none; background: none; font-size: 1.1rem; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; outline: none;">+</button>
                        </div>
                    </div>
                    <!-- Delete Button -->
                    ${i > 0 ? `
                    <button type="button" class="remove-room-btn" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0.25rem; display: flex; align-items: center; margin-top: 1rem;" title="Remove Room">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                    ` : '<div style="width: 24px; margin-top: 1rem;"></div>'}
                </div>
            `;
            
            row.querySelector('.dec-adult-btn').onclick = () => {
                const minAdults = (i === 0) ? 1 : 0;
                if (room.adults > minAdults) {
                    if (room.adults + room.children > 1) {
                        room.adults--;
                        renderRoomsList();
                        updatePricing();
                    }
                }
            };
            row.querySelector('.dec-child-btn').onclick = () => {
                if (room.children > 0) {
                    if (room.adults + room.children > 1) {
                        room.children--;
                        renderRoomsList();
                        updatePricing();
                    }
                }
            };
            
            row.querySelector('.inc-adult-btn').onclick = () => {
                const currentTotal = room.adults + room.children;
                if (currentTotal >= maxG) {
                    if (confirm(`You have reached the maximum occupancy of ${maxG} guests for Room ${i+1}. Would you like to add another room?`)) {
                        selectedRooms.push({ adults: 1, children: 0 });
                        renderRoomsList();
                        updatePricing();
                    }
                } else {
                    room.adults++;
                    renderRoomsList();
                    updatePricing();
                }
            };
            
            row.querySelector('.inc-child-btn').onclick = () => {
                const currentTotal = room.adults + room.children;
                if (currentTotal >= maxG) {
                    if (confirm(`You have reached the maximum occupancy of ${maxG} guests for Room ${i+1}. Would you like to add another room?`)) {
                        selectedRooms.push({ adults: 0, children: 1 });
                        renderRoomsList();
                        updatePricing();
                    }
                } else {
                    room.children++;
                    renderRoomsList();
                    updatePricing();
                }
            };
            
            if (i > 0) {
                row.querySelector('.remove-room-btn').onclick = () => {
                    selectedRooms.splice(i, 1);
                    renderRoomsList();
                    updatePricing();
                };
            }
            
            roomsContainer.appendChild(row);
        });
    }
    
    function updatePricing() {
        const selectedType = roomSelect.value;
        const checkInVal = checkInInput.value;
        const checkOutVal = checkOutInput.value;
        const selectedCurrency = currencySelect ? currencySelect.value : 'USD';
        
        const roomConfig = rooms.find(r => r.type === selectedType);

        if (!selectedType || !checkInVal || !checkOutVal || !roomConfig) {
            summaryContainer.classList.add('hidden');
            summaryContainer.innerHTML = '';
            if (submitBtnText) submitBtnText.textContent = 'Request Booking';
            return;
        }
        
        const [inY, inM, inD] = checkInVal.split('-').map(Number);
        const [outY, outM, outD] = checkOutVal.split('-').map(Number);
        const checkInDate = new Date(inY, inM - 1, inD);
        const checkOutDate = new Date(outY, outM - 1, outD);
        const timeDiff = checkOutDate.getTime() - checkInDate.getTime();
        const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (nights <= 0) {
            summaryContainer.classList.add('hidden');
            summaryContainer.innerHTML = '';
            if (submitBtnText) submitBtnText.textContent = 'Request Booking';
            return;
        }
        
        const ratePerNight = calculateTotalPrice(roomConfig, selectedRooms, selectedCurrency);
        const finalPrice = ratePerNight * nights;
        
        // Construct detailed HTML breakdown
        let roomsBreakdown = '';
        selectedRooms.forEach((rm, i) => {
            const cp = roomConfig.currency_prices || {};
            const rates = cp[selectedCurrency] || {
                adult: parseFloat(roomConfig.price_per_adult || roomConfig.price_per_night || 100.00),
                additional_adult: parseFloat(roomConfig.price_additional_adult || 50.00),
                child: parseFloat(roomConfig.price_per_child || 40.00)
            };
            const pAdult = parseFloat(rates.adult);
            const pAdd = parseFloat(rates.additional_adult);
            const pChild = parseFloat(rates.child);
            
            let rmCost = 0;
            if (rm.adults > 0) {
                rmCost = pAdult + (rm.adults - 1) * pAdd + (rm.children * pChild);
            } else {
                rmCost = rm.children * pChild;
            }
            
            roomsBreakdown += `
                <div class="pricing-row sub-row" style="font-size: 0.8rem; padding-left: 0.5rem; display: flex; justify-content: space-between;">
                    <span class="text-muted">Room ${i+1}: ${rm.adults} Adult${rm.adults>1?'s':''}${rm.children>0?`, ${rm.children} Child${rm.children>1?'ren':''}`:''}</span>
                    <span class="text-muted">${formatPrice(rmCost, selectedCurrency)}/nt</span>
                </div>
            `;
        });
        
        let pricingHTML = `
            <div class="pricing-summary-card" style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; background: #fafaf9; display: flex; flex-direction: column; gap: 0.5rem;">
                <h4 class="pricing-summary-title" style="font-weight: bold; font-size: 0.95rem; color: #374151; margin-bottom: 0.25rem;">Price Summary</h4>
                <div class="pricing-row" style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem;">
                    <span>${escapeHtml(selectedType)} &times; ${nights} night${nights > 1 ? 's' : ''}</span>
                    <span>${formatPrice(finalPrice, selectedCurrency)}</span>
                </div>
                ${roomsBreakdown}
                <div class="pricing-row total-row" style="display: flex; justify-content: space-between; border-top: 1px solid #e5e7eb; padding-top: 0.5rem; margin-top: 0.25rem; font-weight: bold; font-size: 1.1rem; color: #0E8040;">
                    <span>Estimated Total</span>
                    <span class="total-value">${formatPrice(finalPrice, selectedCurrency)}</span>
                </div>
            </div>
        `;
        
        summaryContainer.innerHTML = pricingHTML;
        summaryContainer.classList.remove('hidden');
        
        if (submitBtnText) {
            submitBtnText.innerHTML = `Book Now — ${formatPrice(finalPrice, selectedCurrency)}`;
        }
    }
    
    // Bind buttons and select fields
    if (addRoomBtn) {
        addRoomBtn.onclick = () => {
            selectedRooms.push({ adults: 1, children: 0 });
            renderRoomsList();
            updatePricing();
        };
    }
    
    roomSelect.addEventListener('change', () => {
        const selectedType = roomSelect.value;
        const roomConfig = rooms.find(r => r.type === selectedType);
        if (roomConfig) {
            const maxG = parseInt(roomConfig.max_guests, 10) || 2;
            selectedRooms.forEach(rm => {
                while (rm.adults + rm.children > maxG) {
                    if (rm.children > 0) rm.children--;
                    else if (rm.adults > 1) rm.adults--;
                    else break;
                }
            });
        }
        renderRoomsList();
        updatePricing();
    });
    
    if (currencySelect) {
        currencySelect.addEventListener('change', updatePricing);
    }
    
    checkInInput.addEventListener('change', updatePricing);
    checkOutInput.addEventListener('change', updatePricing);
    
    // Reset selectedRooms when loading modal
    selectedRooms = [{ adults: 1, children: 0 }];
    
    // Render initially
    renderRoomsList();
    updatePricing();
}

// Create booking modal
function createBookingModal(type) {
    const modal = document.createElement('div');
    modal.className = 'booking-modal';
    modal.innerHTML = `
        <div class="booking-modal-backdrop" onclick="closeCurrentModal()"></div>
        <div class="booking-modal-content">
            <button onclick="closeCurrentModal()" class="booking-modal-close" aria-label="Close booking modal">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            <div class="booking-modal-header">
                <div class="booking-modal-icon">
                    ${getModalIcon(type)}
                </div>
                <h2 class="booking-modal-title">${getModalTitle(type)}</h2>
                <p class="booking-modal-subtitle">${getModalSubtitle(type)}</p>
            </div>
            <form class="booking-modal-form" id="booking-form-${type}">
                ${getBookingForm(type)}
                ${type === 'room' || type === 'spa' || type === 'meeting' ? '<div id="catalog-pricing-summary" class="room-pricing-summary hidden"></div>' : ''}
                <button type="submit" class="booking-modal-submit" id="submit-btn-${type}">
                    <span class="submit-text">Request Booking</span>
                    <span class="submit-loading hidden">Submitting...</span>
                </button>
            </form>
        </div>
    `;
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Set minimum dates and bind interactions
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const today = `${year}-${month}-${day}`;
    
    modal.querySelectorAll('input[type="date"]').forEach(input => {
        input.min = today;
        
        // Open the native date picker calendar when the field or icon is touched/clicked
        input.addEventListener('click', () => {
            try {
                input.showPicker();
            } catch (err) {
                console.warn('Native showPicker is not supported or failed:', err);
            }
        });
        
        // Prevent typing or pasting a past date
        input.addEventListener('change', () => {
            if (input.value && input.value < input.min) {
                input.value = input.min;
                showToast('Dates cannot be in the past.', 'error');
            }
        });
    });
    
    // Handle form submission
    const form = modal.querySelector('form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await submitBooking(type, form);
    });
    
    // Handle check-in date change for check-out minimum
    if (type === 'room') {
        const checkInInput = form.querySelector('input[name="checkInDate"]');
        const checkOutInput = form.querySelector('input[name="checkOutDate"]');
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', (e) => {
                const checkInDate = e.target.value;
                if (checkInDate) {
                    const [y, m, d] = checkInDate.split('-').map(Number);
                    const checkIn = new Date(y, m - 1, d);
                    const checkOut = new Date(checkIn);
                    checkOut.setDate(checkOut.getDate() + 1);
                    
                    const cy = checkOut.getFullYear();
                    const cm = String(checkOut.getMonth() + 1).padStart(2, '0');
                    const cd = String(checkOut.getDate()).padStart(2, '0');
                    const minCheckOut = `${cy}-${cm}-${cd}`;
                    
                    checkOutInput.min = minCheckOut;
                    if (checkOutInput.value && checkOutInput.value < minCheckOut) {
                        checkOutInput.value = minCheckOut;
                    }
                }
            });
        }
    }

    // Enhance number inputs with +/- controls
    form.querySelectorAll('.number-input').forEach(container => {
        const input = container.querySelector('input[type="number"]');
        if (!input) return;
        const min = parseInt(container.dataset.min || input.min || '0', 10);
        const max = parseInt(container.dataset.max || input.max || '0', 10);
        const clampValue = value => {
            let result = value;
            if (!Number.isNaN(min)) {
                result = Math.max(min, result);
            }
            if (!Number.isNaN(max) && max > 0) {
                result = Math.min(max, result);
            }
            return result;
        };
        container.querySelectorAll('.number-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const step = parseInt(btn.dataset.step || '1', 10);
                const current = parseInt(input.value || String(min || 0), 10) || (min || 0);
                const next = clampValue(current + step);
                input.value = next;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        input.addEventListener('change', () => {
            const current = parseInt(input.value || String(min || 0), 10) || (min || 0);
            input.value = clampValue(current);
        });
    });
    
    if (type === 'room') {
        initRoomPricingCalculator(modal);
    } else if (type === 'spa' || type === 'meeting') {
        initCatalogPricingCalculator(modal, type);
    }
    
    return modal;
}

async function initCatalogPricingCalculator(modal, type) {
    const form = modal.querySelector('form');
    if (!form) return;
    const config = await fetchBootstrapConfig();
    const currencies = config.currencies || [];
    const items = type === 'spa' ? (config.spaServices || []) : (config.meetingVenues || []);
    const itemSelect = form.querySelector(type === 'spa' ? 'select[name="service"]' : 'select[name="venueName"]');
    const currencySelect = form.querySelector('select[name="currency"]');
    const summaryContainer = form.querySelector('#catalog-pricing-summary');
    const submitBtn = form.querySelector('.booking-modal-submit');
    const submitBtnText = submitBtn ? submitBtn.querySelector('.submit-text') : null;

    if (currencySelect) {
        currencySelect.innerHTML = '';
        currencies.forEach(curr => {
            const opt = document.createElement('option');
            opt.value = curr.code;
            opt.textContent = `${curr.name} (${curr.code} - ${curr.symbol})`;
            if (curr.is_default) opt.selected = true;
            currencySelect.appendChild(opt);
        });
    }

    if (itemSelect) {
        itemSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = type === 'spa' ? 'Select a service…' : 'Select a venue…';
        itemSelect.appendChild(placeholder);
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.name;
            const note = item.capacity_note ? ` ${item.capacity_note}` : '';
            opt.textContent = `${item.name}${note}`;
            itemSelect.appendChild(opt);
        });
    }

    function formatPrice(val, curr) {
        const found = currencies.find(c => c.code === curr);
        const symbol = found ? found.symbol : '';
        return `${symbol}${Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${curr}`;
    }

    function updatePricing() {
        if (!summaryContainer || !itemSelect) return;
        const selectedName = itemSelect.value;
        const selectedCurrency = currencySelect ? currencySelect.value : 'USD';
        const item = items.find(i => i.name === selectedName);
        if (!item) {
            summaryContainer.classList.add('hidden');
            summaryContainer.innerHTML = '';
            if (submitBtnText) submitBtnText.textContent = 'Request Booking';
            return;
        }
        const prices = item.currency_prices || {};
        const price = prices[selectedCurrency] != null
            ? parseFloat(prices[selectedCurrency])
            : parseFloat(Object.values(prices)[0] || 0);
        summaryContainer.innerHTML = `
            <div class="pricing-summary-card" style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; background: #fafaf9;">
                <h4 class="pricing-summary-title" style="font-weight: bold; font-size: 0.95rem; color: #374151; margin-bottom: 0.5rem;">Price Summary</h4>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem; color: #0E8040;">
                    <span>${escapeHtml(item.name)}</span>
                    <span>${formatPrice(price, selectedCurrency)}</span>
                </div>
            </div>
        `;
        summaryContainer.classList.remove('hidden');
        if (submitBtnText) {
            submitBtnText.innerHTML = `Book Now — ${formatPrice(price, selectedCurrency)}`;
        }
    }

    itemSelect?.addEventListener('change', updatePricing);
    currencySelect?.addEventListener('change', updatePricing);
    updatePricing();
}

// Get modal icon
function getModalIcon(type) {
    if (type === 'room') {
        return '<i data-lucide="bed-double" class="w-8 h-8"></i>';
    } else if (type === 'spa') {
        return '<i data-lucide="heart-pulse" class="w-8 h-8"></i>';
    } else {
        return '<i data-lucide="users" class="w-8 h-8"></i>';
    }
}

// Get modal title
function getModalTitle(type) {
    if (type === 'room') return 'Book Your Stay';
    if (type === 'spa') return 'Book a Treatment';
    return 'Enquire About Event';
}

// Get modal subtitle
function getModalSubtitle(type) {
    if (type === 'room') return 'Fill in the details below to reserve your room.';
    if (type === 'spa') return 'Fill in the details below to book your spa treatment.';
    return 'Fill in the details below to inquire about your event.';
}

// Get booking form HTML
function getBookingForm(type) {
    if (type === 'room') {
        return `
            <div class="form-group">
                <label for="guestName" class="form-label">Full Name</label>
                <input type="text" id="guestName" name="guestName" class="form-input" required placeholder="Your full name">
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" required placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" class="form-input" placeholder="e.g., +251 91 123 4567" required>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="checkInDate" class="form-label">Check-in Date</label>
                    <input type="date" id="checkInDate" name="checkInDate" class="form-input form-input-date" required>
                </div>
                <div class="form-group">
                    <label for="checkOutDate" class="form-label">Check-out Date</label>
                    <input type="date" id="checkOutDate" name="checkOutDate" class="form-input form-input-date" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="roomType" class="form-label">Room Type</label>
                    <select id="roomType" name="roomType" class="form-input form-input-select" required>
                        <option value="">Loading room types...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="currencySelect" class="form-label">Preferred Currency</label>
                    <select id="currencySelect" name="currency" class="form-input form-input-select" required>
                        <option value="">Loading currencies...</option>
                    </select>
                </div>
            </div>
            <div id="room-type-image-preview" class="room-type-image-preview hidden" aria-live="polite">
                <div class="room-type-image-preview__stage">
                    <button type="button" class="room-type-image-preview__nav room-type-image-preview__nav--prev hidden" aria-label="Previous room image">&lsaquo;</button>
                    <img src="" alt="" class="room-type-image-preview__img">
                    <button type="button" class="room-type-image-preview__nav room-type-image-preview__nav--next hidden" aria-label="Next room image">&rsaquo;</button>
                </div>
                <div class="room-type-image-preview__dots"></div>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; margin-bottom: 0.5rem;">Select number of guests</label>
                <div id="rooms-guest-allocation-container" class="space-y-4" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <!-- Rooms list will be rendered dynamically here -->
                </div>
                <div style="margin-top: 0.75rem;">
                    <button type="button" id="add-room-row-btn" class="add-room-link" style="background: none; border: none; color: #0E8040; cursor: pointer; font-size: 0.875rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0;">
                        <span style="font-size: 1.25rem; line-height: 1;">+</span> Add another room
                    </button>
                </div>
            </div>
        `;
    } else if (type === 'spa') {
        return `
            <div class="form-group">
                <label for="guestName" class="form-label">Full Name</label>
                <input type="text" id="guestName" name="guestName" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" class="form-input" placeholder="e.g., +251 91 123 4567" required>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="service" class="form-label">Service</label>
                    <select id="service" name="service" class="form-input form-input-select" required>
                        <option value="">Loading services...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="spaCurrency" class="form-label">Preferred Currency</label>
                    <select id="spaCurrency" name="currency" class="form-input form-input-select" required>
                        <option value="">Loading currencies...</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" name="date" class="form-input form-input-date" required>
                </div>
                <div class="form-group">
                    <label for="time" class="form-label">Time</label>
                    <input type="time" id="time" name="time" class="form-input" required>
                </div>
            </div>
        `;
    } else {
        return `
            <div class="form-grid">
                <div class="form-group">
                    <label for="contactName" class="form-label">Full Name</label>
                    <input type="text" id="contactName" name="contactName" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
            </div>
            <div class="form-group">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" class="form-input" placeholder="e.g., +251 91 123 4567" required>
            </div>
            <div class="form-group">
                <label for="companyName" class="form-label">Company Name (Optional)</label>
                <input type="text" id="companyName" name="companyName" class="form-input">
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="venueName" class="form-label">Venue</label>
                    <select id="venueName" name="venueName" class="form-input form-input-select" required>
                        <option value="">Loading venues...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="meetingCurrency" class="form-label">Preferred Currency</label>
                    <select id="meetingCurrency" name="currency" class="form-input form-input-select" required>
                        <option value="">Loading currencies...</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="date" class="form-label">Preferred Date</label>
                <input type="date" id="date" name="date" class="form-input form-input-date" required>
            </div>
        `;
    }
}

// Submit booking - rebuilt following chatbot pattern
async function submitBooking(type, form) {
    const submitBtn = form.querySelector('.booking-modal-submit');
    const submitText = submitBtn.querySelector('.submit-text');
    const submitLoading = submitBtn.querySelector('.submit-loading');
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitLoading.classList.remove('hidden');
    
    // Get form data
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Convert number fields
    if (type === 'room') {
        data.rooms = selectedRooms;
        data.numberOfGuests = selectedRooms.reduce((sum, rm) => sum + rm.adults + rm.children, 0);
    } else if (data.numberOfGuests) {
        data.numberOfGuests = parseInt(data.numberOfGuests, 10);
    }
    
    // Get base path - same method as chatbot
    let basePath = normalizeBasePath(getBookingBasePath());
    
    // Determine endpoint based on type
    let endpoint;
    if (type === 'room') {
        endpoint = (basePath || '') + '/booking/room';
    } else if (type === 'spa') {
        endpoint = (basePath || '') + '/booking/spa';
    } else {
        endpoint = (basePath || '') + '/booking/meeting';
    }
    
    let response;
    try {
        response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(data),
        });
    } catch (networkError) {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoading.classList.add('hidden');
        showToast('Unable to reach the server. Please check your connection and try again.', 'error');
        return;
    }
    
    const contentType = response.headers.get('Content-Type') || '';
    let result = null;
    
    if (contentType.includes('application/json')) {
        try {
            result = await response.json();
        } catch (parseError) {
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
            showToast('Invalid JSON response from server.', 'error');
            return;
        }
    } else {
        const rawText = await response.text();
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoading.classList.add('hidden');
        showToast(rawText || 'Invalid response from server.', 'error');
        return;
    }
    
    if (!response.ok) {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoading.classList.add('hidden');
        showToast((result && result.error) || `Server error: ${response.status}`, 'error');
        return;
    }
    
    if (result && result.success) {
        showToast(result.message || 'Booking request sent successfully!', 'success');
        setTimeout(() => closeCurrentModal(), 1500);
    } else {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoading.classList.add('hidden');
        showToast((result && result.error) || 'Booking failed', 'error');
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${escapeHtml(message)}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="toast-close">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    `;
    
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    // Initialize icon
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Show toast
    setTimeout(() => toast.classList.add('toast-show'), 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.remove('toast-show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && currentModal) {
        closeCurrentModal();
    }
});
