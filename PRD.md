AK

# Angkringan Pos

# Product Requirements

# Document

Rancangan kebutuhan produk untuk sistem kasir dan operasional usaha angkringan


Informasi

Nilai

Versi dokumen

1.0

Status

Draft siap menjadi acuan pengembangan

Tanggal

24 Oktober 2026

Platform

- Aplikasi web responsif untuk tablet

Teknologi target

Laravel, PHP, MySQL, Blade, Bootstrap, JavaScript

- Pengguna utama

Pemilik (Owner) dan Kasir (Cashier)

Pembayaran

Tunai dan QRIS dengan verifikasi manual pada MVP

Perangkat cetak

Printer thermal 58 mm atau 80 mm, bergantung kompatibilitas

TUJUAN DOKUMEN

Menyepakati ruang lingkup, kebutuhan pengguna, aturan bisnis, struktur data, dan kriteria penerimaan sebelum implementasi serta uji operasional.




Kontrol Dokumen

Atribut

Detail

Nama produk

Angkringan POS

Pemilik produk

Pemilik usaha angkringan

Pembaca utama

Developer, QA/tester, pemilik usaha, dan pihak implementasi

Status dokumen

Draft baseline; keputusan terbuka harus dikonfirmasi sebelum rilis

Versi

1.0

Riwayat Perubahan

Versi

Tanggal

Perubahan

Status

1.0

24 Okt 2026

Baseline PRD: kebutuhan MVP POS tablet, pembayaran tunai/QRIS, stok produk siap jual, shift, struk, dan laporan.

Draft untuk implementasi

Ringkasan Produk

Angkringan POS adalah aplikasi web untuk mencatat penjualan makanan dan minuman, mengelola stok produk siap jual, memproses pembayaran tunai atau QRIS, mencetak struk, mengelola shift kasir, serta menyediakan laporan sederhana untuk pemilik. Aplikasi dirancang untuk satu lokasi usaha dan digunakan melalui tablet.

Prinsip utama

Transaksi, pembayaran, perubahan stok, dan laporan harus konsisten. Tampilan yang menarik saja tidak cukup: setiap proses kritis harus tersimpan ke database, tervalidasi di server, dan dapat diaudit.

Isi Dokumen

## 1. Latar Belakang dan Tujuan Produk

## 2. Visi, Sasaran, dan Metrik Keberhasilan

## 3. Pengguna dan Hak Akses

## 4. Ruang Lingkup MVP

## 5. Alur Pengguna dan Proses Bisnis

## 6. Kebutuhan Fungsional

## 7. Aturan Bisnis dan Logika Data

## 8. Rancangan Data dan Relasi

## 9. Kebutuhan Antarmuka

## 10. Kebutuhan Nonfungsional

## 11. Laporan dan Definisi Metrik

## 12. Integrasi dan Perangkat

## 13. Kriteria Penerimaan dan Strategi Pengujian

## 14. Roadmap Implementasi

## 15. Risiko, Asumsi, dan Keputusan Terbuka

## 16. Definition of Done dan Glosarium




## 1. Latar Belakang dan Tujuan Produk

### 1.1 Latar Belakang

Operasional angkringan umumnya melibatkan banyak produk dengan harga berbeda, transaksi kecil tetapi sering, pencatatan stok, serta kebutuhan menghitung uang kas di akhir shift. Pencatatan manual dapat menimbulkan selisih uang, kesalahan hitung, stok yang tidak sesuai, dan kesulitan mengetahui menu terlaris atau estimasi keuntungan.

Angkringan POS ditujukan untuk memusatkan pencatatan transaksi dan aktivitas operasional ke dalam satu aplikasi yang mudah digunakan di tablet.

### 1.2 Pernyataan Masalah

- Kasir perlu memilih produk dan menghitung total dengan cepat tanpa perhitungan manual.

Pemilik membutuhkan riwayat penjualan dan perubahan stok yang dapat ditelusuri.

- Pembayaran tunai memerlukan perhitungan kembalian dan rekonsiliasi laci kas.

- Pembayaran QRIS perlu dibedakan dari pembayaran yang benar-benar sudah terkonfirmasi.

Omzet, estimasi laba kotor, dan pengeluaran perlu ditampilkan sebagai ukuran yang berbeda.

- Transaksi yang selesai tidak boleh dihapus sembarangan karena dapat merusak laporan dan histori stok.

### 1.3 Tujuan Produk

- Mempercepat proses transaksi kasir dengan antarmuka sentuh yang sederhana.

- Mengurangi kesalahan hitung total, kembalian, dan pengurangan stok.

- Menyediakan catatan transaksi, pembayaran, pembatalan, dan pergerakan stok yang dapat diaudit.

Membantu pemilik memantau omzet, penjualan per metode pembayaran, stok menipis, dan estimasi laba kotor.

- Memungkinkan pencetakan struk melalui mekanisme yang kompatibel dengan tablet dan printer thermal.

## 2. Visi, Sasaran, dan Metrik Keberhasilan

### 2.1 Visi

- Menjadi alat operasional sederhana yang membantu angkringan melayani pelanggan secara konsisten dan memberi pemilik gambaran kondisi usaha berdasarkan data transaksi yang tercatat.

### 2.2 Sasaran Produk

Sasaran

Indikator keberhasilan yang diusulkan

Cara verifikasi

- Transaksi cepat

- Checkout standar setelah keranjang siap dapat diselesaikan dalam ≤ 30 detik, tidak termasuk waktu verifikasi QRIS.

- Uji waktu dengan skenario nyata.

Akurasi stok

- Setiap transaksi selesai mengurangi stok tepat satu kali; pembatalan sah memulihkan jumlah yang tepat.

Feature test dan rekonsiliasi stok.

Akurasi kas

Saldo kas yang diharapkan dapat direkonsiliasi dari modal awal, penjualan tunai, pengeluaran dan pengembalian tunai.

