<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
include('../includes/db.php');

$page = $_GET['page'] ?? 'dashboard';

// Attempt to fetch admin profile using session user id if available
$admin_profile = [
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'avatar' => null
];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name, email, avatar FROM users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows) {
            $r = $res->fetch_assoc();
            $admin_profile['name'] = $r['name'] ?? $admin_profile['name'];
            $admin_profile['email'] = $r['email'] ?? $admin_profile['email'];
            $admin_profile['avatar'] = $r['avatar'] ?? null;
        }
        $stmt->close();
    }
}

// Handle Approve/Reject for adoption requests
if ($page === 'adoptions' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adoption_id'], $_POST['action'])) {
    $adoption_id = (int)$_POST['adoption_id'];
    $action = $_POST['action'];

    // Get the pet_id associated with this adoption
    $stmt = $conn->prepare("SELECT pet_id FROM adoptions WHERE id=?");
    $stmt->bind_param('i', $adoption_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pet_id = $row['pet_id'];
        $stmt->close();

        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE adoptions SET status='approved', updated_at=NOW() WHERE id=?");
            $stmt->bind_param('i', $adoption_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE pets SET sale_status='adopted' WHERE id=?");
            $stmt->bind_param('i', $pet_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE adoptions SET status='rejected', updated_at=NOW() WHERE id=?");
            $stmt->bind_param('i', $adoption_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch data for dashboard and lists
$pet_types = $conn->query("SELECT type, COUNT(*) as total FROM pets GROUP BY type");
$types = $counts = [];
if ($pet_types) {
    while ($row = $pet_types->fetch_assoc()) {
        $types[] = $row['type'];
        $counts[] = (int)$row['total'];
    }
}

$adopters = $conn->query("SELECT id, name, email FROM users WHERE user_type='adopter' ORDER BY name ASC");
$centers = $conn->query("SELECT id, name, email FROM users WHERE user_type='center' ORDER BY name ASC");
$pets = $conn->query("SELECT pets.*, users.name AS center_name FROM pets LEFT JOIN users ON pets.added_by = users.id AND users.user_type='center' ORDER BY pets.id DESC");
$contacts = $conn->query("SELECT * FROM contact ORDER BY created_at DESC");
$feedbacks = $conn->query("
    SELECT f.id, f.message, f.created_at, u.name AS username
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
");
$adoption_requests = $conn->query("
    SELECT a.*, p.name AS pet_name, u.name AS adopter_name
    FROM adoptions a
    JOIN pets p ON a.pet_id = p.id
    JOIN users u ON a.adopter_id = u.id
    ORDER BY a.requested_at DESC
");

// Fetch quick KPIs for widgets
$total_adopters = $adopters ? $adopters->num_rows : 0;
$total_centers = $centers ? $centers->num_rows : 0;
$total_pets = $pets ? $pets->num_rows : 0;
$pending_adoptions = $conn->query("SELECT COUNT(*) as cnt FROM adoptions WHERE status='pending'")->fetch_assoc()['cnt'] ?? 0;
$total_contacts = $contacts ? $contacts->num_rows : 0;

// Handle report generation
    
// admin_dashboard.php
$page = $_GET['page'] ?? '';

if ($page === 'reports') {
    
    $month = $_GET['month'] ?? '';
    $year = $_GET['year'] ?? '';
    $type = $_GET['type'] ?? 'all';
    $center = $_GET['center'] ?? 'all';

    $conditions = [];
    if ($month && $year) {
        $conditions[] = "MONTH(a.requested_at)=" . intval($month) . " AND YEAR(a.requested_at)=" . intval($year);
    } elseif ($year) {
        $conditions[] = "YEAR(a.requested_at)=" . intval($year);
    }

    if ($type !== 'all') {
        $conditions[] = "p.type='" . $conn->real_escape_string($type) . "'";
    }

    if ($center !== 'all') {
        $conditions[] = "c.id=" . intval($center);
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

    $reportQuery = "
        SELECT a.id AS adoption_id, 
               p.name AS pet_name, 
               p.type AS pet_type, 
               u.name AS adopter_name, 
               c.name AS center_name, 
               a.status, 
               a.requested_at
        FROM adoptions a
        JOIN pets p ON a.pet_id = p.id
        JOIN users u ON a.adopter_id = u.id
        LEFT JOIN users c ON p.added_by = c.id
        $where
        ORDER BY a.requested_at DESC
    ";

    $reportData = $conn->query($reportQuery);

    // Download CSV if download clicked
    if (isset($_GET['download']) && $reportData) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="report.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen("php://output", "w");
        fputcsv($output, ["ID", "Pet", "Type", "Adopter", "Center", "Status", "Requested At"]);

        while ($row = $reportData->fetch_assoc()) {
            fputcsv($output, [
                $row['adoption_id'],
                $row['pet_name'],
                $row['pet_type'],
                $row['adopter_name'],
                $row['center_name'] ?? '',
                $row['status'],
                $row['requested_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - PawConnect</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --primary:#00bfa6;
      --accent:#0ea5a4;
      --sidebar:#1f2937;
      --muted:#6b7280;
      --card:#ffffff;
      --bg:#f3f6fb;
    }
    html,body{height:100%}
    body{font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:var(--bg); margin:0;}
    /* Sidebar */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      width: 260px;
      background: linear-gradient(180deg,#213547 0%, #17232b 100%);
      color: #dbeafe;
      padding: 24px 18px;
      overflow-y: auto;
    }
    .brand {
      display:flex;gap:12px;align-items:center;margin-bottom:18px;
    }
    .brand img{width:44px;height:44px;border-radius:8px;object-fit:cover}
    .brand h4{margin:0;color:#fff;font-weight:700}
    .sidebar nav a{display:flex;align-items:center;gap:12px;padding:10px 12px;color:#cbd5e1;border-radius:8px;text-decoration:none;font-weight:600;margin-bottom:6px}
    .sidebar nav a:hover,.sidebar nav a.active{background: rgba(255,255,255,0.04);color:#fff;box-shadow: inset 0 0 0 2px rgba(255,255,255,0.02)}
    .sidebar .small{font-size:12px;color:var(--muted);margin-top:6px}
    /* Main content */
    main.content { margin-left: 260px; padding: 22px 32px 80px; min-height:100vh;}
    .topbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
    .searchbox { max-width:520px; width:100%; display:flex; gap:8px; }
    .profile-btn { display:flex; gap:10px; align-items:center; cursor:pointer; }
    .profile-btn img{width:40px;height:40px;border-radius:50%;object-fit:cover}
    /* cards and widgets */
    .card.widget { border: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(18, 38, 63, 0.08); overflow:hidden; background:var(--card); }
    .widget .card-body { padding:18px; }
    .kpi { font-size:1.5rem; font-weight:700; color:#0f172a; }
    .kpi-sub { font-size:0.9rem; color:var(--muted); }
    .small-chart { height:50px; }
    /* right large income chart card */
    .income-card { min-height:280px; }
    /* newsletter */
    .newsletter { background: linear-gradient(135deg,#f97316,#f43f5e); color: #fff; border-radius:12px; padding:20px; }
    .newsletter input { border-radius:30px; }
    /* table styling */
    .table thead th { background:linear-gradient(90deg,#0f172a,#1e293b); color:#fff; border:none; }
    .table td, .table th { vertical-align:middle; }
    /* responsive */
    @media (max-width: 991px){
      .sidebar{width:80px;padding:16px}
      main.content{margin-left:80px;padding:16px}
      .brand h4 { display:none }
      .sidebar nav a span.label{display:none}
    }
  </style>
</head>
<body>

  <aside class="sidebar">
    <div class="brand">
      <img src="<?= htmlspecialchars($admin_profile['avatar'] ?: '../uploads/logo.png') ?>" alt="logo">
      <div>
        <h4>PawConnect</h4>
        <div class="small">Admin Panel</div>
      </div>
    </div>

    <nav class="mb-4">
      <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> <span class="label"> Dashboard</span></a>
      <a href="?page=adopters" class="<?= $page === 'adopters' ? 'active' : '' ?>"><i class="bi bi-people"></i> <span class="label"> Adopters</span></a>
      <a href="?page=centers" class="<?= $page === 'centers' ? 'active' : '' ?>"><i class="bi bi-building"></i> <span class="label"> Centers</span></a>
      <a href="?page=pets" class="<?= $page === 'pets' ? 'active' : '' ?>"><i class="bi bi-bug"></i> <span class="label"> Pets</span></a>
      <a href="?page=adoptions" class="<?= $page === 'adoptions' ? 'active' : '' ?>"><i class="bi bi-heart-pulse"></i> <span class="label"> Adoptions</span></a>
      <a href="?page=contacts" class="<?= $page === 'contacts' ? 'active' : '' ?>"><i class="bi bi-envelope"></i> <span class="label"> Contact</span></a>
      <a href="?page=feedbacks" class="<?= $page === 'feedbacks' ? 'active' : '' ?>"><i class="bi bi-chat-left-text"></i> <span class="label"> Feedback</span></a>
      <a href="?page=reports" class="<?= $page === 'reports' ? 'active' : '' ?>"><i class="bi bi-file-earmark-spreadsheet"></i> <span class="label"> Reports</span></a>
      <a href="../logout.php" class="text-danger mt-3"><i class="bi bi-box-arrow-right"></i> <span class="label"> Logout</span></a>
    </nav>

    <div class="mt-3 small">Quick stats</div>
    <div class="mt-2">
      <div style="display:flex;gap:8px;flex-direction:column">
        <div style="display:flex;justify-content:space-between;color:#cbd5e1">
          <small>Adopters</small><strong><?= intval($total_adopters) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;color:#cbd5e1">
          <small>Centers</small><strong><?= intval($total_centers) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;color:#cbd5e1">
          <small>Pets</small><strong><?= intval($total_pets) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;color:#cbd5e1">
          <small>Pending</small><strong><?= intval($pending_adoptions) ?></strong>
        </div>
      </div>
    </div>
  </aside>

  <main class="content">
    <div class="topbar">
      <div style="display:flex;gap:12px;align-items:center;">
        <h3 style="margin:0;color:#0f172a"><?= $page === 'dashboard' ? 'Dashboard' : ucfirst($page) ?></h3>
        <small class="text-muted">Home › <?= ucfirst($page) ?></small>
      </div>

      <div style="display:flex;align-items:center;gap:12px;">
        <div class="searchbox">
          <input class="form-control form-control-sm" placeholder="Search..." />
          <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        </div>

        <div class="profile-btn dropdown">
          <a class="d-flex align-items-center text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?= htmlspecialchars($admin_profile['avatar'] ?: '../uploads/avatar_default.png') ?>" alt="avatar">
            <div style="text-align:left;">
              <div style="font-weight:700;color:#0f172a;font-size:0.95rem"><?= htmlspecialchars($admin_profile['name']) ?></div>
              <div style="font-size:0.8rem;color:var(--muted)"><?= htmlspecialchars($admin_profile['email']) ?></div>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="?page=profile"><i class="bi bi-person"></i> Profile</a></li>
            <li><a class="dropdown-item" href="?page=settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <?php if ($page === 'dashboard'): ?>
      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-3">
          <div class="card widget">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="kpi"><?= intval($total_adopters) ?></div>
                  <div class="kpi-sub">Total Adopters</div>
                </div>
                <div>
                  <i class="bi bi-people" style="font-size:28px;color:var(--accent)"></i>
                </div>
              </div>
              <div class="small-chart mt-2">
                <canvas id="mini1"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-3">
          <div class="card widget">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="kpi"><?= intval($total_centers) ?></div>
                  <div class="kpi-sub">Adoption Centers</div>
                </div>
                <div>
                  <i class="bi bi-building" style="font-size:28px;color:#f59e0b"></i>
                </div>
              </div>
              <div class="small-chart mt-2"><canvas id="mini2"></canvas></div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-3">
          <div class="card widget">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="kpi"><?= intval($total_pets) ?></div>
                  <div class="kpi-sub">Pets Listed</div>
                </div>
                <div>
                  <i class="bi bi-bug" style="font-size:28px;color:#ef4444"></i>
                </div>
              </div>
              <div class="small-chart mt-2"><canvas id="mini3"></canvas></div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-3">
          <div class="card widget">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="kpi"><?= intval($pending_adoptions) ?></div>
                  <div class="kpi-sub">Pending Adoptions</div>
                </div>
                <div>
                  <i class="bi bi-clock-history" style="font-size:28px;color:#6366f1"></i>
                </div>
              </div>
              <div class="small-chart mt-2"><canvas id="mini4"></canvas></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
          <div class="card income-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h5 class="mb-0">Monthly Activity</h5>
                  <small class="text-muted">Adoptions & requests trend</small>
                </div>
                <div>
                  <button class="btn btn-sm btn-outline-secondary">Last 30 days</button>
                </div>
              </div>
              <canvas id="incomeChart" style="height:240px;"></canvas>
            </div>
          </div>
        </div>

        <div class="col-12 col-xl-4">
          <div class="card">
            <div class="card-body">
              <h6 class="mb-3">Newsletter</h6>
              <div class="newsletter">
                <h5 style="margin:0 0 8px 0">Subscribe</h5>
                <p style="margin:0 0 12px 0">Get updates about new pets & adoptions.</p>
                <div class="input-group">
                  <input type="email" class="form-control" placeholder="email address">
                  <button class="btn btn-white">Subscribe</button>
                </div>
              </div>

              <div class="mt-4">
                <h6 class="mb-2">Overall Sales (example)</h6>
                <canvas id="pieChart" style="height:140px;"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <h5 class="mt-3 mb-2">Pets by Type</h5>
      <div class="card mb-4">
        <div class="card-body">
          <canvas id="petChart" style="height:160px;"></canvas>
        </div>
      </div>

    <?php elseif ($page === 'adopters'): ?>

      <h4 class="mb-3">Adopters List</h4>
      <div class="card">
        <div class="card-body">
          <table class="table table-striped table-bordered align-middle">
            <thead>
              <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if ($adopters && $adopters->num_rows > 0): ?>
                <?php while ($row = $adopters->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                      <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                      <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this adopter?')">Delete</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center">No adopters found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'centers'): ?>

      <h4 class="mb-3">Adoption Centers</h4>
      <div class="card">
        <div class="card-body">
          <table class="table table-striped table-bordered align-middle">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($centers && $centers->num_rows > 0): ?>
              <?php while ($row = $centers->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td>
                    <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this center?')">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center">No adoption centers found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'pets'): ?>

      <h4 class="mb-3">Pets List</h4>
      <div class="card">
        <div class="card-body">
          <table class="table table-hover table-bordered">
            <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Age</th><th>Center</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($pets && $pets->num_rows > 0): ?>
              <?php while ($row = $pets->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['type']) ?></td>
                  <td><?= htmlspecialchars($row['age']) ?></td>
                  <td><?= htmlspecialchars($row['center_name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($row['sale_status'] ?? 'available') ?></td>
                  <td>
                    <a href="edit_pet.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete_pet.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this pet?')">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center">No pets found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'adoptions'): ?>

      <h4 class="mb-3">Adoption Requests</h4>
      <div class="card">
        <div class="card-body">
          <table class="table table-striped table-bordered">
            <thead><tr><th>ID</th><th>Pet</th><th>Adopter</th><th>Status</th><th>Requested At</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($adoption_requests && $adoption_requests->num_rows > 0): ?>
              <?php while ($row = $adoption_requests->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['pet_name']) ?></td>
                  <td><?= htmlspecialchars($row['adopter_name']) ?></td>
                  <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                  <td><?= htmlspecialchars($row['requested_at']) ?></td>
                  <td>
                    <?php if ($row['status'] === 'pending'): ?>
                      <form method="POST" style="display:inline-block">
                        <input type="hidden" name="adoption_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                      </form>
                      <form method="POST" style="display:inline-block">
                        <input type="hidden" name="adoption_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                      </form>
                    <?php else: ?>
                      <span class="badge <?= $row['status'] === 'approved' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($row['status']) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center">No adoption requests found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'contacts'): ?>

      <h4 class="mb-3">Contact Messages</h4>
      <div class="card">
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Received At</th></tr></thead>
            <tbody>
            <?php if ($contacts && $contacts->num_rows > 0): ?>
              <?php while ($row = $contacts->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                  <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center">No contact messages found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'feedbacks'): ?>

      <h4 class="mb-3">User Feedbacks</h4>
      <div class="card">
        <div class="card-body">
          <table class="table table-bordered">
            <thead><tr><th>ID</th><th>User</th><th>Message</th><th>Created At</th></tr></thead>
            <tbody>
            <?php if ($feedbacks && $feedbacks->num_rows > 0): ?>
              <?php while ($row = $feedbacks->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                  <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                  <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center">No feedbacks found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'reports'): ?>

      <h4 class="mb-3">Generate Reports</h4>
      <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="page" value="reports">
        <div class="col-md-2">
          <label class="form-label">Month</label>
          <select name="month" class="form-select">
            <option value="">All</option>
            <?php for ($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= (isset($_GET['month']) && $_GET['month']==$m) ? 'selected' : '' ?>><?= date("F", mktime(0,0,0,$m,1)) ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Year</label>
          <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($_GET['year'] ?? '') ?>" placeholder="YYYY">
        </div>
        <div class="col-md-2">
          <label class="form-label">Pet Type</label>
          <select name="type" class="form-select">
            <option value="all">All</option>
            <?php
            $typesList = $conn->query("SELECT DISTINCT type FROM pets");
            while ($t = $typesList->fetch_assoc()):
            ?>
              <option value="<?= htmlspecialchars($t['type']) ?>" <?= (($_GET['type'] ?? '')==$t['type']) ? 'selected' : '' ?>><?= htmlspecialchars($t['type']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Center</label>
          <select name="center" class="form-select">
            <option value="all">All</option>
            <?php
            $centerList = $conn->query("SELECT id, name FROM users WHERE user_type='center'");
            while ($c = $centerList->fetch_assoc()):
            ?>
              <option value="<?= $c['id'] ?>" <?= (($_GET['center'] ?? '')==$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" name="generate" value="1" class="btn btn-primary me-2">Generate</button>
          <?php if (isset($reportData)): ?>
            <button type="submit" name="download" value="1" class="btn btn-success">Download CSV</button>
          <?php endif; ?>
        </div>
      </form>

      <?php if (isset($reportData)): ?>
        <div class="card">
          <div class="card-body">
            <table class="table table-bordered table-striped">
              <thead><tr><th>ID</th><th>Pet</th><th>Type</th><th>Adopter</th><th>Center</th><th>Status</th><th>Requested At</th></tr></thead>
              <tbody>
              <?php if ($reportData->num_rows > 0): ?>
                <?php while ($row = $reportData->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['adoption_id']) ?></td>
                    <td><?= htmlspecialchars($row['pet_name']) ?></td>
                    <td><?= htmlspecialchars($row['pet_type']) ?></td>
                    <td><?= htmlspecialchars($row['adopter_name']) ?></td>
                    <td><?= htmlspecialchars($row['center_name']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['requested_at']) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center">No records found for selected filters.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    <?php elseif ($page === 'profile'): ?>

      <h4 class="mb-3">Admin Profile</h4>
      <div class="row">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body text-center">
              <img src="<?= htmlspecialchars($admin_profile['avatar'] ?: '../uploads/avatar_default.png') ?>" alt="avatar" style="width:120px;height:120px;border-radius:12px;object-fit:cover">
              <h5 class="mt-3"><?= htmlspecialchars($admin_profile['name']) ?></h5>
              <p class="text-muted"><?= htmlspecialchars($admin_profile['email']) ?></p>
              <a href="edit_profile.php" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card">
            <div class="card-body">
              <h6>About</h6>
              <p class="text-muted">This admin account can manage adopters, centers, pets, adoptions, feedback and reports.</p>
              <hr>
              <h6>Account Details</h6>
              <ul class="list-unstyled">
                <li><strong>Name:</strong> <?= htmlspecialchars($admin_profile['name']) ?></li>
                <li><strong>Email:</strong> <?= htmlspecialchars($admin_profile['email']) ?></li>
                <li><strong>Role:</strong> Admin</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>

      <div class="card">
        <div class="card-body">
          <h5>Page not found</h5>
          <p>The requested page "<?= htmlspecialchars($page) ?>" does not exist in the dashboard.</p>
        </div>
      </div>

    <?php endif; ?>

  </main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // small mini charts (dummy trends) used in top KPIs
  const rand = ()=> Math.round(Math.random()*30)+5;
  const makeSpark = (id) => {
    const ctx = document.getElementById(id);
    if(!ctx) return;
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: Array.from({length:8},(_,i)=>i+1),
        datasets:[{ data: Array.from({length:8},()=>rand()), fill:true, tension:0.4, borderWidth:1, pointRadius:0 }]
      },
      options:{ responsive:true, scales:{x:{display:false}, y:{display:false}}, plugins:{legend:{display:false}, tooltip:{enabled:false}} }
    });
  }

  makeSpark('mini1'); makeSpark('mini2'); makeSpark('mini3'); makeSpark('mini4');

  // income chart (example)
  const incomeCtx = document.getElementById('incomeChart')?.getContext('2d');
  if (incomeCtx) {
    new Chart(incomeCtx, {
      type:'line',
      data:{
        labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep'],
        datasets:[
          { label:'Adoption Requests', data: [20,35,28,40,32,50,45,55,48], fill:true, tension:0.4, borderColor:'#06b6d4', backgroundColor:'rgba(6,182,212,0.06)' },
          { label:'Approved Adoptions', data: [10,18,15,22,18,30,27,33,31], fill:true, tension:0.4, borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,0.06)' }
        ]
      },
      options:{ responsive:true, plugins:{legend:{display:true}} }
    });
  }

  // pet type bar chart
  const petDataLabels = <?= json_encode($types) ?>;
  const petDataCounts = <?= json_encode($counts) ?>;
  const petCtx = document.getElementById('petChart')?.getContext('2d');
  if (petCtx) {
    new Chart(petCtx, {
      type:'bar',
      data:{
        labels: petDataLabels,
        datasets:[{ label:'Number of Pets', data: petDataCounts, borderRadius:6 }]
      },
      options:{ responsive:true, plugins:{legend:{display:false}} }
    });
  }

  // pie chart example
  const pieCtx = document.getElementById('pieChart')?.getContext('2d');
  if (pieCtx) {
    new Chart(pieCtx, {
      type:'pie',
      data:{
        labels:['Dogs','Cats','Others'],
        datasets:[{ data: [55,30,15] }]
      },
      options:{ responsive:true }
    });
  }
</script>

</body>
</html>
