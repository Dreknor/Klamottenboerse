{{-- Rendert die Bausteine einer Seite. $bloecke: Liste der Bausteine, $k: SeitenKontext --}}
<div class="space-y-6">
    @foreach ($bloecke as $block)
        @includeIf('website.bloecke.'.$block['typ'], ['b' => $block, 'k' => $k])
    @endforeach
</div>
