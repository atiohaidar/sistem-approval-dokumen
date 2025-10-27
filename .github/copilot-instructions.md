setiap prompt yang saya lakukan, tolong catat di file Prompt-Tracking yang ada pada folder docs dengan format berikut di bawah nya. jadi paling baru itu paling bawah. untuk pencatatanya di akhir saja setelah proses code generation nya selesai. jadi supaya ada rekap hasilnya 
contoh:
# Prompt Tracking
**Tanggal:** [tanggal dan jam prompt dilakukan, format: DD MMMM YYYY]
**Prompt:** [isi prompt yang saya berikan]

**Evaluasi:** [evaluasi singkat tentang kejelasan dan fokus prompt (misal kesalahan apa saja dilakukan), serta saran untuk perbaikan jika diperlukan]

**Rekap Hasil:** [ringkasan hasil yang dihasilkan dari prompt tersebut, apakah sesuai harapan atau tidak, dan catatan penting lainnya]

tambahkan ringkasan evaluasi nya di paling bawah file ini, dan ini di update setiap kali ada prompt baru.

## Design Guidelines for Frontend
# Palet Warna & Panduan Desain

Dokumen singkat yang merangkum palet warna dan aturan desain yang digunakan di project.

## Palet warna (variables SCSS)
- Primary: `$primary` — #4F5686
  - Kegunaan: warna utama branding, tombol utama, header/ikon aksen.
- Secondary: `$secondary` — #F17641
  - Kegunaan: aksen, CTA, link penting.
- Success: `$success` — #05AA5B
  - Kegunaan: status sukses, aksi positif, badge.
- Danger: `$danger` — #DA3732
  - Kegunaan: error, aksi destruktif, peringatan.
- Dark: `$dark` — #212529
  - Kegunaan: teks utama, latar gelap.

Variabel ini didefinisikan di: `assets/scss/colors.scss` dan di-mapping ke `$theme-colors` untuk override tema Bootstrap.

## Tipografi & spacing
- Font global: `Poppins`, sans-serif.
- Ukuran font dasar: `14px` (di `assets/scss/app.scss`).
- Letter-spacing: `1px` untuk elemen teks (`*a` override di `app.scss`).

## Aturan tombol yang ada
- `.btn { font-size: 14px; }` — ukuran font tombol.
- `.btn-success` dan `.btn-secondary` dipaksa berwarna teks putih (`color: white !important;`) di `assets/scss/button.scss` untuk meningkatkan kontras.
- `.btn-outline-success:hover` juga memaksa teks putih pada hover.

> Catatan: penggunaan `!important` membuat override berat. Pertimbangkan menggunakan variabel Bootstrap (`$btn-*`) atau menurunkan spesifisitas untuk maintainability.

## Cara menggunakan warna dalam SCSS
Import urutannya penting: override variabel harus di-define sebelum mengimpor Bootstrap.

Contoh SCSS singkat:

```scss
// assets/scss/colors.scss  (sudah ada)
@import 'colors';
@import 'bootstrap/scss/bootstrap';

// memakai variabel
.header {
  background-color: $primary;
  color: #fff;
}

.btn-cta {
  background-color: $secondary;
  color: #fff;
}
```

## Lokasi file terkait
- `assets/scss/colors.scss` — definisi variabel warna dan `$theme-colors` map
- `assets/scss/app.scss` — mengimpor colors dan bootstrap, serta aturan global
- `assets/scss/button.scss` — override styling tombol

## Rekomendasi singkat
- Buat satu halaman dokumentasi (contoh ini) sudah dibuat di `docs/colors.md` untuk rujukan tim.
- Hindari `!important` jika memungkinkan; prefer men-set variabel Bootstrap seperti `$btn-success-color` atau gunakan class spesifik.
- Jika ingin akses palet di TypeScript (komponen inline style atau storybook), saya bisa membuat `src/styles/colors.ts` yang mengekspor object warna.

