<section class="news reveal" aria-labelledby="newsTitle">
    <div class="container news__in">
        <div>
            <h2 id="newsTitle">Nhận ưu đãi & mùa vụ sớm nhất</h2>
            <p>Mỗi tháng 1 email: mùa vụ Tây Bắc, công thức vào bếp và mã giảm riêng.</p>
        </div>
        <form class="news__form" id="newsForm" action="{{ url('/newsletter') }}" method="POST">
            @csrf
            <input type="email" required placeholder="Email của bạn" aria-label="Email nhận ưu đãi">
            <button class="btn btn--clay" type="submit">Đăng ký</button>
        </form>
    </div>
</section>