# Group State Machine

Dokumen ini mendefinisikan state kelompok magang dan transisi yang diperbolehkan.

## FORMING

Tahap pembentukan kelompok.

Mahasiswa dapat:

* Bergabung ke kelompok.
* Keluar dari kelompok.
* Membubarkan kelompok.

Ketua kelompok dapat mengajukan magang.

Transisi:

FORMING
↓
SUBMITTED

---

## SUBMITTED

Kelompok telah mengajukan magang.

Perubahan anggota kelompok tidak diperbolehkan.

Transisi:

SUBMITTED
↓
UNDER_REVIEW

---

## UNDER_REVIEW

Operator atau administrator melakukan pemeriksaan.

Kemungkinan hasil:

UNDER_REVIEW
↓
LETTER_SENT

atau

UNDER_REVIEW
↓
FORMING

Jika terdapat kesalahan data atau berkas, pengajuan ditolak dan kelompok kembali ke tahap FORMING untuk memperbaiki pengajuan.

---

## LETTER_SENT

Surat permohonan magang telah diterbitkan dan dikirim kepada perusahaan tujuan.

Transisi:

LETTER_SENT
↓
WAITING_RESPONSE

---

## WAITING_RESPONSE

Kelompok sedang menunggu respons perusahaan.

Kemungkinan hasil:

WAITING_RESPONSE
↓
INTERNSHIP_STARTED

atau

WAITING_RESPONSE
↓
PARTIALLY_ACCEPTED

atau

WAITING_RESPONSE
↓
COMPANY_REJECTED

---

## INTERNSHIP_STARTED

Seluruh anggota kelompok telah diterima dan memulai magang.

State setelah magang selesai belum didefinisikan.

---

## PARTIALLY_ACCEPTED

Sebagian anggota kelompok diterima oleh perusahaan dan sebagian lainnya ditolak.

Operator atau administrator mencatat hasil penerimaan masing-masing mahasiswa.

Mahasiswa yang diterima melanjutkan proses magang.

Mahasiswa yang ditolak dapat:

* Keluar dari kelompok.
* Membentuk kelompok baru.
* Bergabung ke kelompok lain.

Kelompok yang tersisa dapat melanjutkan dengan anggota yang diterima.

State setelah seluruh penyesuaian anggota selesai belum didefinisikan.

---

## COMPANY_REJECTED

Seluruh anggota kelompok ditolak oleh perusahaan.

Mahasiswa diperbolehkan:

* Keluar dari kelompok.
* Membentuk kelompok baru.
* Bergabung ke kelompok lain.

Kelompok dapat digunakan kembali untuk melakukan pengajuan baru apabila seluruh anggota sepakat melanjutkan bersama.

State ini bukan terminal state.

Transisi:

COMPANY_REJECTED
↓
FORMING

melalui pengajuan baru.

---

# Invariant

* Setiap kelompok memiliki tepat satu ketua.
* Ketua kelompok merupakan anggota kelompok.
* Kelompok memiliki minimal satu anggota.
* Perubahan anggota hanya diperbolehkan pada state FORMING, kecuali penyesuaian akibat penerimaan sebagian oleh perusahaan.
* Mahasiswa hanya dapat memiliki satu keanggotaan kelompok aktif.
* State setelah magang selesai belum didefinisikan.
* Mekanisme detail penanganan penerimaan sebagian masih akan ditentukan.
