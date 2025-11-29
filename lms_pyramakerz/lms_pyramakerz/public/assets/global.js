document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("form").forEach((form) => {
        form.addEventListener("submit", function () {
            let submitButtons = form.querySelectorAll("[type='submit']");
            submitButtons.forEach((button) => {
                button.disabled = true;
                button.innerHTML = "Submitting..."; // Optional: Change button text
            });
        });
    });
});