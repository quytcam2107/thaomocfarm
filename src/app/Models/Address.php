<?php


namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(AddressFactory::class)]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id', 'full_name', 'phone', 'province',
        'district', 'ward', 'detail', 'is_default',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Chuỗi địa chỉ đầy đủ dùng cho snapshot đơn hàng */
    public function full(): string
    {
        return trim("{$this->detail}, {$this->ward}, {$this->district}, {$this->province}");
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }
}