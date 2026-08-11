<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$salida_id = intval($_GET['salida_id']);

$sql_check = "SELECT destino FROM salidas WHERE id = $salida_id";
$res_check = $conexion->query($sql_check);
if ($res_check->num_rows == 0) {
    header("Location: salidas.php");
    exit;
}
$destino = $res_check->fetch_assoc()['destino'];

$check_insc = "SELECT id FROM inscripciones WHERE salida_id = $salida_id AND usuario_id = " . $_SESSION['usuario_id'];
$res_insc = $conexion->query($check_insc);
if ($res_insc->num_rows > 0) {
    header("Location: salidas.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apuntarse a salida - Ratas del Queiles</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#131313">
    <link rel="apple-touch-icon" href="images/logo2.jpg">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=Hanken+Grotesk:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body { background: #131313; color: #e5e2e1; font-family: 'Hanken Grotesk', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .bg-surface { background: #201f1f; }
        .chrome-border { border: 1px solid rgba(255,255,255,0.1); }
        .input-dark { background-color: #1a1a1a; border: 1px solid rgba(255,255,255,0.1); color: #e5e2e1; padding: 0.6rem; border-radius: 0.25rem; width: 100%; font-family: 'Hanken Grotesk', sans-serif; font-size: 1rem; }
        .input-dark:focus { outline: none; border-color: #ffb59e; }
        .input-dark::placeholder { color: #666; }
        .btn-primary { background: #ff5719; color: black; border: 2px solid black; padding: 0.75rem 2rem; border-radius: 0.25rem; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-family: 'Anybody', sans-serif; font-size: 1rem; text-transform: uppercase; }
        .btn-primary:hover { background: #ffb59e; }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .text-secondary { color: #b8b8b8; }
        .text-primary { color: #ffb59e; }
        .text-on-background { color: #e5e2e1; }
        .font-headline-lg { font-family: 'Anybody', sans-serif; font-size: 1.5rem; font-weight: 700; }
        .radio-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
        .radio-label input[type="radio"] { accent-color: #ff5719; width: 1.1rem; height: 1.1rem; }
    </style>
</head>
<body>
    <div class="bg-surface rounded-xl chrome-border p-8 max-w-lg w-full">
        <h2 class="font-headline-lg text-on-background uppercase mb-1">Apuntarse a</h2>
        <p class="text-primary font-headline-md text-xl font-bold"><?php echo htmlspecialchars($destino); ?></p>
        <p class="text-secondary text-sm mt-4">¿Vienes con acompañantes?</p>
        <form action="guardar_apuntarse.php" method="POST">
            <input type="hidden" name="salida_id" value="<?php echo $salida_id; ?>">
            <div class="mt-3 flex gap-6">
                <label class="radio-label text-on-background">
                    <input type="radio" name="acompanantes" value="0" checked onchange="toggleAcompanantes()"> No
                </label>
                <label class="radio-label text-on-background">
                    <input type="radio" name="acompanantes" value="1" onchange="toggleAcompanantes()"> Sí
                </label>
            </div>
            <div id="acompanantes-container" style="display: none; margin-top: 1.5rem;">
                <label class="label-dark" for="num_acompanantes">Número de acompañantes</label>
                <select name="num_acompanantes" id="num_acompanantes" class="input-dark" onchange="generarCampos()">
                    <?php for ($i=1; $i<=5; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <div id="nombres-container" class="mt-3 space-y-3"></div>
            </div>
            <button type="submit" class="btn-primary mt-6">
                <span class="material-symbols-outlined">check_circle</span> Apuntarse
            </button>
            <a href="salidas.php" class="block text-center text-secondary hover:text-primary transition-colors mt-4 text-sm uppercase font-label-md">⬅ Volver a salidas</a>
        </form>
    </div>
    <script>
        function toggleAcompanantes() {
            const radios = document.querySelectorAll('input[name="acompanantes"]');
            let selected = 0;
            radios.forEach(r => { if (r.checked) selected = parseInt(r.value); });
            document.getElementById('acompanantes-container').style.display = (selected === 1) ? 'block' : 'none';
            if (selected === 1) generarCampos();
        }
        function generarCampos() {
            const num = parseInt(document.getElementById('num_acompanantes').value);
            const container = document.getElementById('nombres-container');
            container.innerHTML = '';
            for (let i=0; i<num; i++) {
                const div = document.createElement('div');
                div.innerHTML = `<label class="label-dark" for="acomp_${i}">Nombre acompañante ${i+1}</label><input type="text" name="acompanante[]" id="acomp_${i}" class="input-dark" placeholder="Nombre del acompañante" required>`;
                container.appendChild(div);
            }
        }
        toggleAcompanantes();
    </script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(registration => {
                    console.log('ServiceWorker registrado con éxito', registration.scope);
                })
                .catch(error => {
                    console.log('Fallo al registrar ServiceWorker', error);
                });
        });
    }
</script>
</body>
</html>