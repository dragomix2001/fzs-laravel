@extends('layouts.layout')
@section('page_heading','Tables')

@section('section')
<div class="col-span-12">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
	<div>
		<x-card>
			<x-slot:title>Regular Table</x-slot:title>
			@include('widgets.table', array('class'=>''))
		</x-card>
	</div>
	<div>
		<x-card>
			<x-slot:title>Bordered Table</x-slot:title>
			<div class="border border-secondary-200 rounded-lg overflow-hidden">
				@include('widgets.table', array('class'=>''))
			</div>
		</x-card>
	</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
	<div>
		<x-card>
			<x-slot:title>Striped Table</x-slot:title>
			@include('widgets.table', array('class'=>''))
		</x-card>
	</div>
	<div>
		<x-card>
			<x-slot:title>Hover Table</x-slot:title>
			@include('widgets.table', array('class'=>''))
		</x-card>
	</div>
	<div>
		<x-card>
			<x-slot:title>Condensed Table</x-slot:title>
			@include('widgets.table', array('class'=>''))
		</x-card>
	</div>
</div>
<div class="mt-4">
	<x-card>
		<x-slot:title>Coloured Table</x-slot:title>
		<table class="min-w-full divide-y divide-secondary-200">
			<thead class="bg-secondary-50">
				<tr>
					<th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Name</th>
					<th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Email</th>
					<th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Address</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-secondary-200">
				<tr class="bg-success-50 hover:bg-success-100">
					<td class="px-3 py-2 text-sm text-success-800">John</td>
					<td class="px-3 py-2 text-sm text-success-800">john@gmail.com</td>
					<td class="px-3 py-2 text-sm text-success-800">London, UK</td>
				</tr>
				<tr class="hover:bg-secondary-50">
					<td class="px-3 py-2 text-sm text-secondary-700">Wayne</td>
					<td class="px-3 py-2 text-sm text-secondary-700">wayne@gmail.com</td>
					<td class="px-3 py-2 text-sm text-secondary-700">Manchester, UK</td>
				</tr>
				<tr class="bg-cyan-50 hover:bg-cyan-100">
					<td class="px-3 py-2 text-sm text-cyan-800">Andy</td>
					<td class="px-3 py-2 text-sm text-cyan-800">andy@gmail.com</td>
					<td class="px-3 py-2 text-sm text-cyan-800">Merseyside, UK</td>
				</tr>
				<tr class="hover:bg-secondary-50">
					<td class="px-3 py-2 text-sm text-secondary-700">Danny</td>
					<td class="px-3 py-2 text-sm text-secondary-700">danny@gmail.com</td>
					<td class="px-3 py-2 text-sm text-secondary-700">Middlesborough, UK</td>
				</tr>
				<tr class="bg-warning-50 hover:bg-warning-100">
					<td class="px-3 py-2 text-sm text-warning-800">Frank</td>
					<td class="px-3 py-2 text-sm text-warning-800">frank@gmail.com</td>
					<td class="px-3 py-2 text-sm text-warning-800">Southampton, UK</td>
				</tr>
				<tr class="hover:bg-secondary-50">
					<td class="px-3 py-2 text-sm text-secondary-700">Scott</td>
					<td class="px-3 py-2 text-sm text-secondary-700">scott@gmail.com</td>
					<td class="px-3 py-2 text-sm text-secondary-700">Newcastle, UK</td>
				</tr>
				<tr class="bg-danger-50 hover:bg-danger-100">
					<td class="px-3 py-2 text-sm text-danger-800">Rickie</td>
					<td class="px-3 py-2 text-sm text-danger-800">rickie@gmail.com</td>
					<td class="px-3 py-2 text-sm text-danger-800">Burnley, UK</td>
				</tr>
			</tbody>
		</table>
	</x-card>
</div>
</div>
@stop
