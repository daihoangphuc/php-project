// Hàm format số tiền
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(amount);
}

// Hàm format ngày giờ
function formatDateTime(datetime) {
    return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(datetime));
}

// Hàm xác nhận xóa
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return confirm(message);
}

// Hàm hiển thị thông báo
function showAlert(message, type = 'success') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} animate-fade-in`;
    alert.textContent = message;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Hàm tải ảnh preview
function previewImage(input, imgElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imgElement.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Hàm copy text
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Đã sao chép vào clipboard');
    });
}

// Hàm scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Thêm nút scroll to top khi scroll xuống
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scroll-to-top');
    if (window.scrollY > 300) {
        scrollBtn.classList.remove('hidden');
    } else {
        scrollBtn.classList.add('hidden');
    }
}); 