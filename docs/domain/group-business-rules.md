# MagangHub - Aturan Bisnis Kelompok Magang

Dokumen ini mendefinisikan aturan bisnis MagangHub berdasarkan kondisi magang yang sebenarnya.

---

# Entitas

* Mahasiswa
* Kelompok Magang
* Pengajuan Magang

Setiap mahasiswa hanya boleh memiliki satu keanggotaan kelompok aktif.

---

# Prasyarat Akun & Profil Mahasiswa

Sebelum mahasiswa dapat berinteraksi dengan kelompok magang (membuat kelompok baru, bergabung dengan kelompok, atau mengajukan magang), mereka wajib memenuhi kondisi berikut:

1. **Ganti Password**: Mahasiswa harus sudah mengganti password awal mereka (default: NIM).
2. **Lengkapi Biodata**: Mahasiswa wajib melengkapi profil biodata diri, yang meliputi:
   - Nama Lengkap
   - Email
   - NIM
   - Jurusan (Major)
   - Nomor Telepon (Phone)
   - Alamat (Address)
   - Jenis Kelamin (Gender)
   - Semester
   - Bidang Minat (Field of Interest)
   - *Catatan*: Kolom Divisi (Division) bersifat opsional.

Jika prasyarat di atas belum terpenuhi, mahasiswa tidak akan diizinkan mengakses dashboard kelompok atau melakukan aktivitas kelompok lainnya.

---

# State Kelompok

1. `forming` (Membentuk Kelompok)
2. `submitted` (Mengajukan Magang)
3. `letter_published` (Surat Permohonan Terbit/Dikirim)
4. `accepted` (Diterima Perusahaan)
5. `partially_accepted` (Diterima Sebagian Perusahaan)
6. `rejected` (Ditolak Perusahaan)
7. `internship_started` (Magang Dimulai)
8. `completed` (Selesai)

---

# Membuat atau Bergabung Kelompok

Mahasiswa yang telah memenuhi prasyarat profil dapat:

* **Membuat kelompok baru** dan otomatis ditunjuk sebagai Ketua Kelompok.
* **Bergabung ke kelompok lain** menggunakan kode unik kelompok. Proses bergabung ini bersifat tidak langsung:
  1. Mahasiswa mengirimkan permintaan bergabung (*Join Request*).
  2. Ketua kelompok berhak menyetujui atau menolak permintaan tersebut.

Mahasiswa tidak dapat membuat kelompok baru atau bergabung ke kelompok lain apabila:

* Sudah memiliki keanggotaan kelompok aktif.
* Status kelompok yang bersangkutan sudah melewati tahap `forming`.

---

# Keluar dari Kelompok

* Mahasiswa biasa dapat keluar dari kelompok apabila status kelompok masih `forming`.
* Ketua kelompok tidak dapat keluar secara mandiri tanpa membubarkan kelompok atau menunjuk ketua baru (oleh administrator).
* Setelah kelompok mengajukan magang (`submitted`), anggota kelompok dikunci dan tidak dapat keluar, kecuali dikeluarkan akibat penolakan dari perusahaan atau intervensi administrator.

---

# Ketua Kelompok

* Setiap kelompok wajib memiliki tepat satu ketua kelompok aktif.
* Ketua bertanggung jawab untuk:
  - Menyetujui atau menolak permintaan mahasiswa lain untuk bergabung ke kelompok.
  - Mengisi formulir pengajuan magang (draf dan pengajuan final).
  - Melakukan submisi pengajuan magang.

---

# Hasil Akhir Pengajuan Magang

Setelah surat permohonan magang diterbitkan (`letter_published`) dan respons dari perusahaan didapatkan, Administrator/Operator akan memproses hasil pengajuan melalui halaman manajemen magang dengan 3 opsi:

1. **Diterima Perusahaan**:
   - Proses selesai, status kelompok berlanjut ke `internship_started`.
2. **Sebagian Anggota Diterima**:
   - Administrator atau operator memilih secara spesifik siapa saja anggota yang diterima dan siapa yang ditolak.
   - Anggota yang ditolak otomatis dikeluarkan dari kelompok.
   - Terkadang tidak selalu ditolak perusahaan, administrator/operator dapat memasukkan alasan ditolak.
   - **Aturan Pergantian Ketua**: Jika Ketua kelompok termasuk salah satu yang ditolak, maka sebelum perubahan disimpan, harus dipilih Ketua baru (oleh administrator) dari anggota kelompok yang diterima.
3. **Ditolak Perusahaan**:
   - Status kelompok diubah menjadi `company_rejected`.
   - **Pencabutan Ketua**: Anggota kelompok tetap berada di dalam kelompok tersebut untuk kebutuhan histori, namun jabatan Ketua dicabut (statusnya diturunkan menjadi anggota biasa). Hal ini bertujuan agar mantan ketua dapat bergabung ke kelompok lain atau membuat kelompok baru.

*Penting*: Seluruh data penolakan tidak dihapus dari sistem melainkan disimpan sebagai **histori** (snapshot keanggotaan pada pengajuan tetap dipertahankan). Siapa pun yang ditolak wajib keluar dari kelompok aktif agar dapat mencari kelompok baru.

---

# Invariant Kelompok

* Setiap mahasiswa hanya memiliki satu kelompok aktif.
* Setiap kelompok memiliki tepat satu ketua.
* Ketua harus merupakan anggota aktif di dalam kelompok tersebut.
* Kelompok minimal memiliki satu anggota (dan minimal dua anggota saat akan mengajukan magang ke admin).
* Anggota yang sudah memulai magang tidak dapat berpindah kelompok.
