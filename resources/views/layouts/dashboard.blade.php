@extends('layouts.plane')

@section('body')
<div id="wrapper">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-secondary-200 fixed top-0 left-0 right-0 z-50" role="navigation">
        <div class="flex items-center justify-between h-16 px-4">
            <div class="flex items-center gap-3">
                <button type="button" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 focus:outline-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <img src="{{"/"}}images/logo_fzs.png" alt="FZS" class="h-8 w-auto" loading="lazy">
                <a class="text-lg font-bold text-secondary-900 hidden sm:block" href="{{ url('') }}">Fakultet za sport</a>
            </div>
            
            <!-- Top right nav -->
            <ul class="flex items-center gap-1">
                <!-- Messages -->
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 rounded-lg text-secondary-500 hover:bg-secondary-100 relative">
                        <i class="fas fa-envelope"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                        <div class="px-4 py-2 border-b border-secondary-100 text-xs font-medium text-secondary-500 uppercase tracking-wider">Messages</div>
                        <a href="#" class="block px-4 py-3 hover:bg-secondary-50">
                            <div class="flex items-start gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 shrink-0"><i class="fas fa-user text-xs"></i></div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-secondary-900">John Smith</p>
                                    <p class="text-xs text-secondary-500 truncate">Lorem ipsum dolor sit amet...</p>
                                </div>
                            </div>
                        </a>
                        <div class="border-t border-secondary-100">
                            <a href="#" class="block px-4 py-2 text-sm text-center text-primary-600 hover:bg-secondary-50 font-medium">Read All Messages</a>
                        </div>
                    </div>
                </li>
                <!-- Tasks -->
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 rounded-lg text-secondary-500 hover:bg-secondary-100 relative">
                        <i class="fas fa-tasks"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                        <div class="px-4 py-2 border-b border-secondary-100 text-xs font-medium text-secondary-500 uppercase tracking-wider">Tasks</div>
                        <a href="#" class="block px-4 py-3 hover:bg-secondary-50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Task 1</span>
                                <span class="text-xs text-secondary-400">40%</span>
                            </div>
                            <div class="mt-1 w-full bg-secondary-200 rounded-full h-1.5">
                                <div class="bg-success-500 h-1.5 rounded-full" style="width: 40%"></div>
                            </div>
                        </a>
                        <a href="#" class="block px-4 py-3 hover:bg-secondary-50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Task 2</span>
                                <span class="text-xs text-secondary-400">20%</span>
                            </div>
                            <div class="mt-1 w-full bg-secondary-200 rounded-full h-1.5">
                                <div class="bg-cyan-500 h-1.5 rounded-full" style="width: 20%"></div>
                            </div>
                        </a>
                        <a href="#" class="block px-4 py-3 hover:bg-secondary-50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Task 3</span>
                                <span class="text-xs text-secondary-400">60%</span>
                            </div>
                            <div class="mt-1 w-full bg-secondary-200 rounded-full h-1.5">
                                <div class="bg-warning-500 h-1.5 rounded-full" style="width: 60%"></div>
                            </div>
                        </a>
                        <a href="#" class="block px-4 py-3 hover:bg-secondary-50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Task 4</span>
                                <span class="text-xs text-secondary-400">80%</span>
                            </div>
                            <div class="mt-1 w-full bg-secondary-200 rounded-full h-1.5">
                                <div class="bg-danger-500 h-1.5 rounded-full" style="width: 80%"></div>
                            </div>
                        </a>
                        <div class="border-t border-secondary-100">
                            <a href="#" class="block px-4 py-2 text-sm text-center text-primary-600 hover:bg-secondary-50 font-medium">See All Tasks</a>
                        </div>
                    </div>
                </li>
                <!-- Alerts -->
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 rounded-lg text-secondary-500 hover:bg-secondary-100 relative">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                        <div class="px-4 py-2 border-b border-secondary-100 text-xs font-medium text-secondary-500 uppercase tracking-wider">Alerts</div>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-secondary-50">
                            <i class="fas fa-comment text-primary-500 w-4 text-center"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-secondary-900">New Comment</p>
                                <p class="text-xs text-secondary-400">4 minutes ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-secondary-50">
                            <i class="fas fa-twitter text-cyan-500 w-4 text-center"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-secondary-900">3 New Followers</p>
                                <p class="text-xs text-secondary-400">12 minutes ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-secondary-50">
                            <i class="fas fa-envelope text-success-500 w-4 text-center"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-secondary-900">Message Sent</p>
                                <p class="text-xs text-secondary-400">4 minutes ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-secondary-50">
                            <i class="fas fa-tasks text-warning-500 w-4 text-center"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-secondary-900">New Task</p>
                                <p class="text-xs text-secondary-400">4 minutes ago</p>
                            </div>
                        </a>
                        <div class="border-t border-secondary-100">
                            <a href="#" class="block px-4 py-2 text-sm text-center text-primary-600 hover:bg-secondary-50 font-medium">See All Alerts</a>
                        </div>
                    </div>
                </li>
                <!-- User -->
                <li x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 rounded-lg text-secondary-500 hover:bg-secondary-100 relative">
                        <i class="fas fa-user"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50"><i class="fas fa-user w-4 text-center"></i> User Profile</a>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50"><i class="fas fa-cog w-4 text-center"></i> Settings</a>
                        <div class="border-t border-secondary-100"></div>
                        <a href="{{ url('login') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-danger-600 hover:bg-secondary-50"><i class="fas fa-sign-out-alt w-4 text-center"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-secondary-200 overflow-y-auto z-40 hidden lg:block" id="sidebar">
        <!-- Search -->
        <div class="p-3 border-b border-secondary-100">
            <div class="relative">
                <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm pl-9" placeholder="Search...">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400"></i>
            </div>
        </div>
        <ul class="py-2 space-y-1">
            <!-- Studenti -->
            <li x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-user-graduate text-secondary-500 w-5 text-center"></i>
                    <span class="flex-1 text-left">Studenti</span>
                    <i class="fas fa-chevron-down text-xs text-secondary-400 transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <ul x-show="open" class="py-1 bg-secondary-50/50">
                    <li>
                        <a href="{{ url('panels') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*panels') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Dodavanje</a>
                    </li>
                    <li>
                        <a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*buttons') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Pregled</a>
                    </li>
                </ul>
            </li>
            <!-- Šifarnici -->
            <li x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-book text-secondary-500 w-5 text-center"></i>
                    <span class="flex-1 text-left">Šifarnici</span>
                    <i class="fas fa-chevron-down text-xs text-secondary-400 transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <ul x-show="open" class="py-1 bg-secondary-50/50">
                    <li><a href="{{ url('panels') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Tip studija</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Studijski program</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Godina studija</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Sportovi</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Sportsko angažovanje</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Status studiranja</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Predmet</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Srednje škole i fakulteti</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Krsna slava</a></li>
                </ul>
            </li>
            <!-- Dashboard -->
            <li>
                <a href="{{ url('') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors {{ Request::is('/') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">
                    <i class="fas fa-chart-pie text-secondary-500 w-5 text-center"></i> Dashboard
                </a>
            </li>
            <!-- Novi moduli -->
            <li x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-calendar text-secondary-500 w-5 text-center"></i>
                    <span class="flex-1 text-left">Нови модули</span>
                    <i class="fas fa-chevron-down text-xs text-secondary-400 transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <ul x-show="open" class="py-1 bg-secondary-50/50">
                    <li><a href="{{ route('prisustvo.index') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Присуство</a></li>
                    <li><a href="{{ route('aktivnost.index') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Активности</a></li>
                    <li><a href="{{ route('raspored.index') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Распоред</a></li>
                    <li><a href="{{ route('obavestenja.index') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Обавештења</a></li>
                    <li><a href="{{ route('dashboard.index') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Аналитика</a></li>
                </ul>
            </li>
            <!-- Charts -->
            <li>
                <a href="{{ url('charts') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors {{ Request::is('*charts') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">
                    <i class="fas fa-chart-bar text-secondary-500 w-5 text-center"></i> Charts
                </a>
            </li>
            <!-- Tables -->
            <li>
                <a href="{{ url('tables') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors {{ Request::is('*tables') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">
                    <i class="fas fa-table text-secondary-500 w-5 text-center"></i> Tables
                </a>
            </li>
            <!-- Forms -->
            <li>
                <a href="{{ url('forms') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors {{ Request::is('*forms') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">
                    <i class="fas fa-edit text-secondary-500 w-5 text-center"></i> Forms
                </a>
            </li>
            <!-- UI Elements -->
            <li x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-wrench text-secondary-500 w-5 text-center"></i>
                    <span class="flex-1 text-left">UI Elements</span>
                    <i class="fas fa-chevron-down text-xs text-secondary-400 transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <ul x-show="open" class="py-1 bg-secondary-50/50">
                    <li><a href="{{ url('panels') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*panels') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Panels and Collapsibles</a></li>
                    <li><a href="{{ url('buttons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*buttons') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Buttons</a></li>
                    <li><a href="{{ url('notifications') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*notifications') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Alerts</a></li>
                    <li><a href="{{ url('typography') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*typography') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Typography</a></li>
                    <li><a href="{{ url('icons') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*icons') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Icons</a></li>
                    <li><a href="{{ url('grid') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*grid') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Grid</a></li>
                </ul>
            </li>
            <!-- Sample Pages -->
            <li x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors">
                    <i class="fas fa-file text-secondary-500 w-5 text-center"></i>
                    <span class="flex-1 text-left">Sample Pages</span>
                    <i class="fas fa-chevron-down text-xs text-secondary-400 transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <ul x-show="open" class="py-1 bg-secondary-50/50">
                    <li><a href="{{ url('blank') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100 {{ Request::is('*blank') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">Blank Page</a></li>
                    <li><a href="{{ url('login') }}" class="block px-4 py-2 pl-12 text-sm text-secondary-600 hover:bg-secondary-100">Login Page</a></li>
                </ul>
            </li>
            <!-- Documentation -->
            <li>
                <a href="{{ url('documentation') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-secondary-700 hover:bg-secondary-50 transition-colors {{ Request::is('*documentation') ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' : '' }}">
                    <i class="fas fa-file-alt text-secondary-500 w-5 text-center"></i> Documentation
                </a>
            </li>
        </ul>
    </div>

    <!-- Page Content -->
    <div id="page-wrapper" class="lg:ml-64 pt-16">
        <div class="px-4 py-6">
            <h1 class="text-2xl font-bold text-secondary-900 mb-6">@yield('page_heading')</h1>
            <div class="grid grid-cols-12 gap-4">
                @yield('section')
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('block');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('z-50');
        });
    }
});
</script>
@stop
