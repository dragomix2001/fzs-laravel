<div style="height: 70px;">
    <img src="{{$putanja}}/images/zaglavlje.png" alt="test alt attribute" width="250" height="65" border="0"/>
</div>
<hr>

<div>
    <h1 style="padding-bottom: 100px;">Списак студената на смеру {{$program->naziv}}</h1>
    <br/>
    <br/>
    @foreach($godina as $godina)
        <h1 style="padding-bottom: 100px;">{{$godina->naziv}} година</h1>
        @foreach($uslov as $test)
            @if($test->studijskiProgram_id == $program->id && $test->godinaStudija_id == $godina->id)

                <label style="padding-bottom: 10px;"></label>
                <br/>
                <br/>
                <table style="border: 1px solid black;">
                    <thead>
                    <tr>
                        <th style="border: 1px solid black;">Р.бр.</th>
                        <th style="border: 1px solid black;"><b>Број индекса</b>
                        </th>
                        <th style="border: 1px solid black;"><b>Име</b>
                        </th>
                        <th style="border: 1px solid black;"><b>Презиме</b>
                        </th>
                        <th style="border: 1px solid black;"><b>Број бодова</b>
                        </th>
                    </tr>
                    </thead>
                    <?php $a = 0; $b = 0; ?>
                    @foreach($kandidat as $index => $item)
                        @if($item->godinaStudija_id == $godina->id)
                            <?php $a++; ?>
                            <tr>
                                <td style="border: 1px solid black;">{{$a}}</td>
                                <td style="border: 1px solid black;">{{$item->brojIndeksa}}</td>
                                <td style="border: 1px solid black;">{{$item->imeKandidata}}</td>
                                <td style="border: 1px solid black;">{{$item->prezimeKandidata}}</td>
                                <td style="border: 1px solid black;">{{$item->ukupniBrojBodova}}</td>
                            </tr>
                        @endif

                    @endforeach
                </table>
                <br/>
                <br/>
                <br/>

            @endif
                @endforeach

</div>
@endforeach


<br/>
<br/>
<br/>
<div>
    <table>
        <tr>
            <td></td>
            <td></td>
            <td>Председник комисије</td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-bottom: 10px;"></td>
            <td style="border-bottom: 1px solid black;"></td>
        </tr>
    </table>
</div>

