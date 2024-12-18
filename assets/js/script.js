// Fungsi untuk konfirmasi hapus
function confirmDelete(url, name = '') {
    if (confirm('Apakah Anda yakin ingin menghapus ' + name + ' ini?')) {
        window.location.href = url;
    }
}

// Fungsi untuk preview gambar sebelum upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Inisialisasi Select2 untuk semua elemen dengan class 'select2'
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});

// Inisialisasi DatePicker untuk semua elemen dengan class 'datepicker'
$(document).ready(function() {
    $('.datepicker').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: 'Pilih',
            cancelLabel: 'Batal',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        }
    });
});

// Format angka ke format rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(angka);
}

// Validasi form sebelum submit
function validateForm(formId) {
    var form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    return true;
}

// Fungsi untuk print area tertentu
function printArea(elementId) {
    var printContent = document.getElementById(elementId).innerHTML;
    var originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
}

// Fungsi untuk export tabel ke Excel
function exportTableToExcel(tableId, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableId);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    filename = filename ? filename + '.xls' : 'excel_data.xls';
    
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}

// Fungsi untuk menampilkan loading spinner
function showLoading() {
    document.getElementById('loadingSpinner').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

// Fungsi untuk validasi NIK
function validateNIK(nik) {
    return /^[0-9]{16}$/.test(nik);
}

// Fungsi untuk validasi email
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Fungsi untuk auto-capitalize input
function autoCapitalize(input) {
    input.value = input.value.toUpperCase();
}

// Fungsi untuk membatasi input hanya angka
function numbersOnly(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal(tanggal) {
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(tanggal).toLocaleDateString('id-ID', options);
}

// Event listener untuk form submit dengan AJAX
$(document).on('submit', '.ajax-form', function(e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');
    var method = form.attr('method');
    var data = new FormData(this);

    $.ajax({
        url: url,
        type: method,
        data: data,
        processData: false,
        contentType: false,
        beforeSend: function() {
            showLoading();
        },
        success: function(response) {
            if(response.success) {
                alert(response.message);
                if(response.redirect) {
                    window.location.href = response.redirect;
                }
            } else {
                alert(response.message || 'Terjadi kesalahan');
            }
        },
        error: function() {
            alert('Terjadi kesalahan sistem');
        },
        complete: function() {
            hideLoading();
        }
    });
});

// Inisialisasi tooltip Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
}); 