<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaStk extends Model
{
    protected $table = 'mpesa_stks';

    protected $fillable = [
        'merchant_request_id',
        'checkout_request_id',
        'response_code',
        'response_description',
        'customer_message',
        'response',
        'user_id',
        'order_id',
        'attendee_id',
        'result_code',
        'result_desc',
        'amount',
        'mpesa_receipt_number',
        'transaction_date',
        'phone_number',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'response' => 'array',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }

    // Status helpers
    public function isSuccessful()
    {
        return $this->result_code == '0';
    }

    public function isPending()
    {
        return $this->result_code == '1';
    }

    public function isFailed()
    {
        return $this->result_code == '2';
    }

    // Get status label
    public function getStatusLabelAttribute()
    {
        if ($this->isSuccessful()) {
            return 'Success';
        } elseif ($this->isPending()) {
            return 'Pending';
        } else {
            return 'Failed';
        }
    }

    // Get status color class
    public function getStatusColorAttribute()
    {
        if ($this->isSuccessful()) {
            return 'green';
        } elseif ($this->isPending()) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    // Scope for successful transactions
    public function scopeSuccessful($query)
    {
        return $query->where('result_code', '0');
    }

    // Scope for pending transactions
    public function scopePending($query)
    {
        return $query->where('result_code', '1');
    }

    // Scope for failed transactions
    public function scopeFailed($query)
    {
        return $query->where('result_code', '2');
    }

    // Get formatted amount
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    /**
     * Update from callback data - FIXED VERSION
     * Properly extracts ALL metadata from Safaricom
     */
    public function updateFromCallback($callbackData)
    {
        $updateData = [
            'result_code' => (string) ($callbackData['ResultCode'] ?? '1'),
            'result_desc' => $callbackData['ResultDesc'] ?? 'Unknown',
            'response' => array_merge($callbackData, ['processed_at' => now()->toDateTimeString()]),
        ];

        // ✅ Check if successful
        if (isset($callbackData['ResultCode']) && $callbackData['ResultCode'] == 0) {
            // Look for CallbackMetadata
            if (isset($callbackData['CallbackMetadata']) && isset($callbackData['CallbackMetadata']['Item'])) {
                $metadata = $callbackData['CallbackMetadata']['Item'];
                
                \Log::info('Processing metadata', ['metadata' => $metadata]);
                
                foreach ($metadata as $item) {
                    $name = $item['Name'] ?? '';
                    $value = $item['Value'] ?? null;
                    
                    switch ($name) {
                        case 'MpesaReceiptNumber':
                            $updateData['mpesa_receipt_number'] = $value;
                            \Log::info('✅ Receipt number found', ['receipt' => $value]);
                            break;
                        case 'TransactionDate':
                            $updateData['transaction_date'] = (string) $value;
                            \Log::info('✅ Transaction date found', ['date' => $value]);
                            break;
                        case 'Amount':
                            $updateData['amount'] = $value;
                            \Log::info('✅ Amount found', ['amount' => $value]);
                            break;
                        case 'PhoneNumber':
                            $updateData['phone_number'] = (string) $value;
                            \Log::info('✅ Phone number found', ['phone' => $value]);
                            break;
                        case 'Balance':
                            // Skip balance field
                            break;
                        default:
                            \Log::info('⚠️ Unknown metadata field', ['name' => $name, 'value' => $value]);
                            break;
                    }
                }
            } else {
                \Log::warning('⚠️ CallbackMetadata not found in callback data', [
                    'callback_data' => $callbackData
                ]);
            }
        }

        // Update the record
        $this->update($updateData);
        return $this;
    }
}