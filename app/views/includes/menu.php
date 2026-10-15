<nav class="bg-white border-b border-slate-200 shadow-sm rounded-xl mb-6">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex flex-wrap justify-between min-h-[4rem] items-center py-3 gap-3">
			<div class="flex items-center space-x-3">
				<div class="bg-indigo-600 text-white p-2 rounded-lg font-bold text-lg leading-none">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 18 6-6-6-6M8 6l-6 6 6 6m6-16-4 20"></path>
					</svg>
				</div>
				<span class="font-bold text-slate-800 text-lg tracking-tight"><?php echo htmlspecialchars(siteName, ENT_QUOTES, 'UTF-8'); ?></span>
			</div>
			<div class="flex flex-wrap items-center gap-2">
				<a href="<?php echo urlRoot; ?>/pages/index" class="px-3 py-2 rounded-md text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">Inicio</a>
				<a href="<?php echo urlRoot; ?>/categories/index" class="px-3 py-2 rounded-md text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">Categorías</a>
                <a href="<?php echo urlRoot; ?>/products/index" class="px-3 py-2 rounded-md text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">Productos</a>
                <a href="<?php echo urlRoot; ?>/sales/index" class="px-3 py-2 rounded-md text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">Ventas</a>
				<!-- Desplegable de Reportes -->
				<?php if(isLoggedIn()): ?>
					<div class="relative group">
						<button class="text-slate-600 group-hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors inline-flex items-center gap-1">
							<span>Reportes</span>
							<i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
						</button>

						<!-- Submenú -->
						<div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-lg border border-slate-100 py-1 hidden group-hover:block z-50">
							<a href="<?php echo urlRoot; ?>/sales/reporteFacturas" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
								<i class="fa-solid fa-file-pdf mr-2 text-rose-500"></i>Facturas por cliente
							</a>
							<a href="<?php echo urlRoot; ?>/categories/pdfCategorias" target="_blank" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
								<i class="fa-solid fa-file-pdf mr-2 text-rose-500"></i>Categorías
							</a>
						</div>
					</div>
				<?php endif; ?>
				<a href="<?php echo urlRoot; ?>/pages/about" class="px-3 py-2 rounded-md text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">Acerca de..</a>
				<a href="<?php echo urlRoot; ?>/sales/asistida" class="px-3 py-2 rounded-md text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">Venta Asistida</a>
				<?php if(isLoggedIn()): ?>
					<a href="<?php echo urlRoot; ?>/users/logout" class="ml-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-sm">Salir</a>
				<?php else: ?>
					<a href="<?php echo urlRoot; ?>/users/login" class="ml-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">Ingresar</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</nav> 