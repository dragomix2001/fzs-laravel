<title>Крсна слава</title>
@extends('layouts.layout')
@section('page_heading','Крсна слава')
@section('section')
    <div>
        <form class="btn" method="GET" action="krsnaSlava/add">
            <input type="submit" class="btn btn-primary" value="Додавање">
        </form>
    </div>
    <br/>
    <div class="col-md-9">
        <div class="table-responsive">
            <table id="tabela" class="table">
                <thead>
                <th>
                    Назив
                </th>
                <th>
                    Датум
                </th>
                <th>
                    Акције
                </th>
                </thead>

                @foreach($krsnaSlava as $krsnaSlava)
                    <tr>
                        <td>{{$krsnaSlava->naziv}}</td>
                        <td>{{$krsnaSlava->datumSlave}}</td>
                        <td>
                            <div class="btn-group">
                                <form class="btn" action="krsnaSlava/{{$krsnaSlava->id}}/edit">
                                    <input type="submit" class="btn btn-primary" value="Измени">
                                </form>
                                <form onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке овог кандидата?');"
                                      class="btn" action="krsnaSlava/{{$krsnaSlava->id}}/delete">
                                    <input type="submit" class="btn btn-danger" value="Обриши">
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <br/>
    </div>

@endsection