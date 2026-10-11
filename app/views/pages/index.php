<?php /** @var array<string, mixed> $data */ ?>
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
			<h1 class="text-2xl font-bold text-slate-800 mb-2"><?php echo htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
			<p class="text-slate-600">
				<?php if(isLoggedIn()): ?>
					Usted es: <strong class="text-indigo-600"><?php echo htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?></strong>
				<?php else: ?>
					Por favor autentíquese ante nosotros...
				<?php endif; ?>
			</p>
		</main>

		<?php require_once(appRoot . '/views/includes/pie.php'); ?>
	</div>
</body>
</html>
