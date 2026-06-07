/**
 * ARMAS Client-side Form Validation
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Form validation rules
    const validators = {
        required: function(value) {
            return value && value.trim().length > 0;
        },
        
        email: function(value) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        },
        
        phone: function(value) {
            return /^[\d\s\-\+\(\)]{7,20}$/.test(value);
        },
        
        password: function(value) {
            return value.length >= 8;
        },
        
        passwordMatch: function(value, form) {
            const confirmPassword = form.querySelector('[name="confirm_password"]');
            return value === confirmPassword?.value;
        },
        
        minLength: function(value, min) {
            return value.length >= min;
        },
        
        maxLength: function(value, max) {
            return value.length <= max;
        },
        
        date: function(value) {
            return !isNaN(Date.parse(value));
        },
        
        futureDate: function(value) {
            return new Date(value) > new Date();
        },
        
        pastDate: function(value) {
            return new Date(value) < new Date();
        },
        
        numeric: function(value) {
            return /^\d+$/.test(value);
        },
        
        alphanumeric: function(value) {
            return /^[a-zA-Z0-9]+$/.test(value);
        }
    };
    
    // Validate a single field
    function validateField(field) {
        const rules = field.dataset.validate?.split(',') || [];
        const value = field.value;
        let isValid = true;
        let errorMessage = '';
        
        for (const rule of rules) {
            const [ruleName, param] = rule.split(':');
            
            switch (ruleName) {
                case 'required':
                    if (!validators.required(value)) {
                        isValid = false;
                        errorMessage = 'This field is required';
                    }
                    break;
                    
                case 'email':
                    if (value && !validators.email(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                    
                case 'phone':
                    if (value && !validators.phone(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                    break;
                    
                case 'password':
                    if (value && !validators.password(value)) {
                        isValid = false;
                        errorMessage = 'Password must be at least 8 characters';
                    }
                    break;
                    
                case 'min':
                    if (value && !validators.minLength(value, parseInt(param))) {
                        isValid = false;
                        errorMessage = `Minimum ${param} characters required`;
                    }
                    break;
                    
                case 'max':
                    if (value && !validators.maxLength(value, parseInt(param))) {
                        isValid = false;
                        errorMessage = `Maximum ${param} characters allowed`;
                    }
                    break;
                    
                case 'date':
                    if (value && !validators.date(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid date';
                    }
                    break;
                    
                case 'future':
                    if (value && !validators.futureDate(value)) {
                        isValid = false;
                        errorMessage = 'Date must be in the future';
                    }
                    break;
                    
                case 'past':
                    if (value && !validators.pastDate(value)) {
                        isValid = false;
                        errorMessage = 'Date must be in the past';
                    }
                    break;
                    
                case 'match':
                    const matchField = document.querySelector(`[name="${param}"]`);
                    if (matchField && value !== matchField.value) {
                        isValid = false;
                        errorMessage = `Passwords do not match`;
                    }
                    break;
            }
            
            if (!isValid) break;
        }
        
        // Show/hide error
        const errorEl = field.parentElement.querySelector('.error-message');
        if (!isValid) {
            field.classList.add('error');
            field.classList.remove('valid');
            if (errorEl) {
                errorEl.textContent = errorMessage;
                errorEl.style.display = 'block';
            } else {
                showError(field, errorMessage);
            }
        } else {
            field.classList.remove('error');
            field.classList.add('valid');
            if (errorEl) {
                errorEl.style.display = 'none';
            }
        }
        
        return isValid;
    }
    
    // Show error message
    function showError(field, message) {
        let errorEl = field.parentElement.querySelector('.error-message');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'error-message';
            field.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
    
    // Validate entire form
    function validateForm(form) {
        const fields = form.querySelectorAll('[data-validate]');
        let isValid = true;
        
        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Add blur validation to all validated fields
    document.querySelectorAll('[data-validate]').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    // Form submission handlers
    document.querySelectorAll('form[data-validate="true"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                
                // Show general error message
                showAlert('error', 'Please fix the errors in the form before submitting.');
            }
        });
    });
    
    // Alert helper
    window.showAlert = function(type, message) {
        const alert = document.createElement('div');
        alert.className = `flash flash-${type}`;
        alert.innerHTML = `
            <span>${message}</span>
            <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    };
    
    // Real-time password match validation
    const passwordField = document.querySelector('[name="password"]');
    const confirmField = document.querySelector('[name="confirm_password"]');
    
    if (passwordField && confirmField) {
        confirmField.addEventListener('input', function() {
            if (this.value && this.value !== passwordField.value) {
                this.classList.add('error');
                this.classList.remove('valid');
                let errorEl = this.parentElement.querySelector('.error-message');
                if (!errorEl) {
                    errorEl = document.createElement('div');
                    errorEl.className = 'error-message';
                    this.parentElement.appendChild(errorEl);
                }
                errorEl.textContent = 'Passwords do not match';
                errorEl.style.display = 'block';
            } else {
                this.classList.remove('error');
                this.classList.add('valid');
                const errorEl = this.parentElement.querySelector('.error-message');
                if (errorEl) errorEl.style.display = 'none';
            }
        });
    }
    
    // Date range validation
    const dateFrom = document.querySelector('[name="date_from"]');
    const dateTo = document.querySelector('[name="date_to"]');
    
    if (dateFrom && dateTo) {
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && this.value && new Date(this.value) < new Date(dateFrom.value)) {
                showAlert('error', 'End date must be after start date');
                this.value = '';
            }
        });
    }
    
    // Character counter for textareas
    document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        counter.style.cssText = 'text-align: right; font-size: 0.75rem; color: var(--mid); margin-top: 4px;';
        textarea.parentElement.appendChild(counter);
        
        function updateCounter() {
            const remaining = textarea.maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
});
