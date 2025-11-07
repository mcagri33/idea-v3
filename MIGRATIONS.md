# 📋 VERİTABANI MİGRATİON DOSYALARI - TAM LİSTE

## 📅 Migration Dosyaları (Tarih Sırasına Göre)

---

## 1. Users Tablosu
**Dosya:** `0001_01_01_000000_create_users_table.php`

### Tablo Yapısı
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    current_team_id BIGINT UNSIGNED NULL,
    profile_photo_path VARCHAR(2048) NULL,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

### İlişkiler
- Foreign Keys: Yok
- Index'ler: `email` (unique)
- Soft Deletes: ✅ Evet

### Ayrıca Oluşturulan Tablolar
- `password_reset_tokens`
- `sessions`

---

## 2. Cache Tabloları
**Dosya:** `0001_01_01_000001_create_cache_table.php`

### Tablo: cache
```sql
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INTEGER NOT NULL
);
```

### Tablo: cache_locks
```sql
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);
```

---

## 3. Jobs Tabloları
**Dosya:** `0001_01_01_000002_create_jobs_table.php`

### Tablo: jobs
```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INTEGER UNSIGNED NULL,
    available_at INTEGER UNSIGNED NOT NULL,
    created_at INTEGER UNSIGNED NOT NULL,
    INDEX(queue)
);
```

### Tablo: job_batches
```sql
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);
```

### Tablo: failed_jobs
```sql
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 4. Document Categories Tablosu
**Dosya:** `2024_12_24_075056_create_document_categories_table.php`

### Tablo Yapısı
```sql
CREATE TABLE document_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    template_file VARCHAR(255) NULL,
    slug VARCHAR(255) NULL,
    `order` INTEGER DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

### İlişkiler
- Foreign Keys: Yok
- Soft Deletes: ✅ Evet

---

## 5. Documents Tablosu ⭐ **KRİTİK**
**Dosya:** `2024_12_24_075106_create_documents_table.php`

### Tablo Yapısı
```sql
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status BOOLEAN DEFAULT 2, -- ⚠️ NOT: boolean ama default 2 (HATA! integer olmalı)
    rejection_note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE CASCADE
);
```

### İlişkiler
- Foreign Keys:
  - `user_id` → `users.id` (onDelete: CASCADE)
  - `category_id` → `document_categories.id` (onDelete: CASCADE)
- Soft Deletes: ✅ Evet

### ⚠️ EKSİK ALANLAR (Model'de var ama migration'da yok):
- `document_name` VARCHAR(255) NULL
- `description` TEXT NULL
- `file_year` INTEGER NULL (4 digit)

### ⚠️ SORUN:
- `status` BOOLEAN olarak tanımlı ama default değer 2 (INTEGER olmalı: 0=Red, 1=Onay, 2=Beklemede)

---

## 6. Document Logs Tablosu
**Dosya:** `2024_12_24_075230_create_document_logs_table.php`

### Tablo Yapısı
```sql
CREATE TABLE document_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    performed_by BIGINT UNSIGNED NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### İlişkiler
- Foreign Keys:
  - `document_id` → `documents.id` (onDelete: CASCADE)
  - `performed_by` → `users.id` (onDelete: CASCADE)

---

## 7. Two Factor Authentication (Users Tablosuna Eklenen)
**Dosya:** `2024_12_30_150106_add_two_factor_columns_to_users_table.php`

### Eklenen Kolonlar
```sql
ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL AFTER password;
ALTER TABLE users ADD COLUMN two_factor_recovery_codes TEXT NULL AFTER two_factor_secret;
-- (Eğer Fortify::confirmsTwoFactorAuthentication() true ise)
ALTER TABLE users ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL AFTER two_factor_recovery_codes;
```

---

## 8. Personal Access Tokens Tablosu
**Dosya:** `2024_12_30_150152_create_personal_access_tokens_table.php`

### Tablo Yapısı
```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX(tokenable_type, tokenable_id)
);
```

### İlişkiler
- Polymorphic: `tokenable` (morphs: tokenable_type, tokenable_id)
- Index'ler: `token` (unique)

---

## 9. Permission Tabloları (Spatie Permission)
**Dosya:** `2024_12_30_150940_create_permission_tables.php`

### 9.1. permissions
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_permission (name, guard_name)
);
```

