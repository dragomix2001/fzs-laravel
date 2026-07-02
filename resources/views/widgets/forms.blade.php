<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <form class="space-y-4">
            <div class="space-y-1">
                <label for="exampleInputEmail1" class="block text-sm font-medium text-secondary-700">Name</label>
                <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="exampleInputEmail1" placeholder="Enter name">
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"> <span class="text-sm text-secondary-700">Check me out</span>
            </label>
            <x-button variant="secondary-soft" size="md">Submit</x-button>
        </form>
    </div>
    <div>
        <div class="onoffswitch mb-4">
            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" checked>
            <label class="onoffswitch-label" for="myonoffswitch">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>
        <div class="onoffswitch2 mb-4">
            <input type="checkbox" name="onoffswitch2" class="onoffswitch-checkbox2" id="myonoffswitch2" checked>
            <label class="onoffswitch-label2" for="myonoffswitch2">
                <span class="onoffswitch-inner2"></span>
                <span class="onoffswitch-switch2"></span>
            </label>
        </div>
    </div>
</div>
<div class="mt-4">
    <p class="text-xs text-secondary-500 mb-2">Add <code class="bg-secondary-100 px-1 py-0.5 rounded text-xs">.form-search</code> to the form and <code class="bg-secondary-100 px-1 py-0.5 rounded text-xs">.search-query</code> to the <code class="bg-secondary-100 px-1 py-0.5 rounded text-xs">input</code> for an extra-rounded text input.</p>
    <form class="flex gap-2">
        <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Search...">
        <x-button variant="primary" size="md" type="submit" class="shrink-0">Search</x-button>
    </form>
</div>
