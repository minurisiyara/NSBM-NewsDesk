/* NSBM NewsDesk Admin Panel Client Script */
(function () {
    "use strict";

    function toggleSidebar() {
        const side = document.getElementById("adminSidebar");
        if (side) {
            side.classList.toggle("open");
        }
    }

    function closeSidebar() {
        const side = document.getElementById("adminSidebar");
        if (side) {
            side.classList.remove("open");
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add("open");
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove("open");
        }
    }

    window.AdminUI = {
        toggleSidebar,
        closeSidebar,
        openModal,
        closeModal
    };

    document.addEventListener("DOMContentLoaded", function () {
        const burger = document.getElementById("sidebarBurger");
        if (burger) {
            burger.addEventListener("click", toggleSidebar);
        }

        document.querySelectorAll("#adminSidebar nav a").forEach(function (link) {
            link.addEventListener("click", closeSidebar);
        });

        document.querySelectorAll(".mod").forEach(function (modal) {
            modal.addEventListener("click", function (e) {
                if (e.target === modal) {
                    modal.classList.remove("open");
                }
            });
        });

        // Live client-side table filter for search/status/category inputs
        const searchInput = document.getElementById("adminSearchInput");
        const statusFilter = document.getElementById("adminStatusFilter");
        const categoryFilter = document.getElementById("adminCategoryFilter");
        const tableBody = document.getElementById("adminFilterBody");

        function applyTableFilters() {
            if (!tableBody) return;
            const q = (searchInput ? searchInput.value : "").trim().toLowerCase();
            const st = (statusFilter ? statusFilter.value : "").trim().toLowerCase();
            const cat = (categoryFilter ? categoryFilter.value : "").trim().toLowerCase();

            tableBody.querySelectorAll("tr[data-filter-row]").forEach(function (row) {
                const text = (row.dataset.search || "").toLowerCase();
                const rowStatus = (row.dataset.status || "").toLowerCase();
                const rowCat = (row.dataset.category || "").toLowerCase();

                const matchQ = !q || text.includes(q);
                const matchS = !st || rowStatus === st;
                const matchC = !cat || rowCat === cat;

                row.style.display = (matchQ && matchS && matchC) ? "" : "none";
            });
        }

        if (searchInput) searchInput.addEventListener("input", applyTableFilters);
        if (statusFilter) statusFilter.addEventListener("change", applyTableFilters);
        if (categoryFilter) categoryFilter.addEventListener("change", applyTableFilters);

        // Render Chart.js charts if present on Reports page
        if (window.Chart && window.ADMIN_REPORT_DATA) {
            const c1El = document.getElementById("categoryChart");
            const c2El = document.getElementById("monthlyChart");

            if (c1El) {
                new Chart(c1El, {
                    type: "doughnut",
                    data: {
                        labels: window.ADMIN_REPORT_DATA.categoryLabels || [],
                        datasets: [{
                            data: window.ADMIN_REPORT_DATA.categoryCounts || [],
                            backgroundColor: ["#0b4b32", "#2f8f66", "#d08a3c", "#3b82a6", "#7c6bc4"]
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { position: "bottom" } }
                    }
                });
            }

            if (c2El) {
                new Chart(c2El, {
                    type: "bar",
                    data: {
                        labels: ["Jun", "Jul", "Aug", "Sep", "Oct", "Nov"],
                        datasets: [{
                            label: "Articles",
                            data: window.ADMIN_REPORT_DATA.monthlyCounts || [0, 0, 0, 0, 0, 0],
                            backgroundColor: "#0b4b32",
                            borderRadius: 6
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }
    });
})();