- Uji buka/tutup shift.

Keandalan transaksi

Permintaan checkout yang dikirim ulang tidak menciptakan transaksi ganda.

- Uji idempotensi dan pengiriman ulang.

Kemudahan penggunaan

- Kasir dapat menyelesaikan alur penjualan standar setelah orientasi singkat.

- Uji tugas dengan pengguna.

Keterlacakan

- Perubahan harga, stok, dan pembatalan penting memiliki catatan aktivitas.

Inspeksi audit log.

- Catatan metrik

- Target di atas adalah usulan awal untuk uji penerimaan, bukan hasil performa yang sudah terbukti. Target dapat dikalibrasi setelah pengujian pada tablet, jaringan, dan volume transaksi sebenarnya.

## 3. Pengguna dan Hak Akses

### 3.1 Persona Pengguna

Peran

Kebutuhan utama

Kendala yang perlu diatasi

- Owner / Pemilik

Melihat kinerja, mengelola produk dan harga, memantau stok, menyetujui pembatalan, mengelola pengguna dan pengaturan.

Perlu informasi yang ringkas serta laporan yang tidak mencampur omzet dengan laba.

Cashier / Kasir

Memilih menu, mengelola keranjang, menerima pembayaran, memberi struk, membuka dan menutup shift.

Antarmuka harus mudah disentuh, cepat, dan mencegah salah pilih atau checkout ganda.

### 3.2 Matriks Hak Akses

Fitur

Owner

Cashier

Dashboard

Seluruh metrik dan laporan

Ringkasan operasional dan shift yang diperlukan

POS dan checkout

Ya

Ya, saat shift terbuka

Lihat menu aktif

Ya

Ya

Tambah/edit/nonaktifkan produk dan kategori

Ya

Tidak

Ubah harga dan modal produk

Ya

Tidak

Tambah stok dan penyesuaian stok

Ya

Tidak; hanya melihat ketersediaan

Lihat transaksi

- Semua transaksi

- Transaksi sendiri atau yang terkait shift sesuai kebijakan

Pembatalan transaksi selesai

Menyetujui dan melakukan sesuai hak

Mengajukan permintaan sesuai kebijakan

Kelola pengeluaran

Ya

Pencatatan terbatas jika diizinkan

Buka/tutup shift sendiri

Ya

Ya

- Laporan laba dan ekspor

Ya

Tidak, kecuali laporan operasional yang diizinkan

Kelola akun, pengaturan, audit log

Ya

Tidak

- Semua hak akses wajib diverifikasi di backend. Menyembunyikan tombol pada frontend bukan kontrol keamanan yang memadai.




## 4. Ruang Lingkup MVP

### 4.1 Termasuk dalam MVP

Login, logout, akun Owner dan Cashier, serta pembatasan akses.

Dashboard operasional dan laporan berbasis data database.

CRUD kategori dan produk, harga jual, snapshot modal, stok siap jual, stok minimum, dan status aktif.

POS responsif tablet dengan pencarian, kategori, keranjang, kuantitas, makan di tempat/bungkus, dan checkout.

- Pembayaran tunai, perhitungan kembalian, QRIS dengan verifikasi manual, dan status pembayaran.

- Simpan transaksi dan detail produk secara konsisten, nomor transaksi unik, idempotensi checkout.

Pergerakan stok, penambahan stok, penyesuaian stok, produk rusak, dan pemulihan stok saat pembatalan yang sah.

Buka/tutup shift dan rekonsiliasi uang tunai.

Riwayat transaksi, pembatalan tercatat, cetak ulang struk, dan activity log.

Pengeluaran usaha, laporan harian/mingguan/bulanan, estimasi laba kotor, dan stok menipis.

Konfigurasi identitas usaha dan template struk thermal.

### 4.2 Tidak Termasuk dalam MVP

- Mode offline penuh ketika aplikasi tidak dapat menjangkau server.

Integrasi otomatis dengan payment gateway atau webhook QRIS; versi awal menggunakan verifikasi manual.

Pengelolaan bahan baku, resep, food costing otomatis, dan produksi makanan.

Pesanan online, integrasi marketplace/ojek online, program loyalitas, voucher kompleks, atau CRM pelanggan.

Pengelolaan meja bernomor, split bill, pembayaran gabungan, dan multi-cabang.

- Aplikasi Android native khusus kecuali integrasi printer yang diputuskan memerlukannya.

Penggajian, akuntansi lengkap, pajak otomatis, serta laporan laba bersih yang memasukkan seluruh biaya yang tidak dicatat.

### 4.3 Batasan Awal

Area

Keputusan baseline

Lokasi

- Satu lokasi usaha.

Perangkat

Tablet sebagai perangkat kasir utama; pemilik dapat mengakses dashboard melalui perangkat yang diizinkan.

Koneksi

- Aplikasi memerlukan koneksi ke server/database pada MVP; offline penuh bukan bagian dari rilis awal.

Pembayaran

- Satu metode pembayaran per transaksi: tunai atau QRIS; tidak ada split payment.

QRIS

Verifikasi manual sebelum transaksi selesai; penyedia dan prosedur merchant dikonfirmasi pemilik.

Stok

Jumlah produk siap jual; resep dan bahan baku tidak dikelola.

Printer

Browser print/driver/integrasi tambahan akan dipilih berdasarkan uji kompatibilitas perangkat nyata.

## 5. Alur Pengguna dan Proses Bisnis

### 5.1 Alur Buka Shift

## 1. Pengguna login dan memilih menu Shift.

## 2. Sistem memeriksa apakah ada shift terbuka yang bertentangan untuk lokasi/perangkat.

## 3. Kasir memasukkan modal tunai awal; sistem menyimpan pengguna, waktu, nilai modal dan status open.

## 4. Setelah shift valid terbuka, halaman POS dapat digunakan.




