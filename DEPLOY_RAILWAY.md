# Panduan Deploy HeavyRent ke Railway

Project ini sudah dirapikan agar bisa langsung terdeteksi Railway sebagai
aplikasi **Laravel + React (Vite)** lewat Railpack, tanpa Dockerfile manual.
Railway otomatis akan: `composer install`, `npm install && npm run build`,
menjalankan migrasi database, lalu menyalakan server (FrankenPHP + Caddy).

## Apa yang sudah diperbaiki
- **Migrasi database yang hilang** — tabel `excavators`, `operators`,
  `bookings`, dan kolom `role` di tabel `users` sebelumnya **tidak punya
  migration sama sekali** (skema lama dibuat manual langsung di MySQL lokal).
  Ini yang membuat aplikasi tetap error walau deploy "sukses" — karena begitu
  API dipanggil, tabelnya tidak ada. Sudah ditambahkan 4 file migration baru.
- **Seeder dibuat idempotent** — Railway menjalankan `migrate --seed` di
  setiap deploy; seeder lama akan error "duplicate entry" di deploy kedua.
  Sekarang pakai `firstOrCreate` dan otomatis membuat 2 akun demo:
  - Admin: `admin@heavyrent.test` / `password`
  - Customer: `customer@heavyrent.test` / `password`
- **`trustProxies`** ditambahkan di `bootstrap/app.php` supaya Laravel
  mengenali HTTPS dengan benar di belakang proxy Railway (penting untuk
  cookie session & CSRF).

## Langkah Deploy

### 1. Push perubahan ini ke GitHub
Repo ini sudah terhubung ke `origin` (GitHub). Commit lalu push seperti biasa:
```bash
git add -A
git commit -m "fix: tambah migration yang hilang, siap deploy Railway"
git push
```

### 2. Tambahkan database di Railway
Di project Railway kamu: **+ New → Database → MySQL** (paling sesuai karena
aplikasi ini memang didesain untuk MySQL). Railway otomatis membuat service
MySQL terpisah dengan variabel `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`,
`MYSQLUSER`, `MYSQLPASSWORD`.

### 3. Set Environment Variables di service Laravel-nya
Buka service backend → tab **Variables** → **Raw Editor**, paste ini (ganti
`<nama-service-mysql>` sesuai nama service MySQL kamu, defaultnya `MySQL`):

```
APP_NAME=HeavyRent
APP_ENV=production
APP_KEY=base64:LvuFdQJrFhmDHOjlHTyxEexRkd1kWRf6tJeURBLFx6w=
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_LEVEL=error
```

> ⚠️ `APP_KEY` di atas sudah berupa key valid siap pakai — cukup copy-paste.
> Kalau mau generate sendiri, boleh juga (harus format `base64:` + 32 byte acak).

> Jika nama service MySQL kamu bukan `MySQL`, sesuaikan referensi
> `${{MySQL.MYSQLHOST}}` dst. dengan nama service aslinya (Railway akan
> menyarankan otomatis lewat autocomplete `${{ }}` di Raw Editor).

### 4. Generate Domain
Tab **Settings → Networking → Generate Domain** pada service Laravel-nya.

### 5. Deploy
Railway akan otomatis build & deploy setiap kali kamu push ke branch yang
terhubung. Build akan: install dependency PHP & JS, build asset React
(`npm run build`), lalu saat start otomatis menjalankan
`php artisan migrate --seed --force` — jadi begitu deploy selesai, tabel dan
akun demo sudah langsung ada.

### 6. Login & Coba
Buka domain yang di-generate, login pakai salah satu akun demo di atas.

## Kalau ingin coba tanpa MySQL (opsi lebih sederhana)
Aplikasi ini juga bisa jalan pakai **SQLite** (tanpa tambah service database),
cukup set `DB_CONNECTION=sqlite` dan hapus semua variabel `DB_*` lain. Catatan:
tanpa Volume, data akan hilang tiap kali redeploy — cocok untuk demo cepat,
tapi untuk data yang perlu tetap ada, gunakan opsi MySQL di atas atau tambahkan
Railway Volume yang di-mount ke folder `database/`.

## Menjalankan secara lokal (opsional, untuk development)
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # kalau pakai sqlite
php artisan migrate --seed
composer run dev   # menjalankan server + vite + queue + log sekaligus
```
