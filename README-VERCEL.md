# Sistem Login/Register — Versi Vercel (Demo)

⚠️ **PENTING — BACA DULU SEBELUM DEPLOY**

Vercel **tidak mendukung PHP secara resmi**. Project ini memakai *community runtime* `vercel-php` (bukan produk resmi Vercel), dan filesystem Vercel **read-only** — hanya folder `/tmp` yang bisa ditulis, tapi **sementara** (isinya hilang setiap function "tidur" / cold start baru, biasanya setelah beberapa menit tidak ada trafik).

**Konsekuensinya:** setiap kali kamu daftar akun baru, datanya akan tersimpan sebentar di `/tmp/data/users.json`, tapi **bisa hilang kapan saja** tanpa peringatan. Ini cocok untuk **demo/uji coba tampilan**, tapi **tidak cocok** kalau tugasmu dinilai berdasarkan data yang harus tersimpan permanen.

Kalau butuh penyimpanan JSON yang benar-benar persisten dan online, gunakan hosting PHP biasa (InfinityFree, 000webhost, Hostinger, dll) — bukan Vercel.

## Struktur Project

```
login-system-vercel/
├── vercel.json          # Konfigurasi runtime PHP komunitas
├── index.html            # Landing page statis, redirect ke /login.php
├── api/                   # SEMUA file PHP wajib di sini (aturan vercel-php)
│   ├── config.php        # Sudah disesuaikan: pakai /tmp/data saat di Vercel
│   ├── functions.php
│   ├── index.php
│   ├── register.php
│   ├── login.php
│   ├── dashboard.php
│   ├── edit_profile.php
│   ├── logout.php
│   └── data/
│       └── users.json    # Hanya dipakai saat run LOKAL (bukan di Vercel)
└── assets/
    └── style.css
```

## Cara Deploy ke Vercel

### Opsi A — Lewat GitHub (paling gampang)

1. Buat repository baru di GitHub, upload seluruh isi folder `login-system-vercel/` ke repo tersebut (bukan folder induknya, isinya langsung).
2. Buka [vercel.com](https://vercel.com), login/daftar (bisa pakai akun GitHub).
3. Klik **"Add New" → "Project"**, pilih repository yang tadi dibuat.
4. Vercel otomatis mendeteksi `vercel.json` — biarkan pengaturan default, klik **Deploy**.
5. Tunggu proses build selesai (biasanya 1-2 menit), lalu buka link yang diberikan (contoh: `https://nama-project.vercel.app`).

### Opsi B — Lewat Vercel CLI

```bash
npm install -g vercel
cd login-system-vercel
vercel login
vercel --prod
```

## Testing Setelah Deploy

1. Buka `https://nama-project.vercel.app` → otomatis redirect ke `/login.php`.
2. Klik "Daftar sekarang", isi form, submit.
3. Kalau berhasil, kamu akan diarahkan ke dashboard — artinya PHP dan session jalan normal.
4. **Ingat**: kalau kamu tunggu beberapa menit lalu login lagi dengan akun yang sama, kemungkinan besar akan muncul "Email atau password salah" karena data di `/tmp` sudah hilang (cold start baru). Ini bukan bug — memang begitu cara kerja Vercel untuk PHP dengan penyimpanan file.

## Kalau Ingin Data Benar-Benar Permanen di Vercel

Solusinya bukan lagi file JSON, melainkan pindah ke database eksternal yang punya koneksi lewat internet, misalnya:
- **Vercel Postgres** / **Supabase** (gratis untuk skala kecil)
- **PlanetScale** (MySQL serverless)

Ini di luar cakupan tugas "penyimpanan file JSON", jadi kalau dosen memang mewajibkan JSON, sebaiknya pakai hosting PHP biasa saja seperti disebutkan di atas.
