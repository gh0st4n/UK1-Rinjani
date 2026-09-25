<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- Toggle Sidebar Mobile ---
        const btnToggle = document.getElementById("btnToggleSidebar");
        const sidebar = document.querySelector(".sidebar");
        const overlay = document.getElementById("sidebarOverlay");

        if (btnToggle && sidebar && overlay) {
            btnToggle.addEventListener("click", function() {
                sidebar.classList.toggle("show");
                overlay.classList.toggle("show");
            });

            overlay.addEventListener("click", function() {
                sidebar.classList.remove("show");
                overlay.classList.remove("show");
            });
        }

        // --- Auto Format Rupiah & Hanya Angka ---
        const hargaDisplay = document.getElementById("harga_display");
        const hargaHidden = document.getElementById("harga");

        if (hargaDisplay && hargaHidden) {
            hargaDisplay.addEventListener("input", function() {
                let cleanValue = this.value.replace(/\D/g, "");
                hargaHidden.value = cleanValue;

                if (cleanValue !== "") {
                    this.value = parseInt(cleanValue, 10).toLocaleString("id-ID");
                } else {
                    this.value = "";
                }
            });

            hargaDisplay.addEventListener("paste", function(e) {
                e.preventDefault();
                let pastedText = (e.clipboardData || window.clipboardData).getData("text");
                let cleanPasted = pastedText.replace(/\D/g, "");

                hargaHidden.value = cleanPasted;
                if (cleanPasted !== "") {
                    this.value = parseInt(cleanPasted, 10).toLocaleString("id-ID");
                } else {
                    this.value = "";
                }
            });
        }
    });
</script>
</body>

</html>