### 9.2. roles
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- team_foreign_key BIGINT UNSIGNED NULL, -- (eğer teams enabled ise)
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_role (name, guard_name) -- veya (team_foreign_key, name, guard_name)
);
```

### 9.3. model_has_permissions
```sql
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    -- team_id BIGINT UNSIGNED NULL, -- (eğer teams enabled ise)
    
    PRIMARY KEY (permission_id, model_id, model_type),
    -- veya PRIMARY KEY (team_id, permission_id, model_id, model_type),
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX(model_id, model_type)
);
```

### 9.4. model_has_roles
```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    -- team_id BIGINT UNSIGNED NULL, -- (eğer teams enabled ise)
    
    PRIMARY KEY (role_id, model_id, model_type),
    -- veya PRIMARY KEY (team_id, role_id, model_id, model_type),
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX(model_id, model_type)
);
```

### 9.5. role_has_permissions
```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (permission_id, role_id),
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

---

## 10. Activity Logs Tablosu
**Dosya:** `2024_12_31_112144_create_activity_logs_table.php`

### Tablo Yapısı
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(255) NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NULL,
    description TEXT NULL,
    approved_at TIMESTAMP NULL,
    file_created_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### İlişkiler
- Foreign Keys:
  - `user_id` → `users.id` (onDelete: CASCADE)

---

## 11. User Documents Tablosu
**Dosya:** `2025_01_01_084443_create_user_documents_table.php`

### Tablo Yapısı
```sql
CREATE TABLE user_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### İlişkiler
- Foreign Keys:
  - `user_id` → `users.id` (onDelete: CASCADE)
- Soft Deletes: ✅ Evet

---

## 12. Notifications Tablosu
**Dosya:** `2025_01_02_093815_create_notifications_table.php`

### Tablo Yapısı
```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX(notifiable_type, notifiable_id)
);
```

### İlişkiler
- Polymorphic: `notifiable` (morphs: notifiable_type, notifiable_id)

---

## 13. Document Category Notes Tablosu
**Dosya:** `2025_06_19_074245_create_document_category_notes_table.php`

### Tablo Yapısı
```sql
CREATE TABLE document_category_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    document_category_id BIGINT UNSIGNED NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_category_id) REFERENCES document_categories(id) ON DELETE CASCADE
);
```

### İlişkiler
- Foreign Keys:
  - `user_id` → `users.id` (onDelete: CASCADE)
  - `document_category_id` → `document_categories.id` (onDelete: CASCADE)

---

## 14. Document Assignments Tablosu
**Dosya:** `2025_07_01_024700_create_document_assignments_table.php`

### Tablo Yapısı
```sql
CREATE TABLE document_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    status ENUM('pending', 'uploaded', 'approved', 'rejected') DEFAULT 'pending',
    document_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL
);
```

### İlişkiler
- Foreign Keys:
  - `user_id` → `users.id` (onDelete: CASCADE)
  - `category_id` → `document_categories.id` (onDelete: CASCADE)
  - `document_id` → `documents.id` (onDelete: SET NULL)

---

## 15. General Note (Users Tablosuna Eklenen)
**Dosya:** `2025_07_21_104934_add_general_note_to_users_table.php`

### Eklenen Kolon
```sql
ALTER TABLE users ADD COLUMN general_note TEXT NULL AFTER email;
```

---

## 16. User General Notes Tablosu
**Dosya:** `2025_07_21_110805_create_user_general_notes_table.php`

### Tablo Yapısı
```sql
CREATE TABLE user_general_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_year (user_id, year)
);
```

### İlişkiler
- Foreign Keys:
  - `user_id` → `users.id` (onDelete: CASCADE)
- Unique Constraint: `(user_id, year)` - Her kullanıcı için her yıl sadece bir not

---

## 17. AI Fields (Documents Tablosuna Eklenen)
**Dosya:** `2025_10_21_000000_add_ai_fields_to_documents_table.php`

### Eklenen Kolonlar
```sql
ALTER TABLE documents 
ADD COLUMN ai_risk_score INTEGER NULL AFTER rejection_note,
ADD COLUMN ai_validity VARCHAR(20) NULL AFTER ai_risk_score, -- ok, warning, critical
ADD COLUMN ai_warnings JSON NULL AFTER ai_validity,
ADD COLUMN ai_summary TEXT NULL AFTER ai_warnings,
ADD COLUMN ai_decision VARCHAR(20) NULL AFTER ai_summary, -- approved, rejected, pending
ADD COLUMN ai_notified_at TIMESTAMP NULL AFTER ai_decision,
ADD COLUMN ai_decided_at TIMESTAMP NULL AFTER ai_notified_at,
ADD COLUMN ai_learned_at TIMESTAMP NULL AFTER ai_decided_at,
ADD COLUMN ai_was_correct BOOLEAN NULL AFTER ai_learned_at;

