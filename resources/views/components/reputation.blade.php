{{-- Hinweis zur Reputation eines Verkäufers – nur sichtbar, wenn es etwas zu beachten gibt. --}}
@props(['person'])
@php [$text, $farbe] = \App\Domain\Reputation\Reputation::anzeige($person); @endphp
@if ($text !== '')
    <a href="{{ route('admin.personen.show', $person) }}#vermerke" class="no-underline" title="Reputation: Vermerke ansehen">
        <x-ui.abzeichen :farbe="$farbe">⚑ {{ $text }}</x-ui.abzeichen>
    </a>
@endif
