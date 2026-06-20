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

### Langkah 9 - Mencetak Surat Permohonan Magang
Setelah disetujui, Administrator atau Operator dapat mencetak/mengunduh Surat Permohonan Magang resmi yang dihasilkan oleh sistem menggunakan template Word yang disesuaikan (sesuai format pada berkas `Contoh Surat Permohonan Magang.docx`). Surat ini kemudian dikirimkan ke perusahaan tujuan.

### Langkah 10 - Penerimaan Surat Balasan & Manajemen Hasil Akhir
Setelah perusahaan memberikan surat balasan (yang nantinya dapat diunggah ke sistem atau diarsipkan), Administrator/Operator membuka halaman Manajemen Magang untuk menentukan hasil akhir penempatan kelompok tersebut.

### Langkah 11 - Keputusan Penempatan Akhir (3 Opsi)
Admin memilih salah satu opsi berdasarkan balasan perusahaan:

1. **Diterima Perusahaan (`accepted`)**:
   - Klik opsi diterima perusahaan. Proses selesai, status kelompok berlanjut ke `internship_started`.
2. **Sebagian Anggota Diterima (`partially_accepted`)**:
   - Admin atau operator memilih secara spesifik siapa saja anggota kelompok yang diterima dan ditolak.
   - Anggota yang ditolak otomatis dikeluarkan dari kelompok agar dapat mencari kelompok baru.
   - Administrator atau operator dapat memasukkan alasan penolakan untuk anggota yang ditolak.
   - **Pergantian Ketua**: Jika Ketua kelompok termasuk yang ditolak, sistem mewajibkan admin menunjuk ketua baru dari sisa anggota yang diterima sebelum perubahan disimpan.
   - Setelah selesai, status kelompok berlanjut ke `internship_started` bagi anggota yang diterima.
3. **Ditolak Perusahaan (`rejected`)**:
   - Klik opsi ditolak perusahaan. Status kelompok diubah menjadi `company_rejected`.
   - **Pencabutan Jabatan Ketua**: Anggota kelompok tetap dipertahankan di dalam kelompok tersebut sebagai histori, namun status Ketua dicabut (menjadi anggota biasa) sehingga mantan Ketua dapat bebas membuat atau bergabung dengan kelompok baru.

*Catatan Histori*: Penolakan tidak menghapus catatan pengajuan; sistem menyimpannya sebagai histori pelaporan magang.
