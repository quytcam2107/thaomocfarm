# Mộc Xanh

Website thương mại điện tử chuyên **thảo mộc và đặc sản Tây Bắc**, xây dựng bằng Laravel 13 và triển khai trên một VPS duy nhất.

Dự án được thiết kế theo hướng đơn giản, dễ vận hành, tối ưu chi phí và phù hợp với mô hình thương mại điện tử quy mô vừa/nhỏ, không phụ thuộc vào các dịch vụ hạ tầng bên ngoài.

---

## 1. Tổng quan

**Tên thương hiệu:** Mộc Xanh  
**Mã dự án:** TMX  
**Loại dự án:** E-commerce  
**Framework:** Laravel 13  
**PHP:** 8.3+  
**Database:** MySQL 8 / MariaDB  
**Frontend:** Blade + Tailwind + Alpine.js / Vanilla JS  
**Build tool:** Vite  
**Cache:** File  
**Queue:** Database + Supervisor  
**Session:** Database  
**Web server:** Nginx  
**Deployment:** Git + Composer + Artisan  
**Infrastructure:** 1 VPS duy nhất

### Nguyên tắc kiến trúc

```text
                    ┌─────────────────────┐
                    │       Internet      │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │        Nginx        │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │     Laravel 13      │
                    │                     │
                    │  Web + Admin Mono    │
                    │  Controllers         │
                    │  Services            │
                    │  Models              │
                    │  Blade               │
                    └───────┬───────┬─────┘
                            │       │
                  ┌─────────┘       └─────────┐
                  ▼                           ▼
          ┌──────────────┐            ┌──────────────┐
          │    MySQL     │            │ File Storage │
          │              │            │    public    │
          └──────────────┘            └──────────────┘
                            │
                            ▼
                   ┌─────────────────┐
                   │ Database Queue  │
                   │   Supervisor    │
                   └─────────────────┘
```

---

# 2. Công nghệ

## Backend

- PHP 8.3+
- Laravel 13
- Eloquent ORM
- Laravel FormRequest
- Laravel Policy
- Laravel Queue
- Laravel Scheduler
- Laravel Cache

## Database

- MySQL 8 hoặc MariaDB
- InnoDB
- `utf8mb4_unicode_ci`
- FULLTEXT index cho tìm kiếm
- Không sử dụng read replica

## Frontend

- Blade
- Tailwind CSS
- Alpine.js
- Vanilla JavaScript
- Vite

## Infrastructure

- 1 VPS
- Nginx
- PHP-FPM
- MySQL/MariaDB
- Supervisor
- Cron
- Git

---

# 3. Giới hạn kiến trúc

Dự án **chỉ sử dụng một VPS**.

Không triển khai các dịch vụ bên ngoài cho các thành phần core của hệ thống.

### Không sử dụng

- Redis
- Meilisearch
- Algolia
- Elasticsearch
- Laravel Octane
- Docker
- Read Replica
- Cloud database
- External cache service

### Các thành phần bắt buộc

| Thành phần | Công nghệ |
|---|---|
| Web server | Nginx |
| Application | Laravel 13 |
| PHP | PHP 8.3+ |
| Database | MySQL 8 / MariaDB |
| Cache | File |
| Session | Database |
| Queue | Database |
| Queue worker | Supervisor |
| Search | MySQL FULLTEXT + LIKE |
| Storage | Local public disk |
| Image processing | Intervention Image + GD |

---

# 4. Mono Project

Website khách hàng và Admin nằm trong **cùng một Laravel application**.

```text
Laravel Application
│
├── Website
│   ├── /
│   ├── /san-pham/*
│   ├── /danh-muc/*
│   ├── /gio-hang/*
│   └── /thanh-toan/*
│
└── Admin
    └── /admin/*
```

Admin sử dụng:

- Route prefix `/admin`
- Middleware
- Policy
- Role `admin`
- Role `staff`

Không tạo một project frontend/backend riêng cho Admin.

---

# 5. Cấu trúc thư mục

Cấu trúc chính:

```text
app/
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   └── Web/
│   └── Requests/
├── Models/
├── Services/
├── DTOs/
├── Support/
│   └── helpers.php
└── View/
    └── Components/

config/
└── thaomoc.php

public/
└── assets/
    ├── fonts.css
    ├── js/
    │   ├── app.js
    │   └── cart.js
    └── css/
        ├── style.css
        └── partials/
            ├── 01-base.css
            ├── 02-header.css
            ├── 03-hero.css
            ├── 04-home.css
            ├── 05-product-card.css
            ├── 06-category-page.css
            ├── 07-product-detail.css
            ├── 08-cart.css
            ├── 09-checkout.css
            ├── 10-success.css
            ├── 11-coupon.css
            └── 12-layout-extras.css

resources/
└── views/
    ├── web/
    └── components/

routes/
└── web.php
```

---

# 6. Quy tắc PHP

Mọi file PHP phải bắt đầu bằng:

```php
<?php

declare(strict_types=1);
```

PHP tối thiểu:

```text
PHP >= 8.3
```

## Model

Model sử dụng chuẩn Laravel 13.

Factory:

```php
#[UseFactory(ProductFactory::class)]
```

Hidden attributes:

```php
#[Hidden(['password', 'remember_token'])]
```

Fillable:

```php
protected $fillable = [
    'name',
    'slug',
];
```

Cast sử dụng:

```php
protected function casts(): array
{
    return [
        'is_featured' => 'boolean',
    ];
}
```

Quan hệ phải có DocBlock generic phù hợp.

Ví dụ:

