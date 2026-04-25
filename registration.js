document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registration');
    const messageElement = document.getElementById('errorm');

    if (!form || !messageElement) {
        console.error('Required form or message element not found.');
        return;
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const successMessage = urlParams.get('success');
    const errorMessage = urlParams.get('error');

    if (successMessage) {
        messageElement.textContent = decodeURIComponent(successMessage);
        messageElement.style.color = 'green';
        window.history.replaceState(null, null, window.location.pathname);
    } else if (errorMessage) {
        messageElement.textContent = decodeURIComponent(errorMessage);
        messageElement.style.color = 'red';
        window.history.replaceState(null, null, window.location.pathname);
    }

    function displayValidationMessage(message, isSuccess = false) {
        messageElement.textContent = message;
        messageElement.style.color = isSuccess ? 'green' : 'red';
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value.trim();
        const confirmPassword = document.getElementById('confirm_password').value.trim();

        displayValidationMessage('');
        
        if (email === '' || phone === '' || password === '' || confirmPassword === '') {
            displayValidationMessage('All fields are required.');
            return;
        }

        if (password !== confirmPassword) {
            displayValidationMessage('Passwords do not match.');
            return;
        }
        
        fetch('registration.php', {
            method: 'POST',
            body: new URLSearchParams({
                email: email, 
                phone: phone, 
                password: password,
                confirmPassword: confirmPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'login.html?success=' + encodeURIComponent(data.message);
            } else {
                displayValidationMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            displayValidationMessage('A critical error occurred: ' + error.message);
        });
    });
});

