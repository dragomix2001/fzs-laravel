@extends('layouts.layout')
@section('page_heading','Увоз/Извоз података')
@section('section')

<div class="col-sm-12 col-lg-10">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Увоз података (Import)</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('import-export.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Excel фајл (.xlsx, .xls, .csv)</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Увоз</button>
                    </form>
                    <hr>
                    <h5>Очекивани формат колона:</h5>
                    <ul class="small">
                        <li>ime, prezime, email, jmbg</li>
                        <li>datum_rodjenja, telefon, adresa</li>
                        <li>tip_studija_id, studijski_program_id</li>
                        <li>skolska_godina_id, status_upisa_id</li>
                        <li>broj_indeksa</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Извоз података (Export)</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('import-export.export', ['format' => 'xlsx']) }}" class="list-group-item">
                            <i class="fa fa-file-excel"></i> Извоз свих кандидата (Excel)
                        </a>
                        <a href="{{ route('import-export.export-studenti') }}" class="list-group-item">
                            <i class="fa fa-users"></i> Извоз активних студената
                        </a>
                        <a href="{{ route('import-export.export-ispiti') }}" class="list-group-item">
                            <i class="fa fa-graduation-cap"></i> Извоз положених испита
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
