// Azzeman Hotel - Main JavaScript

// Booking Modals
function openBookingModal() {
    // Create and show room booking modal
    const modal = createBookingModal('room');
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function openSpaBookingModal() {
    const modal = createBookingModal('spa');
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function openMeetingBookingModal() {
    const modal = createBookingModal('meeting');
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function createBookingModal(type) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book ${type === 'room' ? 'Room' : type === 'spa' ? 'Spa Treatment' : 'Meeting/Event'}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="booking-form-${type}">
                        ${type === 'room' ? getRoomBookingForm() : type === 'spa' ? getSpaBookingForm() : getMeetingBookingForm()}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="booking-form-${type}" class="btn bg-brand-gold text-white">Submit</button>
                </div>
            </div>
        </div>
    `;
    
    const form = modal.querySelector('form');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        submitBooking(type, form);
    });
    
    return modal;
}

function getRoomBookingForm() {
    return `
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="guestName" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="tel" class="form-control" name="phoneNumber" required>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Check-in</label>
                <input type="date" class="form-control" name="checkInDate" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Check-out</label>
                <input type="date" class="form-control" name="checkOutDate" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Room Type</label>
                <select class="form-select" name="roomType" required>
                    <option value="">Select room type</option>
                    <option value="King room">King room</option>
                    <option value="Twin">Twin</option>
                    <option value="Deluxe">Deluxe</option>
                    <option value="Executive Suite">Executive Suite</option>
                    <option value="Junior suite">Junior suite</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Number of Guests</label>
                <input type="number" class="form-control" name="numberOfGuests" min="1" value="1" required>
            </div>
        </div>
    `;
}

function getSpaBookingForm() {
    return `
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="guestName" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="tel" class="form-control" name="phoneNumber" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Service</label>
            <select class="form-select" name="service" required>
                <option value="Swedish Massage">Swedish Massage</option>
                <option value="Deep Tissue Massage">Deep Tissue Massage</option>
                <option value="Aromatherapy Facial">Aromatherapy Facial</option>
                <option value="Hot Stone Therapy">Hot Stone Therapy</option>
            </select>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Time</label>
                <input type="time" class="form-control" name="time" required>
            </div>
        </div>
    `;
}

function getMeetingBookingForm() {
    return `
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="contactName" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="tel" class="form-control" name="phoneNumber" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Company Name (Optional)</label>
            <input type="text" class="form-control" name="companyName">
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Venue</label>
                <select class="form-select" name="venueName" required>
                    <option value="Tiya I">Tiya I (40-60 Pax)</option>
                    <option value="Tiya II">Tiya II (15-25 Pax)</option>
                    <option value="Tiya III">Tiya III (50-100 Pax)</option>
                    <option value="Entoto I">Entoto I (250-400 Pax)</option>
                    <option value="Entoto II">Entoto II (15-35 Pax)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Preferred Date</label>
                <input type="date" class="form-control" name="date" required>
            </div>
        </div>
    `;
}

async function submitBooking(type, form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    const endpoint = type === 'room' ? '/booking/room' : type === 'spa' ? '/booking/spa' : '/booking/meeting';
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            form.closest('.modal').querySelector('.btn-close').click();
            form.reset();
        } else {
            showToast(result.error || 'Booking failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// Chatbot
let chatbotOpen = false;

function initChatbot() {
    const button = document.createElement('button');
    button.id = 'chatbot-button';
    button.innerHTML = '<i class="bi bi-chat-dots"></i>';
    button.onclick = toggleChatbot;
    
    const container = document.createElement('div');
    container.id = 'chatbot-container';
    container.appendChild(button);
    document.body.appendChild(container);
}

function toggleChatbot() {
    // Chatbot implementation would go here
    alert('Chatbot feature - implement with Gemini API integration');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initChatbot();
    
    // Set minimum dates for booking forms and add interactions
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const today = `${year}-${month}-${day}`;
    
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.min = today;
        
        // Open the native date picker calendar when clicked/touched
        input.addEventListener('click', () => {
            try {
                input.showPicker();
            } catch (err) {
                console.warn('Native showPicker is not supported or failed:', err);
            }
        });
        
        // Prevent choosing past dates
        input.addEventListener('change', () => {
            if (input.value && input.value < input.min) {
                input.value = input.min;
            }
        });
    });
});

