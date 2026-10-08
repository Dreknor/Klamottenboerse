{{-- Reputations-Badge eines Interessenten. Erwartet $interessent. --}}
@php($repStatus = $interessent->reputationStatus())
@if ($repStatus === 'gesperrt')
    <span class="badge badge-danger" title="{{ $interessent->reputationPunkte() }} Punkte – keine automatische Nummernvergabe">
        nur manuelle Vergabe
    </span>
@elseif ($repStatus === 'warnung')
    <span class="badge badge-warning" title="{{ $interessent->reputationPunkte() }} Punkte">
        {{ $interessent->reputationPunkte() }} Pkt.
    </span>
@endif
