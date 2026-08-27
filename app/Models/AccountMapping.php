<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'chart_of_account_id',
    ];

    /**
     * Relationship to ChartOfAccount.
     */
    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Defined system default mappings.
     */
    public static function defaultMappings(): array
    {
        return [
            'rental_revenue' => [
                'name' => 'Pendapatan Sewa Kamar',
                'description' => 'Akun pendapatan sewa kamar kos saat tagihan terbayar',
                'default_code' => '4-1000',
            ],
            'deposit_liability' => [
                'name' => 'Utang Deposit Tenant',
                'description' => 'Akun kewajiban/titipan deposit uang jaminan milik tenant',
                'default_code' => '2-1000',
            ],
            'damage_claim_revenue' => [
                'name' => 'Pendapatan Klaim Kerusakan',
                'description' => 'Akun pendapatan atas potongan deposit untuk kerusakan saat check out',
                'default_code' => '4-3000',
            ],
            'default_cash' => [
                'name' => 'Kas / Bank Default',
                'description' => 'Akun kas/bank penerima pembayaran jika metode pembayaran belum dihubungkan ke akun COA',
                'default_code' => '1-1000',
            ],
            'bank_admin_fee' => [
                'name' => 'Beban Administrasi Bank',
                'description' => 'Akun beban untuk pencatatan biaya admin saat melakukan transfer dana antar kas/bank',
                'default_code' => '5-7000',
            ],
        ];
    }

    /**
     * Seed or get missing default mappings.
     */
    public static function seedDefaults(): void
    {
        $defaults = self::defaultMappings();

        foreach ($defaults as $key => $config) {
            $mapping = self::where('key', $key)->first();
            if (!$mapping) {
                $coa = ChartOfAccount::where('code', $config['default_code'])->first();
                self::create([
                    'key' => $key,
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'chart_of_account_id' => $coa?->id,
                ]);
            }
        }
    }

    /**
     * Get chart_of_account_id for a given mapping key with fallback.
     */
    public static function getAccountId(string $key): ?int
    {
        $mapping = self::where('key', $key)->first();

        if ($mapping && $mapping->chart_of_account_id) {
            return $mapping->chart_of_account_id;
        }

        // Fallback to default code if mapping or COA relation missing
        $defaults = self::defaultMappings();
        if (isset($defaults[$key])) {
            $defaultCode = $defaults[$key]['default_code'];
            $coa = ChartOfAccount::where('code', $defaultCode)->first();
            if ($coa) {
                // Optionally associate mapping if record exists without chart_of_account_id
                if ($mapping && !$mapping->chart_of_account_id) {
                    $mapping->update(['chart_of_account_id' => $coa->id]);
                }
                return $coa->id;
            }
        }

        return null;
    }
}
