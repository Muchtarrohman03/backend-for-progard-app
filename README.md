# Backend API – Sistem Monitoring Karyawan

Backend ini merupakan **RESTful API** dan **Admin Dashboard** untuk aplikasi monitoring karyawan. Sistem dibangun menggunakan **Laravel** dengan autentikasi API berbasis token, manajemen role & permission, serta dashboard admin modern.

---

## 🚀 Tech Stack
- **Laravel** (Backend Framework)
- **MySQL** (Database)
- **Laravel Sanctum** (API Authentication)
- **Spatie Laravel Permission** (Role Based Access Control)
- **Filament PHP** (Admin Dashboard)

---

## 🔐 Authentication & Authorization
### Authentication
- Menggunakan **Laravel Sanctum**
- API berbasis **token authentication**
- Digunakan oleh aplikasi mobile (Flutter)

### Authorization
- Role & permission management menggunakan **Spatie Laravel Permission**
- Middleware **role-based access** untuk membatasi akses endpoint

Contoh role:
- `admin`
- `manager`
- `employee`

---

## 🧑‍💼 Admin Dashboard
Admin dashboard dibangun menggunakan **Filament PHP**, menyediakan:
- Manajemen user
- Manajemen role & permission
- Monitoring data karyawan
- CRUD data sistem

Akses dashboard:
```
/admin
```

---

## 📦 Fitur Utama API
- Authentication (Login, Logout)
- Manajemen user
- Role & permission berbasis middleware
- Monitoring data karyawan
- Integrasi dengan aplikasi mobile (Flutter)

---

## 🛠️ Instalasi & Setup

### 1️⃣ Clone Repository
```bash
git clone https://github.com/username/nama-repo-backend.git
cd nama-repo-backend
```

### 2️⃣ Install Dependency
```bash
composer install
```

### 3️⃣ Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

---

### 4️⃣ Migrasi & Seeder
```bash
php artisan migrate --seed
```

---

### 5️⃣ Install Filament
```bash
php artisan filament:install
```

Buat user admin:
```bash
php artisan make:filament-user
```

---

### 6️⃣ Install Spatie Permission
```bash
php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"
php artisan migrate
```

---

### 7️⃣ Install Sanctum
```bash
php artisan install:api
php artisan migrate
```

---

### 8️⃣ Jalankan Server
```bash
php artisan serve
```

---

## 🔑 Middleware Role Based Access (Contoh)
```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});
```

---

## 📂 Struktur Folder Penting
```
app/
 ├─ Http/
 │   ├─ Controllers/
 │   └─ Middleware/
 ├─ Models/
 └─ Providers/

routes/
 ├─ api.php
 └─ web.php

```

---

## 📱 Integrasi Mobile App
Backend ini dikonsumsi oleh aplikasi **Flutter**, menggunakan:
- REST API
- Bearer Token (Sanctum)

---

## 🧪 Testing (Opsional)
```bash
php artisan test
```

---

## 📄 License
Project ini menggunakan lisensi **MIT**.

---

## 👤 Author
**Muchtarrohman**

---

> 📌 *Pastikan file `.env` tidak di-push ke repository. Gunakan `.env.example` untuk dokumentasi.*