```php
/**
 * Lấy các biến thể của sản phẩm.
 *
 * @return HasMany<ProductVariant, $this>
 */
public function variants(): HasMany
{
    return $this->hasMany(ProductVariant::class);
}
```

---

# 7. Controller và Service

Controller phải mỏng.

Không đặt business logic lớn trực tiếp trong Controller.

```text
Request
   ↓
Controller
   ↓
Service
   ↓
Model / Database
   ↓
Service
   ↓
Controller
   ↓
View / JSON
```

Ví dụ:

```php
public function show(
    CategoryShowRequest $request,
    CatalogService $catalogService,
    string $slug
): View {
    $data = $catalogService->getCategoryShow(
        $slug,
        $request->validated()
    );

    abort_if($data === null, 404);

    return view('web.category', $data);
}
```

Service chịu trách nhiệm:

- Business logic
- Validation nghiệp vụ
- Transaction
- Tính toán
- Cache
- Database integrity ở tầng application

Service trả:

- Array
- Scalar
- DTO

---

# 8. Database

Tất cả bảng sử dụng:

```text
InnoDB
utf8mb4
utf8mb4_unicode_ci
```

Mọi bảng có:

```text
id
created_at
updated_at
```

## Không sử dụng Foreign Key Constraint

Database không khai báo foreign key constraint.

Ví dụ:

```text
user_id BIGINT UNSIGNED + INDEX
```

thay vì:

```text
FOREIGN KEY (user_id) REFERENCES users(id)
```

Quan hệ được quản lý ở tầng Eloquent và Service.

Service chịu trách nhiệm đảm bảo toàn vẹn dữ liệu.

---

# 9. Tiền tệ

Tất cả tiền tệ được lưu bằng:

```text
INTEGER UNSIGNED
```

Đơn vị:

```text
VND
```

Ví dụ:

```text
199000 = 199.000₫
```

Không lưu tiền bằng:

```text
199.000
199.0000
199.00
```

Không sử dụng floating point cho tiền.

Hiển thị:

```php
format_vnd($amount)
```

hoặc:

```php
number_format($amount) . '₫'
```

---

# 10. Cache

Cache sử dụng:

```text
file
```

Không sử dụng Redis.

Do đó:

```php
Cache::tags()
```

**BỊ CẤM.**

## Group Cache

Dự án sử dụng:

```php
remember_group()
```

và:

```php
bump_group_version()
```

Cache key có dạng:

```text
{group}:v{version}:{key}
```

Ví dụ:

```text
home:v3:featured_categories
```

### Helpers

```php
remember_group(
    string $group,
    string $key,
    int $ttl,
    Closure $cb
): mixed
```

```php
bump_group_version(string $group): int
```

```php
group_version_key(string $group): string
```

---

# 11. Quy tắc dữ liệu Cache

File cache sử dụng serialization.

**Không lưu object vào cache.**

Không lưu:

- DTO
- stdClass
- Eloquent Model
- Collection object
- Closure

Cache chỉ chứa:

- array
- string
- integer
- float
- boolean
- null

Ví dụ đúng:

```php
return remember_group(
    'home',
    'best_sellers',
    600,
    static function (): array {
        return Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'price_min',
            ])
            ->get()
            ->map(static fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price_min,
            ])
            ->all();
    }
);
```

DTO phải được hydrate **sau khi đọc cache**.

---

# 12. Cache Groups

Các group hiện tại:

```text
home
catalog
content
settings
review
```

## Home

```text
featured_categories = 600s
flash_sale          = 300s
coupons             = 600s
best_sellers        = 600s
herbal_tea          = 600s
banners             = 3600s
```

## Catalog

```text
category_tree       = 3600s
product_detail      = 300s
```

## Content

```text
posts               = 1800s
testimonials        = 3600s
```

## Settings

```text
3600s
```

---

# 13. Bump Cache

Sau khi thay đổi dữ liệu ảnh hưởng tới nội dung hiển thị, phải invalidate cache group.

Các group hỗ trợ:

```bash
php artisan thaomoc:bump-cache home
php artisan thaomoc:bump-cache catalog
php artisan thaomoc:bump-cache review
```

Không tự ý dùng:

```bash
php artisan cache:clear
```

để xử lý cache nghiệp vụ nếu chỉ cần invalidate một group.

---

# 14. Queue

Queue sử dụng:

```env
QUEUE_CONNECTION=database
```

Worker chạy bằng Supervisor.

Queue được sử dụng cho các tác vụ như:

- Sinh thumbnail
- Log search terms
- Cập nhật dữ liệu không cần đồng bộ
- Xử lý các tác vụ hậu kỳ
- Nhả tồn kho
- Hoàn flash-sale slot
- Các tác vụ scheduled

---

# 15. Stock và Flash Sale

Đây là phần yêu cầu đặc biệt quan trọng.

Không sử dụng mô hình:

```text
SELECT stock
↓
if stock > qty
↓
UPDATE stock
```

vì có race condition.

## Trừ tồn kho

Bắt buộc dùng conditional UPDATE nguyên tử:

```text
WHERE stock >= qty
UPDATE stock = stock - qty
```

Sau UPDATE:

```text
affected rows = 0
```

thì:

```text
throw exception
↓
rollback transaction
```

## Flash Sale

Slot được chốt bằng:

```text
WHERE qty_sold < qty_total
UPDATE qty_sold = qty_sold + 1
```

Nếu:

```text
affected rows = 0
```

thì không còn slot.

Không được:

```text
SELECT qty_sold
↓
SELECT qty_total
↓
if qty_sold < qty_total
↓
UPDATE
```

---

# 16. Checkout Transaction

Checkout phải nằm trong:

```php
DB::transaction()
```

