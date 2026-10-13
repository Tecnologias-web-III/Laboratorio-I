<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once(appRoot . '/views/includes/enca.php'); ?>
    <title>Ventas</title>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
    <header>
        <?php require_once(appRoot . '/views/includes/menu.php'); ?>
    </header>

    <main class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[300px]">
        <div class="flex flex-col gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Venta cliente</h1>
                <p class="text-slate-600">Selecciona un cliente y agrega productos al carrito para completar la venta.</p>
            </div>
            <?php if (!empty($data['error'])) : ?>
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <?php echo htmlspecialchars($data['error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1.4fr_0.9fr] gap-6">
            <section class="space-y-5">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <h2 class="text-lg font-semibold mb-3">Productos disponibles</h2>
                    <?php if (empty($data['products'])) : ?>
                        <p class="text-sm text-slate-500">No se encontraron productos en la base de datos.</p>
                    <?php else : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($data['products'] as $product) : ?>
                                <form method="POST" action="<?php echo urlRoot; ?>/sales/addToCart" class="border border-slate-200 rounded-xl p-4 bg-white shadow-sm">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product->ProductID; ?>">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($product->ProductName, ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p class="text-sm text-slate-500">Stock: <?php echo (int)($product->UnitsInStock ?? 0); ?></p>
                                        </div>
                                        <span class="text-lg font-bold text-indigo-600">$<?php echo number_format((float)($product->UnitPrice ?? 0), 2); ?></span>
                                    </div>

                                    <div class="mt-4 flex flex-col gap-3">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente</label>
                                        <select name="customer_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="">Seleccione cliente</option>
                                            <?php foreach ($data['customers'] as $customer) : ?>
                                                <option value="<?php echo htmlspecialchars($customer->CustomerID, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($data['selected_customer'] === $customer->CustomerID) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($customer->CompanyName, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-slate-600" for="qty-<?php echo (int)$product->ProductID; ?>">Cantidad</label>
                                            <input id="qty-<?php echo (int)$product->ProductID; ?>" type="number" name="quantity" min="1" value="1" class="w-20 border border-slate-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>

                                        <button type="submit" class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 font-medium transition-colors">
                                            Agregar al carrito
                                        </button>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <aside class="rounded-xl border border-slate-200 bg-slate-50 p-4 h-fit">
                <h2 class="text-lg font-semibold mb-4">Carrito</h2>
                <?php $cart = $data['cart']; $total = 0.0; ?>
                <?php if (empty($cart)) : ?>
                    <p class="text-sm text-slate-500">Todavía no hay productos en el carrito.</p>
                <?php else : ?>
                    <div class="space-y-3">
                        <?php foreach ($cart as $item) : ?>
                            <?php $lineTotal = ((float)($item['product']->UnitPrice ?? 0)) * (int)($item['quantity'] ?? 0); $total += $lineTotal; ?>
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <div>
                                    <p class="font-medium text-slate-800"><?php echo htmlspecialchars($item['product']->ProductName, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-slate-500">Cantidad: <?php echo (int)$item['quantity']; ?></p>
                                </div>
                                <span class="font-semibold text-slate-700">$<?php echo number_format($lineTotal, 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm uppercase tracking-wide text-slate-500">Total</span>
                            <span class="text-2xl font-bold text-slate-800">$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <form method="POST" action="<?php echo urlRoot; ?>/sales/checkout">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Cliente para la venta</label>
                            <select name="customer_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-4">
                                <option value="">Seleccione cliente</option>
                                <?php foreach ($data['customers'] as $customer) : ?>
                                    <option value="<?php echo htmlspecialchars($customer->CustomerID, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($data['selected_customer'] ?? '') === $customer->CustomerID ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($customer->CompanyName, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 font-medium transition-colors">
                                    Confirmar venta
                                </button>
                                <a href="<?php echo urlRoot; ?>/sales/clearCart" class="flex-1 text-center rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 font-medium transition-colors">
                                    Vaciar carrito
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </main>

    <?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>
