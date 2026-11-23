
document.addEventListener("DOMContentLoaded", function () {

    const commentList = document.getElementById("commentList");
    const commentForm = document.getElementById("commentForm");
    const commentMessage = document.getElementById("commentMessage");

    if (!commentList) return;

    const articleId = new URLSearchParams(window.location.search).get("id") || 1;

    function showMessage(message, type = "info") {
        if (!commentMessage) return;

        commentMessage.textContent = message;
        commentMessage.className = "comment-message " + type;
    }

    async function loadComments() {
        commentList.textContent = "Loading comments...";

        try {
            const response = await fetch(
                "../backend/comments/read.php?article_id=" + encodeURIComponent(articleId)
            );

            if (!response.ok) throw new Error("Unable to load comments.");

            const data = await response.json();

            const comments = Array.isArray(data)
                ? data
                : (data.comments || []);

            commentList.replaceChildren();

            if (comments.length === 0) {
                commentList.textContent = "No comments yet. Be the first to comment!";
                return;
            }

            comments.forEach(function (comment) {

                const card = document.createElement("div");
                card.className = "comment-card";

                const name = document.createElement("h4");
                name.textContent = comment.name || "Student";

                const date = document.createElement("small");
                date.textContent = comment.created_at || "";

                const content = document.createElement("p");
                content.textContent = comment.content || "";

                card.append(name, date, content);

                if (comment.is_owner) {

                    const editButton = document.createElement("button");
                    editButton.type = "button";
                    editButton.textContent = "Edit";

                    const deleteButton = document.createElement("button");
                    deleteButton.type = "button";
                    deleteButton.textContent = "Delete";

                    editButton.addEventListener("click", function () {
                        editComment(card, comment);
                    });

                    deleteButton.addEventListener("click", function () {
                        deleteComment(comment.id);
                    });

                    card.append(editButton, deleteButton);
                }

                commentList.appendChild(card);
            });

        } catch (error) {
            commentList.textContent =
                "Unable to load comments. Please refresh the page.";
        }
    }

    function editComment(card, comment) {

        card.replaceChildren();

        const textarea = document.createElement("textarea");
        textarea.value = comment.content || "";
        textarea.maxLength = 2000;

        const saveButton = document.createElement("button");
        saveButton.type = "button";
        saveButton.textContent = "Save";

        const cancelButton = document.createElement("button");
        cancelButton.type = "button";
        cancelButton.textContent = "Cancel";

        saveButton.addEventListener("click", async function () {

            const content = textarea.value.trim();

            if (!content) {
                showMessage("Please enter a comment.", "error");
                return;
            }

            const form = document.getElementById("commentForm");
            const token = form?.querySelector('[name="csrf_token"]')?.value;

            const formData = new FormData();
            formData.append("csrf_token", token || "");
            formData.append("comment_id", comment.id);
            formData.append("content", content);

            try {
                const response = await fetch("../backend/comments/update.php", {
                    method: "POST",
                    body: formData
                });

                const result = await response.text();

                if (!response.ok) throw new Error(result);

                showMessage("Comment updated successfully.", "success");
                loadComments();

            } catch (error) {
                showMessage("Unable to update comment.", "error");
            }
        });

        cancelButton.addEventListener("click", loadComments);

        card.append(textarea, saveButton, cancelButton);
    }

    async function deleteComment(commentId) {

        if (!confirm("Are you sure you want to delete this comment?")) {
            return;
        }

        const form = document.getElementById("commentForm");
        const token = form?.querySelector('[name="csrf_token"]')?.value;

        const formData = new FormData();
        formData.append("csrf_token", token || "");
        formData.append("comment_id", commentId);

        try {
            const response = await fetch("../backend/comments/delete.php", {
                method: "POST",
                body: formData
            });

            const result = await response.text();

            if (!response.ok) throw new Error(result);

            showMessage("Comment deleted successfully.", "success");
            loadComments();

        } catch (error) {
            showMessage("Unable to delete comment.", "error");
        }
    }

    if (commentForm) {
        commentForm.addEventListener("submit", function (event) {

            const textarea = document.getElementById("commentText");

            if (!textarea || !textarea.value.trim()) {
                event.preventDefault();
                showMessage("Please enter a comment.", "error");
            }
        });
    }

    loadComments();

});
