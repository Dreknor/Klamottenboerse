<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 15mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #000; text-align: center; }
    .seite { page-break-after: always; }
    .seite:last-child { page-break-after: auto; }
    .nummer { font-size: 150pt; font-weight: bold; line-height: 1; margin-top: 10mm; }
    .name { font-size: 22pt; margin-top: 4mm; }
    .kiste { font-size: 18pt; margin-top: 6mm; }
    .code img { height: 40mm; width: 40mm; margin-top: 6mm; }
    .hinweis { font-size: 10pt; color: #444; margin-top: 6mm; }
</style>
</head>
<body>
@for ($i = 1; $i <= $anzahl; $i++)
    <div class="seite">
        <div class="nummer">{{ $teilnahme->nummer }}</div>
        <div class="name">{{ $teilnahme->person?->name }}</div>
        <div class="kiste">Kiste {{ $i }} von ____</div>
        <div class="code"><img src="{{ $qr }}" alt=""></div>
        <div class="hinweis">{{ $teilnahme->boerse->titel }} · bitte gut sichtbar außen an der Kiste befestigen</div>
    </div>
@endfor
</body>
</html>