Quy trình:

```text
Validate request
      ↓
Load cart
      ↓
Calculate totals
      ↓
Validate coupon
      ↓
BEGIN TRANSACTION
      ↓
Create Order
      ↓
Create Order Items
      ↓
Reserve / deduct stock
      ↓
Create Stock Movements
      ↓
Create Shipment
      ↓
Record Coupon Usage
      ↓
COMMIT
```

Nếu bất kỳ bước nào thất bại:

```text
ROLLBACK
```

---

# 17. Order Number

Mã đơn hàng:

```text
TMX-yyyyMMdd-xxxxx
```

Ví dụ:

```text
TMX-20260926-A82KQ
```

Prefix mặc định:

```env
ORDER_PREFIX=TMX
```

Độ dài random:

```text
5
```

---

# 18. Đơn hàng và tồn kho

Khi đơn bị:

- cancelled
- COD quá hạn

hệ thống phải xử lý:

```text
Nhả tồn kho
↓
Hoàn flash-sale slot nếu có
↓
Ghi stock_movements
```

Tất cả thao tác quan trọng phải đảm bảo transaction và concurrency safety.

---

# 19. Image Upload

Ảnh upload vào disk:

```text
public
```

Storage link:

```bash
php artisan storage:link
```

Ảnh được validate:

- MIME
- File size

MIME cho phép:

```text
image/jpeg
image/png
image/webp
```

Dung lượng mặc định:

```text
2048 KB
```

Thumbnail:

```text
small  = 300px
medium = 600px
large  = 1200px
```

Image processing:

```text
Intervention Image
+
GD
```

Thumbnail được tạo bằng queue.

Định dạng:

```text
WebP hoặc JPG
```

---

# 20. Search

Không sử dụng:

- Elasticsearch
- Meilisearch
- Algolia

Tìm kiếm sử dụng:

```text
MySQL FULLTEXT
```

Fallback:

```text
LIKE
```

Chiến lược:

```text
FULLTEXT(name)
      ↓
nếu không có kết quả / không phù hợp
      ↓
LIKE
```

Từ khóa tìm kiếm được log thông qua queue vào:

```text
search_terms
```

Dữ liệu top search terms được sử dụng cho:

```text
Search suggestions
```

---

# 21. Eager Loading và N+1

Tuyệt đối tránh N+1.

Đặc biệt:

```text
Home
Catalog
Product list
Product detail
```

Phải sử dụng:

```php
with()
```

và chỉ select những column cần thiết.

Ví dụ:

```php
->with([
    'coverImage' => static function (Relation $relation): void {
        $relation->select([
            'product_images.id',
            'product_images.product_id',
            'product_images.path',
        ]);
    },
])
```

---

# 22. Eager Loading Relation Closure

Closure constraint trong:

```php
with()
```

nhận:

```text
Relation
```

Không phải:

```text
Builder
```

Đúng:

```php
static function (Relation $relation): void {
    $relation->select([
        'product_images.id',
        'product_images.product_id',
        'product_images.path',
    ]);
}
```

Chỉ:

```text
withCount()
whereHas()
```

mới nhận Builder trong closure tương ứng.

---

# 23. ofMany và select

Với quan hệ sử dụng:

```php
ofMany()
```

nếu eager load có `select()`, tên bảng phải được định danh rõ.

Đúng:

```php
'product_images.product_id'
```

Không dùng:

```php
'product_id'
```

Lý do: `ofMany()` có thể sử dụng INNER JOIN với derived table và gây lỗi MySQL:

```text
1052 Column 'product_id' in field list is ambiguous
```

---

# 24. Blade Components

Dự án chỉ sử dụng:

```text
Anonymous Blade Components
```

Không sử dụng class-based component.

Component nằm trong:

```text
resources/views/components/
```

Ví dụ:

```text
resources/views/components/ui/product-card.blade.php
```

Khai báo:

```blade
@props([
    'product',
])
```

Không dùng:

```blade
$this
```

trong anonymous component.

---

# 25. Component Data Contract

Blade Component chỉ nhận:

```text
scalar
array
```

Không truyền:

- Closure
- Paginator object
- Eloquent object nếu contract không yêu cầu
- Service
- Model không cần thiết

Với paginator, chuyển thành array:

```php
$paginator->toArray()
```

hoặc:

```php
[
    'total' => $paginator->total(),
    'current_page' => $paginator->currentPage(),
    'last_page' => $paginator->lastPage(),
    'from' => $paginator->firstItem(),
    'to' => $paginator->lastItem(),
    'per_page' => $paginator->perPage(),
]
```

---

# 26. Layout Blade

Layout chính:

```text
resources/views/components/layouts/app.blade.php
```

Layout chứa:

```text
Header
Main
Footer
Float navigation
Drawer
Toast
JS
Stack scripts
```

Layout tạo:

```html
<main id="main">
```

Do đó view con **không được lồng thêm `<main>`**.

View con chỉ cần:

```html
<div class="container">
    ...
</div>
```

---

# 27. Optional Layout Props

Layout hỗ trợ:

```text
hideCatnav
hideFloatnav
```

Ví dụ:

```blade
<x-layouts.app
    :hide-catnav="true"
    :hide-floatnav="true"
>
```

Layout truyền prop xuống component con.

Ví dụ:

```blade
<x-floatnav :hide-floatnav="$hideFloatnav" />
```

Component:

```blade
@if (!($hideFloatnav ?? false))
    ...
@endif
```

---

# 28. CSS Architecture

CSS được chia thành 12 partial.

