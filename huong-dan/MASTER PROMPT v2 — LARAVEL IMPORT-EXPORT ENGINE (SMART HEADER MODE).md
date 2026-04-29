# 🚨 MASTER PROMPT v3.5 — LARAVEL IMPORT/EXPORT ENGINE (HARDENED)

---

# 🧠 CORE MINDSET (BẮT BUỘC)

```text
AI KHÔNG ĐƯỢC PHÉP SUY ĐOÁN
AI CHỈ ĐƯỢC CODE THEO SPEC
THIẾU SPEC → FAIL (KHÔNG TỰ BỔ SUNG)
```

---

# 🎯 MỤC TIÊU

Xây dựng hệ thống Import/Export:

* Generic
* Per-model config
* Smart header auto mapping
* High performance (100k–1M rows)
* Queue-ready
* Resume-safe
* Audit logging
* Dry-run support
* Multi-tenant ready

---

# ⚠️ QUY TẮC OUTPUT (BẮT BUỘC)

## STEP 1 — VẼ TREE

```text
Modules/
└── Admin/
    ├── ...
```

## STEP 2 — CONFIRM

```text
CONFIRM STRUCTURE: OK
```

## STEP 3 — CODE

```text
📄 File: path/to/file.php
```

---

# 🚫 NGHIÊM CẤM

* Không pseudo-code
* Không thiếu file
* Không sai namespace
* Không gom nhiều class/file

---

# 🧱 KIẾN TRÚC

```text
Controller → Manager → Pipeline → Chunk → Transformer → Caster → Validator → Upsert
```

---

# 🔥 PIPELINE ORDER (BẮT BUỘC — KHÔNG ĐƯỢC THAY ĐỔI)

```text
1. Read row
2. Normalize header
3. Map column (auto/custom)
4. Filter by $fillable
5. Clean value
6. Transform
7. Type cast
8. Apply default
9. Validate
10. Upsert
```

❌ Sai thứ tự = FAIL

---

# 🔐 DATA SAFETY RULES

```text
- Chỉ xử lý field trong $fillable
- Không trust Excel
- Không insert nếu chưa validate
- Default KHÔNG override dữ liệu có sẵn
```

---

# 🧩 MODEL CONTRACT (BẮT BUỘC 100%)

```php
interface Importable
{
    public function importRules(): array;
    public function importDefaults(): array;
    public function importCasts(): array;
    public function importTransforms(): array;

    public function importUniqueBy(): array;
    public function importUpdatable(): array;

    public function importColumns(): array; // optional
}
```

---

# 🚨 CONTRACT ENFORCEMENT

```text
Thiếu bất kỳ method nào → FAIL
```

---

# 🧠 MODEL IMPLEMENTATION SOP (CHO DEV)

---

## STEP 1 — DEFINE $fillable

```php
protected $fillable = [
    'name',
    'email',
    'phone',
    'is_active',
];
```

👉 Không nằm trong `$fillable` = KHÔNG IMPORT

---

## STEP 2 — VALIDATION

```php
public function importRules(): array
{
    return [
        'name'  => 'required|string',
        'email' => 'required|email',
    ];
}
```

---

## STEP 3 — DEFAULT

```php
public function importDefaults(): array
{
    return [
        'is_active' => true,
    ];
}
```

---

## STEP 4 — TRANSFORM

```php
public function importTransforms(): array
{
    return [
        'phone' => fn($v) => preg_replace('/\D/', '', $v),
    ];
}
```

---

## STEP 5 — CAST

```php
public function importCasts(): array
{
    return [
        'is_active' => 'boolean',
    ];
}
```

---

## STEP 6 — UPSERT CONFIG

```php
public function importUniqueBy(): array
{
    return ['email'];
}

public function importUpdatable(): array
{
    return ['name', 'phone', 'is_active'];
}
```

---

# ⚠️ UPSERT RULES (BẮT BUỘC)

```text
- importUniqueBy() không được rỗng
- importUpdatable() không được rỗng
- Không được update primary key
```

---

# 🧪 DRY RUN

```php
dryRun = true → chỉ validate, không insert
```

---

# ❌ ERROR STRATEGY

```php
skipInvalidRows = true
stopOnFirstError = false
maxErrors = 1000
```

---

# 🔄 RESUME IMPORT

```text
Import phải resume được theo chunk
```

---

# 📊 PROGRESS TRACKING

```text
processed_rows / total_rows
```

---

# 🧾 AUDIT LOG

## import_logs

* model
* total_rows
* success_rows
* failed_rows
* status

## import_errors

* row_number
* message
* payload

---

# 🧬 TYPE CASTING RULES

```text
- boolean: "true", "1", "yes" → true
- number: string → int/float
- date: string → Carbon
```

---

# 🧠 HEADER NORMALIZATION

```php
Str::of($column)
    ->trim()
    ->lower()
    ->ascii()
    ->replaceMatches('/[^a-z0-9]+/', '_')
    ->replaceMatches('/_+/', '_')
    ->trim('_');
```

---

# ⚡ PERFORMANCE RULES

```text
- chunk: 500–1000
- transaction per chunk
- không query trong loop
- stream file
```

---

# 🏢 MULTI-TENANT

```php
tenant_id (optional)
```

---

# 📤 EXPORT RULES

```text
- Không dùng importDefaults
- Output phản ánh DB
- Có exportColumns()
```

---

# 🚫 ANTI-PATTERNS

```text
- Hardcode field
- Query trong loop
- Validate sai thứ tự
- Override default sai
```

---

# 🧠 AI SELF-CHECK (BẮT BUỘC TRƯỚC KHI OUTPUT)

```text
✔ Có đủ tất cả file chưa?
✔ Namespace đúng chưa?
✔ Có thiếu method nào trong Importable không?
✔ Pipeline đúng thứ tự chưa?
✔ Có dùng upsert đúng không?
✔ Có transaction per chunk không?
✔ Có dùng $fillable filter không?
```

❌ Sai bất kỳ → FAIL

---

# 🎯 OUTPUT BẮT BUỘC

```text
1. Full folder tree
2. Tất cả file code
3. Model example
4. Controller usage
5. Route
```

---

# 🚨 FINAL COMMAND

```text
👉 Build hệ thống Import/Export cho model: [MODEL_NAME]

⚠️ BẮT BUỘC:
- Vẽ tree trước
- Confirm structure
- Sau đó mới code
```

---

# 🔥 STRICT MODE

```text
Thiếu file → FAIL
Sai namespace → FAIL
Sai pipeline → FAIL
Code không chạy → FAIL
```

---
Sử dụng MASTER PROMPT v3.5 để build hệ thống Import/Export cho model: User

RÀNG BUỘC:
- KHÔNG được thêm logic ngoài spec
- KHÔNG được thiếu bất kỳ file nào
- KHÔNG được sai namespace
- PHẢI tuân thủ pipeline order đã định nghĩa
- PHẢI implement đầy đủ Importable contract

QUY TRÌNH BẮT BUỘC:
1. Vẽ full folder tree trong Modules/Admin
2. Ghi: CONFIRM STRUCTURE: OK
3. Sau đó mới viết toàn bộ code

KIỂM TRA TRƯỚC KHI OUTPUT:
- Đã có đầy đủ file chưa?
- Đã có đủ method import* chưa?
- Đã dùng upsert + transaction chưa?
- Có filter $fillable chưa?

Nếu bất kỳ điều nào sai → KHÔNG OUTPUT