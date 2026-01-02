document.addEventListener('DOMContentLoaded', function() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input && input.type === 'password') {
                input.type = 'text';
                this.querySelector('.toggle-icon').textContent = '🙈';
            } else if (input && input.type === 'text') {
                input.type = 'password';
                this.querySelector('.toggle-icon').textContent = '👁️';
            }
        });
    });

    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            if (!validateSignupForm()) {
                e.preventDefault();
            }
        });

        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value && password.value !== this.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
});

function validateLoginForm() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    let isValid = true;

    clearFieldErrors();

    if (!email.value.trim()) {
        showFieldError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    }

    if (!password.value) {
        showFieldError(password, 'Password is required');
        isValid = false;
    }

    return isValid;
}

function validateSignupForm() {
    const firstName = document.getElementById('first_name');
    const lastName = document.getElementById('last_name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    let isValid = true;

    clearFieldErrors();

    if (!firstName.value.trim()) {
        showFieldError(firstName, 'First name is required');
        isValid = false;
    }

    if (!lastName.value.trim()) {
        showFieldError(lastName, 'Last name is required');
        isValid = false;
    }

    if (!email.value.trim()) {
        showFieldError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    }

    if (!phone.value.trim()) {
        showFieldError(phone, 'Phone number is required');
        isValid = false;
    }

    if (!password.value) {
        showFieldError(password, 'Password is required');
        isValid = false;
    } else if (password.value.length < 6) {
        showFieldError(password, 'Password must be at least 6 characters');
        isValid = false;
    }

    if (!confirmPassword.value) {
        showFieldError(confirmPassword, 'Please confirm your password');
        isValid = false;
    } else if (password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }

    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    field.style.borderColor = '#d32f2f';
    
    const existingError = field.parentElement.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#d32f2f';
    errorDiv.style.fontSize = '0.75rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    field.parentElement.appendChild(errorDiv);
}

function clearFieldErrors() {
    const errorMessages = document.querySelectorAll('.field-error');
    errorMessages.forEach(error => error.remove());

    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.style.borderColor = '#e0e0e0';
    });
}

