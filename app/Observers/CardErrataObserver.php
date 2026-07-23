<?php

namespace App\Observers;

use App\Models\CardErrata;
use Str;

class CardErrataObserver
{
    public function creating(CardErrata $cardErrata): void
    {
        $cardErrata->slug = Str::slug($cardErrata->faction.'-'.$cardErrata->card_name);
    }

    public function created(CardErrata $cardErrata): void
    {
        $cardErrata->updateQuietly([
            'slug' => $cardErrata->id.'-'.Str::slug($cardErrata->faction.'-'.$cardErrata->card_name),
        ]);
    }

    public function updating(CardErrata $cardErrata): void
    {
        $cardErrata->slug = $cardErrata->id.'-'.Str::slug($cardErrata->faction.'-'.$cardErrata->card_name);
    }

    public function deleted(CardErrata $cardErrata): void
    {
        $cardErrata->loadMissing('approval');
        $cardErrata->approval?->delete();
    }
}
