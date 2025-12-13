<?php
$page_title = "Reakce na připomínky";
require 'db_connect.php';
require 'header.php';

// 1. Kontrola přihlášení
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'autor') {
    header("Location: index.php"); exit;
}

$user_id = $_SESSION['user_id'];
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Načtení článku a ověření vlastníka
$stmt = $db_connection->prepare("
    SELECT a.*, i.nazev as issue_nazev 
    FROM articles a
    JOIN issues i ON a.issue_id = i.id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->bind_param("ii", $article_id, $user_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Článek nenalezen nebo k němu nemáte přístup.</div></div>';
    require 'footer.php'; exit;
}

// 3. Kontrola stavu (Reagovat lze jen, pokud je vráceno k opravě)
if ($article['status'] !== 'vraceno_k_oprave') {
    echo '<div class="container mt-5"><div class="alert alert-warning">Na tento článek momentálně nelze reagovat (není ve stavu k opravě). <a href="my_articles.php">Zpět</a></div></div>';
    require 'footer.php'; exit;
}

// 4. Získání poslední zprávy od redaktora (pro kontext)
$last_msg = $db_connection->query("
    SELECT message, created_at FROM article_messages 
    WHERE article_id = $article_id AND sender_id != $user_id 
    ORDER BY created_at DESC LIMIT 1
")->fetch_assoc();

$error = '';
$success = '';

// -------------------------------------------------------
// ZPRACOVÁNÍ FORMULÁŘE
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $message = trim($_POST['message']);
    $file_uploaded = false;
    $newFileName = $article['filename']; // Defaultně starý název

    // A) Kontrola a nahrání nového souboru (volitelné)
    if (isset($_FILES['new_file']) && $_FILES['new_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['new_file']['tmp_name'];
        $fileName = $_FILES['new_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('pdf', 'docx', 'doc');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Nový unikátní název (verze 2, 3...)
            $newFileName = $user_id . '_' . time() . '_v2_' . $fileName;
            $uploadFileDir = './uploads/';
            
            if(move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $file_uploaded = true;
            } else {
                $error = "Chyba při nahrávání souboru.";
            }
        } else {
            $error = "Nepovolený formát souboru.";
        }
    }

    // B) Uložení do DB
    if (!$error) {
        // 1. Uložit zprávu autora
        if (!empty($message)) {
            $stmt = $db_connection->prepare("INSERT INTO article_messages (article_id, sender_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $article_id, $user_id, $message);
            $stmt->execute();
        }

        // 2. Aktualizovat článek (nový soubor + změna stavu na PODÁNO)
        // Vracíme stav na 'podano', aby si toho redaktor všiml jako "nové" věci
        $stmt = $db_connection->prepare("UPDATE articles SET filename = ?, status = 'podano' WHERE id = ?");
        $stmt->bind_param("si", $newFileName, $article_id);
        
        if ($stmt->execute()) {
            $success = "Vaše reakce byla odeslána a článek byl předán zpět redakci.";
        } else {
            $error = "Chyba databáze: " . $stmt->error;
        }
    }
}
?>

<div class="container mt-5 mb-5" style="max-width: 800px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Oprava článku</h2>
        <a href="my_articles.php" class="btn btn-outline-light">Zpět</a>
    </div>

    <?php if ($success): ?>
        <div class="article-card p-5 text-center">
            <i class="fa-solid fa-check-circle fa-4x text-success mb-3"></i>
            <h3 class="text-white">Odesláno!</h3>
            <p class="text-muted"><?= $success ?></p>
            <a href="my_articles.php" class="btn btn-primary mt-3">Zpět na přehled</a>
        </div>
    <?php else: ?>

        <div class="article-card p-4 mb-4" style="border-left: 4px solid #facc15;">
            <h5 class="text-warning mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Požadavek redakce</h5>
            <?php if ($last_msg): ?>
                <p class="text-white mb-0 fst-italic">"<?= nl2br(htmlspecialchars($last_msg['message'])) ?>"</p>
                <div class="text-muted small mt-2 text-end">- <?= date("d.m.Y H:i", strtotime($last_msg['created_at'])) ?></div>
            <?php else: ?>
                <p class="text-muted">Bez textového komentáře.</p>
            <?php endif; ?>
        </div>

        <div class="article-card p-4">
            <h4 class="text-white mb-4">Vaše odpověď</h4>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Zpráva pro redaktora (nepovinné)</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Např: Doplnil jsem citace na straně 5 a opravil formátování..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Nahrát opravenou verzi (PDF/DOCX)</label>
                    <input type="file" name="new_file" class="form-control" accept=".pdf,.docx,.doc">
                    <div class="form-text text-muted">
                        <i class="fa-solid fa-info-circle me-1"></i> Pokud nenahrajete nový soubor, zůstane zachována původní verze.
                    </div>
                </div>

                <hr class="border-secondary my-4">

                <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-5">
                    <i class="fa-solid fa-paper-plane me-2"></i> Odeslat opravu redaktorovi
                </button>
            </form>
        </div>

    <?php endif; ?>
</div>

<?php require 'footer.php'; require 'db_close.php'; ?>