@extends('layouts.layout')

@section ('page_heading','Documentation')

@section('section')

<x-card>
    <x-slot:title>Panel</x-slot:title>
    <x-card>
        <x-slot:title>Default title</x-slot:title>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
    </x-card>
    <div class="mt-3 bg-secondary-50 rounded-lg p-4 text-xs font-mono text-secondary-600 overflow-x-auto">
        <div>@section ('inside_panel_title', 'Default title')</div>
        <div>@section ('inside_panel_body')</div>
        <div>&lt;p&gt;Lorem ipsum...&lt;/p&gt;</div>
        <div>@endsection</div>
        <div>@include('widgets.panel', array('header'=>true, 'as'=>'inside'))</div>
    </div>
</x-card>

<x-card>
    <x-slot:title>Collapsible</x-slot:title>
    @include('widgets.collapse', array('id'=>'2', 'header'=> 'This is a header', 'body'=>'Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo.'))
    <div class="mt-3 bg-secondary-50 rounded-lg p-4 text-xs font-mono text-secondary-600 overflow-x-auto">
        @include('widgets.collapse', array('id'=>'2', 'header'=> 'This is a header', 'body'=>'Nihil anim...'))
    </div>
</x-card>

<x-card>
    <x-slot:title>Button</x-slot:title>
    <div class="flex flex-wrap gap-2 mb-4">
        @include('widgets.button', array('value'=>'Info button', 'class'=>'info'))
        @include('widgets.button', array('class'=>'danger', 'size'=>'lg', 'value'=>'Large Button'))
        @include('widgets.button', array('class'=>'success btn-outline', 'value'=>'Primary'))
    </div>
    <div class="bg-secondary-50 rounded-lg p-4 text-xs font-mono text-secondary-600 overflow-x-auto">
        <div>@include('widgets.button', array('value'=>'Info button', 'class'=>'info'))</div>
        <div>@include('widgets.button', array('class'=>'danger', 'size'=>'lg', 'value'=>'Large Button'))</div>
        <div>@include('widgets.button', array('class'=>'success btn-outline', 'value'=>'Primary'))</div>
    </div>
</x-card>

<x-card>
    <x-slot:title>Alerts</x-slot:title>
    <div class="space-y-2 mb-4">
        @include('widgets.alert', array('class'=>'success', 'message'=> 'You have an alert', 'icon'=> 'user'))
        @include('widgets.alert', array('class'=>'info', 'dismissable'=>true, 'message'=> 'My message'))
    </div>
    <div class="bg-secondary-50 rounded-lg p-4 text-xs font-mono text-secondary-600 overflow-x-auto">
        <div>@include('widgets.alert', array('class'=>'success', 'message'=> 'You have an alert', 'icon'=> 'user'))</div>
        <div>@include('widgets.alert', array('class'=>'info', 'dismissable'=>true, 'message'=> 'My message'))</div>
    </div>
</x-card>

<x-card>
    <x-slot:title>Progressbars</x-slot:title>
    <div class="space-y-2 mb-4">
        @include('widgets.progress', array('class'=> 'success', 'value'=>'44'))
        @include('widgets.progress', array('animated'=> true, 'value'=>'72'))
        @include('widgets.progress', array('class'=> 'danger', 'value'=>'12', 'badge'=>true))
    </div>
    <div class="bg-secondary-50 rounded-lg p-4 text-xs font-mono text-secondary-600 overflow-x-auto">
        <div>@include('widgets.progress', array('class'=> 'success', 'value'=>'44'))</div>
        <div>@include('widgets.progress', array('animated'=> true, 'value'=>'72'))</div>
        <div>@include('widgets.progress', array('class'=> 'danger', 'value'=>'12', 'badge'=>true))</div>
    </div>
</x-card>

@stop
