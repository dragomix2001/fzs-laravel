<nav class="bg-{{ $class === 'inverse' ? 'secondary-900' : 'secondary-100' }} shadow-sm">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center gap-4">
                <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-secondary-500 hover:text-secondary-700 hover:bg-secondary-200 lg:hidden" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fas fa-bars"></i>
                </x-button>
                <a class="text-lg font-bold {{ $class === 'inverse' ? 'text-white' : 'text-secondary-900' }}" href="#">Brand</a>
            </div>
            <div class="hidden lg:flex lg:items-center lg:gap-6" id="navbarNav">
                <ul class="flex items-center gap-1">
                    <li><a href="#" class="px-3 py-2 text-sm font-medium {{ $class === 'inverse' ? 'text-white bg-white/10' : 'text-primary-600 bg-primary-50' }} rounded-md">Link <span class="sr-only">(current)</span></a></li>
                    <li><a href="#" class="px-3 py-2 text-sm font-medium {{ $class === 'inverse' ? 'text-secondary-300 hover:text-white' : 'text-secondary-700 hover:text-secondary-900 hover:bg-secondary-50' }} rounded-md transition-colors">Link</a></li>
                    <li x-data="{ open: false }" class="relative">
                        <a href="#" @click.prevent="open = !open" class="px-3 py-2 text-sm font-medium {{ $class === 'inverse' ? 'text-secondary-300 hover:text-white' : 'text-secondary-700 hover:text-secondary-900' }} rounded-md inline-flex items-center transition-colors" role="button">
                            Dropdown <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </a>
                        <ul x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Action</a></li>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Another action</a></li>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Something else here</a></li>
                            <li><hr class="my-1 border-secondary-200"></li>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Separated link</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="flex items-center gap-2 ml-4">
                    <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Search">
                    <x-button variant="primary" size="xs" type="submit">Submit</button>
                </form>
                <ul class="flex items-center gap-1 ml-4">
                    <li><a href="#" class="px-3 py-2 text-sm font-medium {{ $class === 'inverse' ? 'text-secondary-300 hover:text-white' : 'text-secondary-700 hover:text-secondary-900' }} rounded-md transition-colors">Link</a></li>
                    <li x-data="{ open: false }" class="relative">
                        <a href="#" @click.prevent="open = !open" class="px-3 py-2 text-sm font-medium {{ $class === 'inverse' ? 'text-secondary-300 hover:text-white' : 'text-secondary-700 hover:text-secondary-900' }} rounded-md inline-flex items-center transition-colors" role="button">
                            Dropdown <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </a>
                        <ul x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Action</a></li>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Another action</a></li>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Something else here</a></li>
                            <li><hr class="my-1 border-secondary-200"></li>
                            <li><a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Separated link</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
