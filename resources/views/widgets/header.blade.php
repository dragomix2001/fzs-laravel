<nav class="bg-secondary-900 shadow-lg mb-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">
            <a class="text-lg font-bold text-white" href="/me">Laravel</a>
            <ul class="flex items-center gap-1">
                <li><a href="/me" class="px-3 py-2 text-sm font-medium text-secondary-300 hover:text-white rounded-md transition-colors">Home</a></li>
                <li x-data="{ open: false }" class="relative">
                    <a href="#" @click.prevent="open = !open" class="px-3 py-2 text-sm font-medium text-secondary-300 hover:text-white rounded-md inline-flex items-center transition-colors" role="button">
                        Components <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </a>
                    <ul x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                        <li><a href="/table" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Tables</a></li>
                        <li><a href="/button" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Buttons</a></li>
                        <li><a href="/badge" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Labels and badges</a></li>
                        <li><a href="/alert" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Alerts</a></li>
                        <li><a href="/breadcrumbs" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Breadcrumbs and Pagination</a></li>
                        <li><a href="/glyphicons" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Glyphicons</a></li>
                        <li><a href="/progressBars" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Progress Bars</a></li>
                        <li><a href="/jumbotron" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Jumbotron</a></li>
                        <li><a href="/panel" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Panels</a></li>
                        <li><a href="/form" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Forms</a></li>
                        <li><a href="/navbar" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Navigation Bar</a></li>
                        <li><a href="/popup" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Popovers and Tooltips</a></li>
                        <li><a href="/collapse" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">Collapse</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
