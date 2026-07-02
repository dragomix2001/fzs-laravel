@extends('layouts.layout')
@section('page_heading','Form')

@section('section')
<div class="col-span-12">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <x-card>
            <x-slot:title>Form Controls</x-slot:title>
            <form class="space-y-4">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Text Input</label>
                    <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <p class="text-xs text-secondary-400">Example block-level help text here.</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Text Input with Placeholder</label>
                    <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Enter text">
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Static Control</label>
                    <p class="py-2 px-3 bg-secondary-50 rounded-lg text-sm text-secondary-600">email@example.com</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">File input</label>
                    <input type="file" class="block w-full text-sm text-secondary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Text area</label>
                    <textarea class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" rows="3"></textarea>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Checkboxes</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500" value="">
                            <span class="text-sm text-secondary-700">Checkbox 1</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500" value="">
                            <span class="text-sm text-secondary-700">Checkbox 2</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500" value="">
                            <span class="text-sm text-secondary-700">Checkbox 3</span>
                        </label>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Inline Checkboxes</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">1</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">2</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">3</span>
                        </label>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Radio Buttons</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="optionsRadios" value="option1" checked class="border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">Radio 1</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="optionsRadios" value="option2" class="border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">Radio 2</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="optionsRadios" value="option3" class="border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">Radio 3</span>
                        </label>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Inline Radio Buttons</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="optionsRadiosInline" value="option1" checked class="border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">1</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="optionsRadiosInline" value="option2" class="border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">2</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="optionsRadiosInline" value="option3" class="border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">3</span>
                        </label>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Selects</label>
                    <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Multiple Selects</label>
                    <select multiple class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-button variant="primary" size="md" type="submit">Submit Button</x-button>
                    <x-button variant="secondary-soft" size="md">Reset Button</x-button>
                </div>
            </form>
        </x-card>
    </div>
    <div class="space-y-6">
        <x-card>
            <x-slot:title>Disabled Form States</x-slot:title>
            <form>
                <fieldset disabled class="space-y-4 opacity-60">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Disabled input</label>
                        <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" type="text" placeholder="Disabled input" disabled>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Disabled select menu</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option>Disabled select</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500" disabled>
                        <span class="text-sm text-secondary-700">Disabled Checkbox</span>
                    </label>
                    <x-button variant="primary" size="md" type="submit" disabled>Disabled Button</x-button>
                </fieldset>
            </form>
        </x-card>

        <x-card>
            <x-slot:title>Form Validation</x-slot:title>
            <form class="space-y-4">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-success-700">Input with success</label>
                    <input type="text" class="block w-full rounded-lg border-success-400 shadow-sm focus:border-success-500 focus:ring-success-500 sm:text-sm" value="Valid input">
                    <p class="text-xs text-success-600"><i class="fas fa-check mr-1"></i> Looks good!</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-warning-700">Input with warning</label>
                    <input type="text" class="block w-full rounded-lg border-warning-400 shadow-sm focus:border-warning-500 focus:ring-warning-500 sm:text-sm" value="Warning value">
                    <p class="text-xs text-warning-600"><i class="fas fa-exclamation-triangle mr-1"></i> This value may not be correct.</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-danger-700">Input with error</label>
                    <input type="text" class="block w-full rounded-lg border-danger-400 shadow-sm focus:border-danger-500 focus:ring-danger-500 sm:text-sm" value="Error value">
                    <p class="text-xs text-danger-600"><i class="fas fa-times mr-1"></i> Please correct this error.</p>
                </div>
            </form>
        </x-card>

        <x-card>
            <x-slot:title>Input Groups</x-slot:title>
            <form class="space-y-4">
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-secondary-300 bg-secondary-50 text-secondary-500 text-sm">@</span>
                    <input type="text" class="block w-full rounded-none rounded-r-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Username">
                </div>
                <div class="flex">
                    <input type="text" class="block w-full rounded-l-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-secondary-300 bg-secondary-50 text-secondary-500 text-sm">.00</span>
                </div>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-secondary-300 bg-secondary-50 text-secondary-500 text-sm"><i class="fas fa-euro-sign"></i></span>
                    <input type="text" class="block w-full rounded-none rounded-r-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Font Awesome Icon">
                </div>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-secondary-300 bg-secondary-50 text-secondary-500 text-sm">$</span>
                    <input type="text" class="block w-full border-x-0 border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-secondary-300 bg-secondary-50 text-secondary-500 text-sm">.00</span>
                </div>
                <div class="flex">
                    <input type="text" class="block w-full rounded-l-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <x-button variant="secondary-soft" size="md" type="button" class="rounded-r-lg border border-l-0 border-secondary-300">
                        <i class="fas fa-search"></i>
                    </x-button>
                </div>
            </form>
        </x-card>
        <p class="text-xs text-secondary-400">For complete documentation, please visit <a href="https://tailwindcss.com/docs/forms" class="text-primary-600 hover:underline">Tailwind CSS Forms</a>.</p>
    </div>
</div>
</div>
@stop
