<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once(appRoot . '/views/includes/enca.php'); ?>
    <title><?php echo siteName; ?> - Venta Asistida CR</title>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
    <header>
        <?php require_once(appRoot . '/views/includes/menu.php'); ?>
    </header>

    <main class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 max-w-4xl mx-auto w-full">
        <div class="mb-6 pb-4 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Módulo de Venta Asistida</h1>
                <p class="text-sm text-slate-500">Registro asistido por un colaborador de la empresa en Costa Rica</p>
            </div>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-full border border-indigo-200">
                Moneda: Colones (₡)
            </span>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                ¡Venta asistida registrada exitosamente en la base de datos!
            </div>
        <?php endif; ?>

        <?php if (!empty($data['err'])): ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm font-medium">
                <?php echo $data['err']; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo urlRoot; ?>/sales/asistida" method="POST" class="space-y-6">
            
            <!-- Datos Generales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Colaborador que atiende *</label>
                    <select name="cmbEmpleado" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <option value="">-- Seleccionar Colaborador --</option>
                        <?php foreach ($data['employees'] as $emp): ?>
                            <option value="<?php echo $emp->EmployeeID; ?>">
                                <?php echo htmlspecialchars($emp->LastName . ', ' . $emp->FirstName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Cliente *</label>
                    <select name="cmbCliente" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <option value="">-- Seleccionar Cliente --</option>
                        <?php foreach ($data['customers'] as $cust): ?>
                            <option value="<?php echo $cust->CustomerID; ?>">
                                <?php echo htmlspecialchars($cust->CompanyName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Flete / Encomienda -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Empresa de Encomienda / Transporte *</label>
                    <select name="cmbShipper" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <option value="">-- Seleccionar Servicio de Encomienda --</option>
                        <?php foreach ($data['shippers'] as $ship): ?>
                            <option value="<?php echo $ship->ShipperID; ?>">
                                <?php echo htmlspecialchars($ship->CompanyName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Costo de Envío (₡)</label>
                    <input type="number" step="100" name="txtFlete" value="2500" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <!-- Producto -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Detalle del Producto</h3>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Producto *</label>
                    <select id="cmbProducto" name="cmbProducto" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" onchange="actualizarPrecio()" required>
                        <option value="" data-precio="0">-- Seleccionar Producto Nacional --</option>
                        <?php foreach ($data['products'] as $prod): ?>
                            <option value="<?php echo $prod->ProductID; ?>" data-precio="<?php echo $prod->UnitPrice; ?>">
                                <?php echo htmlspecialchars($prod->ProductName); ?> - ₡<?php echo number_format($prod->UnitPrice, 2); ?> (Disponibles: <?php echo $prod->UnitsInStock; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Precio Unitario (₡)</label>
                        <input type="number" step="0.01" id="txtPrecio" name="txtPrecio" readonly class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-sm font-mono text-slate-600">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Cantidad *</label>
                        <input type="number" name="txtCantidad" value="1" min="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Descuento (%)</label>
                        <input type="number" step="0.01" name="txtDescuento" value="0" min="0" max="100" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                    Procesar Venta Asistida
                </button>
            </div>
        </form>
    </main>

    <script>
        function actualizarPrecio() {
            const select = document.getElementById('cmbProducto');
            const precioInput = document.getElementById('txtPrecio');
            const selectedOption = select.options[select.selectedIndex];
            const precio = selectedOption.getAttribute('data-precio') || 0;
            precioInput.value = precio;
        }
    </script>

    <?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>