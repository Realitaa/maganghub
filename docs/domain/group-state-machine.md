# Group State Machine

Dokumen ini mendefinisikan status (*state*) kelompok magang dan transisi yang diperbolehkan di MagangHub.

---

# Daftar Status Kelompok

Kelompok magang memiliki siklus hidup yang diwakili oleh kolom `status` di tabel `internship_groups` dengan nilai-nilai berikut:

### 1. `forming` (Membentuk Kelompok)
* **Kondisi Awal**: Status default setelah kelompok baru dibuat oleh seorang mahasiswa (Ketua).
* **Aksi yang Diperbolehkan**:
  - Mahasiswa lain dapat mengirim permintaan bergabung (*join request*).
  - Ketua kelompok menyetujui/menolak permintaan bergabung.
  - Anggota (selain Ketua) dapat keluar dari kelompok.
  - Ketua dapat membubarkan kelompok.
  - Ketua mengisi draf formulir pengajuan magang.
* **Transisi**:
  - `forming` → (terhapus) jika dibubarkan oleh Ketua.
  - `forming` → `submitted` jika Ketua mengajukan magang.

### 2. `submitted` (Mengajukan Magang)
* **Kondisi**: Pengajuan dikirim ke administrator dan operator. Semua data perusahaan dan keanggotaan kelompok dikunci.
* **Transisi**:
  - `submitted` → `forming` jika pengajuan ditolak oleh administrator saat review.
  - `submitted` → `letter_published` jika pengajuan disetujui oleh administrator.

### 3. `letter_published` (Surat Permohonan Terbit)
* **Kondisi**: Surat permohonan magang resmi telah diterbitkan oleh sistem. Surat siap dicetak oleh Administrator/Operator atau didownload untuk dikirimkan ke perusahaan.
* **Transisi**:
  - `letter_published` → `accepted` jika seluruh anggota kelompok diterima magang oleh perusahaan.
  - `letter_published` → `partially_accepted` (dengan modifikasi keanggotaan) jika sebagian anggota diterima (anggota yang ditolak dikeluarkan dari kelompok aktif).
  - `letter_published` → `rejected` jika seluruh anggota ditolak oleh perusahaan.

### 4. `accepted` (Diterima Perusahaan)
* **Kondisi**: 
* **Transisi**:
  - `accepted` → `internship_started` jika seluruh anggota kelompok diterima magang oleh perusahaan.

### 5. `partially_accepted` (Diterima Sebagian Perusahaan)
* **Kondisi**: 
* **Transisi**:
  - `partially_accepted` → `internship_started` (dengan modifikasi keanggotaan) jika sebagian anggota diterima (anggota yang ditolak dikeluarkan dari kelompok aktif).

### 6. `rejected` (Ditolak Perusahaan)
* **Kondisi**: Seluruh pengajuan kelompok ditolak oleh perusahaan.
* **Penanganan**: 
  - Keanggotaan kelompok dipertahankan untuk histori.
  - Jabatan Ketua kelompok dicabut (diturunkan ke anggota biasa) agar mantan Ketua bisa bergabung/membuat kelompok baru.
* **Transisi**:
  - Kelompok tetap berada pada state ini sebagai histori.

### 6. `internship_started` (Magang Dimulai)
* **Kondisi**: Kelompok (atau sisa anggota yang diterima) telah memulai kegiatan magang mereka secara resmi.
* **Transisi**:
  - `internship_started` → `completed` saat periode magang berakhir dan laporan diselesaikan.

### 7. `completed` (Selesai)
* **Kondisi**: Mahasiswa telah menyelesaikan kegiatan magang dan proses administrasi akhir selesai.

---

# Aturan Invariant State

* **Konsistensi Keanggotaan**: Perubahan anggota (menambah/mengeluarkan/keluar sendiri) hanya diperbolehkan pada status `forming`. Setelah status berubah menjadi `submitted`, keanggotaan tidak dapat berubah kecuali diproses melalui keputusan hasil perusahaan oleh administrator (di mana anggota yang ditolak dikeluarkan).
* **Kepemimpinan**: Kelompok pada status `forming`, `submitted`, `letter_sent`, dan `internship_started` wajib memiliki tepat satu ketua. Jabatan ketua hanya boleh dicabut pada status `company_rejected` (seluruhnya ditolak) atau digantikan apabila ketua ditolak pada skenario sebagian diterima (`letter_sent` → `internship_started`).
