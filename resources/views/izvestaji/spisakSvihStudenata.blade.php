<div style="height: 70px;">
    <img src="{{$putanja}}/images/zaglavlje.png" alt="test alt attribute" width="250" height="65" border="0"/>
</div>
<hr>

<div>
    <h1 style="padding-bottom: 100px;">Списак студената</h1>
    <br/>
    <br/>

    @foreach($studijskiProgram as $program)
        <label style="padding-bottom: 10px;">{{$program->naziv}}</label>
        <br/>
        <br/>
        <table style="border: 1px solid black;">
            <thead>
            <tr>
                <th style="border: 1px solid black;">
                    <b>Име</b>
                </th>
                <th style="border: 1px solid black;">
                    <b>Презиме</b>
                </th>
                <th style="border: 1px solid black;">
                    <b>Број бодова</b>
                </th>
            </tr>
            </thead>
            @foreach($kandidat as $item)
                @if($item->program->id == $program->id)
                    <tr>
                        <td style="border: 1px solid black;">{{$item->imeKandidata}}</td>
                        <td style="border: 1px solid black;">{{$item->prezimeKandidata}}</td>
                        <td style="border: 1px solid black;">{{$item->brojBodovaTest}}</td>
                    </tr>
                @endif
            @endforeach
        </table>
        <br/>
        <br/>
        <br/>
    @endforeach
</div>

<br/>
<br/>
<br/>
<div>
    <table>
        <tr>
            <td></td>
            <td></td>
            <td>Одговорно лице</td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-bottom: 10px;"></td>
            <td style="border-bottom: 1px solid black;"></td>
        </tr>
    </table>
</div>






















