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
				<h1 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b">Insertar Nuevo Producto</h1>

				<?php if(!empty($data['db_err'])): ?>
					<div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm"><?php echo htmlspecialchars($data['db_err'], ENT_QUOTES, 'UTF-8'); ?></div>
				<?php endif; ?>

				<form action="<?php echo urlRoot; ?>/products/create" method="POST" class="space-y-4">
					<div>
						<label for="txtNombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
						<input id="txtNombre" type="text" name="txtNombre" value="<?php echo htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
						<?php if(!empty($data['name_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['name_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="cboCategoria" class="block text-sm font-semibold text-slate-700 mb-1">Categoría</label>
						<select id="cboCategoria" name="cboCategoria" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
							<option value="">-- Seleccione una categoría --</option>
							<?php foreach ($data['categories'] as $cat) : ?>
								<option value="<?php echo $cat->CategoryID; ?>" <?php echo ($data['category'] == $cat->CategoryID) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($cat->CategoryName); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php if(!empty($data['category_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['category_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="cboProveedor" class="block text-sm font-semibold text-slate-700 mb-1">Proveedor</label>
						<select id="cboProveedor" name="cboProveedor" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
							<option value="">-- Seleccione un proveedor --</option>
							<?php foreach ($data['suppliers'] as $sup) : ?>
								<option value="<?php echo (int)$sup->SupplierID; ?>" <?php echo ($data['supplier'] == $sup->SupplierID) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($sup->CompanyName); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php if(!empty($data['supplier_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['supplier_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="txtPrecio" class="block text-sm font-semibold text-slate-700 mb-1">Precio</label>
						<input id="txtPrecio" type="number" step="0.01" min="0" name="txtPrecio" value="<?php echo htmlspecialchars($data['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
						<?php if(!empty($data['price_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['price_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="txtStock" class="block text-sm font-semibold text-slate-700 mb-1">Unidades en stock</label>
						<input id="txtStock" type="number" min="0" name="txtStock" value="<?php echo htmlspecialchars($data['stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
						<?php if(!empty($data['stock_err'])): ?>
							<span class="text-xs text-rose-500"><?php echo htmlspecialchars($data['stock_err'], ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>

					<div class="flex justify-end gap-3 pt-4">
						<a href="<?php echo urlRoot; ?>/products" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition">Cancelar</a>
						<button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">Aceptar</button>
					</div>
				</form>
			</div>
		</main>

		<?php require_once(appRoot . '/views/includes/pie.php'); ?>
	</div>
</body>
</html>