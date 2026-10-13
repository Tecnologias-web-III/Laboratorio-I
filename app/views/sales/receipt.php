<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once(appRoot . '/views/includes/enca.php'); ?>
    <title>Comprobante de venta</title>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">
<div class="max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
    <header>
        <?php require_once(appRoot . '/views/includes/menu.php'); ?>
    </header>

    <main class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <?php if (empty($data['order'])) : ?>
            <h1 class="text-2xl font-bold text-slate-800 mb-3">Venta no disponible</h1>
            <p class="text-slate-600">La venta solicitada no se encontró en la base de datos.</p>
        <?php else : ?>
            <div class="flex flex-col gap-3 mb-6">
                <h1 class="text-3xl font-bold text-slate-800">Venta registrada</h1>
                <p class="text-slate-600">Pedido #<?php echo (int)$data['order']->OrderID; ?> para <?php echo htmlspecialchars($data['order']->CompanyName ?? 'Cliente', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Fecha</p>
                    <p class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($data['order']->OrderDate ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Total</p>
                    <p class="mt-1 font-semibold text-slate-800">$<?php echo number_format((float)($data['order']->Total ?? 0), 2); ?></p>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Producto</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Cantidad</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Precio</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php foreach ($data['order']->Items as $item) : ?>
                            <tr>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($item->ProductName ?? 'Producto', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-4 py-3"><?php echo (int)($item->Quantity ?? 0); ?></td>
                                <td class="px-4 py-3">$<?php echo number_format((float)($item->UnitPrice ?? 0), 2); ?></td>
                                <td class="px-4 py-3">$<?php echo number_format(((float)($item->UnitPrice ?? 0)) * (int)($item->Quantity ?? 0), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?php echo urlRoot; ?>/sales/index" class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 font-medium transition-colors">Nueva venta</a>
                <a href="<?php echo urlRoot; ?>/sales/document" class="rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 font-medium transition-colors">Descargar documento PDF</a>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>
