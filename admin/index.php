<?php
session_start();
require __DIR__ . '/../inc/config.php';
require __DIR__ . '/../inc/d1.php';

$error   = '';
$success = '';

// --- Odhlášení ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- Přihlášení ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (password_verify($_POST['password'] ?? '', ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
    } else {
        sleep(1); // zpomalí zkoušení hesel nahrubo
        $error = 'Nesprávné heslo.';
    }
}

$loggedIn = !empty($_SESSION['logged_in']);

// --- Přidání článku ---
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $title   = trim($_POST['title'] ?? '');
    $tag     = trim($_POST['tag'] ?? '');
    $date    = trim($_POST['article_date'] ?? '');
    $content = trim(str_replace("\r\n", "\n", $_POST['content'] ?? ''));
    $linkUrl = trim($_POST['link_url'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;

    if ($title === '' || $content === '' || $date === '') {
        $error = 'Vyplňte prosím nadpis, datum a text článku.';
    } elseif (!in_array($tag, ARTICLE_TAGS, true)) {
        $error = 'Vyberte prosím platnou kategorii.';
    } else {
        $image = handle_photo_upload($_FILES['photo'] ?? []);
        if ($image === false) {
            $error = 'Fotku se nepodařilo nahrát (povolené formáty: jpg, png, webp, max 5 MB).';
        } else {
            $slug = make_slug($title) . '-' . substr(md5(uniqid('', true)), 0, 5);
            $ok = d1_insert_article($slug, $title, $tag, $date, $content, $image, $linkUrl ?: null, $published);
            if ($ok) {
                clear_articles_cache();
                header('Location: index.php?added=1');
                exit;
            }
            $error = 'Uložení do databáze selhalo — zkontrolujte údaje v inc/config.php.';
        }
    }
}

// --- Smazání článku ---
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && !empty($_POST['id'])) {
    d1_delete_article((int) $_POST['id']);
    clear_articles_cache();
    header('Location: index.php');
    exit;
}

// --- Přepnutí zveřejněno / koncept ---
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle' && isset($_POST['id'])) {
    d1_toggle_published((int) $_POST['id'], (int) $_POST['published']);
    clear_articles_cache();
    header('Location: index.php');
    exit;
}

if (isset($_GET['added'])) {
    $success = 'Článek byl přidán.';
}

$articles = $loggedIn ? d1_fetch_all_articles() : [];
$todayDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Administrace — Petr v kleci Media</title>
<style>
:root {
    --bg-color: #1a1622; --surface-color: #272236; --surface-hover: #322b46;
    --text-main: #f4f1f8; --text-muted: #a39bb3; --accent: #9d4edd; --accent-soft: #c299f0;
    --accent-glow: rgba(157, 78, 221, 0.6); --glass-border: rgba(157, 78, 221, 0.3);
    --accent-warn: #ff5c6c; --radius-lg: 20px; --radius-md: 14px; --radius-sm: 10px;
}
* { box-sizing: border-box; }
body {
    margin: 0; background: var(--bg-color); color: var(--text-main);
    font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; padding: 2.5rem 1.5rem 5rem;
}
.wrap { max-width: 780px; margin: 0 auto; }
h1 { font-size: 1.6rem; margin: 0; }
.topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.topbar a { color: var(--text-muted); font-size: 0.9rem; text-decoration: none; }
.topbar a:hover { color: var(--accent); }

.panel { background: var(--surface-color); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 1.8rem; margin-bottom: 1.6rem; }
.panel h2 { font-size: 1.15rem; margin: 0 0 1.2rem; }

label { display: block; font-size: 0.85rem; color: var(--text-muted); margin: 1rem 0 0.4rem; }
label:first-of-type { margin-top: 0; }
input[type=text], input[type=date], input[type=url], select, textarea {
    width: 100%; padding: 10px 12px; border-radius: var(--radius-sm);
    border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-main);
    font-family: inherit; font-size: 0.95rem;
}
textarea { min-height: 160px; resize: vertical; }
input[type=file] { color: var(--text-muted); margin-top: 4px; }
.checkbox-row { display: flex; align-items: center; gap: 8px; margin-top: 1.1rem; }
.checkbox-row input { width: auto; }
.hint { font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; }

