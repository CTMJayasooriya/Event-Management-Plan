/**
 * Student Event Management (EMS) - Client-side Form Validation
 */

function showAlert(message, type = 'danger') {
    // Check if an alert container already exists
    let alertBox = document.querySelector('.js-alert');
    if (!alertBox) {
        alertBox = document.createElement('div');
        alertBox.className = `alert alert-${type} js-alert`;
        const form = document.querySelector('form');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(alertBox, form);
        } else {
            alert(message);
            return;
        }
    } else {
        alertBox.className = `alert alert-${type} js-alert`;
    }
    alertBox.textContent = message;
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearAlert() {
    const alertBox = document.querySelector('.js-alert');
    if (alertBox) {
        alertBox.remove();
    }
}

/**
 * Validates Login Form
 */
function validateLoginForm() {
    clearAlert();
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    if (!email || !email.value.trim()) {
        showAlert('Please enter your email address.');
        if (email) email.focus();
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
        showAlert('Please enter a valid email address.');
        email.focus();
        return false;
    }

    if (!password || !password.value) {
        showAlert('Please enter your password.');
        if (password) password.focus();
        return false;
    }

    return true;
}

/**
 * Validates Signup Form
 */
function validateSignupForm() {
    clearAlert();
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (!name || !name.value.trim()) {
        showAlert('Please enter your full name.');
        if (name) name.focus();
        return false;
    }

    if (!email || !email.value.trim()) {
        showAlert('Please enter your email address.');
        if (email) email.focus();
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
        showAlert('Please enter a valid email address.');
        email.focus();
        return false;
    }

    if (!password || password.value.length < 6) {
        showAlert('Password must be at least 6 characters long.');
        if (password) password.focus();
        return false;
    }

    if (!confirmPassword || password.value !== confirmPassword.value) {
        showAlert('Passwords do not match. Please re-enter.');
        if (confirmPassword) confirmPassword.focus();
        return false;
    }

    return true;
}

/**
 * Validates Event Registration Form
 */
function validateRegistrationForm() {
    clearAlert();
    const studentId = document.getElementById('student_id');
    const contactNumber = document.getElementById('contact_number');

    if (!studentId || !studentId.value.trim()) {
        showAlert('Please enter your Student ID.');
        if (studentId) studentId.focus();
        return false;
    }

    if (!contactNumber || !contactNumber.value.trim()) {
        showAlert('Please enter your contact phone number.');
        if (contactNumber) contactNumber.focus();
        return false;
    }

    // Basic phone number validation (digits, dashes, plus, spaces, min 7 chars)
    const phoneRegex = /^[0-9+\s-]{7,20}$/;
    if (!phoneRegex.test(contactNumber.value.trim())) {
        showAlert('Please enter a valid contact phone number (e.g. 0771234567 or +94771234567).');
        contactNumber.focus();
        return false;
    }

    return true;
}

/**
 * Validates Admin Event Creation / Edit Form
 */
function validateEventForm(formId) {
    const form = formId ? document.getElementById(formId) : document.querySelector('form');
    if (!form) return true;

    const title = form.querySelector('[name="title"]');
    const description = form.querySelector('[name="description"]');
    const date = form.querySelector('[name="date"]');
    const time = form.querySelector('[name="time"]');
    const venue = form.querySelector('[name="venue"]');
    const organizer = form.querySelector('[name="organizer"]');
    const capacity = form.querySelector('[name="capacity"]');

    if (!title || !title.value.trim()) {
        alert('Event title is required.');
        if (title) title.focus();
        return false;
    }

    if (!date || !date.value) {
        alert('Event date is required.');
        if (date) date.focus();
        return false;
    }

    if (!time || !time.value) {
        alert('Event time is required.');
        if (time) time.focus();
        return false;
    }

    if (!venue || !venue.value.trim()) {
        alert('Event venue is required.');
        if (venue) venue.focus();
        return false;
    }

    if (!organizer || !organizer.value.trim()) {
        alert('Event organizer is required.');
        if (organizer) organizer.focus();
        return false;
    }

    if (!capacity || parseInt(capacity.value, 10) <= 0) {
        alert('Capacity must be a positive number greater than 0.');
        if (capacity) capacity.focus();
        return false;
    }

    return true;
}
