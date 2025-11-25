## Penjelasan Aplikasi Sistem Approval Dokumen (untuk orang awam)

Tujuan singkat
Sistem ini dibuat untuk membantu organisasi (kantor, departemen, atau tim) mengelola proses persetujuan dokumen secara bertahap. Daripada mengirim dokumen lewat email atau chat dan menunggu balasan, aplikasi ini membuat alur yang teratur: siapa yang harus menyetujui, urutan persetujuan, serta catatan hasil tiap langkah.

Siapa yang memakai
- Pembuat dokumen (orang yang mengunggah/membuat surat atau kontrak).
- Para approver (orang atau jabatan yang harus menyetujui dokumen). Bisa tersusun berlapis: level 1, level 2, dst.
- Admin atau manajer yang memantau progres dan hasil persetujuan.

Ringkasan cara kerja (sederhana)
1. Pembuat membuat atau mengunggah dokumen (hanya file PDF saat ini).
2. Pembuat menentukan daftar approver yang dibutuhkan, disusun berdasarkan level (mis. atasan langsung dulu, lalu bagian legal).
3. Sistem mengelola persetujuan secara berurutan: semua approver di level 1 harus menyetujui dulu, baru lanjut ke level 2.
4. Setiap approver bisa memberi catatan (notes) saat menyetujui atau menolak.
5. Jika ada penolakan, dokumen bisa dibatalkan dan pembuat diminta membuat dokumen baru berdasarkan komentar.
6. Dokumen yang belum disetujui diberi watermark dan QR code; QR mengarah ke halaman informasi publik tentang status dokumen.

Contoh alur nyata
- Siti membuat surat kontrak dan menunjuk 2 approver di level 1 (Atasan langsung dan HR) dan 1 approver di level 2 (Legal).
- Atasan langsung menyetujui → HR menyetujui → otomatis dokumen diteruskan ke Legal.
- Legal memberi catatan dan menyetujui → status dokumen menjadi "approved" dan file final dapat diunduh tanpa watermark.

Manfaat untuk organisasi
- Transparansi: siapa yang sudah menyetujui dan siapa yang belum.
- Audit trail: catatan approval dan notes tersimpan untuk referensi atau kepatuhan.
- Efisiensi: mengurangi kebingungan soal urutan persetujuan dan menghindari thread email yang berantakan.

Kata-kata sederhana (glossary)
- Approver: orang yang harus menyetujui dokumen.
- Level: tingkatan persetujuan; semua approver di satu level harus selesai dulu sebelum ke level berikutnya.
- Watermark: teks yang menandai dokumen belum disetujui.
- QR code: kode yang bisa dipindai untuk melihat status dokumen di web.

Hal yang belum ada (catatan singkat)
- Notifikasi otomatis belum diaktifkan (bisa ditambahkan nantinya).
- Fokus saat ini adalah backend (logika dan API). Integrasi dan tampilan frontend menyesuaikan setelah backend stabil.

Langkah selanjutnya untuk tim
- Uji manual dengan skenario nyata (buat dokumen, jalankan approval bertingkat).
- Jika perlu, tambahkan notifikasi (email/pesan) dan fitur pencarian dokumen.

Jika mau saya tulis versi singkat untuk presentasi atau versi instruksi untuk pengguna (step-by-step), beri tahu target audiensnya (mis. staff admin, HR, legal) dan format yang diinginkan.

