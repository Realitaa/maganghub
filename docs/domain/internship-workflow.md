# Alur Pengajuan Magang (Internship Workflow)

Dokumen ini menjelaskan alur lengkap pengajuan magang di MagangHub dari awal hingga keputusan akhir penerimaan.

---

### Langkah 1 - Impor Data Mahasiswa
Administrator atau operator mengimpor data mahasiswa ke dalam sistem MagangHub.

### Langkah 2 - Login Pertama Kali
Mahasiswa masuk ke sistem menggunakan Nomor Induk Mahasiswa (**NIM**) baik sebagai *username* maupun *password* default awal.

### Langkah 3 - Mengisi Profil dan Ganti Password
Sebelum mahasiswa diperbolehkan membuat kelompok baru atau bergabung ke kelompok lain, sistem akan mengunci halaman utama dan mewajibkan mereka untuk:
- Mengganti password default.
- Mengisi dan melengkapi biodata profil (termasuk **semester** dan **field of interest**; kolom **division** bersifat opsional).

### Langkah 4 - Membuat Kelompok Magang
Mahasiswa yang ingin menjadi Ketua Kelompok membuat kelompok magang baru di sistem. Setelah kelompok dibuat, sistem menghasilkan kode kelompok unik yang dapat dibagikan kepada calon anggota kelompok lainnya.

### Langkah 4.5 - Menggabungkan Anggota Kelompok
Mahasiswa lain yang ingin bergabung memasukkan kode kelompok tersebut. Penggabungan ini tidak otomatis:
- Permintaan bergabung masuk ke daftar antrean (*Join Requests*) kelompok tersebut.
- Ketua kelompok harus meninjau dan memilih untuk **menyetujui** atau **menolak** mahasiswa tersebut.

### Langkah 5 - Mengisi Pengajuan Magang (Draf)
Secara paralel selama pembentukan kelompok, Ketua kelompok mengisi data pengisian magang, seperti:
- Nama Perusahaan
- Alamat Perusahaan
- Kontak Perusahaan
- Bidang Pekerjaan / Divisi (Division)
- Bidang Minat Kelompok (Field of Interest)
- Tanggal Pelaksanaan
- Berkas Pendukung (Surat Pengantar/Proposal)

Ketua kelompok dapat menyimpan pengisian ini sebagai **Draf** kapan saja.

### Langkah 6 - Mengajukan Magang
Setelah seluruh anggota terkumpul (minimal 2 anggota kelompok) dan data pengajuan sudah lengkap, Ketua kelompok mengirimkan pengajuan magang.
- **Validasi**: Semua kolom wajib diisi, kecuali kolom **Divisi (Division)** yang bersifat opsional.
- Setelah diajukan, komposisi anggota kelompok dan data pengajuan dikunci untuk keperluan verifikasi.

### Langkah 7 - Peninjauan oleh Administrator / Operator
Administrator atau operator melihat daftar pengajuan magang masuk di panel admin dalam bentuk tabel kelompok magang yang mengajukan. Admin dapat membuka modal untuk melihat detail pengajuan dan daftar anggota kelompok beserta profil lengkap masing-masing.

### Langkah 8 - Keputusan Peninjauan Admin (Review)
Administrator/Operator menentukan hasil review pengajuan:
- **Ditolak**: Pengajuan dikembalikan ke fase awal (`forming`). Data pengajuan dikunci kembali ke status draf, kunci anggota dibuka, sehingga Ketua dapat memodifikasi anggota (tambah/keluarkan) atau memperbaiki data pengajuan sebelum diajukan kembali.
- **Diterima**: Status pengajuan dan kelompok diubah menjadi `letter_published`. Keanggotaan pada saat disetujui disimpan sebagai **snapshot** resmi.

### Langkah 9 - Mencetak Surat Permohonan Magang dan Menandai Sedang Mengajukan
Setelah disetujui, Administrator atau Operator dapat mengakses menu **"Siap Magang"** (Siap Cetak Surat). Di sini admin/operator dapat:
- Mencetak Surat Permohonan Magang resmi menggunakan template Word yang disesuaikan.
- Menandai kelompok sedang mengajukan ke perusahaan. Hal ini akan mengubah status kelompok dan pengajuan menjadi `applying`.

### Langkah 10 - Penerimaan Surat Balasan & Unggah Respons
Setelah kelompok berstatus `applying`, mereka akan mengajukan surat permohonan ke perusahaan. Mahasiswa dapat mengunggah berkas surat balasan resmi dari perusahaan ke sistem. 
Setelah berkas balasan diunggah, pengajuan kelompok tersebut akan berpindah ke tabel **"Menerima Balasan"** pada menu **"Siap Magang"** di sisi Administrator/Operator.

### Langkah 11 - Keputusan Penempatan Akhir (3 Opsi)
Administrator/Operator meninjau berkas balasan dan memproses keputusan akhir dengan 3 opsi:

1. **Diterima Perusahaan (`accepted`)**:
   - Status kelompok dan pengajuan berlanjut ke `accepted` (kemudian `internship_started`).
2. **Sebagian Anggota Diterima (`partially_accepted`)**:
   - Administrator/Operator memilih secara spesifik anggota yang diterima dan yang ditolak.
   - Anggota yang ditolak otomatis dikeluarkan dari keanggotaan aktif kelompok dan dapat memasukkan alasan/catatan penolakan (`rejection_note`) untuk setiap anggota tersebut agar dibaca mahasiswa.
   - **Pergantian Ketua**: Jika Ketua kelompok termasuk yang ditolak, sistem mewajibkan admin menunjuk ketua baru dari sisa anggota yang diterima.
   - Sisa anggota yang diterima berlanjut dengan kelompok berstatus `partially_accepted` (dan kemudian `internship_started`).
3. **Ditolak Perusahaan (`rejected`)**:
   - Semua anggota ditolak. Status kelompok dan pengajuan diubah menjadi `rejected`.
   - **Pencabutan Jabatan Ketua**: Keanggotaan kelompok dipertahankan sebagai histori, namun jabatan Ketua dicabut (menjadi anggota biasa) sehingga mantan Ketua bisa membuat/bergabung kelompok baru.

*Catatan Histori*: Penolakan tidak menghapus catatan pengajuan; sistem menyimpannya sebagai histori pelaporan magang.
