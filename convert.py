import re

with open('/home/dragomix/fzs-laravel/resources/views/layouts/layout.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Remove Bootstrap CDN
content = re.sub(r'<link href="https://cdn.jsdelivr.net/npm/bootstrap@[^>]+>\n?', '', content)
content = re.sub(r'<script src="https://cdn.jsdelivr.net/npm/bootstrap@[^>]+></script>\n?', '', content)

# General Bootstrap to Tailwind utility replacements
content = content.replace('d-flex', 'flex')
content = content.replace('align-items-center', 'items-center')
content = content.replace('me-3', 'mr-3')
content = content.replace('me-2', 'mr-2')

# Replace top header dropdown with Alpine.js
dropdown_old = """                    @if(!Auth::guest())
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle flex items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle mr-2"></i>
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt mr-2"></i>Одјава</a></li>
                            </ul>
                        </div>
                    @endif"""

dropdown_new = """                    @if(!Auth::guest())
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="px-3 py-1.5 border border-gray-400 text-gray-700 hover:bg-gray-100 rounded text-sm flex items-center transition-colors" type="button" aria-expanded="false">
                                <i class="fas fa-user-circle mr-2"></i>
                                {{ Auth::user()->name }}
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt mr-2"></i>Одјава</a>
                            </div>
                        </div>
                    @endif"""

content = content.replace(dropdown_old, dropdown_new)

# If dropdown_old didn't match perfectly, let's just do a regex replace for the dropdown div
if "x-data" not in content:
    content = re.sub(
        r'<div class="dropdown">.*?</div>\s*@endif',
        dropdown_new.strip(),
        content,
        flags=re.DOTALL
    )

# Fix buttons
content = content.replace('btn btn-outline-secondary btn-sm', 'px-3 py-1.5 border border-gray-400 text-gray-700 hover:bg-gray-100 rounded text-sm transition-colors')

# Sidebar Toggle using Alpine instead of Vanilla JS for body wrapper?
# Wait, the prompt says "Convert Bootstrap navbar/sidebar to Tailwind + Alpine.js"
# The original vanilla JS works but we can put x-data="{ sidebarOpen: false }" on wrapper.
wrapper_old = '<div class="wrapper">'
wrapper_new = '<div class="wrapper" x-data="{ sidebarOpen: false }">'
content = content.replace(wrapper_old, wrapper_new)

# Sidebar overlay
overlay_old = '<div class="sidebar-overlay" id="sidebarOverlay"></div>'
overlay_new = '<div class="sidebar-overlay" id="sidebarOverlay" :class="{ \'show\': sidebarOpen }" @click="sidebarOpen = false"></div>'
content = content.replace(overlay_old, overlay_new)

# Sidebar
sidebar_old = '<aside class="sidebar" id="sidebar">'
sidebar_new = '<aside class="sidebar" id="sidebar" :class="{ \'show\': sidebarOpen }">'
content = content.replace(sidebar_old, sidebar_new)

# Mobile toggle button
toggle_old = '<button class="mobile-toggle mr-3" id="sidebarToggle">'
toggle_new = '<button class="mobile-toggle mr-3" id="sidebarToggle" @click="sidebarOpen = !sidebarOpen">'
content = content.replace(toggle_old, toggle_new)

# Delete vanilla JS for sidebar
js_old = """        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });"""
content = content.replace(js_old, "")

with open('/home/dragomix/fzs-laravel/resources/views/layouts/layout.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done")