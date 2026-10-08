<x-layouts.admin titel="Vermerk erfassen">
    <x-ui.kopf titel="Vermerk erfassen" :unter="$person ? 'für '.$person->name : 'Kiste nicht gebracht, Termin verpasst, defekte Ware …'" />
    <x-ui.karte class="max-w-2xl">
        @include('admin.vermerke._formular')
    </x-ui.karte>
</x-layouts.admin>
