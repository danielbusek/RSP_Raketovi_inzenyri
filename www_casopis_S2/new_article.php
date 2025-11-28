<?php
$page_title = "Nahrát nový článek";
require 'db_connect.php';
require 'header.php';

// Přístup jen pro přihlášené s rolí 'autor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'autor') {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = trim($_POST['title']);
    
    // Zpracování souboru
    if (isset($_FILES['article_file']) && $_FILES['article_file']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['article_file']['tmp_name'];
        $fileName = $_FILES['article_file']['name'];
        $fileSize = $_FILES['article_file']['size'];
        $fileType = $_FILES['article_file']['type'];
        
        // Získání přípony souboru
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Povolené přípony
        $allowedfileExtensions = array('pdf', 'docx', 'doc');
        
        if (in_array($fileExtension, $allowedfileExtensions)) {
            
            // Vytvoření unikátního názvu souboru (aby se nepřepsaly, když dva lidi nahrají "clanek.pdf")
            $newFileName = $_SESSION['user_id'] . '_' . time() . '_' . $fileName;
            
            // Složka pro nahrávání (musíš ji vytvořit!)
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // Info do DB
                $stmt = $db_connection->prepare("INSERT INTO articles (user_id, title, filename, status) VALUES (?, ?, ?, 'podano')");
                $user_id = $_SESSION['user_id'];
                $stmt->bind_param("iss", $user_id, $title, $newFileName);
                
                if($stmt->execute()){
                    $success = "Článek byl úspěšně nahrán a odeslán k recenzi.";
                    // Volitelně přesměrování na přehled:
                    // header("Location: my_articles.php"); exit;
                } else {
                    $error = "Chyba při ukládání do databáze.";
                }
                $stmt->close();
            } else {
                $error = "Chyba při přesunu souboru do složky uploads. Zkontrolujte práva.";
            }
        } else {
            $error = "Nepovolený formát. Nahrávejte pouze .pdf nebo .docx";
        }
    } else {
        $error = "Nebyl vybrán žádný soubor nebo došlo k chybě při nahrávání.";
    }
}
?>

<div class="container mt-5" style="max-width: 600px;">
    <h2 class="text-center mb-4">Nahrát nový článek</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="mb-3">
            <label class="form-label">Název článku</label>
            <input type="text" name="title" class="form-control" required placeholder="Zadejte název vašeho článku">
        </div>

        <div class="mb-4">
            <label class="form-label">Soubor s článkem (.pdf, .docx)</label>
            <input type="file" name="article_file" class="form-control" required accept=".pdf,.docx,.doc">
            <div class="form-text">Maximální velikost souboru je omezena nastavením serveru (obvykle 2MB).</div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Odeslat článek</button>
    </form>
</div>

<?php
require 'footer.php';
require 'db_close.php';
?>