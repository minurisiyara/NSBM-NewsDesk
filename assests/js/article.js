// ================= BOOKMARK BUTTON =================

const bookmarkBtn = document.getElementById("bookmarkBtn");

if (bookmarkBtn) {
    bookmarkBtn.addEventListener("click", function () {

        // Toggle the saved state.
        this.classList.toggle("saved");

        if (this.classList.contains("saved")) {
            this.innerHTML = "♥ <span>Article Saved</span>";
        } else {
            this.innerHTML = "♡ <span>Save Article</span>";
        }
    });
}


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


// ================= COMMENT FORM =================

const commentForm = document.getElementById("commentForm");

if (commentForm) {

    commentForm.addEventListener("submit", function (event) {

        // Prevent the page from refreshing.
        event.preventDefault();

        const name = document.getElementById("commentName").value.trim();
        const comment = document.getElementById("commentText").value.trim();

        const commentMessage = document.getElementById("commentMessage");
        const commentList = document.getElementById("commentList");

        if (name === "" || comment === "") {
            commentMessage.textContent = "Please fill in all fields.";
            return;
        }

        // Create a new comment element.
        const commentCard = document.createElement("div");
        commentCard.className = "comment-card";

        const commentAuthor = document.createElement("h4");
        commentAuthor.textContent = name;

        const commentContent = document.createElement("p");
        commentContent.textContent = comment;

        commentCard.appendChild(commentAuthor);
        commentCard.appendChild(commentContent);

        commentList.prepend(commentCard);

        commentMessage.textContent = "Your comment was added temporarily.";

        // Clear the form.
        commentForm.reset();

    });
}