### 5.2 Alur Penjualan Tunai

## 1. Kasir memilih jenis pesanan dan menambahkan produk dari katalog.

## 2. Sistem menampilkan kuantitas, harga terkini, subtotal, diskon yang diizinkan, dan total.

## 3. Kasir memasukkan uang yang diterima; sistem menghitung kembalian dan menolak nilai yang kurang.

## 4. Backend memvalidasi harga, status produk, shift, stok, dan idempotency key.

## 5. Dalam satu transaksi database, sistem menyimpan sales dan sale_items, mengurangi stok yang dilacak, menulis inventory_movements, serta menetapkan pembayaran paid dan transaksi completed.

## 6. Sistem menampilkan konfirmasi dan menyediakan cetak struk.

### 5.3 Alur Pembayaran QRIS

## 1. Kasir memilih QRIS dan menampilkan QRIS usaha yang sudah dikonfigurasi.

## 2. Sistem mencatat status pending_payment dan payment_status pending; transaksi belum masuk laporan penjualan selesai dan stok belum berkurang.

## 3. Kasir memeriksa pembayaran pada kanal merchant yang disetujui usaha.

## 4. Setelah dikonfirmasi, backend memeriksa status transaksi dan stok terbaru.

## 5. Jika valid, backend menyelesaikan transaksi dan menurunkan stok satu kali dalam database transaction; jika stok tidak cukup, penyelesaian ditolak dan perlu tindakan operasional.

## 6. Jika pembayaran tidak diterima, transaksi tetap pending atau dibatalkan sesuai aturan dan audit log.

### 5.4 Alur Pembatalan / Pengembalian

## 1. Kasir mengajukan pembatalan dan menulis alasan; pembatalan tidak dilakukan dengan menghapus baris transaksi.

## 2. Owner memeriksa dan menyetujui tindakan sesuai kebijakan.

## 3. Sistem mengunci transaksi, memvalidasi status, lalu memastikan pembalikan hanya dapat dilakukan satu kali.

## 4. Sistem mencatat pengguna, waktu, alasan, status pembatalan/pengembalian, pergerakan stok balik, serta arus kas/refund jika ada.

## 5. Laporan mengecualikan transaksi void dan mengurangi refund sesuai definisi metrik.

### 5.5 Alur Tutup Shift

## 1. Kasir memilih tutup shift; sistem menghentikan transaksi baru pada shift tersebut.

## 2. Sistem menghitung saldo kas yang diharapkan dari modal awal, penjualan tunai, pengeluaran tunai, dan pengembalian tunai yang tercatat.

## 3. Kasir menghitung uang fisik dan memasukkan nominal aktual.

## 4. Sistem menyimpan saldo yang diharapkan, nominal fisik, selisih, catatan penutupan, pengguna, dan waktu.

## 5. Owner dapat meninjau rekonsiliasi dan menelusuri komponen yang membentuk saldo.

## 6. Kebutuhan Fungsional

Prioritas: P0 = wajib untuk rilis MVP; P1 = penting dan ditargetkan setelah alur inti stabil; P2 = pengembangan lanjutan. Setiap requirement harus dapat diverifikasi melalui demonstrasi, inspeksi, atau pengujian.

### 6.1 Autentikasi dan akses

ID

Fitur

Kebutuhan / kriteria ringkas

Prioritas

**FR-AUTH-01**

Login dan logout

- Pengguna login memakai kredensial valid dan logout mengakhiri sesi.

`P0`

**FR-AUTH-02**

Role-based access

- Owner dan Cashier memiliki akses berbeda; endpoint juga menerapkan authorization.

`P0`

**FR-AUTH-03**

Manajemen akun kasir

- Owner dapat membuat, mengubah status aktif, dan mengatur akses akun kasir.

`P0`

**FR-AUTH-04**

Proteksi akun nonaktif

Akun nonaktif tidak dapat membuat sesi baru dan sesi aktif ditangani sesuai kebijakan keamanan.

`P0`

### 6.2 Kategori dan produk

ID

Fitur

Kebutuhan / kriteria ringkas

Prioritas

**FR-PROD-01**

CRUD kategori

- Owner dapat membuat, mengubah, mengurutkan, dan menonaktifkan kategori.

`P0`

**FR-PROD-02**

CRUD produk

- Owner dapat mengelola nama, kategori, satuan, SKU, harga jual, modal, stok, minimum stok, dan status aktif.

`P0`

**FR-PROD-03**

Pencarian dan filter

- Kasir dapat mencari produk dan memfilter kategori dengan responsif.

`P0`

**FR-PROD-04**

Nonaktifkan produk

- Produk nonaktif tidak muncul untuk penjualan baru tetapi histori transaksi tetap ada.

`P0`

**FR-PROD-05**

Aturan harga

- Harga dan total transaksi dihitung dari data server; kasir tidak dapat memanipulasi harga melalui request.

`P0`

### 6.3 POS dan checkout

ID

Fitur

Kebutuhan / kriteria ringkas

Prioritas

**FR-POS-01**

Keranjang pesanan

- Kasir dapat menambah, mengurangi, menghapus item, serta melihat kuantitas dan total sebelum checkout.

`P0`

**FR-POS-02**

Tipe pesanan

- Setiap transaksi memilih dine_in atau takeaway.

`P0`

**FR-POS-03**

Validasi shift

- Transaksi hanya dapat diselesaikan jika shift wajib berstatus open.

`P0`

**FR-POS-04**

Validasi stok

- Sistem menolak penjualan ketika stok yang dilacak tidak mencukupi.

`P0`

**FR-POS-05**

Perhitungan total

Subtotal, diskon, dan total dihitung ulang di backend dan konsisten dengan rincian item.

`P0`

**FR-POS-06**

Pencegahan transaksi ganda

