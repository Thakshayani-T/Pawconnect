<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'center') {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');

$center_id = (int) $_SESSION['user_id'];

/* Center info */
$center = $conn->prepare("SELECT name, email FROM users WHERE id=?");
$center->bind_param("i", $center_id);
$center->execute();
$center = $center->get_result()->fetch_assoc();

/* Analytics */
$totalPets = $conn->query("SELECT COUNT(*) c FROM pets WHERE added_by=$center_id")->fetch_assoc()['c'];
$availablePets = $conn->query("SELECT COUNT(*) c FROM pets WHERE added_by=$center_id AND sale_status='Available'")->fetch_assoc()['c'];
$adoptedPets = $conn->query("SELECT COUNT(*) c FROM pets WHERE added_by=$center_id AND sale_status='Adopted'")->fetch_assoc()['c'];

/* Pets */
$pets = $conn->prepare("SELECT * FROM pets WHERE added_by=? ORDER BY created_at DESC");
$pets->bind_param("i", $center_id);
$pets->execute();
$pets = $pets->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Center Dashboard</title>
<link rel="stylesheet" href="../assets/css/center-dashboard.css">
</head>
<body>

<div class="dashboard-wrapper">

<!-- Sidebar -->
<aside class="sidebar">
    <h2>PawConnect</h2>
    <p class="role"><?= htmlspecialchars($center['name']); ?></p>

    <nav>
        <a class="active">Dashboard</a>
        <a href="profile_manage.php">Profile</a>
        <a href="../pets/add_pet.php">Add Pet</a>
        <a href="../logout.php" class="logout">Logout</a>
    </nav>
</aside>

<!-- Main Content -->
<main class="main-content">

<header class="top-header">
    <h2>Adoption Center Dashboard</h2>
    <span><?= htmlspecialchars($center['email']); ?></span>
</header>

<!-- Analytics Section -->
<section class="stats">
    <div class="stat-card">
        <h3><?= $totalPets; ?></h3>
        <p>Total Pets</p>
    </div>
    <div class="stat-card green">
        <h3><?= $availablePets; ?></h3>
        <p>Available</p>
    </div>
    <div class="stat-card red">
        <h3><?= $adoptedPets; ?></h3>
        <p>Adopted</p>
    </div>
</section>

<!-- Pets Section -->
<section class="panel">
<h3>Your Pets</h3>

<table>
<thead>
<tr>
    <th>Name</th>
    <th>Type</th>
    <th>Age</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php if ($pets->num_rows): while ($p = $pets->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($p['name']); ?></td>
    <td><?= htmlspecialchars($p['type']); ?></td>
    <td><?= htmlspecialchars($p['age']); ?></td>
    <td><?= htmlspecialchars($p['sale_status']); ?></td>
    <td>
        <a class="btn view" href="../pets/view_pet.php?id=<?= $p['id']; ?>">View</a>
        <a class="btn edit" href="../pets/edit_pet.php?id=<?= $p['id']; ?>">Edit</a>
        <a class="btn delete" href="../pets/delete_pet.php?id=<?= $p['id']; ?>" 
           onclick="return confirm('Are you sure you want to delete this pet?');">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; else: ?>
<tr>
    <td colspan="5" class="empty">No pets added</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</section>

</main>
</div>

</body>
</html>
