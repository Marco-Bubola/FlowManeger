<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankSyncTransaction extends Model
{
    protected $fillable = ['user_id', 'connection_account_id', 'provider_tx_id', 'target_type', 'target_id', 'linked_existing'];

    protected $casts = ['linked_existing' => 'boolean'];
}