- Checkout menggunakan idempotency key atau mekanisme setara untuk menolak pengiriman ulang yang sama.

`P0`

### 6.4 Pembayaran dan transaksi

ID

Fitur

Kebutuhan / kriteria ringkas

Prioritas

**FR-PAY-01**

- Pembayaran tunai

- Sistem memvalidasi uang diterima, menghitung kembalian, dan menyelesaikan pembayaran yang mencukupi.

`P0`

**FR-PAY-02**

- QRIS manual

- Transaksi QRIS dimulai pending dan hanya diselesaikan setelah konfirmasi oleh petugas berwenang.

`P0`

**FR-PAY-03**

- Simpan transaksi atomik

Penjualan, detail item, stok, dan movement yang terkait berhasil bersama atau dibatalkan bersama saat terjadi error.

`P0`

**FR-SALE-01**

- Nomor transaksi unik

- Setiap penjualan mempunyai nomor struk unik yang tidak berubah.

`P0`

**FR-SALE-02**

Riwayat dan detail

- Pengguna berwenang dapat mencari transaksi, melihat detail item, pembayaran, kasir, waktu, dan status.

`P0`

**FR-SALE-03**

Pembatalan tercatat

Pembatalan punya alasan, pelaku, penyetuju, waktu, dan pembalikan stok/arus kas yang sesuai.

`P0`

**FR-SALE-04**

Cetak ulang

Struk dapat dicetak ulang tanpa membuat penjualan baru atau mengubah saldo.

`P0`

### 6.5 Stok dan shift

ID

Fitur

Kebutuhan / kriteria ringkas

Prioritas

**FR-INV-01**

- Stok masuk

- Owner dapat menambah stok dan sistem menyimpan pergerakan serta pengguna pencatat.

`P0`

**FR-INV-02**

Penyesuaian stok

- Owner dapat melakukan koreksi dengan alasan dan stok sebelum/sesudah.

`P0`

**FR-INV-03**

- Produk rusak

Pengurangan produk rusak tercatat sebagai movement tersendiri.

`P0`

**FR-INV-04**

- Stok menipis

- Produk dengan stok di bawah atau sama dengan minimum_stock ditandai pada dashboard.

`P0`

**FR-SHIFT-01**

Buka shift

- Kasir memasukkan modal awal dan sistem menyimpan waktu serta pengguna.

`P0`

**FR-SHIFT-02**

Tutup shift

- Kasir memasukkan uang fisik; sistem menghitung saldo harapan dan selisih.

`P0`

**FR-SHIFT-03**

Rumus saldo kas

Perhitungan kas memasukkan hanya arus kas tunai, tidak memasukkan QRIS sebagai uang laci.

`P0`

### 6.6 Pengeluaran, laporan, dan pengaturan

ID

Fitur

Kebutuhan / kriteria ringkas

Prioritas

**FR-EXP-01**

Catat pengeluaran

- Pengguna berwenang menyimpan kategori, jumlah, tanggal, metode pembayaran, deskripsi, dan shift terkait.

`P0`

**FR-REP-01**

- Laporan penjualan

- Owner melihat omzet per periode, jumlah transaksi, metode pembayaran, dan tipe pesanan.

`P0`

**FR-REP-02**

- Laporan produk

- Owner melihat kuantitas terjual dan menu terlaris.

`P0`

**FR-REP-03**

Estimasi laba

- Sistem memakai snapshot modal transaksi dan pengeluaran tercatat; UI menjelaskan keterbatasan hitungan.

`P0`

**FR-REP-04**

Filter dan ekspor

- Laporan dapat difilter berdasarkan tanggal; ekspor menjadi target P1 bila memperpanjang jadwal MVP.

`P1`

**FR-SET-01**

Identitas usaha

- Owner mengelola nama, alamat, kontak, footer struk, dan preferensi printer.

`P0`

**FR-AUD-01**

Audit aktivitas

- Perubahan harga, stok, pembatalan, akun, dan pengaturan penting tercatat serta tidak dapat diedit kasir.

`P0`

## 7. Aturan Bisnis dan Logika Data

### 7.1 Status Transaksi dan Pembayaran

Objek

Status

Arti dan ketentuan

Transaksi

pending_payment

- Transaksi sedang menunggu pembayaran; belum masuk omzet selesai dan stok belum berkurang.

Transaksi

completed

- Pembayaran terkonfirmasi; item, penjualan, stok, dan movement sudah tersimpan konsisten.

Transaksi

voided

- Transaksi dibatalkan secara resmi dengan alasan, pelaku, waktu, dan pembalikan yang sesuai.

Pembayaran

pending

- Pembayaran belum diverifikasi atau belum diterima.

Pembayaran

paid

- Pembayaran telah dikonfirmasi menurut metode yang digunakan.

Pembayaran

failed

- Pembayaran gagal atau tidak dapat dilanjutkan menurut prosedur.

Pembayaran

refunded

Pengembalian dana telah dicatat; nominal dan metode refund harus dapat ditelusuri.

### 7.2 Aturan Stok

- Stok hanya berubah melalui transaksi penjualan, stok masuk, waste/produk rusak, penyesuaian yang beralasan, atau pembalikan yang sah.

- Produk dengan track_stock=false tidak divalidasi terhadap stock_qty dan tidak menghasilkan movement penjualan.

- Untuk produk dengan track_stock=true, stock_qty tidak boleh turun di bawah nol.

- Checkout mengunci record produk atau menggunakan strategi konkurensi yang setara agar dua kasir tidak menjual unit terakhir secara bersamaan.

- Setiap efek stok mempunyai referensi sumber unik atau penanda idempotensi untuk mencegah pergerakan ganda.

- Histori penjualan merujuk snapshot nama, satuan, harga jual, dan harga modal pada saat transaksi; bukan nilai produk terkini.

### 7.3 Aturan Uang dan Pembulatan

