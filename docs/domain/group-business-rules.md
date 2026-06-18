# MagangHub - Aturan Bisnis dan State Machine

Dokumen ini mendefinisikan aturan bisnis MagangHub berdasarkan kondisi magang yang sebenarnya.

---

# Entitas

* Mahasiswa
* Kelompok Magang
* Pengajuan Magang

Setiap mahasiswa hanya boleh memiliki satu keanggotaan kelompok aktif.

---

# State Kelompok

1. MEMBENTUK_KELOMPOK
2. MENGAJUKAN_MAGANG
3. DIREVIEW_ADMIN
4. SURAT_DIKIRIM
5. MENUNGGU_RESPON
6. MAGANG_DIMULAI
7. SELESAI
8. DITOLAK_PERUSAHAAN

---

# Membuat atau Bergabung Kelompok

Mahasiswa dapat:

* Membuat kelompok baru.
* Bergabung ke kelompok lain menggunakan kode kelompok.

Mahasiswa tidak dapat membuat atau bergabung ke kelompok lain apabila:

* Menjadi ketua kelompok aktif.
* Memiliki keanggotaan kelompok aktif.
* Status kelompok telah memasuki tahap MAGANG_DIMULAI.

---

# Keluar dari Kelompok

Mahasiswa tidak dapat keluar apabila:

* Merupakan ketua kelompok.
* Kelompok telah mengajukan magang.

Mahasiswa dapat keluar apabila:

* Status kelompok masih MEMBENTUK_KELOMPOK.
* Seluruh anggota kelompok ditolak oleh perusahaan.

---

# Ketua Kelompok

Ketua kelompok tidak dapat keluar dari kelompok.

Ketua kelompok dapat diganti oleh administrator apabila:

* Ketua melakukan pelanggaran.
* Ketua mengundurkan diri.
* Ketua tidak memenuhi syarat untuk melanjutkan magang.

Sebelum ketua dikeluarkan, administrator harus menunjuk ketua baru.

---

# Perubahan Anggota

Perubahan anggota hanya diperbolehkan ketika status kelompok:

* MEMBENTUK_KELOMPOK

Setelah kelompok mengajukan magang, anggota tidak dapat:

* Ditambah.
* Dikeluarkan.
* Diganti.

Kecuali oleh administrator karena alasan khusus.

---

# Penolakan oleh Perusahaan

Apabila seluruh anggota ditolak:

Status kelompok menjadi:

DITOLAK_PERUSAHAAN

Seluruh anggota diperbolehkan:

* Membuat kelompok baru.
* Bergabung ke kelompok lain.

Apabila hanya sebagian anggota yang ditolak:

Status kelompok tetap berjalan.

Anggota yang ditolak dapat dikeluarkan oleh administrator dan diperbolehkan mencari kelompok lain.

---

# Pelanggaran Mahasiswa

Administrator atau operator dapat menghentikan status magang mahasiswa apabila:

* Melakukan pelanggaran.
* Tidak memenuhi syarat akademik.
* Diputuskan oleh program studi.

Jika mahasiswa tersebut adalah ketua kelompok, maka ketua baru harus ditentukan terlebih dahulu.

---

# Pembubaran Kelompok

Kelompok hanya dapat dibubarkan apabila statusnya:

MEMBENTUK_KELOMPOK

Setelah pengajuan dilakukan, pembubaran hanya dapat dilakukan oleh administrator.

---

# Invariant

* Setiap mahasiswa hanya memiliki satu kelompok aktif.
* Setiap kelompok memiliki tepat satu ketua.
* Ketua harus merupakan anggota kelompok.
* Kelompok minimal memiliki satu anggota.
* Anggota yang sudah memulai magang tidak dapat berpindah kelompok.
* Surat yang telah diterbitkan mengacu pada anggota pada saat pengajuan dilakukan.
