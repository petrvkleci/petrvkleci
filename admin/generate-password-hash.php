<?php
/**
 * JEDNORÁZOVÁ POMŮCKA — vygeneruje bezpečný hash hesla pro administraci.
 *
 * Postup:
 *   1) Nahrajte tento soubor na server spolu s ostatními.
 *   2) Otevřete ho v prohlížeči (např. https://vasedomena.cz/admin/generate-password-hash.php)
 *   3) Napište si heslo, klikněte na "Vygenerovat", zkopírujte výsledný hash
 *      do inc/config.php (ADMIN_PASSWORD_HASH).
 *   4) DŮLEŽITÉ: tento soubor pak ze serveru smažte — ať nikdo jiný nemůže
 *      hesla generovat.
 *
 * Heslo samotné se nikam neukládá, natož v čitelné podobě — jen jeho hash.
 */

$hash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generátor hashe hesla</title>
<style>
    body { background:#1a1622; color:#f4f1f8; font-family: system-ui, sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
    .box { background:#272236; border:1px solid rgba(157,78,221,0.3); border-radius:16px; padding:2rem; max-width:520px; width:90%; }
    h1 { font-size:1.3rem; margin-bottom:1rem; }
    input[type=password] { width:100%; padding:10px 12px; border-radius:8px; border:1px solid rgba(157,78,221,0.3); background:#1a1622; color:#f4f1f8; margin-bottom:1rem; box-sizing:border-box; }
    button { padding:10px 20px; border:none; border-radius:8px; background:#9d4edd; color:#fff; font-weight:600; cursor:pointer; }
    .result { margin-top:1.5rem; padding:1rem; background:#1a1622; border-radius:8px; word-break:break-all; font-size:0.85rem; color:#c299f0; }
    .warn { margin-top:1.5rem; font-size:0.85rem; color:#ff5c6c; }
</style>
</head>
<body>
<div class="box">
    <h1>Generátor hashe hesla</h1>
    <form method="post">
        <input type="password" name="password" placeholder="Zadejte heslo pro administraci" required>
        <button type="submit">Vygenerovat</button>
    </form>
    <?php if ($hash): ?>
        <div class="result"><?php echo htmlspecialchars($hash, ENT_QUOTES, 'UTF-8'); ?></div>
        <p style="font-size:0.85rem;color:#a39bb3;margin-top:0.8rem;">
            Zkopírujte tento řetězec do <code>inc/config.php</code>, do řádku
            <code>ADMIN_PASSWORD_HASH</code>, mezi uvozovky.
        </p>
    <?php endif; ?>
    <p class="warn">Až budete mít hash uložený v config.php, tento soubor ze serveru smažte.</p>
</div>
</body>
</html>
