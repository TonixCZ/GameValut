document.addEventListener('DOMContentLoaded', function () {
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');
    const cardContainer = document.getElementById('card-container');
    const nicknameInput = document.getElementById('nickname');
    const nicknameCheck = document.getElementById('nickname-check');
    const regEmailInput = document.getElementById('reg-email');
    const emailCheck = document.getElementById('email-check');
    const alertBox = document.getElementById('message-alert');

    if (showRegister) {
        showRegister.addEventListener('click', function (e) {
            e.preventDefault();
            cardContainer.classList.add('flip');
        });
    }

    if (showLogin) {
        showLogin.addEventListener('click', function (e) {
            e.preventDefault();
            cardContainer.classList.remove('flip');
        });
    }

    if (alertBox) {
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 5000);
    }

    if (nicknameInput) {
        nicknameInput.addEventListener('input', function () {
            const val = this.value.trim();
            if (val.length < 3) {
                nicknameCheck.textContent = '';
                return;
            }
            fetch('includes/check_user.php?nickname=' + encodeURIComponent(val))
                .then(r => r.text())
                .then(msg => nicknameCheck.textContent = msg);
        });
    }

    if (regEmailInput) {
        regEmailInput.addEventListener('input', function () {
            const val = this.value.trim();
            if (!val.includes('@')) {
                emailCheck.textContent = '';
                return;
            }
            fetch('includes/check_user.php?email=' + encodeURIComponent(val))
                .then(r => r.text())
                .then(msg => emailCheck.textContent = msg);
        });
    }
});