- Gunakan DECIMAL untuk nilai uang; jangan menggunakan floating-point untuk menyimpan nilai finansial.

Total penjualan dihitung ulang di backend. Nilai item dan total harus mengikuti kebijakan diskon yang terdokumentasi.

Uang diterima pada pembayaran tunai harus sama dengan atau lebih besar dari total transaksi.

Kembalian tunai = uang diterima - total yang harus dibayar.

Pendapatan tunai untuk laporan dan shift berdasarkan total penjualan tunai yang selesai, bukan uang tender pelanggan sebelum kembalian.

- QRIS tidak dihitung sebagai saldo uang tunai di laci.

Diskon, jika diaktifkan, pada MVP hanya dapat diberikan oleh owner atau role yang secara eksplisit diberi hak; setiap diskon harus tercatat.

### 7.4 Konsistensi Checkout

- Transaksi atomik

- Simpan penjualan, item, pembayaran, update stok, dan inventory movement dalam satu database transaction ketika transaksi diselesaikan. Jika satu operasi kritis gagal, rollback semua perubahan terkait. Status dan idempotency key harus diperiksa sebelum efek transaksi dijalankan ulang.

### 7.5 Rumus Shift

Saldo kas yang diharapkan = modal awal + penerimaan penjualan tunai yang selesai - pengeluaran tunai - refund tunai + setoran kas masuk lain yang tercatat - pengeluaran kas lain yang tercatat.

Selisih kas = uang fisik yang dihitung saat tutup shift - saldo kas yang diharapkan. Setiap arus kas non-penjualan yang memengaruhi uang laci perlu dicatat terpisah agar rumus bisa direkonsiliasi.

## 8. Rancangan Data dan Relasi

Database baseline: angkringan_pos_db. Gunakan MySQL/InnoDB, foreign key, unique constraint, serta indeks pada kolom yang sering digunakan untuk pencarian dan filter.

Tabel

Kolom inti

Tanggung jawab dan catatan

users

id, name, username, password, role, is_active, last_login_at, timestamps

Akun owner/kasir. Username unik dan password wajib di-hash.

categories

id, name, description, sort_order, is_active, timestamps

Kategori produk; kategori lama dinonaktifkan, bukan dihapus jika masih direferensikan.

products

id, category_id, sku, name, unit, selling_price, cost_price, stock_qty, minimum_stock, track_stock, image_path, is_active, timestamps

Menu dan stok siap jual. Harga jual/modal DECIMAL; SKU unik bila diisi.

cashier_shifts

id, user_id, opening_cash, opened_at, closing_cash, expected_cash, difference_cash, status, closing_note, timestamps

Buka/tutup shift dan rekonsiliasi kas. Status open/closed.

sales

id, sale_number, idempotency_key, cashier_shift_id, cashier_id, order_type, status, payment_method, payment_status, subtotal, discount_amount, total_amount, amount_paid, change_amount, payment_reference, paid_at, cancellation fields, timestamps

Header penjualan. Nomor struk dan idempotency key harus unik sesuai aturan.

sale_items

id, sale_id, product_id, product_name, unit, unit_price, unit_cost, quantity, discount_amount, line_total, timestamps

Snapshot item, harga jual dan modal agar laporan historis tidak berubah.

inventory_movements

id, product_id, user_id, sale_id nullable, movement_type, quantity_change, stock_before, stock_after, source_key, note, created_at

Ledger pergerakan stok. source_key atau aturan ekuivalen mencegah efek ganda.

business_expenses

id, user_id, cashier_shift_id nullable, category, description, amount, payment_method, expense_date, note, timestamps

Pengeluaran operasional dan pengaruhnya pada rekonsiliasi bila dibayar tunai.

activity_logs

id, user_id, action, subject_type, subject_id, description, old_values, new_values, created_at

Log untuk perubahan harga, stok, pembatalan, akun, dan pengaturan penting.

store_settings

id, key, value, updated_at

Konfigurasi identitas usaha, footer struk, printer, dan informasi QRIS.

### 8.1 Relasi Penting

categories 1:N products.

products 1:N sale_items; sales 1:N sale_items.

users 1:N sales, cashier_shifts, inventory_movements, business_expenses, dan activity_logs.

cashier_shifts 1:N sales; sebuah shift memiliki banyak transaksi.

products 1:N inventory_movements; satu penjualan dapat terhubung ke beberapa movement melalui sale_id.

Produk/pengguna yang memiliki histori tidak dihapus permanen; gunakan status aktif/nonaktif atau strategi arsip yang konsisten.

### 8.2 Indeks dan Constraint Minimum

UNIQUE users.username; UNIQUE sales.sale_number; UNIQUE sales.idempotency_key bila nilainya ada; UNIQUE products.sku bila tidak null.

Index sales.created_at, status, payment_method, cashier_id, cashier_shift_id.

Index products.category_id, is_active, serta inventory_movements pada product_id dan created_at.

Foreign key dengan aturan ON DELETE yang tidak merusak histori transaksi.

Validasi status dan jumlah di application layer; gunakan constraint database tambahan bila cocok dengan versi MySQL yang tersedia.




## 9. Kebutuhan Antarmuka

Halaman

Komponen utama

Kondisi dan interaksi

Login

Username, password, tombol masuk, pesan validasi.

Kesalahan tidak membocorkan apakah username tertentu terdaftar; akun nonaktif ditolak.

Dashboard owner

KPI omzet, transaksi, penjualan tunai/QRIS, stok menipis, produk terlaris, shift terbaru.

- Semua angka dari database dan dapat difilter menurut periode jika disediakan.

POS kasir

Pencarian, filter kategori, kartu produk, keranjang, kuantitas, tipe pesanan, total, bayar.

Tombol sentuh besar; indikator loading dan stok; total diperbarui konsisten.

Pembayaran

