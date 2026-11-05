// ==============================================================
// MAIN JAVASCRIPT SISTEM PET SHOP
// ==============================================================

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle untuk Tampilan Mobile / Tablet
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('d-none');
        });
    }

    // 2. Konfirmasi Hapus Data
    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            const itemName = this.getAttribute('data-name') || 'data ini';
            const confirmMsg = `Apakah Anda yakin ingin menghapus "${itemName}"?\n\nData yang berkaitan mungkin akan terhapus atau dibatasi sesuai constraint database.`;
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });

    // 3. Kalkulator Otomatis Biaya Penitipan (pada form tambah/edit penitipan)
    const tglMasukInput = document.getElementById('tgl_masuk');
    const tglKeluarInput = document.getElementById('tgl_keluar');
    const kandangSelect = document.getElementById('id_kandang');
    const layananSelect = document.getElementById('id_layanan');
    const totalHariInput = document.getElementById('total_hari');
    const totalBiayaInput = document.getElementById('total_biaya');
    const totalBiayaPreview = document.getElementById('total_biaya_preview');

    function hitungTotal() {
        if (!tglMasukInput || !tglKeluarInput || !kandangSelect || !layananSelect) return;

        const tglMasuk = new Date(tglMasukInput.value);
        const tglKeluar = new Date(tglKeluarInput.value);

        if (isNaN(tglMasuk.getTime()) || isNaN(tglKeluar.getTime())) return;

        // Hitung selisih hari
        const timeDiff = tglKeluar.getTime() - tglMasuk.getTime();
        let daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        if (daysDiff <= 0) daysDiff = 1; // Minimal dihitung 1 hari

        if (totalHariInput) totalHariInput.value = daysDiff;

        // Ambil tarif kandang dan tarif layanan dari data attribute
        const selectedKandang = kandangSelect.options[kandangSelect.selectedIndex];
        const selectedLayanan = layananSelect.options[layananSelect.selectedIndex];

        const tarifKandang = parseFloat(selectedKandang ? selectedKandang.getAttribute('data-tarif') || 0 : 0);
        const tarifLayanan = parseFloat(selectedLayanan ? selectedLayanan.getAttribute('data-tarif') || 0 : 0);

        const total = (daysDiff * tarifKandang) + tarifLayanan;

        if (totalBiayaInput) totalBiayaInput.value = total;
        if (totalBiayaPreview) {
            totalBiayaPreview.innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    }

    if (tglMasukInput) tglMasukInput.addEventListener('change', hitungTotal);
    if (tglKeluarInput) tglKeluarInput.addEventListener('change', hitungTotal);
    if (kandangSelect) kandangSelect.addEventListener('change', hitungTotal);
    if (layananSelect) layananSelect.addEventListener('change', hitungTotal);
});

