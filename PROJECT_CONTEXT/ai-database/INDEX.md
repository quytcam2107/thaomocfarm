# Chỉ mục Database — bản xuất cho AI

> Sinh tự động bởi `php artisan ai:export-database`. KHÔNG SỬA THỦ CÔNG — chạy lại lệnh để cập nhật.
- Thời điểm xuất: 2026-10-02T10:14:54+00:00
- Kết nối: `mysql` (driver: `mysql`, database: `laravel`)
- Tổng số bảng: 60
- Tổng số bản ghi: 430

## Cách đọc các file

- `tables/<tên_bảng>.md`: cấu trúc (cột, kiểu, index, FK), thống kê và MẪU dữ liệu của từng bảng.
- `data/<tên_bảng>.jsonl`: TOÀN BỘ dữ liệu, mỗi dòng là một bản ghi JSON (đọc file này khi cần dữ liệu đầy đủ).
- Nếu cần tái tạo cấu trúc trong code, xem thư mục `database/migrations/`.

## Danh sách bảng

| Bảng | Số bản ghi | File cấu trúc + mẫu | File dữ liệu đầy đủ |
|---|---:|---|---|
| [addresses](tables/addresses.md) | 0 | tables/addresses.md | - |
| [addresses](tables/addresses.md) | 0 | tables/addresses.md | - |
| [banners](tables/banners.md) | 0 | tables/banners.md | - |
| [banners](tables/banners.md) | 0 | tables/banners.md | - |
| [cart_items](tables/cart_items.md) | 7 | tables/cart_items.md | data/cart_items.jsonl |
| [cart_items](tables/cart_items.md) | 7 | tables/cart_items.md | data/cart_items.jsonl |
| [carts](tables/carts.md) | 111 | tables/carts.md | data/carts.jsonl |
| [carts](tables/carts.md) | 111 | tables/carts.md | data/carts.jsonl |
| [categories](tables/categories.md) | 5 | tables/categories.md | data/categories.jsonl |
| [categories](tables/categories.md) | 5 | tables/categories.md | data/categories.jsonl |
| [coupon_categories](tables/coupon_categories.md) | 0 | tables/coupon_categories.md | - |
| [coupon_categories](tables/coupon_categories.md) | 0 | tables/coupon_categories.md | - |
| [coupon_products](tables/coupon_products.md) | 0 | tables/coupon_products.md | - |
| [coupon_products](tables/coupon_products.md) | 0 | tables/coupon_products.md | - |
| [coupon_usages](tables/coupon_usages.md) | 2 | tables/coupon_usages.md | data/coupon_usages.jsonl |
| [coupon_usages](tables/coupon_usages.md) | 2 | tables/coupon_usages.md | data/coupon_usages.jsonl |
| [coupons](tables/coupons.md) | 4 | tables/coupons.md | data/coupons.jsonl |
| [coupons](tables/coupons.md) | 4 | tables/coupons.md | data/coupons.jsonl |
| [newsletter_subscribers](tables/newsletter_subscribers.md) | 0 | tables/newsletter_subscribers.md | - |
| [newsletter_subscribers](tables/newsletter_subscribers.md) | 0 | tables/newsletter_subscribers.md | - |
| [order_items](tables/order_items.md) | 13 | tables/order_items.md | data/order_items.jsonl |
| [order_items](tables/order_items.md) | 13 | tables/order_items.md | data/order_items.jsonl |
| [order_status_histories](tables/order_status_histories.md) | 0 | tables/order_status_histories.md | - |
| [order_status_histories](tables/order_status_histories.md) | 0 | tables/order_status_histories.md | - |
| [orders](tables/orders.md) | 5 | tables/orders.md | data/orders.jsonl |
| [orders](tables/orders.md) | 5 | tables/orders.md | data/orders.jsonl |
| [payments](tables/payments.md) | 0 | tables/payments.md | - |
| [payments](tables/payments.md) | 0 | tables/payments.md | - |
| [post_categories](tables/post_categories.md) | 3 | tables/post_categories.md | data/post_categories.jsonl |
| [post_categories](tables/post_categories.md) | 3 | tables/post_categories.md | data/post_categories.jsonl |
| [posts](tables/posts.md) | 6 | tables/posts.md | data/posts.jsonl |
| [posts](tables/posts.md) | 6 | tables/posts.md | data/posts.jsonl |
| [product_images](tables/product_images.md) | 12 | tables/product_images.md | data/product_images.jsonl |
| [product_images](tables/product_images.md) | 12 | tables/product_images.md | data/product_images.jsonl |
| [product_variants](tables/product_variants.md) | 10 | tables/product_variants.md | data/product_variants.jsonl |
| [product_variants](tables/product_variants.md) | 10 | tables/product_variants.md | data/product_variants.jsonl |
| [products](tables/products.md) | 9 | tables/products.md | data/products.jsonl |
| [products](tables/products.md) | 9 | tables/products.md | data/products.jsonl |
| [promotion_products](tables/promotion_products.md) | 4 | tables/promotion_products.md | data/promotion_products.jsonl |
| [promotion_products](tables/promotion_products.md) | 4 | tables/promotion_products.md | data/promotion_products.jsonl |
| [promotions](tables/promotions.md) | 1 | tables/promotions.md | data/promotions.jsonl |
| [promotions](tables/promotions.md) | 1 | tables/promotions.md | data/promotions.jsonl |
| [reviews](tables/reviews.md) | 0 | tables/reviews.md | - |
| [reviews](tables/reviews.md) | 0 | tables/reviews.md | - |
| [search_terms](tables/search_terms.md) | 5 | tables/search_terms.md | data/search_terms.jsonl |
| [search_terms](tables/search_terms.md) | 5 | tables/search_terms.md | data/search_terms.jsonl |
| [settings](tables/settings.md) | 0 | tables/settings.md | - |
| [settings](tables/settings.md) | 0 | tables/settings.md | - |
| [shipments](tables/shipments.md) | 5 | tables/shipments.md | data/shipments.jsonl |
| [shipments](tables/shipments.md) | 5 | tables/shipments.md | data/shipments.jsonl |
| [stock_movements](tables/stock_movements.md) | 13 | tables/stock_movements.md | data/stock_movements.jsonl |
| [stock_movements](tables/stock_movements.md) | 13 | tables/stock_movements.md | data/stock_movements.jsonl |
| [stores](tables/stores.md) | 0 | tables/stores.md | - |
| [stores](tables/stores.md) | 0 | tables/stores.md | - |
| [testimonials](tables/testimonials.md) | 0 | tables/testimonials.md | - |
| [testimonials](tables/testimonials.md) | 0 | tables/testimonials.md | - |
| [users](tables/users.md) | 0 | tables/users.md | - |
| [users](tables/users.md) | 0 | tables/users.md | - |
| [wishlists](tables/wishlists.md) | 0 | tables/wishlists.md | - |
| [wishlists](tables/wishlists.md) | 0 | tables/wishlists.md | - |
