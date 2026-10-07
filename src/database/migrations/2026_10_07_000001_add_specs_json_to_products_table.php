<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Thêm cột specs_json (JSON, nullable) cho bảng products — nguồn dữ liệu cho
 * khối "Thông số sản phẩm" trong tab "Chi tiết sản phẩm" của PDP.
 *
 * Key cố định (11 trường theo yêu cầu):
 *   thuong_hieu, xuat_xu, han_su_dung, thanh_phan, huong_vi, bao_quan,
 *   cong_dung, cach_dung, san_xuat, phan_phoi, lien_he
 *
 * Convention dự án: idempotent (kiểm tra hasColumn trước) để migrate lại không throw.
 * SQLite dev không có CAST(... AS JSON) -> phần回填 dữ liệu dùng binding tham số
 * và bọc try/catch (NULL là chấp nhận được, view tự ẩn khối thiếu data).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'specs_json')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->json('specs_json')->nullable()->after('seo');
            });
        }

        //回填 thông số cho 9 sản phẩm demo đang tồn tại (theo SKU — an toàn hơn id)
        $specs = self::seedSpecs();

        foreach ($specs as $sku => $payload) {
            $exists = DB::table('products')->where('sku', $sku)->exists();
            if (!$exists) {
                continue; // môi trường khác dataset -> bỏ qua, không fail migrate
            }

            // Chỉ ghi khi chưa có dữ liệu (không đè tay admin đã nhập)
            DB::table('products')
                ->where('sku', $sku)
                ->whereNull('specs_json')
                ->update(['specs_json' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'specs_json')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('specs_json');
            });
        }
    }

    /**
     * Dữ liệu mẫu khớp nội dung description thật từng sản phẩm
     * (nguồn: ai-database/data/products.jsonl).
     *
     * @return array<string, array<string, string>> sku => payload
     */
    private static function seedSpecs(): array
    {
        return [
            'MXF-TT-001' => [
                'thuong_hieu' => 'Thảo Mộc Farm',
                'xuat_xu' => 'Quản Bạ – Hà Giang',
                'han_su_dung' => '3–5 năm với củ nguyên; bột đã tán dùng trong 3 tháng',
                'thanh_phan' => '100% củ tam thất bắc (Panax notoginseng) phơi khô tự nhiên, không tẩm sulphur',
                'huong_vi' => 'Đắng nhẹ nơi đầu lưỡi, chuyển ngọt hậu sâu',
                'bao_quan' => 'Để củ nguyên trong túi zip kín kèm gói hút ẩm, nơi khô ráo tránh nắng',
                'cong_dung' => 'Bổ huyết sinh tân, hỗ trợ tuần hoàn và bảo vệ tim mạch, tăng sức đề kháng',
                'cach_dung' => 'Tán bột 2–4g/ngày chia 2 lần; ngâm mật ong; hầm gà ác – chân giò 5–10g',
                'san_xuat' => 'Hợp tác xã Nông sản Dược liệu Quản Bạ',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Farm (Thảo Mộc Xanh)',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocfarm.vn',
            ],
            'MXF-TT-002' => [
                'thuong_hieu' => 'Thảo Mộc Xanh',
                'xuat_xu' => 'Quản Bạ – Hà Giang (độ cao 1.200–1.800m)',
                'han_su_dung' => '12 tháng kể từ ngày đóng gói ghi trên bao bì',
                'thanh_phan' => '100% nụ tam thất bắc loại 1 sấy lạnh dưới 40°C, không tẩm lưu huỳnh',
                'huong_vi' => 'Vị thanh mát, ngọt hậu hơi đắng nhẹ, hương thơm dịu',
                'bao_quan' => 'Nơi khô ráo thoáng mát, tránh ánh nắng; khóa kín zip sau mỗi lần dùng, không để tủ lạnh',
                'cong_dung' => 'An thần, hỗ trợ ngủ ngon; hỗ trợ ổn định huyết áp; thanh nhiệt, hỗ trợ tuần hoàn máu',
                'cach_dung' => 'Tráng 5–7 nụ với nước 90°C rồi đổ bỏ; châm 300ml nước sôi ủ 5–7 phút, lặp lại 2–3 lần nước',
                'san_xuat' => 'Hợp tác xã Nông sản Dược liệu Quản Bạ',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Xanh',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocxanh.vn',
            ],
            'MXF-TGB-001' => [
                'thuong_hieu' => 'Thảo Mộc Xanh',
                'xuat_xu' => 'Sơn La – Điện Biên (Tây Bắc)',
                'han_su_dung' => '6 tháng kể từ ngày sản xuất in trên bao bì',
                'thanh_phan' => 'Thịt trâu tươi, muối hạt, mắc khén, hạt dổi, ớt rừng',
                'huong_vi' => 'Đậm đà mặn ngọt, thơm nồng mắc khén – hạt dổi, khói bếp nhẹ',
                'bao_quan' => 'Bảo quản ngăn đá tủ lạnh (-18°C); đã mở hút chân không dùng hết trong 7 ngày',
                'cong_dung' => 'Cung cấp đạm và năng lượng cho bữa ăn hằng ngày, món nhậu đặc sản vùng cao',
                'cach_dung' => 'Hấp 10–15 phút hoặc nướng than, quay lò vi sóng 2 phút rồi xé mỏng chấm chẳm chéo',
                'san_xuat' => 'Cơ sở Thực phẩm Tây Bắc Mộc Châu',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Xanh',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocxanh.vn',
            ],
            'MXF-TD-001' => [
                'thuong_hieu' => 'Thảo Mộc Farm',
                'xuat_xu' => 'Tân Cương (Nhã Mỹ), Trung Quốc',
                'han_su_dung' => '12 tháng kể từ ngày đóng gói',
                'thanh_phan' => '100% táo đỏ sấy khô, không tẩm đường, không xử lý lưu huỳnh',
                'huong_vi' => 'Ngọt thanh, thịt dẻo bùi',
                'bao_quan' => 'Nơi khô ráo, thoáng mát, tránh ánh nắng; buộc kín túi sau khi mở',
                'cong_dung' => 'Hỗ trợ bổ khí huyết, an thần, tốt cho tiêu hóa; dùng nấu chè, hầm trà',
                'cach_dung' => 'Ăn trực tiếp 3–5 quả/ngày; hãm trà cùng kỷ tử hoặc hầm chè, nấu cháo',
                'san_xuat' => 'Nhà máy Sơ chế Nông sản Nhã Mỹ',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Farm (Thảo Mộc Xanh)',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocfarm.vn',
            ],
            'MXF-KTD-001' => [
                'thuong_hieu' => 'Thảo Mộc Farm',
                'xuat_xu' => 'Ninh Hạ, Cam Túc (Trung Quốc)',
                'han_su_dung' => '18 tháng kể từ ngày sản xuất',
                'thanh_phan' => '100% kỷ tử đỏ (fructus lycii) sấy khô tự nhiên, không tẩm đường',
                'huong_vi' => 'Ngọt dịu, chua thanh nhẹ',
                'bao_quan' => 'Để nơi khô ráo, tránh nắng; đậy kín sau mỗi lần dùng',
                'cong_dung' => 'Hỗ trợ sáng mắt, bổ gan thận, tăng cường đề kháng và chống oxy hóa',
                'cach_dung' => 'Ngày 10–15g ăn trực tiếp, hãm trà hoặc nấu chè, hầm canh',
                'san_xuat' => 'HTX Nông sản Hữu cơ Ninh Hạ',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Farm (Thảo Mộc Xanh)',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocfarm.vn',
            ],
            'MXF-HHS-001' => [
                'thuong_hieu' => 'Thảo Mộc Xanh',
                'xuat_xu' => 'Lâm Đồng (Đà Lạt)',
                'han_su_dung' => '12 tháng kể từ ngày đóng gói',
                'thanh_phan' => '100% cánh hoa hồng sấy khô nhiệt độ thấp, nguyên bông, không chất tạo màu',
                'huong_vi' => 'Hương thơm dịu đặc trưng, vị thanh nhẹ hơi chát',
                'bao_quan' => 'Tránh ánh nắng trực tiếp, để nơi khô mát, buộc kín túi zip',
                'cong_dung' => 'Hỗ trợ đẹp da, thư giãn, giảm căng thẳng, hỗ trợ điều hòa nội tiết tố nữ',
                'cach_dung' => 'Hãm 3–5 bông với 250ml nước 80–90°C trong 5 phút, thêm mật ong khi trà còn ấm',
                'san_xuat' => 'Vườn Hoa & Sơ chế Đà Lạt GreenFarm',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Xanh',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocxanh.vn',
            ],
            'MXF-THA-001' => [
                'thuong_hieu' => 'Thảo Mộc Farm',
                'xuat_xu' => 'Sa Pa – Lào Cai',
                'han_su_dung' => '24 tháng kể từ ngày sản xuất',
                'thanh_phan' => '100% nụ hoa atiso sấy khô nguyên nụ, không lẫn cành lá',
                'huong_vi' => 'Vị đắng nhẹ, ngọt hậu, mùi thảo mộc dịu',
                'bao_quan' => 'Nơi khô ráo thoáng mát, tránh ẩm mốc và ánh nắng',
                'cong_dung' => 'Hỗ trợ mát gan, lợi mật, thanh nhiệt, hỗ trợ tiêu hóa',
                'cach_dung' => 'Hãm 1–2 nụ với 500ml nước sôi 10 phút hoặc nấu nước atiso uống trong ngày',
                'san_xuat' => 'Xí nghiệp Dược liệu Sa Pa',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Farm (Thảo Mộc Xanh)',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocfarm.vn',
            ],
            'MXF-LX-001' => [
                'thuong_hieu' => 'Thảo Mộc Xanh',
                'xuat_xu' => 'Hải Dương (vùng dâu Thanh Hà)',
                'han_su_dung' => '12 tháng kể từ ngày đóng gói',
                'thanh_phan' => '100% dâu tằm sấy khô nguyên quả, không tẩm đường',
                'huong_vi' => 'Ngọt thanh, hơi chua dịu, thơm mùi dâu',
                'bao_quan' => 'Bảo quản nơi khô ráo, tránh nắng; đậy kín sau khi mở bao bì',
                'cong_dung' => 'Hỗ trợ bổ huyết, sáng mắt, nhuận tràng, hỗ trợ ngủ ngon',
                'cach_dung' => 'Ăn trực tiếp 20–30g/ngày, hãm trà hoặc ngâm rượu, nấu siro',
                'san_xuat' => 'Hợp tác xã Nông sản Thanh Hà',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Xanh',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocxanh.vn',
            ],
            'MXF-HC-001' => [
                'thuong_hieu' => 'Thảo Mộc Farm',
                'xuat_xu' => 'Hưng Yên (cúc chi tiến vua)',
                'han_su_dung' => '12 tháng kể từ ngày đóng gói',
                'thanh_phan' => '100% bông cúc chi sấy lạnh nguyên bông, không xông lưu huỳnh, không tạo màu',
                'huong_vi' => 'Hương thơm ngát dịu nhẹ, vị thanh ngọt hậu',
                'bao_quan' => 'Buộc chặt túi zip, nơi khô ráo, tránh ánh nắng trực tiếp',
                'cong_dung' => 'Thanh nhiệt giải độc, an thần dễ ngủ, hỗ trợ sáng mắt và hạ huyết áp nhẹ',
                'cach_dung' => 'Hãm 5–7 bông với 300ml nước 85–90°C trong 3–5 phút, không dùng nước sôi 100°C',
                'san_xuat' => 'HTX Cúc Chi Tiến Vua Hưng Yên',
                'phan_phoi' => 'Công ty TNHH Thảo Mộc Farm (Thảo Mộc Xanh)',
                'lien_he' => 'Hotline 0352.806.324 – hotro@thaomocfarm.vn',
            ],
        ];
    }
};