```text
public/assets/css/partials/

01-base.css
02-header.css
03-hero.css
04-home.css
05-product-card.css
06-category-page.css
07-product-detail.css
08-cart.css
09-checkout.css
10-success.css
11-coupon.css
12-layout-extras.css
```

## Entry file

```text
public/assets/css/style.css
```

File này **chỉ chứa 12 dòng import**.

Ví dụ:

```css
@import url('partials/01-base.css');
@import url('partials/02-header.css');
@import url('partials/03-hero.css');
@import url('partials/04-home.css');
@import url('partials/05-product-card.css');
@import url('partials/06-category-page.css');
@import url('partials/07-product-detail.css');
@import url('partials/08-cart.css');
@import url('partials/09-checkout.css');
@import url('partials/10-success.css');
@import url('partials/11-coupon.css');
@import url('partials/12-layout-extras.css');
```

Không đưa CSS nghiệp vụ trực tiếp vào `style.css`.

Font được import trong:

```text
01-base.css
```

thông qua:

```css
@import url('../fonts.css');
```

Không sử dụng command build CSS riêng.

---

# 29. CSS Naming

CSS sử dụng class tự đặt theo phong cách BEM.

Ví dụ:

```text
.pcard
.pcard__img
.pcard__body
.pcard__price
```

Modifier:

```text
.cpn--applied
```

Không phụ thuộc vào tên class framework cho business component.

---

# 30. JavaScript

JavaScript chính:

```text
public/assets/js/app.js
public/assets/js/cart.js
```

## app.js

Phụ trách:

- Drawer
- Toast
- Reveal animation
- UI behavior dùng chung

## cart.js

Phụ trách:

- Cart drawer
- Add cart từ PDP
- Mini-cart badge

## Inline JavaScript

Các behavior đặc thù của trang có thể nằm trong Blade tương ứng.

Ví dụ Cart:

```text
syncApplyButton
copyCouponCode
applyCouponCode
removeAppliedCoupon
qty +/-
remove item
drawer open/close
ESC close
```

---

# 31. JavaScript Event

Lưu ý quan trọng:

```javascript
input.value = code;
```

không tự trigger:

```text
input
change
```

Do đó sau khi gán value bằng JavaScript phải dispatch event:

```javascript
input.dispatchEvent(
    new Event('input', {
        bubbles: true,
    })
);
```

Đặc biệt áp dụng cho:

```text
syncApplyButton
```

trong giỏ hàng.

---

# 32. Coupon

Session key:

```text
cart_coupon_code
```

Được định nghĩa tại:

```php
CouponService::SESSION_KEY
```

Coupon hỗ trợ:

```text
fixed
percent
shipping
```

Đối với:

```text
shipping
```

coupon được xử lý như free shipping.

Không cộng coupon shipping vào goods discount.

Checkout và Cart phải sử dụng cùng logic tính tiền.

---

# 33. Coupon Snapshot

Khi tạo order, phải lưu mã coupon tại thời điểm đặt hàng:

```text
orders.coupon_code_snapshot
```

Mục đích:

```text
Audit
Historical data
Order integrity
```

Không phụ thuộc hoàn toàn vào record coupon hiện tại.

---

# 34. Route

Route hiện tại nằm trong:

```text
routes/web.php
```

Thứ tự route rất quan trọng.

Các route cụ thể phải đứng trước catch-all.

Catch-all:

```text
/{slug}
```

luôn nằm **cuối cùng**.

Bắt buộc constraint:

```php
->where('slug', '[a-z0-9\-]+')
```

Mục đích tránh catch-all nuốt:

```text
.ico
.xml
URL có dấu .
```

Sau khi thêm hoặc thay đổi route:

```bash
php artisan route:clear
```

Nếu cần kiểm tra:

```bash
php artisan route:list
```

---

# 35. Image Path Convention

Các đường dẫn ảnh hiện tại phải được giữ nguyên.

## Category icon

```php
asset('assets/images/categories/' . $icon)
```

Fallback:

```php
asset('assets/images/category-default.svg')
```

## Flash sale image

```php
asset('assets/images/' . $path)
```

Fallback:

```php
asset('images/product-default.svg')
```

## Product detail gallery

Component tự xử lý:

```php
asset($path)
```

Fallback:

```text
images/placeholder.svg
```

## Category card

```php
asset('storage/' . ltrim($path, '/'))
```

Fallback:

```php
asset('images/product-default.svg')
```

Không tự ý thay đổi convention nếu không có yêu cầu cụ thể.

---

# 36. Brand Contract

Tên thương hiệu chính thức:

```text
Mộc Xanh
```

Không tự đổi thành:

```text
Mộc Xanh Farm
Xanh Farm
Bản Xanh
```

Tên:

```text
TMX
```

được sử dụng làm prefix đơn hàng.

JSON-LD Product cũng phải giữ:

```text
Mộc Xanh
```

---

# 37. View Contract

Home Controller truyền:

```text
featuredCategories
flashProducts
coupons
bestSellers
herbalTeaProducts
```

Đặc biệt:

```text
$flashProducts
```

là tên biến chính thức của block Flash Sale.

Không đổi thành:

```text
$flashSale
```

---

# 38. Null Safety trong Blade

Không sử dụng trực tiếp:

```php
trim($value)
```

khi `$value` có khả năng null.

Thay vào đó:

```php
$description = (string) ($value ?? '');
```

sau đó:

```php
trim($description)
```

Điều này tránh:

```text
TypeError
```

trên PHP 8.1+.

---

# 39. Component tự ẩn khi không có dữ liệu

Các section không có dữ liệu không được render fallback cứng nếu contract yêu cầu tự ẩn.

Ví dụ:

