<?php

namespace Laravel\Spark;

use App\UserSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class LocalInvoice extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(Spark::userModel(), 'user_id');
    }

    public function subscription() {
        return $this->belongsTo('App\Subscription');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($invoice) {
            $mask = Config::get('app.receipt_mask');
            $invoice->receipt_id = $mask.sprintf("%'.08d\n", $invoice['id']);
            $invoice->save();
        });
    }
}
