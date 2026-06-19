# Group State Machine

Dokumen ini mendefinisikan state kelompok magang dan transisi yang diperbolehkan.

## FORMING

Tahap pembentukan kelompok.

Mahasiswa dapat:

* Bergabung ke kelompok.
* Keluar dari kelompok.
* Membubarkan kelompok.

Ketua kelompok dapat mengajukan magang.

---

## INTERNSHIP_STARTED

Kelompok telah memulai kegiatan magang.

State setelah magang selesai belum didefinisikan.

---

# Invariant

* Setiap kelompok memiliki tepat satu ketua.
* Ketua kelompok merupakan anggota kelompok.
* Kelompok memiliki minimal satu anggota.
* Perubahan anggota hanya diperbolehkan pada state FORMING.
* Mekanisme penanganan penerimaan sebagian anggota akan ditentukan kemudian.
* Mahasiswa hanya dapat memiliki satu keanggotaan kelompok aktif.
* State setelah magang selesai belum didefinisikan.
* Mekanisme detail penanganan penerimaan sebagian masih akan ditentukan.