```blade
@if (is_array($data) && count($data) > 0)
    ...
@endif
```

View cha có thể truyền:

```blade
$data ?? null
```

Mục tiêu:

```text
Không có DB record
↓
Component không render
↓
Không tạo khoảng trắng vô nghĩa
```

---

# 40. Database Schema

## Users

```text
users
├── id
├── name
├── email UNIQUE
├── phone UNIQUE NULL
├── password
├── role
├── email_verified_at
└── remember_token
```

Role:

```text
admin
staff
customer
```

## Addresses

```text
addresses
├── id
├── user_id
├── full_name
├── phone
├── province
├── district
├── ward
├── detail
└── is_default
```

## Categories

```text
categories
├── id
├── parent_id
├── name
├── slug UNIQUE
├── icon
├── description
├── sort_order
├── is_featured
└── status
```

Status:

```text
active
hidden
```

## Products

```text
products
├── id
├── category_id
├── sku UNIQUE
├── slug UNIQUE
├── name
├── subtitle
├── description
├── price_min
├── compare_price
├── stock_total
├── sold_count
├── rating_avg
├── rating_count
├── view_count
├── is_featured
├── status
├── published_at
└── seo
```

Search:

```text
FULLTEXT(name)
```

## Product Variants

```text
product_variants
├── id
├── product_id
├── sku UNIQUE
├── label
├── price
├── compare_price
├── stock
├── is_default
└── sort_order
```

Mỗi sản phẩm phải có ít nhất:

```text
1 variant
```

và có:

```text
is_default = true
```

## Product Images

```text
product_images
├── id
├── product_id
├── path
├── thumb_path
├── alt
├── sort_order
└── is_cover
```

## Promotions

```text
promotions
├── id
├── type
├── name
├── description
├── start_at
├── end_at
└── status
```

Promotion type:

```text
flash_sale
campaign
```

## Promotion Products

```text
promotion_products
├── id
├── promotion_id
├── product_id
├── product_variant_id
├── flash_price
├── discount_percent
├── qty_total
├── qty_sold
├── per_user_limit
└── sort_order
```

## Coupons

```text
coupons
├── id
├── code UNIQUE
├── type
├── value
├── min_order_value
├── max_discount
├── starts_at
├── expires_at
├── usage_limit
├── per_user_limit
├── status
└── description
```

## Orders

```text
orders
├── id
├── order_number UNIQUE
├── user_id
├── customer_name
├── customer_phone
├── customer_email
├── address_snapshot
├── note
├── subtotal
├── discount_amount
├── coupon_id
├── coupon_code_snapshot
├── shipping_fee
├── total
├── payment_method
├── payment_status
├── status
├── paid_at
└── cancelled_at
```

## Order Items

```text
order_items
├── id
├── order_id
├── product_id
├── product_variant_id
├── name_snapshot
├── sku_snapshot
├── image_snapshot
├── price
├── qty
└── subtotal
```

Snapshot dữ liệu sản phẩm nhằm đảm bảo order history không thay đổi khi sản phẩm được chỉnh sửa sau này.

## Stock Movements

```text
stock_movements
├── id
├── product_variant_id
├── type
├── qty
├── ref_type
├── ref_id
├── note
└── created_by
```

Stock movement type:

```text
import
export
adjust
order_reserve
order_release
```

---

# 41. PHP Enums

Các enum nằm tại:

```text
app/Enums/
```

Bao gồm:

```text
OrderStatus
OrderPaymentStatus
PaymentMethod
CouponType
PromotionType
PromotionStatus
ProductStatus
ReviewStatus
BannerPosition
ShipmentCarrier
StockMovementType
```

## OrderStatus

```text
new
confirmed
packing
shipping
delivered
cancelled
returning
```

Có:

```text
label()
next()
```

---

# 42. DTOs

DTO nằm tại:

```text
app/DTOs/
```

Các DTO hiện tại:

```text
ProductViewDTO
CategoryViewDTO
RelatedProductDTO
ReviewViewDTO
```

DTO dùng để định nghĩa contract dữ liệu giữa Service và View.

DTO không được đưa trực tiếp vào file cache.

---

# 43. Services hiện tại

```text
app/Services/

HomeService.php
CatalogService.php
CartService.php
CouponService.php
CheckoutService.php
```

## HomeService

Phụ trách:

```text
featuredCategories()
flashSale()
homeCoupons()
bestSellers()
herbalTea()
```

Cache group:

```text
home
```

## CatalogService

Phụ trách:

```text
getProductDetail()
getProductReviews()
getCategoryShow()
```

## CartService

Phụ trách:

```text
addToCart()
getOrCreateCart()
getCartSummary()
getCartDetails()
mergeGuestCart()
removeItem()
calculateShippingFee()
getCartItemCount()
updateCartItem()
```

## CouponService

Phụ trách:

```text
resolveAppliedCoupon()
calculateDiscount()
applyToCart()
removeAppliedCoupon()
getAvailableCoupons()
getPublicCoupons()
recordUsage()
```

## CheckoutService

Phụ trách:

```text
getCheckoutData()
calculateShippingFee()
processCheckout()
generateOrderNumber()
```

---

# 44. FormRequest

Mọi input từ người dùng phải sử dụng FormRequest.

Hiện tại:

```text
AddToCartRequest
UpdateCartRequest
ApplyCouponRequest
CategoryShowRequest
CheckoutRequest
```

Không đưa validation chính vào Controller.

Không tin tưởng dữ liệu từ frontend.

---

# 45. Security

Bắt buộc:

