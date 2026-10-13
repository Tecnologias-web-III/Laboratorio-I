<!DOCTYPE html>
<html lang="es">
<head>
	<?php require_once(appRoot . '/views/includes/enca.php'); ?>
	<title><?php echo htmlspecialchars(siteName, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">
	<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
		<header>
			<?php require_once(appRoot . '/views/includes/menu.php'); ?>
		</header>

		<main class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[300px]">
			<div class="max-w-2xl mx-auto my-10 p-8 bg-white rounded-xl shadow-lg border border-slate-100">
				<h1 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b">Insertar Nueva Categoría</h1>

				<form action="<?php echo urlRoot; ?>/categories/create" method="POST" enctype="multipart/form-data" class="space-y-4">
					<div>
						<label for="txtNombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
						<input id="txtNombre" type="text" name="txtNombre" value="<?php echo htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
						<?php if(!empty($data['name_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['name_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="txtDescrip" class="block text-sm font-semibold text-slate-700 mb-1">Descripción</label>
						<textarea id="txtDescrip" name="txtDescrip" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?php echo htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
					</div>

					<div>
						<label for="txtArchi" class="block text-sm font-semibold text-slate-700 mb-1">Imagen (Guardada en BD)</label>
						<input id="txtArchi" type="file" name="txtArchi" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
						<p class="mt-1 text-xs text-slate-500">Formatos permitidos: JPG, PNG, GIF o WEBP. Máximo 2 MB.</p>
						<?php if(!empty($data['image_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['image_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div id="previewContainer" class="hidden">
						<label class="block text-sm font-semibold text-slate-700 mb-1">Vista Previa</label>
						<div class="w-40 h-40 rounded-lg border-2 border-dashed border-slate-300 overflow-hidden flex items-center justify-center bg-slate-50">
							<img id="imgSalida" class="w-full h-full object-cover" alt="Vista previa de la imagen seleccionada">
						</div>
					</div>

					<div class="flex justify-end gap-3 pt-4">
						<a href="<?php echo urlRoot; ?>/categories" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition">Cancelar</a>
						<button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">Aceptar</button>
					</div>
				</form>
			</div>
		</main>

		<script>
			// Se muestra una vista previa de la imagen
			document.getElementById('txtArchi').addEventListener('change', function(e) {
				const file = e.target.files[0];
				const preview = document.getElementById('previewContainer');

				if (file && file.type.match('image.*')) {
					const reader = new FileReader();
					reader.onload = function(e) {
						document.getElementById('imgSalida').src = e.target.result;
						preview.classList.remove('hidden');
					};
					reader.readAsDataURL(file);
				} else {
					preview.classList.add('hidden');
				}
			});
		</script>

		<?php require_once(appRoot . '/views/includes/pie.php'); ?>
	</div>
</body>
</html>
