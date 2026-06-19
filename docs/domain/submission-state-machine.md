# Submission State Machine

Dokumen ini mendefinisikan state pengajuan magang.

## DRAFT

Ketua kelompok masih dapat mengubah data pengajuan.

Transisi:

DRAFT
↓
SUBMITTED

---

## SUBMITTED

Pengajuan telah dikirim.

Perubahan anggota dan data perusahaan dikunci.

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
REJECTED

---

## LETTER_SENT

Surat permohonan magang telah diterbitkan.

Mahasiswa dapat mendownload dan mencetak surat.

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
COMPANY_REJECTED

---

## COMPANY_REJECTED

Seluruh anggota kelompok ditolak oleh perusahaan.

Kelompok kembali ke tahap FORMING dan dapat melakukan pengajuan baru.

---

## INTERNSHIP_STARTED

Seluruh anggota kelompok telah diterima dan memulai magang.

State setelah magang selesai belum didefinisikan.

---

# Invariant

* Setiap kelompok hanya dapat memiliki satu pengajuan aktif.
* Snapshot anggota dibuat ketika pengajuan dikirim.
* Anggota kelompok tidak dapat berubah selama pengajuan aktif.
* Mekanisme penerimaan sebagian anggota belum termasuk ruang lingkup MVP.