- FormRequest cho input
- Policy cho Admin
- Rate limit login
- Rate limit checkout
- Rate limit apply coupon
- Rate limit search
- Rate limit send OTP
- Blade escaping mặc định
- Unique slug
- Unique SKU
- Unique order number
- Validate upload MIME/size
- Transaction cho checkout
- Atomic update cho stock

Rate limit mặc định:

```text
login         = 5
checkout      = 3
apply_coupon  = 10
search        = 30
send_otp      = 3
```

---

# 46. Config

Cấu hình nghiệp vụ:

```text
config/thaomoc.php
```

Các nhóm:

```text
order
rate_limits
cache
shipping
upload
review
```

Không hard-code các giá trị nghiệp vụ nếu đã có config tương ứng.

---

# 47. Shipping

Phương thức hiện tại:

```text
standard
fast
```

## Standard

```text
>= 500.000₫
→ miễn phí
```

Dưới ngưỡng:

```text
20.000₫
```

## Fast

```text
30.000₫
```

Cấu hình:

```env
SHIPPING_DEFAULT_FEE=30000
SHIPPING_FREE_THRESHOLD=500000
```

Tên carrier nội bộ:

```text
Giao hàng nội bộ Mộc Xanh
```

---

# 48. Trang hiện đã hoàn thành

Đã hoàn thiện:

- Danh mục nổi bật
- Flash Sale
- Countdown Flash Sale
- Bán chạy tuần này
- Trà hoa thảo mộc
- Coupon từ DB
- Product detail
- Product gallery
- Product variants
- Product quantity
- Add cart
- Wishlist UI
- Product related
- Product schema JSON-LD
- Category listing
- Category filters
- Category sorting
- Category pagination
- Cart
- Coupon cart
- Coupon drawer mobile
- Sticky cart bar mobile
- Checkout COD
- Shipping calculation
- Coupon calculation
- Order creation
- Checkout success
- Responsive layout

---

# 49. Các phần còn lại

## Website

```text
Trang chủ hoàn thiện thêm:
├── Topbar
├── Hero
├── USP
├── Region
├── Trust
├── Blog
├── Newsletter
└── Footer

Trang khác:
├── Tra cứu đơn
├── Đăng nhập
├── Đăng ký
├── OTP
├── Wishlist
├── Blog list
├── Blog detail
├── Stores
└── Chính sách
```

## Admin

Tối thiểu:

```text
/admin
├── Dashboard
├── Categories
├── Products
│   ├── Product
│   ├── Variants
│   └── Images
├── Promotions
├── Coupons
├── Banners
├── Reviews
├── Orders
├── Posts
├── Testimonials
└── Settings
```

---

# 50. Dữ liệu hiện tại

Snapshot:

```text
26/09/2026
```

## Categories

Có 4 category active:

```text
1. Thảo mộc
2. Thịt gác bếp
3. Gia vị Tây Bắc
4. Mật ong
```

Tất cả:

```text
is_featured = 1
status = active
```

## Products

Có:

```text
8 active products
```

Mỗi product có:

```text
cover image
default variant
```

## Flash Sale

Promotion:

```text
id = 1
type = flash_sale
status = active
```

Có:

```text
4 promotion products
```

## Coupons

Đã seed:

```text
SALETO500K
SALE9K
SALE22K
SALETO300K
```

Các coupon đang active và có thời gian hết hạn trong tương lai tại thời điểm snapshot.

---

# 51. Dữ liệu chưa có

Các bảng sau có thể đang trống và chờ dữ liệu thực tế:

```text
reviews
coupon_products
coupon_categories
coupon_usages
banners
carts
wishlists
orders
order_items
order_status_histories
payments
shipments
stock_movements
newsletter_subscribers
stores
search_terms
addresses
```

Một số dữ liệu có thể đã được seed:

```text
Admin user
Testimonials
Posts
Settings
```

---

# 52. Cài đặt môi trường

## Requirements

Máy chủ cần:

```text
PHP >= 8.3
Composer
MySQL 8 / MariaDB
Nginx
PHP-FPM
Supervisor
Git
Node.js + npm
```

Kiểm tra:

```bash
php -v
composer --version
mysql --version
nginx -v
node -v
npm -v
```

---

# 53. Clone project

```bash
git clone <repository-url>
cd <project-directory>
```

Cài PHP dependencies:

```bash
composer install
```

---

# 54. Environment

Copy:

```bash
cp .env.example .env
```

Tạo application key:

```bash
php artisan key:generate
```

Cấu hình database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thaomoc
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Queue:

```env
QUEUE_CONNECTION=database
```

Session:

```env
SESSION_DRIVER=database
```

Cache:

```env
CACHE_STORE=file
```

Order:

```env
ORDER_PREFIX=TMX
```

---

# 55. Database

Chạy migration:

```bash
php artisan migrate
```

Nếu triển khai production:

```bash
php artisan migrate --force
```

Seed dữ liệu nếu cần:

```bash
php artisan db:seed
```

---

# 56. Storage

Tạo symbolic link:

```bash
php artisan storage:link
```

Kiểm tra:

```bash
ls -la public/storage
```

Nginx phải có quyền đọc storage.

---

# 57. Frontend

Cài dependencies:

```bash
npm install
```

Development:

```bash
npm run dev
```

Production:

```bash
npm run build
```

Frontend sử dụng Vite.

Lưu ý: CSS business hiện tại được tổ chức thành các partial trong:

```text
public/assets/css/partials/
```

và entry:

```text
public/assets/css/style.css
```

không được tự ý chuyển đổi architecture nếu không có yêu cầu.

---

# 58. Laravel Optimization

Production:

```bash
php artisan optimize
```

Sau khi thay đổi route:

```bash
php artisan route:clear
php artisan optimize
```

