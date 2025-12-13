<?php
$page_title = "Nahrát nový článek";
require 'db_connect.php';
require 'header.php';

// Ochrana: Přístup jen pro přihlášené s rolí 'autor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'autor') {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

// -------------------------------------------------------
// 1. ZPRACOVÁNÍ FORMULÁŘE (PO ODESLÁNÍ)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    // Získáme ID vybraného čísla (integer)
    $issue_id = intval($_POST['issue_id']);

    // Validace, zda bylo vybráno číslo
    if (!$issue_id) {
        $error = "Musíte vybrat číslo časopisu.";
    }
    // Zpracování souboru
    elseif (isset($_FILES['article_file']) && $_FILES['article_file']['error'] === UPLOAD_ERR_OK) {

        $fileTmpPath = $_FILES['article_file']['tmp_name'];
        $fileName = $_FILES['article_file']['name'];
        // Získání přípony
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Povolené formáty
        $allowedfileExtensions = array('pdf', 'docx', 'doc');

        if (in_array($fileExtension, $allowedfileExtensions)) {

            // Unikátní název souboru: ID_TIMESTAMP_NAZEV
            $newFileName = $_SESSION['user_id'] . '_' . time() . '_' . $fileName;

            // Kontrola/vytvoření složky pro nahrávání
            $uploadFileDir = './uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {

                // ULOŽENÍ DO DB VČETNĚ ISSUE_ID
                $stmt = $db_connection->prepare("
                    INSERT INTO articles (user_id, issue_id, title, filename, status) 
                    VALUES (?, ?, ?, ?, 'podano')
                ");
                $user_id = $_SESSION['user_id'];

                // iiss = integer, integer, string, string
                $stmt->bind_param("iiss", $user_id, $issue_id, $title, $newFileName);

                if ($stmt->execute()) {
                    $success = "Článek byl úspěšně nahrán a odeslán k recenzi.";
                } else {
                    $error = "Chyba při ukládání do databáze: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Chyba při přesunu souboru. Zkontrolujte práva složky uploads.";
            }
        } else {
            $error = "Nepovolený formát. Nahrávejte pouze .pdf nebo .docx";
        }
    } else {
        $error = "Nebyl vybrán žádný soubor nebo došlo k chybě při nahrávání.";
    }
}

// -------------------------------------------------------
// 2. NAČTENÍ DOSTUPNÝCH ČÍSEL ČASOPISU + KAPACITA
// -------------------------------------------------------
// SQL dotaz, který rovnou spočítá obsazenost
// POZOR: ZDE JE TA ZMĚNA (status = 'open')
$sql_issues = "
    SELECT 
        i.id, 
        i.nazev, 
        i.deadline, 
        i.max_capacity,
        (SELECT COUNT(*) FROM articles a WHERE a.issue_id = i.id) as occupied
    FROM issues i
    WHERE i.status = 'open' 
    -- Pokud chceš skrýt čísla po uzávěrce, odkomentuj tento řádek:
    -- AND i.deadline >= CURDATE()
    ORDER BY i.deadline ASC
";

$result_issues = $db_connection->query($sql_issues);
?>

<div class="container mt-5" style="max-width: 600px;">

    <h2 class="text-center mb-4 text-white">Nahrát nový článek</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success text-center">
            <?= htmlspecialchars($success) ?>
        </div>
        <div class="text-center mt-3">
            <a href="my_articles.php" class="btn btn-primary">Přejít na mé články</a>
        </div>
    <?php else: ?>

        <form method="POST" enctype="multipart/form-data" id="registerForm" style="margin-top: 0;">

            <div>
                <label class="form-label">Název článku</label>
                <input type="text" name="title" class="form-control" required placeholder="Zadejte název vašeho článku">
            </div>

            <div>
                <label class="form-label">Vyberte číslo časopisu</label>
                <select name="issue_id" class="form-control" required>
                    <option value="" disabled selected>-- Zvolte téma --</option>

                    <?php if ($result_issues && $result_issues->num_rows > 0): ?>
                        <?php while ($row = $result_issues->fetch_assoc()): ?>
                            <?php
                            // Výpočet volných míst
                            $free_slots = $row['max_capacity'] - $row['occupied'];
                            $is_full = $free_slots <= 0;
                            $deadline_cz = date("d.m.Y", strtotime($row['deadline']));
                            ?>

                            <option value="<?= $row['id'] ?>" <?= $is_full ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($row['nazev']) ?>
                                (Uzávěrka: <?= $deadline_cz ?>)
                                <?= $is_full ? ' - PLNĚ OBSAZENO' : " - Zbývá míst: $free_slots" ?>
                            </option>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>Žádná otevřená čísla nejsou k dispozici</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Soubor s článkem (.pdf, .docx)</label>
                <input type="file" name="article_file" class="form-control" required accept=".pdf,.docx,.doc">
                <div class="form-text mt-2 text-muted">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Vyberte finální verzi dokumentu.
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-paper-plane me-2"></i> Odeslat článek
            </button>
        </form>
    <?php endif; ?>
</div>

<?php
require 'footer.php';
require 'db_close.php';
?>