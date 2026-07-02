@extends('layouts.layout')
@section('page_heading','Buttons')
@section('section')
	<div class="col-span-12 lg:col-span-6">
		<x-card>
			<x-slot:title>Default Buttons</x-slot:title>
			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Normal Buttons</h4>

			<div class="flex flex-wrap gap-2 mb-4">
				@include('widgets.button', array('value'=>'Primary', 'class'=>'primary'))
				@include('widgets.button', array('value'=>'Success', 'class'=>'success'))
				@include('widgets.button', array('value'=>'Warning', 'class'=>'warning'))
				@include('widgets.button', array('value'=>'Danger', 'class'=>'danger'))
			</div>
			
			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Disabled Buttons</h4>
			<div class="flex flex-wrap gap-2 mb-4">
				@include('widgets.button', array('value'=>'Default', 'disabled'=>true))
				@include('widgets.button', array('class'=>'primary', 'value'=>'Primary', 'disabled'=>true))
				@include('widgets.button', array('class'=>'success', 'value'=>'Success', 'disabled'=>true))
				@include('widgets.button', array('class'=>'warning', 'value'=>'Warning', 'disabled'=>true))
				@include('widgets.button', array('class'=>'danger', 'value'=>'Danger', 'disabled'=>true))
			</div>
			
			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Button Sizes</h4>
			<div class="flex flex-wrap items-center gap-2 mb-4">
				@include('widgets.button', array('class'=>'primary', 'size'=>'lg', 'value'=>'Large Button'))
				@include('widgets.button', array('class'=>'primary', 'value'=>'Default'))
				@include('widgets.button', array('class'=>'primary', 'size'=>'sm', 'value'=>'Small'))
				@include('widgets.button', array('class'=>'primary', 'size'=>'xs', 'value'=>'Extra Small'))
			</div>
			<div class="mt-3">
				@include('widgets.button', array('class'=>'primary', 'size'=>'lg btn-block', 'value'=>'Block level Button'))
			</div>
		</x-card>
	</div>

	<div class="col-span-12 lg:col-span-6 space-y-4">
		<x-card>
			<x-slot:title>Circle Buttons</x-slot:title>
			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Normal Circle Buttons</h4>
			<div class="flex flex-wrap gap-3 mb-6">
				<button type="button" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-secondary-200 hover:bg-secondary-300 text-secondary-700 transition-colors"><i class="fas fa-check"></i></button>
				<button type="button" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-primary-600 hover:bg-primary-500 text-white transition-colors"><i class="fas fa-list"></i></button>
				<button type="button" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-success-600 hover:bg-success-500 text-white transition-colors"><i class="fas fa-link"></i></button>
				<button type="button" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-cyan-600 hover:bg-cyan-500 text-white transition-colors"><i class="fas fa-check"></i></button>
				<button type="button" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-warning-500 hover:bg-warning-400 text-white transition-colors"><i class="fas fa-times"></i></button>
				<button type="button" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-danger-600 hover:bg-danger-500 text-white transition-colors"><i class="fas fa-heart"></i></button>
			</div>

			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Large Circle Buttons</h4>
			<div class="flex flex-wrap gap-3">
				<button type="button" class="w-14 h-14 rounded-full inline-flex items-center justify-center bg-secondary-200 hover:bg-secondary-300 text-secondary-700 transition-colors"><i class="fas fa-check text-lg"></i></button>
				<button type="button" class="w-14 h-14 rounded-full inline-flex items-center justify-center bg-primary-600 hover:bg-primary-500 text-white transition-colors"><i class="fas fa-list text-lg"></i></button>
				<button type="button" class="w-14 h-14 rounded-full inline-flex items-center justify-center bg-success-600 hover:bg-success-500 text-white transition-colors"><i class="fas fa-link text-lg"></i></button>
				<button type="button" class="w-14 h-14 rounded-full inline-flex items-center justify-center bg-cyan-600 hover:bg-cyan-500 text-white transition-colors"><i class="fas fa-check text-lg"></i></button>
				<button type="button" class="w-14 h-14 rounded-full inline-flex items-center justify-center bg-warning-500 hover:bg-warning-400 text-white transition-colors"><i class="fas fa-times text-lg"></i></button>
				<button type="button" class="w-14 h-14 rounded-full inline-flex items-center justify-center bg-danger-600 hover:bg-danger-500 text-white transition-colors"><i class="fas fa-heart text-lg"></i></button>
			</div>
		</x-card>

		<x-card>
			<x-slot:title>Outline buttons with smooth transition</x-slot:title>
			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Outline Buttons</h4>
			<div class="flex flex-wrap gap-2 mb-4">
				@include('widgets.button', array('value'=>'Default', 'class'=>'default btn-outline'))
				@include('widgets.button', array('class'=>'primary btn-outline', 'value'=>'Primary'))
				@include('widgets.button', array('class'=>'success btn-outline', 'value'=>'Success'))
				@include('widgets.button', array('class'=>'warning btn-outline', 'value'=>'Warning'))
				@include('widgets.button', array('class'=>'danger btn-outline', 'value'=>'Danger'))
			</div>
			<h4 class="text-sm font-semibold text-secondary-900 mb-3">Outline Button Sizes</h4>
			<div class="flex flex-wrap items-center gap-2 mb-4">
				@include('widgets.button', array('class'=>'primary btn-outline', 'size'=>'lg', 'value'=>'Large Button'))
				@include('widgets.button', array('class'=>'primary btn-outline', 'value'=>'Default'))
				@include('widgets.button', array('class'=>'primary btn-outline', 'size'=>'sm', 'value'=>'Small'))
				@include('widgets.button', array('class'=>'primary btn-outline', 'size'=>'xs', 'value'=>'Extra Small'))
			</div>
			<div class="mt-3">
				@include('widgets.button', array('class'=>'primary btn-o', 'size'=>'lg btn-block', 'value'=>'Block level Button'))
			</div>
		</x-card>
	</div>
@stop
