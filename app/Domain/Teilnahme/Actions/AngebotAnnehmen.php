<?php

namespace App\Domain\Teilnahme\Actions;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Teilnahme\Links;
use App\Enums\TeilnahmeStatus;
use App\Models\Teilnahme;
use DomainException;

class AngebotAnnehmen
{
    public function __invoke(Teilnahme $teilnahme): void
    {
        if ($teilnahme->status === TeilnahmeStatus::Zugeteilt) {
            return;
        }

        if ($teilnahme->status !== TeilnahmeStatus::Angeboten || $teilnahme->angebot_bis?->isPast()) {
            throw new DomainException('Dieses Angebot ist leider nicht mehr gültig.');
        }

        $teilnahme->update([
            'status' => TeilnahmeStatus::Zugeteilt,
            'zugeteilt_at' => now(),
            'angebot_bis' => null,
            'wartelisten_position' => null,
        ]);

        activity()->performedOn($teilnahme)->withProperties(['nummer' => $teilnahme->nummer])->log('Angebot angenommen');

        if ($teilnahme->person) {
            Postausgang::einplanen($teilnahme->person, 'nummer_zugeteilt', $teilnahme->boerse, [
                'absage_link' => Links::absage($teilnahme),
            ]);
        }
    }
}
