<?php
$page_title = "Statistiky časopisu (Chief Dashboard)";
require 'db_connect.php';
require 'header.php';

// 1. Kontrola práv (Pouze Admin a Šéfredaktor)
$role = mb_strtolower($_SESSION['user_role'] ?? '', 'UTF-8');
if (!in_array($role, ['admin', 'šéfredaktor'])) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Nemáte oprávnění.</div></div>';
    require 'footer.php'; exit;
}

// -----------------------------------------------------
// VÝPOČTY KPI (METRIK)
// -----------------------------------------------------

// A) ACCEPTANCE RATE (Míra přijetí)
// Počet přijatých / (Počet přijatých + Počet zamítnutých)
$sql_ar = "SELECT 
    COUNT(CASE WHEN status = 'prijato' THEN 1 END) as accepted,
    COUNT(CASE WHEN status = 'zamitnuto' THEN 1 END) as rejected
FROM articles";
$res_ar = $db_connection->query($sql_ar)->fetch_assoc();
$total_decided = $res_ar['accepted'] + $res_ar['rejected'];
$acceptance_rate = ($total_decided > 0) ? round(($res_ar['accepted'] / $total_decided) * 100, 1) : 0;

// B) PRŮMĚRNÁ DÉLKA RECENZNÍHO ŘÍZENÍ (ve dnech)
// Rozdíl mezi decision_date a created_at
$sql_time = "SELECT AVG(DATEDIFF(decision_date, created_at)) as avg_days 
             FROM articles WHERE decision_date IS NOT NULL";
$res_time = $db_connection->query($sql_time)->fetch_assoc();
$avg_days = round($res_time['avg_days'] ?? 0);

// C) GRAF: POČET PODANÝCH vs. PUBLIKOVANÝCH (Posledních 12 měsíců)
$months = [];
$data_submitted = [];
$data_published = [];

for ($i = 11; $i >= 0; $i--) {
    $month_start = date("Y-m-01", strtotime("-$i months"));
    $month_end = date("Y-m-t", strtotime("-$i months"));
    $label = date("M Y", strtotime("-$i months")); // Např. "Dec 2023"
    
    $months[] = $label;

    // Podané
    $q1 = $db_connection->query("SELECT COUNT(*) as c FROM articles WHERE created_at BETWEEN '$month_start 00:00:00' AND '$month_end 23:59:59'")->fetch_assoc();
    $data_submitted[] = $q1['c'];

    // Publikované (Přijaté)
    $q2 = $db_connection->query("SELECT COUNT(*) as c FROM articles WHERE status='prijato' AND decision_date BETWEEN '$month_start 00:00:00' AND '$month_end 23:59:59'")->fetch_assoc();
    $data_published[] = $q2['c'];
}

// D) VYTÍŽENOST REDAKTORŮ (Kdo má kolik článků)
// Počítáme články, kde je editor_id přiřazeno
$sql_editors = "SELECT u.jmeno, u.prijmeni, COUNT(a.id) as count 
                FROM users u 
                LEFT JOIN articles a ON u.id = a.editor_id 
                JOIN role r ON u.id_role = r.id_role
                WHERE r.role IN ('redaktor', 'šéfredaktor')
                GROUP BY u.id 
                ORDER BY count DESC";
$res_editors = $db_connection->query($sql_editors);
?>

<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white mb-1"><i class="fa-solid fa-chart-line me-3"></i>Dashboard Šéfredaktora</h2>
            <p class="text-muted small">Přehled výkonnosti redakce a publikačního procesu</p>
        </div>
        <a href="editor_dashboard.php" class="btn btn-outline-light">
            <i class="fa-solid fa-list me-2"></i>Zpět na přehled článků
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="article-card p-4 text-center h-100 d-flex flex-column justify-content-center">
                <div class="text-muted text-uppercase small fw-bold mb-2">Míra přijetí (Acceptance Rate)</div>
                <div class="display-4 fw-bold text-white"><?= $acceptance_rate ?> %</div>
                <div class="progress mt-3" style="height: 6px; background: #333;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $acceptance_rate ?>%"></div>
                </div>
                <small class="text-muted mt-2"><?= $res_ar['accepted'] ?> přijato / <?= $res_ar['rejected'] ?> zamítnuto</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="article-card p-4 text-center h-100 d-flex flex-column justify-content-center">
                <div class="text-muted text-uppercase small fw-bold mb-2">Průměrná délka řízení</div>
                <div class="display-4 fw-bold text-info"><?= $avg_days ?> <span class="fs-4">dní</span></div>
                <small class="text-muted mt-2">Od podání po finální rozhodnutí</small>
                <i class="fa-solid fa-stopwatch fa-2x text-info opacity-25 mt-3"></i>
            </div>
        </div>

        <div class="col-md-4">
            <?php 
            $active_count = $db_connection->query("SELECT COUNT(*) as c FROM articles WHERE status NOT IN ('prijato', 'zamitnuto')")->fetch_assoc()['c'];
            ?>
            <div class="article-card p-4 text-center h-100 d-flex flex-column justify-content-center" style="border-bottom: 4px solid #6e2bf9;">
                <div class="text-muted text-uppercase small fw-bold mb-2">Aktuálně v řešení</div>
                <div class="display-4 fw-bold text-primary"><?= $active_count ?></div>
                <small class="text-muted mt-2">Otevřené případy</small>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-8 mb-4">
            <div class="article-card p-4 h-100">
                <h5 class="text-white mb-4">Vývoj počtu článků (poslední rok)</h5>
                <canvas id="articlesChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="article-card p-4 h-100">
                <h5 class="text-white mb-4">Vytíženost redaktorů</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Redaktor</th>
                                <th class="text-end">Článků</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ed = $res_editors->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-white">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-dark rounded-circle text-secondary d-flex justify-content-center align-items-center me-2" style="width:30px; height:30px; font-size:0.8rem;">
                                                <?= substr($ed['jmeno'],0,1) . substr($ed['prijmeni'],0,1) ?>
                                            </div>
                                            <?= htmlspecialchars($ed['prijmeni'] . ' ' . substr($ed['jmeno'],0,1)) ?>.
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?php if($ed['count'] > 5): ?>
                                            <span class="text-danger"><?= $ed['count'] ?></span>
                                        <?php elseif($ed['count'] > 2): ?>
                                            <span class="text-warning"><?= $ed['count'] ?></span>
                                        <?php else: ?>
                                            <span class="text-success"><?= $ed['count'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-dark border-secondary mt-3 p-2 small text-muted">
                    <i class="fa-solid fa-info-circle me-1"></i> Počet aktivně přiřazených článků.
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data z PHP
const labels = <?= json_encode($months) ?>;
const dataSubmitted = <?= json_encode($data_submitted) ?>;
const dataPublished = <?= json_encode($data_published) ?>;

const ctx = document.getElementById('articlesChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Podané články',
                data: dataSubmitted,
                backgroundColor: 'rgba(110, 43, 249, 0.5)', // Fialová
                borderColor: '#6e2bf9',
                borderWidth: 1
            },
            {
                label: 'Publikované (Přijaté)',
                data: dataPublished,
                backgroundColor: 'rgba(34, 197, 94, 0.5)', // Zelená
                borderColor: '#22c55e',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: '#94a3b8' }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#2d3748' },
                ticks: { color: '#94a3b8' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8' }
            }
        }
    }
});
</script>

<?php require 'footer.php'; require 'db_close.php'; ?>