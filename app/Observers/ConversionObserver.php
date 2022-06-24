<?php

namespace App\Observers;

use App\Models\Conversion;
use Vinkla\Hashids\Facades\Hashids;

class ConversionObserver
{
    public function created(Conversion $conversion)
    {
        $conversion->hashed_id = Hashids::connection('conversion')->encode($conversion->id);
        $conversion->saveQuietly();
    }

    public function updated(Conversion $conversion)
    {
        //
    }

    public function deleted(Conversion $conversion)
    {
        //
    }

    public function restored(Conversion $conversion)
    {
        //
    }

    public function forceDeleted(Conversion $conversion)
    {
        //
    }
}
