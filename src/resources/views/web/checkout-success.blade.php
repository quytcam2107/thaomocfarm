<x-layouts.app title="Đặt hàng thành công | Thảo Mộc Farm" seoDescription="Cảm ơn bạn đã đặt hàng tại Thảo Mộc Farm">
    <div class="container text-center py-12">
        <div class="success-icon" style="font-size: 5rem;">🎉</div>
        <h1 class="text-2xl font-bold mb-4">Đặt hàng thành công!</h1>
        <p class="mb-2">Cảm ơn bạn đã tin tưởng và mua sắm tại <strong>Thảo Mộc Farm</strong>.</p>
        <p class="mb-6">Mã đơn hàng của bạn là: <strong class="text-emerald-700">{{ $order->order_number }}</strong></p>
        
        <div class="bg-white p-6 rounded-lg shadow-sm max-w-md mx-auto mb-8 text-left">
            <h3 class="font-semibold mb-3">Thông tin giao hàng</h3>
            <p><strong>Người nhận:</strong> {{ $order->customer_name }}</p>
            <p><strong>Số điện thoại:</strong> {{ $order->customer_phone }}</p>
            <p><strong>Địa chỉ:</strong> {{ $order->address_snapshot['detail'] }}, {{ $order->address_snapshot['district'] }}, {{ $order->address_snapshot['province'] }}</p>
            <p><strong>Tổng thanh toán:</strong> <span class="text-orange-600 font-bold">{{ number_format($order->total) }}₫</span> (COD)</p>
        </div>

        <p class="mb-6 text-gray-600">Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận và giao hàng.</p>
        
        <div class="flex justify-center gap-4">
            <a href="{{ route('web.home') }}" class="btn btn--leaf">Tiếp tục mua sắm</a>
            {{-- <a href="{{ route('web.order-tracking.index') }}" class="btn btn--clay">Tra cứu đơn hàng</a> --}}
        </div>
    </div>
</x-layouts.app>