    </div><!-- /admin-body -->
  </div><!-- /admin-content -->
</div><!-- /admin-wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('open');
}

// Auto-dismiss alerts
document.querySelectorAll('.auto-dismiss').forEach(el => {
  setTimeout(() => {
    el.style.opacity = '0';
    el.style.transition = 'opacity 0.5s';
    setTimeout(() => el.remove(), 500);
  }, 4000);
});

// Confirm delete
document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
  btn.addEventListener('click', function(e) {
    if (!confirm('Are you sure you want to delete this? This cannot be undone.')) {
      e.preventDefault();
    }
  });
});

// Image preview
document.querySelectorAll('.img-upload-input').forEach(input => {
  input.addEventListener('change', function() {
    const preview = document.getElementById(this.dataset.preview);
    if (!preview) return;
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(file);
    }
  });
});
</script>
</body>
</html>
