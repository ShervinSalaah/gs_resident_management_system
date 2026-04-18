document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('container');
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    
    // Desktop overlay toggle (only works if overlay exists)
    if (signUpButton) {
        signUpButton.addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });
    }
    if (signInButton) {
        signInButton.addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });
    }

    // Mobile toggle links
    const showSignInMobile = document.getElementById('showSignInMobile');
    const showSignUpMobile = document.getElementById('showSignUpMobile');

    function setMobileActive(showSignup) {
        if (showSignup) {
            container.classList.add('show-signup');
            showSignInMobile.classList.remove('active');
            showSignUpMobile.classList.add('active');
        } else {
            container.classList.remove('show-signup');
            showSignInMobile.classList.add('active');
            showSignUpMobile.classList.remove('active');
        }
    }

    if (showSignInMobile) {
        showSignInMobile.addEventListener('click', (e) => {
            e.preventDefault();
            setMobileActive(false);
        });
    }
    if (showSignUpMobile) {
        showSignUpMobile.addEventListener('click', (e) => {
            e.preventDefault();
            setMobileActive(true);
        });
    }

    // Optional: sync desktop and mobile states? Not necessary.
});