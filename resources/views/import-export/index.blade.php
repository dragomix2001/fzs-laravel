@extends('layouts.layout')
@section('page_heading','Увоз/Извоз података')
@section('section')

<div class="col-span-12 lg:col-span-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card>
            <x-slot:title>Увоз података (Import)</x-slot:title>
            <form method="POST" action="{{ route('import-export.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-1 mb-4">
                    <label class="block text-sm font-medium text-secondary-700">Excel фајл (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                </div>
                <x-button variant="primary" size="md" type="submit">
                    <i class="fas fa-upload mr-2"></i> Увоз
                </x-button>
            </form>
            <hr class="my-4 border-secondary-200">
            <h5 class="text-sm font-semibold text-secondary-900 mb-2">Очекивани формат колона:</h5>
            <ul class="text-xs text-secondary-600 space-y-1">
                <li class="flex items-center gap-1"><i class="fas fa-arrow-right text-primary-500 text-xxs"></i> ime, prezime, email, jmbg</li>
                <li class="flex items-center gap-1"><i class="fas fa-arrow-right text-primary-500 text-xxs"></i> datum_rodjenja, telefon, adresa</li>
                <li class="flex items-center gap-1"><i class="fas fa-arrow-right text-primary-500 text-xxs"></i> tip_studija_id, studijski_program_id</li>
                <li class="flex items-center gap-1"><i class="fas fa-arrow-right text-primary-500 text-xxs"></i> skolska_godina_id, status_upisa_id</li>
                <li class="flex items-center gap-1"><i class="fas fa-arrow-right text-primary-500 text-xxs"></i> broj_indeksa</li>
            </ul>
        </x-card>
        
        <x-card>
            <x-slot:title>Извоз података (Export)</x-slot:title>
            <div class="space-y-2">
                <a href="{{ route('import-export.export', ['format' => 'xlsx']) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-secondary-200 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-file-excel text-xl text-success-600"></i>
                    <span class="text-sm text-secondary-900">Извоз свих кандидата (Excel)</span>
                    <i class="fas fa-chevron-right ml-auto text-secondary-400"></i>
                </a>
                <a href="{{ route('import-export.export-studenti') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-secondary-200 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-users text-xl text-primary-600"></i>
                    <span class="text-sm text-secondary-900">Извоз активних студената</span>
                    <i class="fas fa-chevron-right ml-auto text-secondary-400"></i>
                </a>
                <a href="{{ route('import-export.export-ispiti') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-secondary-200 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-graduation-cap text-xl text-cyan-600"></i>
                    <span class="text-sm text-secondary-900">Извоз положених испита</span>
                    <i class="fas fa-chevron-right ml-auto text-secondary-400"></i>
                </a>
            </div>
        </x-card>
    </div>
</div>
@endsection
