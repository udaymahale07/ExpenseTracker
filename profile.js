document.addEventListener('DOMContentLoaded', function() {
    const logoutButton = document.getElementById('logoutButton');
    const redirectButton = document.getElementById('redirect'); 
    const deleteButton = document.getElementById('deleteAccountButton');
    
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
        });
    }

    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            const confirmation = confirm('Are you sure you want to delete your account? This action cannot be undone.');
            
            if (confirmation) {
                window.location.href = 'delete_account.php';
            }
        });
    }


    if (redirectButton) {
        redirectButton.addEventListener('click', function() {
            window.location.href = "dailyexpense.php"; 
        });
    }
});

