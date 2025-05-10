</div>
        </div>
    </div>

    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> Klinik Sehat.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // konfirmasi hapus
        function confirmDelete(link) {
            if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                window.location.href = link;
            }
            return false;
        }
        
        $(document).ready(function() {
            setTimeout(function() {
                $(".alert").fadeOut("slow");
            }, 3000);
        });
    </script>
</body>
</html>