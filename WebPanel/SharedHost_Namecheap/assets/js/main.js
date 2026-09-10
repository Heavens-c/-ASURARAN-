/**
 * RanOnline Web Panel — Interactive Main Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
    // Password match validation
    const passInput = document.getElementById('reg_password');
    const confirmInput = document.getElementById('reg_confirm_password');
    const errorMsg = document.getElementById('pass_match_error');

    if (passInput && confirmInput && errorMsg) {
        function validateMatch() {
            if (confirmInput.value && passInput.value !== confirmInput.value) {
                errorMsg.style.display = 'block';
                errorMsg.textContent = 'Passwords do not match.';
            } else {
                errorMsg.style.display = 'none';
            }
        }
        passInput.addEventListener('input', validateMatch);
        confirmInput.addEventListener('input', validateMatch);
    }

    // Auto-dismiss alert boxes after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 6000);
    });
});

/**
 * Copy voucher or download link to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard: ' + text);
    }).catch(err => {
        console.error('Could not copy text: ', err);
    });
}
