document.addEventListener('DOMContentLoaded', function() {
    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');

    if (navToggle) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('show');
        });
    }
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
    var allInputs = document.querySelectorAll('input[maxlength], textarea[maxlength]');
    allInputs.forEach(function(input) {
        var max = parseInt(input.getAttribute('maxlength'));
        input.addEventListener('focus', function() {
            updateCharCount(this, max);
        });
        input.addEventListener('input', function() {
            if (this.value.length > max) {
                this.value = this.value.substring(0, max);
            }
            updateCharCount(this, max);
        });
        input.addEventListener('blur', function() {
            var counter = this.parentElement.querySelector('.char-count');
            if (counter) counter.remove();
        });
    });
    var numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            var min = parseFloat(this.getAttribute('min'));
            var max = parseFloat(this.getAttribute('max'));
            var val = parseFloat(this.value);
            
            if (!isNaN(min) && val < min) this.value = min;
            if (!isNaN(max) && val > max) this.value = max;
        });
    });
    if (document.getElementById('registerForm')) {
        setupPasswordStrength('password');
    }
    if (document.getElementById('profileForm')) {
        setupPasswordStrength('new_password');
    }
});
function updateCharCount(input, max) {
    var group = input.closest('.form-group');
    if (!group) return;
    var counter = group.querySelector('.char-count');
    if (!counter) {
        counter = document.createElement('span');
        counter.className = 'char-count';
        group.appendChild(counter);
    }
    
    var remaining = max - input.value.length;
    counter.textContent = input.value.length + ' / ' + max;
    counter.style.color = remaining < 10 ? '#e17055' : '#b2bec3';
}
function setupPasswordStrength(fieldId) {
    var passField = document.getElementById(fieldId);
    if (!passField) return;

    var group = passField.closest('.form-group');
    if (!group) return;
    var meter = document.createElement('div');
    meter.className = 'password-strength';
    meter.innerHTML = '<div class="strength-bar"><div class="strength-fill"></div></div><span class="strength-text"></span>';
    group.appendChild(meter);
    passField.addEventListener('input', function() {
        var password = this.value;
        var result = checkPasswordStrength(password);
        var fill = meter.querySelector('.strength-fill');
        var text = meter.querySelector('.strength-text');
        fill.style.width = result.percent + '%';
        fill.className = 'strength-fill strength-' + result.level;
        text.textContent = password.length > 0 ? result.label : '';
        text.className = 'strength-text strength-' + result.level;
    });
}
function checkPasswordStrength(password) {
    if (password.length === 0) return { percent: 0, level: 'none', label: '' };

    var score = 0;
    var checks = {
        length: password.length >= 8,
        lower: /[a-z]/.test(password),
        upper: /[A-Z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    if (password.length >= 6) score += 1;
    if (checks.length) score += 1;
    if (checks.lower) score += 1;
    if (checks.upper) score += 1;
    if (checks.number) score += 1;
    if (checks.special) score += 1;
    if (password.length >= 12) score += 1;
    if (score <= 2) return { percent: 20, level: 'weak', label: 'Weak — add uppercase, numbers, symbols' };
    if (score <= 3) return { percent: 40, level: 'fair', label: 'Fair — try adding more variety' };
    if (score <= 5) return { percent: 70, level: 'good', label: 'Good — nice password!' };
    return { percent: 100, level: 'strong', label: 'Strong — excellent password!' };
}
function showError(fieldId, message) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    var group = field.closest('.form-group');
    if (group) {
        group.classList.add('error');
        var errorEl = group.querySelector('.form-error');
        if (errorEl) errorEl.textContent = message;
    }
}
function clearErrors(form) {
    var groups = form.querySelectorAll('.form-group');
    groups.forEach(function(group) {
        group.classList.remove('error');
    });
}
function isValidEmail(email) {
    var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
}
function isStrongPassword(password) {
    if (password.length < 6) return false;
    if (!/[a-z]/.test(password)) return false;
    if (!/[A-Z]/.test(password)) return false;
    if (!/[0-9]/.test(password)) return false;
    return true;
}
var loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        clearErrors(this);
        var valid = true;

        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;

        if (email === '') {
            showError('email', 'Please enter your email address.');
            valid = false;
        } else if (email.length > 100) {
            showError('email', 'Email must be at most 100 characters.');
            valid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'Please enter a valid email address.');
            valid = false;
        }

        if (password === '') {
            showError('password', 'Please enter your password.');
            valid = false;
        } else if (password.length > 255) {
            showError('password', 'Password is too long.');
            valid = false;
        }
        if (!valid) { e.preventDefault(); }
    });
}
var registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        clearErrors(this);
        var valid = true;

        var username = document.getElementById('username').value.trim();
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;

        if (username === '') {
            showError('username', 'Please enter a username.');
            valid = false;
        } else if (username.length < 3) {
            showError('username', 'Username must be at least 3 characters.');
            valid = false;
        } else if (username.length > 50) {
            showError('username', 'Username must be at most 50 characters.');
            valid = false;
        } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showError('username', 'Username can only contain letters, numbers, underscores.');
            valid = false;
        }

        if (email === '') {
            showError('email', 'Please enter your email address.');
            valid = false;
        } else if (email.length > 100) {
            showError('email', 'Email must be at most 100 characters.');
            valid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'Please enter a valid email address.');
            valid = false;
        }

        if (password === '') {
            showError('password', 'Please enter a password.');
            valid = false;
        } else if (password.length < 6) {
            showError('password', 'Password must be at least 6 characters.');
            valid = false;
        } else if (password.length > 255) {
            showError('password', 'Password must be at most 255 characters.');
            valid = false;
        } else if (!isStrongPassword(password)) {
            showError('password', 'Password needs at least 1 uppercase, 1 lowercase, and 1 number.');
            valid = false;
        }

        if (confirmPassword === '') {
            showError('confirm_password', 'Please confirm your password.');
            valid = false;
        } else if (password !== confirmPassword) {
            showError('confirm_password', 'Passwords do not match.');
            valid = false;
        }

        if (!valid) { e.preventDefault(); }
    });
}
var checkoutForm = document.getElementById('checkoutForm');
if (checkoutForm) {
    checkoutForm.addEventListener('submit', function(e) {
        clearErrors(this);
        var valid = true;

        var fullname = document.getElementById('fullname').value.trim();
        var address = document.getElementById('address').value.trim();
        var city = document.getElementById('city').value.trim();
        var phone = document.getElementById('phone').value.trim();

        if (fullname === '') { showError('fullname', 'Please enter your full name.'); valid = false; }
        else if (fullname.length > 100) { showError('fullname', 'Name must be at most 100 characters.'); valid = false; }

        if (address === '') { showError('address', 'Please enter your address.'); valid = false; }
        else if (address.length > 255) { showError('address', 'Address must be at most 255 characters.'); valid = false; }

        if (city === '') { showError('city', 'Please enter your city.'); valid = false; }
        else if (city.length > 100) { showError('city', 'City must be at most 100 characters.'); valid = false; }

        if (phone === '') { showError('phone', 'Please enter your phone number.'); valid = false; }
        else if (phone.length > 20) { showError('phone', 'Phone must be at most 20 characters.'); valid = false; }

        if (!valid) { e.preventDefault(); }
    });
}
var profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
        clearErrors(this);
        var valid = true;

        var username = document.getElementById('username').value.trim();
        var email = document.getElementById('email').value.trim();

        if (username === '' || username.length < 3) {
            showError('username', 'Username must be at least 3 characters.');
            valid = false;
        } else if (username.length > 50) {
            showError('username', 'Username must be at most 50 characters.');
            valid = false;
        }

        if (email === '' || !isValidEmail(email)) {
            showError('email', 'Please enter a valid email.');
            valid = false;
        } else if (email.length > 100) {
            showError('email', 'Email must be at most 100 characters.');
            valid = false;
        }
        var newPass = document.getElementById('new_password');
        var confirmPass = document.getElementById('confirm_new_password');
        
        if (newPass && newPass.value !== '') {
            if (newPass.value.length < 6) {
                showError('new_password', 'Password must be at least 6 characters.');
                valid = false;
            } else if (newPass.value.length > 255) {
                showError('new_password', 'Password must be at most 255 characters.');
                valid = false;
            } else if (!isStrongPassword(newPass.value)) {
                showError('new_password', 'Password needs at least 1 uppercase, 1 lowercase, and 1 number.');
                valid = false;
            } else if (confirmPass && newPass.value !== confirmPass.value) {
                showError('confirm_new_password', 'Passwords do not match.');
                valid = false;
            }
        }

        if (!valid) { e.preventDefault(); }
    });
}
var productForm = document.getElementById('productForm');
if (productForm) {
    productForm.addEventListener('submit', function(e) {
        clearErrors(this);
        var valid = true;

        var name = document.getElementById('name').value.trim();
        var price = document.getElementById('price').value;
        var stock = document.getElementById('stock').value;

        if (name === '') { showError('name', 'Product name is required.'); valid = false; }
        else if (name.length > 100) { showError('name', 'Name must be at most 100 characters.'); valid = false; }

        if (price === '' || parseFloat(price) <= 0) { showError('price', 'Enter a valid price.'); valid = false; }
        else if (parseFloat(price) > 99999.99) { showError('price', 'Price must be under €99,999.99.'); valid = false; }

        if (stock === '' || parseInt(stock) < 0) { showError('stock', 'Enter a valid stock quantity.'); valid = false; }
        else if (parseInt(stock) > 9999) { showError('stock', 'Stock must be under 9,999.'); valid = false; }

        var desc = document.getElementById('description');
        if (desc && desc.value.length > 2000) {
            showError('description', 'Description must be at most 2000 characters.');
            valid = false;
        }

        var image = document.getElementById('image');
        if (image && image.value.length > 255) {
            showError('image', 'Filename must be at most 255 characters.');
            valid = false;
        }

        if (!valid) { e.preventDefault(); }
    });
}
