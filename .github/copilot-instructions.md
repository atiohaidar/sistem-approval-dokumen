setiap prompt yang saya lakukan, tolong catat di file Prompt-Tracking yang ada pada folder docs dengan format berikut di bawah nya. jadi paling baru itu paling bawah. untuk pencatatanya di akhir saja setelah proses code generation nya selesai. jadi supaya ada rekap hasilnya 
contoh:
# Prompt Tracking
**Tanggal:** [tanggal dan jam prompt dilakukan, format: DD MMMM YYYY]
**Prompt:** [isi prompt yang saya berikan]

**Evaluasi:** [evaluasi singkat tentang kejelasan dan fokus prompt (misal kesalahan apa saja dilakukan), serta saran untuk perbaikan jika diperlukan]

**Rekap Hasil:** [ringkasan hasil yang dihasilkan dari prompt tersebut, apakah sesuai harapan atau tidak, dan catatan penting lainnya]

tambahkan ringkasan evaluasi nya di paling bawah file ini, dan ini di update setiap kali ada prompt baru.

## Design Guidelines for Frontend

### Telkom University Color Palette

Berdasarkan filosofi dan makna kode warna logo Telkom University, berikut adalah panduan desain untuk frontend project ini. Warna-warna ini telah diintegrasikan ke dalam Tailwind CSS config.

#### Warna Primer dan Filosofinya:

1. **Merah Maroon** (`#B6252A` / `bg-telkom-maroon`)
   - Filosofi: Melambangkan semangat eksplorasi dan keberanian menciptakan keilmuan baru dengan dilandasi oleh tekad yang kuat.
   - Penggunaan: Header, tombol utama, accent color untuk elemen penting.

2. **Merah** (`#ED1E28` / `bg-telkom-red`)
   - Filosofi: Melambangkan semangat eksplorasi dan keberanian.
   - Penggunaan: Highlight, error states, secondary accents.

3. **Abu-abu Tua** (`#55565B` / `bg-telkom-grey-dark`)
   - Filosofi: Menjadi lambang bagi teknologi modern sebagai modal dasar bagi Keluarga Besar Telkom Indonesia.
   - Penggunaan: Text utama, borders, dark backgrounds.

4. **Abu-abu** (`#959597` / `bg-telkom-grey`)
   - Filosofi: Menjadi lambang bagi teknologi modern, kekhasan pada ICT.
   - Penggunaan: Secondary text, muted elements, table headers.

5. **Putih** (`#FFFFFF` / `bg-telkom-white`)
   - Filosofi: Melambangkan tata kelola yang bersih dan bertujuan murni.
   - Penggunaan: Background utama, card backgrounds.

6. **Hitam** (`#000000` / `bg-telkom-black`)
   - Filosofi: Menjadi lambang sebuah ketegasan prinsip dan keyakinan.
   - Penggunaan: Text headings, strong contrasts.

#### Kombinasi Warna:

- **Merah-Putih**: Spirit kebanggaan dalam berkarya.
- **Abu-abu dan Hitam**: Kekuatan karakter bijaksana, dinamika kampus.

#### Implementasi di Tailwind CSS:

```javascript
// tailwind.config.js
colors: {
  'telkom-maroon': '#B6252A',
  'telkom-red': '#ED1E28',
  'telkom-grey-dark': '#55565B',
  'telkom-grey': '#959597',
  'telkom-white': '#FFFFFF',
  'telkom-black': '#000000',
}
```

#### Contoh Penggunaan:

- Header: `bg-telkom-maroon text-telkom-white`
- Body text: `text-telkom-grey-dark`
- Accent: `text-telkom-red`
- Cards: `bg-telkom-white border border-telkom-grey`

#### Prinsip Desain:

1. **Konsistensi**: Gunakan warna sesuai filosofi - maroon untuk eksplorasi, grey untuk teknologi.
2. **Kontras**: Pastikan readability dengan kombinasi warna yang tepat.
3. **Hierarki**: Gunakan ukuran dan warna untuk menunjukkan importance.
4. **Responsivitas**: Pastikan design bekerja di semua device.

#### Page Design Guidelines:

Page `/design-guidelines` telah dibuat sebagai contoh implementasi lengkap dengan:
- Layout blog-style
- Sections untuk setiap warna dengan swatches
- Table kode warna
- Responsive design

Gunakan page ini sebagai reference untuk komponen UI lainnya.