Total, metode, uang diterima, kembalian atau status QRIS.

Konfirmasi sebelum submit; cegah klik ganda; tampilkan kegagalan secara jelas.

Sukses dan struk

- Nomor transaksi, ringkasan item, metode bayar, cetak/cetak ulang.

Cetak tidak mengubah transaksi; struk bisa dibaca di kertas 58/80 mm.

Menu dan produk

Form data produk, kategori, harga, stok, status, gambar opsional.

Validasi field; konfirmasi perubahan harga; tidak menghapus histori.

Stok

Daftar stok, batas minimum, penambahan, koreksi, riwayat pergerakan.

- Perubahan stok perlu alasan dan pengguna pencatat.

Transaksi

Pencarian, filter periode/status/metode, detail dan pembatalan.

Akses berdasar role; pembatalan terkontrol dan tercatat.

Shift

Buka shift, uang awal, tutup shift, saldo sistem, uang fisik, selisih.

- Tidak dapat tutup dua kali atau melakukan penjualan setelah shift ditutup.

Laporan

Filter tanggal, omzet, transaksi, item terlaris, modal, pengeluaran, selisih kas.

Tunjukkan definisi metrik dan bedakan estimasi dengan laba bersih.

Pengaturan

Identitas usaha, alamat/kontak, footer struk, printer, QRIS.

Hanya owner; validasi dan audit perubahan penting.

### 9.1 Pedoman Interaksi Tablet

- Prioritaskan orientasi landscape dengan layout dua area: katalog/menu dan panel keranjang.

- Target desain awal: viewport tablet sekitar 1024 × 768 piksel; layout harus tetap bekerja pada lebar lebih kecil dengan susunan yang adaptif.

Tombol tambah, kurangi, bayar, dan cetak harus mudah disentuh tanpa bergantung pada hover.

- Gunakan konfirmasi untuk pembatalan, koreksi stok, dan tindakan yang berdampak pada uang.

- Tampilkan pesan error dekat input atau tindakan yang gagal; jangan mengosongkan keranjang tanpa alasan.

- Berikan loading state selama checkout dan cegah tombol checkout dikirim berulang.

## 10. Kebutuhan Nonfungsional

ID

Area

Persyaratan

**NFR-01**

Usability

Alur POS standar dapat dilakukan dengan sedikit langkah dan antarmuka mudah dipakai pada layar sentuh.

**NFR-02**

Responsivitas

Halaman berfungsi pada tablet target dan browser yang disepakati; layout tidak memotong tombol inti.

**NFR-03**

Performa

- Target awal: halaman utama dan operasi baca umum p95 ≤ 2 detik pada kondisi uji yang disepakati; checkout tidak termasuk waktu verifikasi manusia untuk QRIS.

**NFR-04**

Konsistensi

Checkout, perubahan stok, pembatalan dan penutupan shift menggunakan transaksi/penguncian sesuai kebutuhan.

**NFR-05**

Keamanan

Autentikasi, authorization server-side, CSRF, validasi input, password hashing, dan proteksi terhadap akses objek yang tidak sah.

**NFR-06**

Auditability

Aktivitas penting dapat ditelusuri menurut pelaku, waktu, objek, dan nilai yang berubah bila relevan.

**NFR-07**

Reliability

Pengiriman ulang checkout tidak menggandakan penjualan; error terkontrol dan tidak meninggalkan data parsial.

**NFR-08**

Backup

Prosedur backup database dan pemulihan diuji sebelum produksi; frekuensi backup ditentukan berdasarkan toleransi kehilangan data.

**NFR-09**

Maintainability

Kode menggunakan migrations, validasi, service layer, naming konsisten, tests, dan dokumentasi setup.

**NFR-10**

Privacy

- Simpan data seminimal mungkin; batasi akses laporan/log; jangan mencatat password atau rahasia pembayaran ke log.

**NFR-11**

Localization

Bahasa Indonesia, format tanggal/waktu Asia/Jakarta, dan tampilan Rupiah yang konsisten.

**NFR-12**

Printing

Stylesheet cetak mendukung ukuran 58/80 mm; kompatibilitas browser, driver, Bluetooth, dan printer diuji pada perangkat sebenarnya.

Kinerja bukan asumsi

- Target waktu respons harus diukur dalam pengujian. Mode offline, cetak Bluetooth otomatis, dan koneksi payment gateway tidak dianggap tersedia sebelum implementasi dan uji integrasinya dilakukan.

## 11. Laporan dan Definisi Metrik

Metrik

Definisi

Catatan

Omzet penjualan

Jumlah total transaksi completed setelah diskon, dikurangi refund yang tercatat sesuai kebijakan pelaporan.

- Transaksi pending dan voided tidak dihitung sebagai omzet selesai.

Jumlah transaksi

Jumlah transaksi completed pada periode terpilih, setelah perlakuan pembatalan/refund ditetapkan.

- Tampilkan void/refund terpisah agar transparan.

Penjualan tunai

Total transaksi completed dengan metode cash.

Nilai penjualan bukan nilai uang tender sebelum kembalian.

Penjualan QRIS

Total transaksi completed yang metode pembayarannya QRIS.

Hanya pembayaran terkonfirmasi.

Modal produk terjual

Kuantitas item terjual dikalikan snapshot unit_cost sesuai transaksi.

Harus konsisten dengan aturan pembatalan/refund.

Estimasi laba kotor

Penjualan bersih dikurangi modal produk terjual.

- Tidak otomatis memasukkan semua biaya usaha.

Estimasi laba usaha

Estimasi laba kotor dikurangi pengeluaran usaha yang tercatat dan relevan.

Bukan laporan akuntansi lengkap atau laba bersih terjamin.

- Stok menipis

- Produk terlacak dengan stock_qty ≤ minimum_stock.

Diperbarui berdasarkan stok yang tersimpan.

Selisih kas

