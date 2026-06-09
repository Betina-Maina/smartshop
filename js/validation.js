/**
 * SmartShop JavaScript Validation & Utilities
 * Client-side form validation and helper functions
 */

// Form validation
(function() {
    'use strict';

    /**
     * Bootstrap form validation
     */
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    /**
     * Real-time password strength indicator
     */
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.name === 'password') {
            input.addEventListener('input', function() {
                const strength = calculatePasswordStrength(this.value);
                updatePasswordStrengthDisplay(this, strength);
            });
        }
    });

    /**
     * Calculate password strength
     * @param {string} password
     * @returns {string} strength level
     */
    function calculatePasswordStrength(password) {
        let strength = 0;

        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        if (strength < 2) return 'weak';
        if (strength < 4) return 'moderate';
        if (strength < 6) return 'strong';
        return 'very-strong';
    }

    /**
     * Display password strength indicator
     */
    function updatePasswordStrengthDisplay(input, strength) {
        let indicator = input.nextElementSibling;

        if (!indicator || !indicator.classList.contains('password-strength')) {
            indicator = document.createElement('div');
            indicator.className = 'password-strength mt-2';
            input.parentElement.appendChild(indicator);
        }

        const colors = {
            'weak': '#dc3545',
            'moderate': '#ffc107',
            'strong': '#20c997',
            'very-strong': '#28a745'
        };

        indicator.innerHTML = `
            <div class="progress" style="height: 5px;">
                <div class="progress-bar" style="width: ${(Object.keys(colors).indexOf(strength) + 1) * 25}%; background-color: ${colors[strength]};"></div>
            </div>
            <small class="text-${strength === 'weak' ? 'danger' : strength === 'moderate' ? 'warning' : 'success'}">
                Strength: ${strength.charAt(0).toUpperCase() + strength.slice(1)}
            </small>
        `;
    }

    /**
     * Email validation helper
     */
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    /**
     * Clear form
     */
    function clearForm(formId) {
        document.getElementById(formId).reset();
    }

    /**
     * Show toast notification
     * @param {string} message
     * @param {string} type - 'success', 'error', 'warning', 'info'
     */
    window.showToast = function(message, type = 'info') {
        const toastHTML = `
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <strong class="me-auto">SmartShop</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);

        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast from DOM after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    };

    /**
     * Create toast container if it doesn't exist
     */
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Format currency
     * @param {number} value
     * @returns {string} formatted currency
     */
    window.formatCurrency = function(value) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(value);
    };

    /**
     * Confirm action
     * @param {string} message
     * @returns {boolean}
     */
    window.confirmAction = function(message = 'Are you sure?') {
        return confirm(message);
    };

    /**
     * Disable button and show loading state
     */
    window.disableButton = function(buttonId) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        }
    };

    /**
     * Enable button and reset state
     */
    window.enableButton = function(buttonId, originalText) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    };
})();
