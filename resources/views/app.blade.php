<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Laravel</title>

	<link href="/css/app.css" rel="stylesheet">
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
</head>
<body>
	<nav x-data="{ navOpen: false }" class="bg-white shadow-sm border-b border-secondary-200">
		<div class="max-w-7xl mx-auto px-4">
			<div class="flex items-center justify-between h-14">
				<div class="flex items-center gap-4">
					<button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-secondary-500 hover:bg-secondary-100 lg:hidden" @click="navOpen = !navOpen">
						<span class="sr-only">Toggle Navigation</span>
						<i class="fas fa-bars"></i>
					</button>
					<a class="text-lg font-bold text-secondary-900" href="#">Laravel</a>
				</div>

				<div :class="navOpen ? 'block' : 'hidden'" class="lg:flex lg:items-center lg:gap-4" id="navbarNav" @click.away="navOpen = false">
					<ul class="flex items-center gap-1">
						<li><a href="/" class="px-3 py-2 text-sm font-medium text-secondary-700 hover:text-secondary-900 hover:bg-secondary-50 rounded-md transition-colors">Home</a></li>
					</ul>

					<ul class="flex items-center gap-1">
						@if (Auth::guest())
							<li><a href="/auth/login" class="px-3 py-2 text-sm font-medium text-secondary-700 hover:text-secondary-900 hover:bg-secondary-50 rounded-md transition-colors">Login</a></li>
							<li><a href="/auth/register" class="px-3 py-2 text-sm font-medium text-secondary-700 hover:text-secondary-900 hover:bg-secondary-50 rounded-md transition-colors">Register</a></li>
						@else
							<li x-data="{ open: false }" class="relative">
								<a href="#" @click.prevent="open = !open" class="px-3 py-2 text-sm font-medium text-secondary-700 hover:text-secondary-900 hover:bg-secondary-50 rounded-md inline-flex items-center transition-colors" role="button">
									{{ Auth::user()->name }} <i class="fas fa-chevron-down ml-1 text-xs"></i>
								</a>
								<ul x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition>
									<li>
										<form method="POST" action="{{ route('logout') }}" style="margin: 0;">
											{{ csrf_field() }}
											<a href="#" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50" onclick="event.preventDefault(); this.closest('form').submit();">Logout</a>
										</form>
									</li>
								</ul>
							</li>
						@endif
					</ul>
				</div>
			</div>
		</div>
	</nav>

	@yield('content')

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
</body>
</html>
