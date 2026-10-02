<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use App\Services\HomeService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller mỏng cho Cẩm nang (blog): danh sách / lọc chuyên mục / chi tiết bài viết.
 * Toàn bộ logic dữ liệu nằm trong BlogService. Route đặt TRƯỚC catch-all /{slug}.
 */
class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService,
        // HomeService: nguồn gợi ý sản phẩm (đã cache group 'home' + đã áp giá flash sale)
        private readonly HomeService $homeService,
    ) {
    }

    /** GET /cam-nang — danh sách bài viết (phân trang). */
    public function index(): View
    {
        $meta = $this->blogService->indexSeoMeta(null);

        return view('web.blog.index', [
            'seo' => $meta,
            'posts' => $this->blogService->paginate(null),
            'categories' => $this->blogService->categories(),
            'currentCat' => null,
        ]);
    }

    /** GET /cam-nang/category/{slug} — danh sách theo chuyên mục. */
    public function category(string $slug): View
    {
        $meta = $this->blogService->indexSeoMeta($slug);

        // Chuyên mục không tồn tại/không active => 404
        if ($meta['heading'] === 'Cẩm nang vào bếp & pha trà') {
            throw new NotFoundHttpException();
        }

        return view('web.blog.index', [
            'seo' => $meta,
            'posts' => $this->blogService->paginate($slug),
            'categories' => $this->blogService->categories(),
            'currentCat' => $slug,
        ]);
    }

    /** GET /cam-nang/{slug} — chi tiết bài viết (tăng view_count atomic). */
    public function show(string $slug): View
    {
        $data = $this->blogService->show($slug);
        
        if ($data === null) {
            throw new NotFoundHttpException();
        }

        // Tăng view_count trực tiếp bằng SQL — không đụng model cache
        $data['post']->incrementViews();

        // Widget "Có thể bạn sẽ thích": 3 sản phẩm bán chạy (array thuần từ cache 'home').
        // buildListingItem đã gồm: url PDP, image, price (giá sau giảm), old_price (giá gốc), discount_percent.
        // Rỗng/DB lỗi => mảng rỗng => widget tự ẩn (rule component).
        $data['suggestedProducts'] = array_slice($this->homeService->bestSellers(), 0, 3);
        
        return view('web.blog.show', $data);
    }
}