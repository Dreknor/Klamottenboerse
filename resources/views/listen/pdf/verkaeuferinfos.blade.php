<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Verkäuferinfos</title>


</head>

<body>
<h3>Wichtige Infos für Verkäufer</h3>
<p>
    <h4>Annahme:</h4>

            Freitag,
                @php
                   echo date('d.m.Y', strtotime($Klamottenboerse->datum."-1 day"));
                @endphp
            von
                @php
                    echo date('G.i', strtotime($Klamottenboerse->anlieferung_von));
                @endphp
                bis
                @php
                    echo date('G.i', strtotime($Klamottenboerse->anlieferung_bis));
               @endphp
 Uhr im Lutherhaus der Friedenskirchgemeinde Radebeul.
            (Altkötzschenbroda 40)
</p>
<br><br>
<p>
    <h4>Abholung:</h4>
        SAMSTAG, {{$Klamottenboerse->datum->format('d.m.Y')}} von
                @php
                   echo date('G.i', strtotime($Klamottenboerse->abholung_von));
                @endphp
                    bis
                @php
                    echo date('G.i', strtotime($Klamottenboerse->abholung_bis));
                @endphp
 Uhr im Lutherhaus.<br>
        Bei Nichtabholung müssen die Kisten unbeaufsichtigt im Luthersaal verbleiben!<br> Die Barauszahlung ihres Verkaufserlöses erfolgt ausschließlich bei Abholung der Kisten.
</p>
<br>
<p>
    <h4>Hinweise:</h4>
    <ul>
        <li>Verkauft werden saisonabhängige Kinderbekleidung <b>NEU: ab Größe 74/80</b>, Schuhe, Gummistiefel, Matschkleidung, Kinderwagen,
           Kindersitze, Laufgitter, Kinderbetten, Spielzeug, Bücher, Fahrzeuge, Tragehilfen, ...</li>
        <li>Es werden maximal {{$Klamottenboerse->maxTeile}} Teile pro Verkäufer zugelassen
             (so können wir mehr Verkäufern die Chance bieten, mitzumachen)</li>
        <li>Bitte packen Sie KEINE Erwachsenenkleidung oder Plüschtiere ein,
            außerdem nur neuwertige Schuhe und ACHTUNG! bei Schuhen nur maximal 4 Paar (natürlich mit Gummistiefeln)!</li>
        <li>Die Kleidungsstücke müssen außen gut sichtbar gekennzeichnet werden.
            Bei Hängeware, wie z.B. Jacken, Regensachen und Kleidern, bitte unbedingt ZWEI Kleber,
            einen innen und einen außen, da diese leider viel zu schnell beim Durchschauen abgehen.
            Das erleichtert uns das Ein- und Aussortieren und den Käufern das Suchen.
            (Kleiner Tipp: Malerkrepp hält gut und lässt sich gut beschriften)</li>
        <li>Bitte nicht unter 0,50 € auspreisen und ausschließlich in 0,50 €-Schritten (erleichtert das
            Abkassieren und den Geldwechsel)</li>
        <li style="color: crimson"><b>Die zu verkaufenden Artikel müssen in einer stapelbaren, stabilen Kiste verpackt und außen gut
                sichtbar mit der Verkäufernummer gekennzeichnet werden. (keine Plastiktüten, Reisetaschen, Wäschekörbe, Windelkartons)</b></li>
        <li>Wir behalten uns vor, Artikel, die wir als schwer verkäuflich erachten, in den
            Verkäuferkisten zu belassen.</li>
        <li>Während der Verkaufszeit bemühen wir uns, auf ihre abgegebenen Artikel zu achten.
            Trotzdem kann es zu Diebstählen kommen. Wir bedauern dies sehr, können aber keine
            Haftung für fehlende Artikel übernehmen.</li>
    </ul>
</p>

<p><b><i>25% des Erlöses gehen an das ev. Kinderhaus Radebeul</i></b></p>

<p>Die Beschriftung des Etiketts erfolgt
    @if(isset($VKnummer))
        für Ihre <b>Verkäufernummer ({{$VKnummer->vknummer}})</b>
    @endif folgendermaßen:
<table style="width: 80%; border: solid black 1px; text-align: center;">
    <tr>
        <td width="33%">
            @if(isset($VKnummer))
                {{$VKnummer->vknummer}}
            @else
                Verk.-Nummer
            @endif
        </td>
        <td width="33%"></td>
        <td width="33%">laufende ArtikelNr.</td>
    </tr>
    <tr>
        <td width="33%"></td>
        <td width="33%"><br></td>
        <td width="33%"></td>
    </tr>
    <tr>
        <td width="33%"></td>
        <td width="33%">Preis</td>
        <td width="33%"></td>
    </tr>
    <tr>
        <td width="33%"></td>
        <td width="33%">Grösse</td>
        <td width="33%"></td>
    </tr>
    <tr>
        <td width="33%"></td>
        <td width="33%"><br></td>
        <td width="33%"></td>
    </tr>
</table>
</p>
<p>
    Rückfragen bitte per Mail an <b>anmeldung@klamottenboerse.de</b> oder in dringenderen Fällen unter der Nummer 0176/26953673
</p>


</body>
</html>