let typingTimer;
let isTyping = false;

function updateTypingStatus(status) {
    fetch("typing_status.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "receiver_id=" + chatUserId + "&typing=" + (status ? 1 : 0)
    });
}

document.getElementById("messageInput").addEventListener("input", () => {
    if (!isTyping) {
        isTyping = true;
        updateTypingStatus(true);
    }
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        isTyping = false;
        updateTypingStatus(false);
    }, 5000);
});

function checkTyping() {
    fetch("check_typing.php?chat=" + chatUserId)
        .then(res => res.text())
        .then(data => {
            const indicator = document.getElementById("typingIndicator");
            indicator.innerHTML = data;
            indicator.style.opacity = data ? 1 : 0;
        });
}
setInterval(checkTyping, 1000);