Sau khi thay đổi config:

```bash
php artisan config:clear
php artisan optimize
```

Sau khi thay đổi view:

```bash
php artisan view:clear
```

---

# 59. Supervisor

Queue worker chạy bằng Supervisor.

Ví dụ process:

```text
Laravel
   ↓
Database jobs
   ↓
Supervisor
   ↓
php artisan queue:work
```

Worker phải được cấu hình để tự restart khi process chết.

Sau khi thay đổi Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart <worker-name>
```

---

# 60. Scheduler

Laravel scheduler chạy thông qua cron.

Cron production:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler chịu trách nhiệm cho các tác vụ định kỳ như:

```text
COD quá hạn
Nhả tồn
Hoàn flash-sale slot
Cleanup
Các job nghiệp vụ định kỳ khác
```

---

# 61. Deploy Production

Quy trình deploy chuẩn:

```bash
git pull
composer install --no-dev
php artisan optimize
php artisan migrate --force
```

Nếu có frontend thay đổi:

```bash
npm ci
npm run build
```

Sau deploy có thể restart queue worker:

```bash
sudo supervisorctl restart <worker-name>
```

---

# 62. ALTER TABLE khi production đã có dữ liệu

Nếu production đã có dữ liệu và không muốn chạy migration ngay vì rủi ro ảnh hưởng dữ liệu:

Có thể thực hiện:

```sql
ALTER TABLE orders
ADD COLUMN coupon_code_snapshot VARCHAR(50) NULL
AFTER coupon_id;
```

Sau đó vẫn tạo migration tương ứng để đồng bộ schema.

Migration phải kiểm tra:

```php
Schema::hasColumn()
```

trước khi:

```text
add column
drop column
```

Mục tiêu là migration có thể chạy an toàn trên cả database mới và database đã được ALTER thủ công.

---

# 63. Debug Route

Nếu gặp lỗi:

```text
Route [web.cart.add] not defined
```

Kiểm tra:

```bash
php artisan route:list
```

Sau đó:

```bash
php artisan route:clear
```

và:

```bash
php artisan optimize
```

---

# 64. Quy tắc phát triển

Trước khi sửa code:

```text
1. Đọc code hiện tại
2. Kiểm tra contract hiện tại
3. Giữ nguyên business logic đang chạy
4. Chỉ mở rộng phần cần thiết
5. Kiểm tra ảnh hưởng tới Service / Controller / View / JS / CSS
6. Kiểm tra route
7. Kiểm tra cache
8. Kiểm tra N+1
9. Kiểm tra transaction
10. Kiểm tra concurrency
```

Không được tự ý:

- Đổi architecture
- Đổi database engine
- Thêm Redis
- Thêm Elasticsearch
- Thêm Meilisearch
- Đưa business logic vào Controller
- Đưa business logic lớn vào Blade
- Đưa DTO vào file cache
- Dùng select-check-update cho stock
- Thêm foreign key constraint
- Đổi tên thương hiệu Mộc Xanh
- Đổi prefix TMX
- Đổi tên biến `$flashProducts`

---

# 65. Coding Checklist

Trước khi hoàn thành một feature:

```text
[ ] PHP >= 8.3
[ ] declare(strict_types=1)
[ ] Controller mỏng
[ ] Business logic nằm trong Service
[ ] FormRequest cho input
[ ] Policy nếu liên quan Admin
[ ] Eager loading
[ ] Không N+1
[ ] Select đúng columns
[ ] Không object trong file cache
[ ] Cache sử dụng remember_group()
[ ] Bump cache khi dữ liệu thay đổi
[ ] Transaction cho nghiệp vụ quan trọng
[ ] Atomic UPDATE cho stock
[ ] Không foreign key DB
[ ] Tiền là INTEGER UNSIGNED VND
[ ] Blade escape mặc định
[ ] Anonymous component
[ ] Không dùng $this trong Blade component
[ ] Không truyền Closure vào component
[ ] Không lồng <main>
[ ] Route mới được kiểm tra
[ ] Catch-all vẫn nằm cuối
[ ] JS dispatch event nếu set input.value bằng JS
[ ] Responsive UI
[ ] Không phá DOM/CSS contract hiện tại
```

---

# 66. Các lỗi cần đặc biệt tránh

### Lỗi 1 — Relation closure

Sai:

```php
with([
    'coverImage' => function (Builder $query) {
        //
    },
])
```

Đúng:

```php
with([
    'coverImage' => function (Relation $relation): void {
        //
    },
])
```

---

### Lỗi 2 — Ambiguous column với ofMany

Sai:

```php
'product_id'
```

Đúng:

```php
'product_images.product_id'
```

---

### Lỗi 3 — Object trong file cache

Sai:

```php
return ProductViewDTO(...);
```

Đúng:

```text
Cache
↓
array
↓
DTO hydration
```

---

### Lỗi 4 — Stock race condition

Sai:

```text
SELECT stock
UPDATE stock
```

Đúng:

```text
UPDATE ... WHERE stock >= qty
```

---

### Lỗi 5 — Catch-all route

Sai:

```text
/{slug}
```

đặt trước route khác.

Đúng:

```text
/{slug}
```

luôn cuối file.

---

### Lỗi 6 — trim(null)

Sai:

```php
trim($description)
```

khi `$description` có thể null.

Đúng:

```php
$description = (string) ($description ?? '');
```

---

### Lỗi 7 — JS không fire event

Sai:

```javascript
input.value = code;
```

Đúng:

```javascript
input.value = code;

input.dispatchEvent(
    new Event('input', {
        bubbles: true,
    })
);
```

---

# 67. Business Flow

## Add to Cart

```text
Product
  ↓
