<?php

namespace App\Models;

use App\Enums\ImportStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $supplier_id
 * @property string $external_import_id
 * @property ImportStatus $status
 * @property string|null $error
 * @property array $payload
 * @property int $total_offers
 * @property CarbonImmutable $sent_at
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Supplier $supplier
 */
class Import extends Model
{
    /** @use HasFactory<ImportFactory> */
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'external_import_id',
        'status',
        'error',
        'payload',
        'total_offers',
        'sent_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'payload' => 'array',
            'sent_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