-- Index'ler
CREATE INDEX idx_ai_decision ON documents(ai_decision);
CREATE INDEX idx_ai_validity ON documents(ai_validity);
CREATE INDEX idx_ai_risk_score ON documents(ai_risk_score);
CREATE INDEX idx_ai_learned_at ON documents(ai_learned_at);
```

### AI Alanları Açıklaması
- `ai_risk_score`: Risk skoru (0-100 arası)
- `ai_validity`: Geçerlilik durumu ('ok', 'warning', 'critical')
- `ai_warnings`: JSON formatında uyarılar
- `ai_summary`: AI özet metni
- `ai_decision`: AI kararı ('approved', 'rejected', 'pending')
- `ai_notified_at`: AI bildirimi tarihi
- `ai_decided_at`: AI karar tarihi
- `ai_learned_at`: Öğrenme tarihi
- `ai_was_correct`: AI kararının doğruluğu

---

## 18. Learning Fields (Documents Tablosuna Eklenen - Boş)
**Dosya:** `2025_10_21_210352_add_learning_fields_to_documents.php`

### Durum
⚠️ **BOŞ MİGRATİON** - Herhangi bir alan eklenmemiş

```php
Schema::table('documents', function (Blueprint $table) {
    // Boş - hiçbir şey yapılmıyor
});
```

---

## 📊 TABLO ÖZETİ

### Ana Tablolar (Business Logic)
1. **users** - Kullanıcılar
2. **document_categories** - Doküman kategorileri
3. **documents** ⭐ - Dokümanlar (KRİTİK)
4. **document_logs** - Doküman logları
5. **document_assignments** - Görev atamaları
6. **document_category_notes** - Kategori notları
7. **user_documents** - Kullanıcı dokümanları (ayrı tablo)
8. **user_general_notes** - Yönetici genel notları
9. **activity_logs** - Sistem aktivite logları

### Yardımcı Tablolar
10. **permissions** - İzinler (Spatie)
11. **roles** - Roller (Spatie)
12. **model_has_permissions** - Model-İzin ilişkisi
13. **model_has_roles** - Model-Rol ilişkisi
14. **role_has_permissions** - Rol-İzin ilişkisi
15. **notifications** - Bildirimler
16. **personal_access_tokens** - API token'ları

### Sistem Tabloları
17. **cache** - Cache
18. **cache_locks** - Cache kilitleri
19. **jobs** - Job kuyruğu
20. **job_batches** - Job batch'leri
21. **failed_jobs** - Başarısız job'lar
22. **password_reset_tokens** - Şifre sıfırlama token'ları
23. **sessions** - Oturumlar

---

## ⚠️ BİLİNEN SORUNLAR VE EKSİKLER

### 1. Documents Tablosu
- ❌ `status` alanı BOOLEAN ama default 2 (INTEGER olmalı)
- ❌ `document_name` alanı eksik (migration yok, model'de var)
- ❌ `description` alanı eksik (migration yok, model'de var)
- ❌ `file_year` alanı eksik (migration yok, model'de var)

### 2. Boş Migration
- ⚠️ `2025_10_21_210352_add_learning_fields_to_documents.php` - Boş migration

---

## ✅ ÇÖZÜM ÖNERİLERİ

### Documents Tablosu İçin Yeni Migration Oluşturulmalı:

```php
// 2025_XX_XX_XXXXXX_fix_documents_table.php
Schema::table('documents', function (Blueprint $table) {
    // Status'u integer yap
    $table->integer('status')->default(2)->change();
    
    // Eksik alanları ekle
    $table->string('document_name')->nullable()->after('category_id');
    $table->text('description')->nullable()->after('document_name');
    $table->integer('file_year')->nullable()->after('description');
});
```

---

## 📝 NOTLAR

- Tüm tablolarda `created_at` ve `updated_at` timestamp'leri var
- Soft delete kullanan tablolar: `users`, `document_categories`, `documents`, `user_documents`
- UUID kullanan tablolar: `users`, `document_categories`, `documents`
- Foreign key constraint'leri CASCADE delete kullanıyor (çoğunlukla)