Variant
  ↓
Check stock
  ↓
Get/Create Cart
  ↓
Insert or increment CartItem
```

Unique:

```text
(cart_id, product_variant_id)
```

---

## Apply Coupon

```text
Input code
   ↓
FormRequest
   ↓
Rate limit
   ↓
CouponService
   ↓
Validate coupon
   ↓
Validate cart eligibility
   ↓
Save session coupon
   ↓
Recalculate cart
```

---

## Checkout

```text
Cart
 ↓
Validate customer data
 ↓
Resolve coupon
 ↓
Calculate subtotal
 ↓
Calculate discount
 ↓
Calculate shipping
 ↓
Calculate total
 ↓
Transaction
 ↓
Create order
 ↓
Create order items
 ↓
Deduct stock
 ↓
Create stock movements
 ↓
Create shipment
 ↓
Record coupon usage
 ↓
Clear cart
 ↓
Clear coupon session
 ↓
Success page
```

---

# 68. UI Contract

Các section Home hiện tại:

```text
x-sections.hero
x-sections.usp
x-sections.collections
x-sections.flash-sale
x-sections.coupons
x-sections.best-sellers
x-sections.herbal-tea
x-sections.region
x-sections.stats
x-sections.testimonials
x-sections.trust
x-sections.tips
x-sections.newsletter
```

Không đổi component contract nếu không có yêu cầu.

---

# 69. Cart UI Contract

Cart gồm:

```text
.cart-layout
├── .coupon-strip
├── .cart-main
└── .summary
```

Mobile:

```text
.cart-main
↓
coupon-strip
↓
summary
```

Mobile còn có:

```text
.cart-bar
.cart-drawer
```

Coupon alert:

```text
.coupon-alert
.coupon-alert--error
.coupon-alert--success
.coupon-alert--loading
```

JS:

```text
showAlert()
```

---

# 70. Flash Sale Countdown

Server chỉ trả:

```text
end_at
```

dạng:

```text
ISO 8601
```

Ví dụ:

```text
2026-09-26T23:59:59+07:00
```

JavaScript phía client tự tính countdown.

Server không cần liên tục gửi số giây còn lại.

---

# 71. Product Counters

Các counter denormalized nằm trên:

```text
products
```

Bao gồm:

```text
price_min
stock_total
sold_count
rating_avg
rating_count
```

Cập nhật bằng:

```text
increment()
```

hoặc:

```text
queue job
```

Không tính lại toàn bộ dữ liệu ở mỗi request nếu counter đã có sẵn.

---

# 72. Quy tắc khi mở rộng hệ thống

Feature mới phải ưu tiên:

```text
Existing architecture
        ↓
Existing Service
        ↓
Existing DTO
        ↓
Existing component contract
        ↓
Existing CSS/JS architecture
```

Không tạo một pattern mới nếu pattern hiện tại đã giải quyết được bài toán.

Nếu cần thay đổi architecture, phải xem xét ảnh hưởng tới:

```text
Database
Models
Services
Controllers
Requests
Views
Components
CSS
JS
Cache
Queue
Deployment
```

---

# 73. Tài liệu tham chiếu nhanh

## Laravel

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan queue:work
php artisan schedule:run
php artisan optimize
```

## Cache

```bash
php artisan thaomoc:bump-cache home
php artisan thaomoc:bump-cache catalog
php artisan thaomoc:bump-cache review
```

## Storage

```bash
php artisan storage:link
```

## Production

```bash
composer install --no-dev
php artisan optimize
php artisan migrate --force
```

---

# 74. Trạng thái dự án

**Project:** Mộc Xanh  
**Version:** Laravel 13  
**PHP:** 8.3+  
**Infrastructure:** Single VPS  
**Architecture:** Mono Laravel application  
**Database:** MySQL 8 / MariaDB  
**Cache:** File  
**Queue:** Database  
**Session:** Database  
**Search:** MySQL FULLTEXT + LIKE  
**Frontend:** Blade + Tailwind + Alpine.js / Vanilla JS

### Development status

```text
Core E-commerce
├── Product                 DONE
├── Category                DONE
├── Product Detail          DONE
├── Cart                    DONE
├── Coupon                  DONE
├── Checkout COD            DONE
├── Order Creation          DONE
├── Stock Deduction         DONE
├── Checkout Success        DONE
├── Home Core Sections      DONE
└── Responsive UI           DONE

Remaining
├── Order Tracking          TODO
├── Authentication          TODO
├── OTP                      TODO
├── Wishlist                 TODO
├── Blog                     TODO
├── Store                     TODO
├── Static Policies          TODO
├── Admin                    TODO
├── Banner Management        TODO
├── Review Management        TODO
└── Statistics               TODO
```

---

# 75. Nguyên tắc cuối cùng

Đây là các nguyên tắc nền tảng của dự án:

```text
Simple infrastructure
        +
Single VPS
        +
Laravel 13
        +
Service-oriented business logic
        +
Database-backed queue/session
        +
File cache
        +
MySQL FULLTEXT
        +
Atomic stock operations
        +
Transaction
        +
No N+1
        +
No unnecessary external services
```

Mọi thay đổi trong tương lai phải ưu tiên:

1. Tính đúng của nghiệp vụ.
2. An toàn dữ liệu.
3. Khả năng chống race condition.
4. Hiệu năng thực tế.
5. Khả năng vận hành trên một VPS.
6. Khả năng bảo trì lâu dài.
7. Giữ nguyên các contract hiện tại nếu không có yêu cầu thay đổi.

**Mộc Xanh — TMX**