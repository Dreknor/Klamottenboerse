<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Verkäuferinfos</title>


</head>

<body>
<h2>Wichtige Infos für Verkäufer</h2>
<p>
    <h3>Annahme:</h3>

            Freitag,
                @php
                   echo date('d.m.Y', strtotime($Klamottenboerse->datum."-1 day"));
                @endphp
            von {{$Klamottenboerse->anlieferung_von}} bis {{$Klamottenboerse->anlieferung_bis}} Uhr im Lutherhaus der Friedenskirchgemeinde Radebeul.
            (Altkötzschenbroda 40)
<br><br><br>
</p>
<p>
    <h3>Abholung:</h3>
        SAMSTAG, {{$Klamottenboerse->datum->format('d.m.Y')}} von {{$Klamottenboerse->anbholung_von}} bis {{$Klamottenboerse->abholung_bis}} Uhr im Lutherhaus
        (Bei Nichtabholung müssen die Kisten unbeaufsichtigt im Luthersaal verbleiben) Die Barauszahlung ihres Verkaufserlöses erfolgt ausschließlich bei Abholung der Kisten.
</p>

<p>
    <br><br>
    <h3>Hinweise:</h3>
    <ul>
        <li>saisonabhängige Kinderbekleidung, Schuhe, Gummistiefel, Matschkleidung, Kinderwagen,
            Auto- und Fahrradsitze, Laufgitter, Kinderbetten, Spielzeug, Bücher, Fahrzeuge, Tragehilfen, ...</li>
        <li>max. {{$Klamottenboerse->maxTeile}} Teile pro Verkäufer (so können wir mehr Verkäufer zulassen)</li>
        <li>keine Erwachsenenkleidung (ausgenommen Umstandskleidung), keine Plüschtiere, nur neuwertige Schuhe und ACHTUNG! bei Schuhen nur maximal 4 Paar (natürlich mit Gummistiefeln)!</li>
        <li>Kleidungsstücke müssen außen gut sichtbar gekennzeichnet werden (bitte nicht innen ans
            Etikett kleben!). Das erleichtert uns das Ein- und Aussortieren und den Käufern das Suchen. (Kleiner Tipp: Malerkrepp hält gut und lässt sich gut beschriften)</li>
        <li>bitte nicht unter 0,50 € auspreisen und ausschließlich in 0,50 €-Schritten (erleichtert das
            Abkassieren und den Geldwechsel)</li>
        <li>die zu verkaufenden Artikel müssen in stapelbare, stabile Kisten verpackt und außen gut
            sichtbar mit der Verkäufernummer gekennzeichnet werden. (keine Plastiktüten, Reisetaschen, Wäschekörbe)</li>
        <li>Wir behalten uns vor, Artikel, die wir als schwer verkäuflich erachten, in den
            Verkäuferkisten zu belassen.</li>
        <li>Während der Verkaufszeit bemühen wir uns, auf ihre abgegebenen Artikel zu achten.
            Trotzdem kann es zu Diebstählen kommen. Wir bedauern dies sehr, können aber keine
            Haftung für fehlende Artikel übernehmen.</li>
    </ul>
</p>


<p><b><i>25% des Erlöses gehen an das ev. Kinderhaus Radebeul</i></b></p>

<p>Beschriftung des Etiketts folgendermaßen:
<table style="width: 80%; border: solid black 1px; text-align: center;">
    <tr>
        <td width="33%">Verkäufernummer</td>
        <td width="33%"></td>
        <td width="33%">laufende ArtikelNr.</td>
    </tr>
    <tr>
        <td width="33%"></td>
        <td width="33%"><br><br></td>
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
        <td width="33%"><br><br></td>
        <td width="33%"></td>
    </tr>
</table>




</p>
<p>
    <br><br>
    Rückfragen per Mail an <b>anmeldung@klamottenboerse.de</b>
</p>


</body>
</html>