button, .btn {
    padding: 11px 22px; border: none; border-radius: var(--radius-sm);
    background: var(--accent); color: #fff; font-weight: 600; cursor: pointer; font-size: 0.9rem;
}
button:hover { box-shadow: 0 0 20px var(--accent-glow); }
.btn-danger { background: var(--accent-warn); }
.btn-ghost { background: transparent; border: 1px solid var(--glass-border); color: var(--text-main); }

.msg { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 1.4rem; font-size: 0.9rem; }
.msg-error { background: rgba(255,92,108,0.12); color: var(--accent-warn); border: 1px solid rgba(255,92,108,0.3); }
.msg-success { background: rgba(157,78,221,0.12); color: var(--accent); border: 1px solid var(--glass-border); }

.login-wrap { display: flex; align-items: center; justify-content: center; min-height: 70vh; }
.login-box { max-width: 360px; width: 100%; }

table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid var(--glass-border); vertical-align: top; }
th { color: var(--text-muted); font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; }
.status { font-size: 0.78rem; padding: 3px 9px; border-radius: 999px; }
.status-pub { background: rgba(157,78,221,0.15); color: var(--accent-soft); }
.status-draft { background: rgba(163,155,179,0.15); color: var(--text-muted); }
.row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.row-actions form { display: inline; }
.row-actions button { padding: 6px 12px; font-size: 0.78rem; }
</style>
</head>
<body>

<?php if (!$loggedIn): ?>

    <div class="login-wrap">
        <div class="login-box panel">
            <h1 style="margin-bottom:1.2rem;">Administrace</h1>
            <?php if ($error): ?><div class="msg msg-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <label for="password">Heslo</label>
                <input type="password" id="password" name="password" required autofocus>
                <button type="submit" style="margin-top:1.2rem;width:100%;">Přihlásit se</button>
            </form>
        </div>
    </div>

<?php else: ?>

    <div class="wrap">
        <div class="topbar">
            <h1>Administrace · Petr v kleci Media</h1>
            <a href="?logout=1">Odhlásit se</a>
        </div>

        <?php if ($error): ?><div class="msg msg-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="msg msg-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

        <div class="panel">
            <h2>Přidat nový článek</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <label for="title">Nadpis</label>
                <input type="text" id="title" name="title" required>

                <label for="tag">Kategorie</label>
                <select id="tag" name="tag" required>
                    <?php foreach (ARTICLE_TAGS as $t): ?>
                        <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="article_date">Datum publikace</label>
                <input type="date" id="article_date" name="article_date" value="<?php echo $todayDate; ?>" required>

                <label for="content">Text článku</label>
                <textarea id="content" name="content" required placeholder="Každý odstavec pište zvlášť a oddělte ho prázdným řádkem (Enter dvakrát)."></textarea>

                <label for="photo">Fotka (volitelné)</label>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp">
                <div class="hint">JPG, PNG nebo WEBP, max 5 MB. Klidně nechte prázdné.</div>

                <label for="link_url">Odkaz (volitelné)</label>
                <input type="url" id="link_url" name="link_url" placeholder="https://…">
                <div class="hint">Např. odkaz na video, tiskovou zprávu nebo zdroj. Klidně nechte prázdné.</div>

                <div class="checkbox-row">
                    <input type="checkbox" id="published" name="published" checked>
                    <label for="published" style="margin:0;">Zveřejnit hned (jinak zůstane jako koncept)</label>
                </div>

                <button type="submit" style="margin-top:1.6rem;">Přidat článek</button>
            </form>
        </div>

        <div class="panel">
            <h2>Existující články (<?php echo count($articles); ?>)</h2>
            <?php if (!$articles): ?>
                <p style="color:var(--text-muted);font-size:0.9rem;">Zatím žádné články — přidejte první výše.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Datum</th><th>Kategorie</th><th>Nadpis</th><th>Stav</th><th>Akce</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($articles as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['article_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($a['tag'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if ((int) $a['published'] === 1): ?>
                                    <span class="status status-pub">Zveřejněno</span>
                                <?php else: ?>
                                    <span class="status status-draft">Koncept</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <form method="post">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                                        <input type="hidden" name="published" value="<?php echo (int) $a['published'] === 1 ? 0 : 1; ?>">
                                        <button type="submit" class="btn-ghost">
                                            <?php echo (int) $a['published'] === 1 ? 'Skrýt' : 'Zveřejnit'; ?>
                                        </button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Opravdu smazat tento článek?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                                        <button type="submit" class="btn-danger">Smazat</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>
</body>
</html>
