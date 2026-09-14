# Budget Tracker Jakarta — Laravel + SQLite + Firebase Hosting / Cloud Run

Aplikasi budget pribadi berbasis konsep siklus tanggal 15: setiap siklus dimulai tanggal 15 dan berakhir sebelum tanggal 15 bulan berikutnya. Contoh: siklus September 2026 = 15 Sep 2026 s.d. 14 Okt 2026; tanggal 15 Okt masuk siklus Oktober. Ini mencegah double count.

## Fitur
- Dashboard mobile friendly.
- Pilih siklus bulan.
- Pilih profil Opsi A / Opsi B.
- Budget per kategori: Kos, Operasional Jakarta, Allianz, Dana Darurat, BMRI, Emas, Uang Bebas.
- Input / hapus transaksi.
- Riwayat bulanan otomatis.
- Download Excel (.xlsx) untuk 1 siklus dan semua riwayat.
- PIN sederhana sebelum masuk aplikasi.
- SQLite untuk development.

## Penting tentang Firebase + SQLite
Firebase Hosting tidak menjalankan PHP secara langsung. Laravel dijalankan di **Cloud Run** dan Firebase Hosting melakukan rewrite/proxy ke service Cloud Run.

Cloud Run memiliki filesystem container yang **tidak persisten**. Karena itu `database/database.sqlite` dapat hilang ketika instance restart. Jangan gunakan SQLite di Cloud Run untuk data keuangan yang harus aman. Untuk production, gunakan Cloud SQL (PostgreSQL/MySQL) atau database persisten lain.

Kode ini tetap memakai SQLite secara default agar mudah dipakai lokal, dan Eloquent membuat perpindahan ke Cloud SQL mudah melalui `.env` tanpa mengubah controller.

## Cara pasang ke project Laravel 12

1. Buat project Laravel baru:

```bash
composer create-project laravel/laravel:^12.0 budget-tracker
cd budget-tracker
```

2. Salin semua folder/file dari paket ini ke root project Laravel, timpa file yang sama.

3. Tambahkan PhpSpreadsheet:

```bash
composer require phpoffice/phpspreadsheet
```

4. Buat SQLite:

```bash
# Windows PowerShell
New-Item database/database.sqlite -ItemType File

# macOS/Linux
mkdir -p database
touch database/database.sqlite
```

5. Salin `.env.example.budget` menjadi `.env` lalu sesuaikan, atau tambahkan variabelnya ke `.env` project.

```bash
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Buka `http://127.0.0.1:8000`.

Akses dari HP pada Wi-Fi yang sama: cari IP laptop (mis. `192.168.1.10`) lalu buka `http://192.168.1.10:8000`. Pastikan firewall mengizinkan port 8000.

## PIN
Default contoh di `.env.example.budget`: `APP_PIN=123456`. Ganti sebelum deploy.

## Deploy
Lihat `DEPLOY_FIREBASE.md`.
