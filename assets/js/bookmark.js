
(function () {
    const button = document.getElementById("bookmarkBtn");
    const endpoint = "../backend/bookmarks/toggle.php";

    if (!button) return;

    let csrfToken = "";

    // Get the security token
    fetch(endpoint)
        .then(response => response.json())
        .then(data => {
            csrfToken = data.csrf;
        })
        .catch(error => console.error("Bookmark setup error:", error));

    button.addEventListener("click", async function () {
        if (!csrfToken) {
            alert("Please wait and try again.");
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    article_id: new URLSearchParams(window.location.search).get("id"),
                    csrf: csrfToken
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Unable to save bookmark.");
                return;
            }

            button.classList.toggle("saved", result.saved);
            button.innerHTML = result.saved
                ? '♥ <span>Article Saved</span>'
                : '♡ <span>Save Article</span>';

        } catch (error) {
            alert("Something went wrong. Please try again.");
            console.error(error);
        } finally {
            button.disabled = false;
        }
    });
})();
