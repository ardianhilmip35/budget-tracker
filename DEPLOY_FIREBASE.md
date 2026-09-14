# Deploy ke Firebase Hosting + Cloud Run

> Firebase Hosting tidak menjalankan PHP. Arsitektur deployment:
>
> **HP / Browser → Firebase Hosting (`PROJECT_ID.web.app`) → Cloud Run → Laravel**

## 0. Peringatan SQLite
Cloud Run memakai filesystem container yang disposable. SQLite yang disimpan di `database/database.sqlite` **tidak tahan restart**. Untuk aplikasi keuangan production, gunakan Cloud SQL PostgreSQL/MySQL.

Langkah di bawah bisa dipakai untuk menguji Laravel + SQLite di Cloud Run, tetapi data dapat hilang saat instance restart/deploy ulang.

## 1. Persiapan
Install:
- PHP 8.2+
- Composer
- Google Cloud CLI (`gcloud`)
- Node.js + Firebase CLI

```bash
npm install -g firebase-tools
gcloud auth login
firebase login
```

## 2. Buat project Firebase / Google Cloud
Buat project di Firebase Console lalu catat `PROJECT_ID`.

```bash
gcloud config set project PROJECT_ID
firebase use --add
```

Aktifkan API:

```bash
gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com
```

## 3. Siapkan `.env` untuk build/deploy
Di local:

```bash
cp .env.example .env
php artisan key:generate --show
```

Catat output APP_KEY, misalnya `base64:...`.

## 4. Deploy Laravel ke Cloud Run
Dari root project:

```bash
gcloud run deploy budget-tracker \
  --source . \
  --region asia-southeast2 \
  --allow-unauthenticated \
  --set-env-vars "APP_ENV=production,APP_DEBUG=false,APP_URL=https://PROJECT_ID.web.app,APP_KEY=PASTE_APP_KEY,APP_PIN=GANTI_PIN,DB_CONNECTION=sqlite,DB_DATABASE=/var/www/html/database/database.sqlite,SESSION_DRIVER=cookie,RUN_MIGRATIONS=true" \
  --max-instances 1
```

Paket ini menjalankan migration + seed saat container start jika `RUN_MIGRATIONS=true`. Ini hanya membantu demo SQLite; **tidak membuat file SQLite menjadi persisten**.

### Opsi yang lebih aman: Cloud SQL
Ganti `DB_CONNECTION` menjadi `pgsql` atau `mysql`, lalu set env host/database/user/password dan deploy ulang. Controller tidak perlu diubah.

## 5. Hubungkan Firebase Hosting ke Cloud Run
`firebase.json` sudah berisi rewrite ke:
- serviceId: `budget-tracker`
- region: `asia-southeast2`

Deploy Hosting:

```bash
firebase deploy --only hosting
```

Aplikasi akan tersedia di:

```text
https://PROJECT_ID.web.app
https://PROJECT_ID.firebaseapp.com
```

Buka URL itu dari HP.

## 6. Kalau region berbeda
Ubah `region` pada `firebase.json` agar sama dengan region Cloud Run, lalu:

```bash
firebase deploy --only hosting
```

## 7. Update aplikasi
Setiap ada perubahan Laravel:

```bash
gcloud run deploy budget-tracker --source . --region asia-southeast2
firebase deploy --only hosting
```

## Rekomendasi production
Untuk data personal finance:
1. Firebase Hosting untuk URL/SSL/CDN.
2. Cloud Run untuk Laravel.
3. **Cloud SQL PostgreSQL** untuk database persisten.
4. `SESSION_DRIVER=cookie`.
5. Ganti PIN default dan jangan commit `.env`.
6. Tambahkan Firebase Authentication atau Laravel authentication jika aplikasi akan dipakai lebih dari satu user.