Uang fisik saat tutup shift dikurangi saldo kas yang diharapkan.

Nilai positif berarti lebih; negatif berarti kurang.

## 12. Integrasi dan Perangkat

### 12.1 Tablet dan Browser

- Sistem versi MVP diakses lewat browser tablet. Browser, sistem operasi, resolusi, dan metode koneksi printer harus dipilih dan diuji sebelum implementasi cetak final. Koneksi aplikasi ke server wajib tersedia pada MVP.

### 12.2 Printer Thermal

Desain struk harus menyediakan lebar kertas 58 mm dan 80 mm, tetapi pemilihan akhir dilakukan setelah mengetahui model printer.

- Implementasi awal dapat menggunakan browser print jika didukung perangkat.

- Jika printer Bluetooth tidak dapat dipakai langsung melalui browser, evaluasi driver, aplikasi bridge, atau wrapper yang aman. Jangan mengklaim fitur auto-print sebelum pengujian.

- Uji karakter Bahasa Indonesia, format Rupiah, panjang item, pemotongan kertas, dan cetak ulang.

### 12.3 QRIS

MVP menggunakan QRIS usaha yang dikonfigurasi pemilik dan verifikasi manual melalui kanal merchant resmi. Integrasi pembayaran otomatis membutuhkan pemilihan penyedia, kredensial, mekanisme callback/webhook, verifikasi signature, idempotensi, dan pengujian sandbox/produksi; seluruhnya berada di luar integrasi MVP sampai keputusan dibuat.

## 13. Kriteria Penerimaan dan Strategi Pengujian

ID

Kriteria penerimaan

**AC-01**

- Owner dan kasir dapat login, logout, dan hanya mengakses menu/endpoint yang diperbolehkan.

**AC-02**

- Owner dapat membuat produk dan kategori; kasir hanya melihat produk aktif.

**AC-03**

- Harga dan total selalu dihitung di backend; manipulasi nominal di browser ditolak.

**AC-04**

- Sistem menolak transaksi tunai jika uang diterima kurang dari total.

**AC-05**

Kembalian sama dengan uang diterima dikurangi total akhir.

**AC-06**

- Transaksi QRIS pending tidak masuk omzet selesai dan tidak mengurangi stok.

**AC-07**

- QRIS baru selesai setelah status pembayaran dikonfirmasi melalui prosedur yang disetujui.

**AC-08**

- Transaksi tunai yang berhasil menyimpan header dan detail sesuai keranjang.

**AC-09**

- Stok berkurang persis satu kali untuk item yang dilacak setelah transaksi selesai.

**AC-10**

- Sistem menolak penjualan jika stok produk tidak mencukupi.

**AC-11**

Pembatalan sah mengembalikan stok persis satu kali dan merekam alasan/pelaku/waktu.

**AC-12**

- Checkout ulang dengan idempotency key yang sama tidak membuat penjualan kedua.

**AC-13**

Error pada salah satu langkah checkout mengakibatkan rollback perubahan yang terkait.

**AC-14**

Penutupan shift merekonsiliasi modal awal, penjualan tunai, pengeluaran, dan refund tunai.

**AC-15**

Penjualan QRIS tidak menambah saldo kas fisik.

**AC-16**

Struk menampilkan nomor, waktu, item, kuantitas, harga, total, metode bayar, dan kembalian tunai.

**AC-17**

Cetak ulang tidak membuat transaksi atau movement stok baru.

**AC-18**

- Laporan menggunakan transaksi selesai yang tersimpan, tidak menggunakan angka hardcoded.

**AC-19**

Snapshot modal transaksi menjaga laporan historis saat modal produk berubah.

**AC-20**

- Perubahan harga, stok, akun, pengaturan penting dan pembatalan dapat ditelusuri melalui audit log.

**AC-21**

- Stok tidak turun di bawah nol pada skenario dua checkout bersamaan.

**AC-22**

Tampilan kasir berfungsi pada tablet target dan proses cetak diuji dengan printer target.

### 13.1 Pengujian Minimum

Jenis pengujian

Fokus

Unit test

Kalkulasi total, kembalian, saldo shift, status transaksi, dan definisi metrik.

Feature test

Autentikasi, role, CRUD data master, checkout, stok, pembatalan, laporan, dan pengeluaran.

Concurrency test

- Produk terakhir dibeli bersamaan; checkout ulang; penguncian stok.

Failure test

Database error saat checkout, refresh layar, timeout, dan pengiriman ulang request.

Device test

Tablet target, orientasi landscape, browser, printer thermal, dan cetak ulang.

UAT

Pemilik dan kasir menjalankan simulasi operasi satu hari dan mencocokkan kas/stok.

## 14. Roadmap Implementasi

Roadmap di bawah adalah urutan pengembangan dan tidak menjanjikan durasi tertentu. Setiap tahap harus melewati kriteria penerimaan sebelum diteruskan.

Tahap

Ruang kerja

Hasil / exit criteria

## 1. Fondasi

Audit workspace, setup Laravel/MySQL, autentikasi, role, layout tablet, migrations awal.

- Aplikasi berjalan; akun owner dan cashier; otorisasi dasar diuji.

## 2. Data master

Kategori, produk, harga, stok, stok minimum, inventory movement.

- Owner dapat mengelola menu; histori stok tercatat.

## 3. POS inti

Katalog, filter, keranjang, tipe pesanan, total, tunai, QRIS manual, idempotensi.

- Checkout utama teruji; transaksi dan stok konsisten.

## 4. Operasional

Shift, pengeluaran, pembatalan, audit, struk thermal.

Buka/tutup shift dan pembatalan dapat direkonsiliasi; cetak diuji pada perangkat.

## 5. Laporan

Dashboard, penjualan per metode, produk terlaris, margin estimasi, filter tanggal.

Nilai diambil dari database dengan definisi yang terdokumentasi.

