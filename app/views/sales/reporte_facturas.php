<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once(appRoot . '/views/includes/enca.php'); ?>
    <title>Reporte de facturas</title>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
    <header>
        <?php require_once(appRoot . '/views/includes/menu.php'); ?>
    </header>

    <main class="max-w-3xl mx-auto w-full bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-800">Reporte de facturas por cliente</h1>
            <p class="text-slate-600 mt-2">Seleccione un cliente y un rango de fechas para generar el reporte en PDF.</p>
        </div>

        <?php if (!empty($data['error'])) : ?>
            <div role="alert" class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?php echo htmlspecialchars($data['error'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo urlRoot; ?>/sales/pdfFacturas" target="_blank" class="space-y-5">
            <div>
                <label for="customer_id" class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                <select id="customer_id" name="customer_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($data['customers'] as $customer) : ?>
                        <option value="<?php echo htmlspecialchars($customer->CustomerID, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $data['selected_customer'] === $customer->CustomerID ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer->CompanyName, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">Fecha inicial</label>
                    <input id="start_date" name="start_date" type="date" required value="<?php echo htmlspecialchars($data['start_date'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Fecha final</label>
                    <input id="end_date" name="end_date" type="date" required value="<?php echo htmlspecialchars($data['end_date'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 font-medium transition-colors">
                    <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                    Generar PDF
                </button>
                <a href="<?php echo urlRoot; ?>/sales/index" class="inline-flex items-center rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 px-5 py-2.5 font-medium transition-colors">
                    Volver a Ventas
                </a>
            </div>
        </form>
    </main>

    <?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>
