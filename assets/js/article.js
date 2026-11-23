// ================= SHARE BUTTON =================

const shareBtn = document.getElementById("shareBtn");

if (shareBtn) {
    shareBtn.addEventListener("click", async function () {

        const shareData = {
            title: document.title,
            url: window.location.href
        };

        try {
            if (navigator.share) {
                await navigator.share(shareData);
            } else {
                await navigator.clipboard.writeText(shareData.url);
                alert("Article link copied!");
            }
        } catch (error) {
            console.log("Sharing was cancelled or unavailable.");
        }

    });
}

// Load bookmark functionality
const bookmarkScript = document.createElement("script");
bookmarkScript.src = "../assets/js/bookmark.js";
document.body.appendChild(bookmarkScript);