## 6. Stabilitas dan rilis

Automated tests, UAT, backup/restore, konfigurasi produksi, dokumentasi deployment.

Kriteria AC terpenuhi; isu kritis ditutup; prosedur backup dan dukungan tersedia.

## 15. Risiko, Asumsi, dan Keputusan Terbuka

### 15.1 Risiko dan Mitigasi

Risiko

Dampak

Mitigasi

Printer tidak kompatibel dengan browser tablet

Struk tidak tercetak atau memerlukan langkah manual.

Identifikasi model printer sejak awal; uji browser/driver; siapkan print stylesheet dan alternatif yang terdokumentasi.

Bukti QRIS tidak valid atau pembayaran belum masuk

Penjualan dicatat padahal uang belum diterima.

Status pending; verifikasi pada kanal merchant resmi; jangan percaya screenshot saja.

- Checkout terkirim dua kali

Omzet dan stok terduplikasi.

Idempotency key, unique constraint, cek status, disable tombol saat proses, dan tes request berulang.

Dua penjualan bersamaan pada stok terbatas

- Stok minus atau overselling.

Locking row dan validasi stok di dalam DB transaction.

- Laporan laba dianggap nilai final

Pemilik salah membaca kondisi keuangan.

Label estimasi; simpan modal transaksi; dorong pencatatan pengeluaran dan jelaskan pengecualian.

Kehilangan database

Riwayat transaksi dan stok hilang.

Backup terjadwal, uji restore, batasi akses database, dan dokumentasikan recovery.

- Koneksi internet/server putus

- Kasir tidak dapat melanjutkan checkout pada MVP.

Prosedur pencatatan darurat yang terkontrol; hindari transaksi ganda saat rekonsiliasi; mode offline masuk backlog.

### 15.2 Asumsi Baseline

Usaha mempunyai satu lokasi dan satu tablet utama untuk kasir.

- Owner bertanggung jawab atas produk, harga, modal, pengaturan QRIS, dan persetujuan pembatalan.

- Produk memiliki satu satuan penjualan per SKU pada MVP; penjualan multi-satuan atau konversi stok belum diperlukan.

- Satu transaksi memakai satu metode pembayaran; split payment tidak termasuk MVP.

- QRIS diverifikasi manual sampai penyedia pembayaran otomatis dipilih dan diintegrasikan.

- Data awal produk dan stok akan dimasukkan oleh pemilik atau admin yang diberi wewenang.

- Koneksi ke server tersedia ketika menggunakan aplikasi; dukungan offline belum menjadi kebutuhan MVP.

### 15.3 Keputusan Terbuka sebelum Rilis

Keputusan

Pilihan/pertanyaan

Penanggung jawab

Model tablet dan browser

Merek/model tablet, OS, browser, orientasi utama.

Pemilik + developer

Model printer

58 mm atau 80 mm; USB/Bluetooth/Wi-Fi; dukungan driver.

Pemilik + developer

QRIS

- QRIS statis/manual atau penyedia dengan API untuk tahap lanjutan.

Pemilik

Pembatalan/refund

Apakah semua pembatalan wajib owner; batas waktu; prosedur refund tunai/QRIS.

Pemilik

Diskon

Apakah fitur diskon diperlukan pada MVP dan siapa yang boleh memberi.

Pemilik

Shift

Apakah satu shift per lokasi atau per kasir; bagaimana bila pergantian kasir tanpa menutup usaha.

Pemilik + developer

Hosting dan backup

Server/LAN/cloud, frekuensi backup, retensi, target pemulihan.

Pemilik + developer

Ekspor laporan

CSV/Excel dan PDF diperlukan sejak MVP atau rilis berikutnya.

Pemilik

## 16. Definition of Done

- Sebuah fitur dianggap selesai apabila seluruh kondisi relevan berikut terpenuhi:

- Implementasi mengikuti requirement dan aturan bisnis yang disetujui.

Validasi input dan otorisasi backend tersedia untuk semua endpoint terkait.

Migration, model, relasi, indeks, serta aturan constraint sudah dibuat bila diperlukan.

Tampilan mempunyai loading, empty state, feedback sukses, dan feedback error yang sesuai.

Unit/feature tests untuk alur penting ditambahkan dan dijalankan; hasilnya dilaporkan dengan jujur.

Transaksi, stok, pembayaran dan laporan diuji untuk mencegah data ganda atau parsial.

- Tidak ada kredensial/rahasia di source code atau log.

Dokumentasi konfigurasi, cara menjalankan, dan cara memverifikasi fitur diperbarui.

- Untuk fitur printer atau QRIS, pengujian perangkat/layanan dilakukan atau keterbatasannya dinyatakan jelas.

- Tidak ada blocker keamanan atau kehilangan data yang diketahui sebelum rilis operasional.

Glosarium

Istilah

Definisi

POS

Point of Sale; sistem untuk mencatat penjualan dan pembayaran.

SKU

Kode unik produk/menu.

Stock movement

- Catatan pergerakan stok masuk/keluar yang menjelaskan perubahan saldo stok.

Idempotensi

Sifat operasi yang ketika dikirim ulang tidak menghasilkan efek bisnis ganda.

Snapshot harga/modal

Salinan harga jual/modal pada saat transaksi agar histori tidak berubah setelah master produk diperbarui.

Omzet

Nilai penjualan dalam periode sesuai definisi laporan; bukan laba.

Laba kotor

Penjualan bersih dikurangi modal produk yang terjual.

Laba usaha estimasi

Laba kotor dikurangi biaya operasional yang tercatat; akurasinya bergantung kelengkapan data.

UAT

User Acceptance Testing; pengujian penerimaan oleh pengguna yang akan menjalankan aplikasi.

MVP

Minimum Viable Product; versi awal yang memenuhi alur inti dan ruang lingkup yang disepakati.
    