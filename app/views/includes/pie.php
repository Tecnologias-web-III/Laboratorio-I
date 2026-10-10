<footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-8 rounded-xl">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
		<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
			<div class="md:col-span-2 space-y-4">
				<div class="flex items-center space-x-3">
					<div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-lg">P</div>
					<span class="text-lg font-bold text-white">Desarrollo Web Avanzado</span>
				</div>
				<p class="text-sm text-slate-400 max-w-sm leading-relaxed">
					Proyecto de demostración para el aprendizaje de PHP, arquitectura MVC y Tailwind CSS.
				</p>
			</div>

			<div>
				<h2 class="text-sm font-semibold text-white tracking-wider uppercase mb-4">Recursos</h2>
				<ul class="space-y-2 text-sm">
					<li><a href="https://www.php.net" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">PHP.net Manual</a></li>
					<li><a href="https://tailwindcss.com" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">Tailwind CSS Docs</a></li>
					<li><a href="https://getcomposer.org" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">Composer</a></li>
				</ul>
			</div>

			<div>
				<h2 class="text-sm font-semibold text-white tracking-wider uppercase mb-4">Servidor</h2>
				<div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-medium">
					<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" aria-hidden="true"></span>
					<span>Localhost Activo</span>
				</div>
			</div>
		</div>

		<div class="pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-3">
			<p>&copy; <?php echo date('Y'); ?> Desarrollo Web Avanzado.</p>
			<p>Construido con PHP y Tailwind CSS</p>
		</div>
	</div>
</footer>