@extends('layouts.layout')
@section('page_heading','Grid System')
@section('section')
<div class="col-span-12 space-y-4">

<x-card>
	<x-slot:title>Twelve</x-slot:title>
	<div class="text-center"><h4 class="text-lg font-semibold">Twelve</h4></div>
</x-card>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
	<x-card><x-slot:title>Six</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Six</h4></div></x-card>
	<x-card><x-slot:title>Six</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Six</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
	<x-card><x-slot:title>Four</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Four</h4></div></x-card>
	<x-card><x-slot:title>Four</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Four</h4></div></x-card>
	<x-card><x-slot:title>Four</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Four</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
	<x-card><x-slot:title>Three</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Three</h4></div></x-card>
	<x-card><x-slot:title>Three</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Three</h4></div></x-card>
	<x-card><x-slot:title>Three</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Three</h4></div></x-card>
	<x-card><x-slot:title>Three</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Three</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
	<x-card class="md:col-span-5"><x-slot:title>Five</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Five</h4></div></x-card>
	<x-card class="md:col-span-7"><x-slot:title>Seven</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Seven</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
	<x-card class="md:col-span-4"><x-slot:title>Four</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Four</h4></div></x-card>
	<x-card class="md:col-span-8"><x-slot:title>Eight</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Eight</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
	<x-card class="md:col-span-3"><x-slot:title>Three</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Three</h4></div></x-card>
	<x-card class="md:col-span-9"><x-slot:title>Nine</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Nine</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
	<x-card class="md:col-span-2"><x-slot:title>Two</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Two</h4></div></x-card>
	<x-card class="md:col-span-10"><x-slot:title>Ten</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Ten</h4></div></x-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
	<x-card class="md:col-span-1"><x-slot:title>One</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">One</h4></div></x-card>
	<x-card class="md:col-span-11"><x-slot:title>Eleven</x-slot:title><div class="text-center"><h4 class="text-lg font-semibold">Eleven</h4></div></x-card>
</div>

</div>
@stop
