<!-- Modal Cropper -->
<div class="cropper-modal-overlay" id="globalCropperModal">
  <div class="cropper-modal-content">
    <h3 style="margin: 0; font-family: var(--font-display); color: var(--teal-900);">Sesuaikan Ukuran Banner</h3>
    <p style="margin: 0; font-size: 0.9rem; color: var(--ink-soft);">Geser dan perbesar gambar untuk menyesuaikan area banner. Area di dalam kotak terang akan menjadi banner website.</p>
    
    <div class="cropper-img-container">
      <img id="globalCropperImage" src="" alt="Cropper Image" style="max-width: 100%;">
    </div>
    
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
      <button type="button" class="btn btn-outline" id="globalBtnCancelCrop">Batal</button>
      <button type="button" class="btn btn-primary" id="globalBtnSaveCrop" style="display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-crop-simple"></i> Potong & Simpan
      </button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  let cropper;
  let currentFileInput = null;
  let currentForm = null;

  const cropperModal = document.getElementById('globalCropperModal');
  const cropperImage = document.getElementById('globalCropperImage');
  const btnCancelCrop = document.getElementById('globalBtnCancelCrop');
  const btnSaveCrop = document.getElementById('globalBtnSaveCrop');

  document.body.addEventListener('change', function(e) {
    if (e.target && e.target.matches('.cropper-upload-input')) {
      currentFileInput = e.target;
      currentForm = e.target.closest('form');
      const files = e.target.files;
      
      if (files && files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
          cropperImage.src = event.target.result;
          cropperModal.classList.add('active');
          
          if (cropper) {
            cropper.destroy();
          }
          
          // Evaluate aspect ratio safely (e.g., "21/6" -> 3.5)
          const ratioStr = currentFileInput.dataset.aspectRatio || "21/9";
          const ratioParts = ratioStr.split('/');
          const aspectRatio = ratioParts.length === 2 ? parseInt(ratioParts[0]) / parseInt(ratioParts[1]) : 21/9;

          cropper = new Cropper(cropperImage, {
            aspectRatio: aspectRatio,
            viewMode: 1,
            autoCropArea: 1,
            dragMode: 'move',
            background: false,
          });
        };
        reader.readAsDataURL(files[0]);
      }
    }
  });

  function closeCropper() {
    cropperModal.classList.remove('active');
    if (currentFileInput) {
        currentFileInput.value = ''; // Reset input file
    }
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    currentFileInput = null;
    currentForm = null;
  }

  btnCancelCrop.addEventListener('click', closeCropper);

  btnSaveCrop.addEventListener('click', function() {
    if (!cropper || !currentFileInput || !currentForm) return;
    
    // Tampilkan loading (ubah teks tombol)
    const originalText = btnSaveCrop.innerHTML;
    btnSaveCrop.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengunggah...';
    btnSaveCrop.disabled = true;

    // Ambil hasil crop dalam bentuk blob kualitas 85% (jpeg)
    cropper.getCroppedCanvas({
      width: 1920, // Max width for high quality banner
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high',
    }).toBlob(function(blob) {
      if (!blob) {
        alert("Gagal memproses gambar.");
        btnSaveCrop.innerHTML = originalText;
        btnSaveCrop.disabled = false;
        return;
      }
      
      const formData = new FormData(currentForm);
      // Replace the file in FormData with the cropped blob
      formData.set(currentFileInput.name, blob, 'cropped_banner.jpg');
      
      fetch(window.location.href, {
        method: 'POST',
        body: formData
      })
      .then(response => {
        window.location.reload();
      })
      .catch(error => {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat mengunggah foto.');
        btnSaveCrop.innerHTML = originalText;
        btnSaveCrop.disabled = false;
      });
      
    }, 'image/jpeg', 0.85);
  });
});
</script>
