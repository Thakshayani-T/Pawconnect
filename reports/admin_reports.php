<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');
include('../includes/header.php');

// Query: Pets count by type
$sql = "SELECT type, COUNT(*) as total FROM pets GROUP BY type";
$result = $conn->query($sql);

$pet_types = [];
$pet_counts = [];
while ($row = $result->fetch_assoc()) {
    $pet_types[] = $row['type'];
    $pet_counts[] = $row['total'];
}

// Query: Pets sold vs available
$sql2 = "SELECT sale_status, COUNT(*) as total FROM pets GROUP BY sale_status";
$result2 = $conn->query($sql2);

$sale_statuses = [];
$sale_counts = [];
while ($row = $result2->fetch_assoc()) {
    $sale_statuses[] = $row['sale_status'];
    $sale_counts[] = $row['total'];
}

// Query: Total users by type
$sql3 = "SELECT user_type, COUNT(*) as total FROM users GROUP BY user_type";
$result3 = $conn->query($sql3);

$user_types = [];
$user_counts = [];
while ($row = $result3->fetch_assoc()) {
    $user_types[] = $row['user_type'];
    $user_counts[] = $row['total'];
}
?>

<div class="container mt-5">
    <h2>Admin Reports</h2>

    <div class="mb-5">
        <h4>Pets by Type</h4>
        <canvas id="petsTypeChart" style="max-width:600px;"></canvas>
    </div>

    <div class="mb-5">
        <h4>Sale Status</h4>
        <canvas id="saleStatusChart" style="max-width:600px;"></canvas>
    </div>

    <div class="mb-5">
        <h4>User Types</h4>
        <canvas id="userTypeChart" style="max-width:600px;"></canvas>
    </div>
</div>

<script src="../assets/js/chart.min.js"></script>
<script>
    const ctx1 = document.getElementById('petsTypeChart').getContext('2d');
    const petsTypeChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode($pet_types) ?>,
            datasets: [{
                label: 'Number of Pets',
                data: <?= json_encode($pet_counts) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });

    const ctx2 = document.getElementById('saleStatusChart').getContext('2d');
    const saleStatusChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?= json_encode($sale_statuses) ?>,
            datasets: [{
                data: <?= json_encode($sale_counts) ?>,
                backgroundColor: ['#4caf50', '#f44336']
            }]
        }
    });

    const ctx3 = document.getElementById('userTypeChart').getContext('2d');
    const userTypeChart = new Chart(ctx3, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($user_types) ?>,
            datasets: [{
                data: <?= json_encode($user_counts) ?>,
                backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56']
            }]
        }
    });
</script>

<?php include('../includes/footer.php'); ?>
