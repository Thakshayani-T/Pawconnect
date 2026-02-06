<?php
session_start();
include('../includes/db.php');
include('../includes/header.php');

if (!isset($_GET['id'])) {
    echo "<div class='container mt-5'><p>Invalid pet ID.</p></div>";
    include('../includes/footer.php');
    exit;
}

$pet_id = intval($_GET['id']);
$sql = "SELECT pets.*, users.name AS center_name, users.contact AS center_contact 
        FROM pets 
        LEFT JOIN users ON pets.added_by = users.id 
        WHERE pets.id = $pet_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div class='container mt-5'><p>Pet not found.</p></div>";
    include('../includes/footer.php');
    exit;
}

$pet = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'adopter') {
    $adopter_id = $_SESSION['user_id'];
    $sql_check = "SELECT * FROM adoptions WHERE pet_id=$pet_id AND adopter_id=$adopter_id";
    $res_check = $conn->query($sql_check);
    if ($res_check->num_rows > 0) {
        echo '<div class="alert alert-info container mt-4">You already sent an adoption inquiry for this pet.</div>';
    } else {
        $conn->query("INSERT INTO adoptions (pet_id, adopter_id) VALUES ($pet_id, $adopter_id)");
        echo '<div class="alert alert-success container mt-4">Adoption inquiry sent!</div>';
    }
}
?>

<style>
    body {
        background: url('../uploads/bc1.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: Arial, sans-serif;
        color: #333;
    }
    .pet-container {
        background-color: rgba(255, 255, 255, 0.95);
        max-width: 700px;
        margin: 60px auto 80px;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.3);
    }
    .pet-container h2 {
        text-align: center;
        margin-bottom: 25px;
        font-weight: 700;
        color: #222;
    }
    .pet-image {
        display: block;
        max-width: 100%;
        max-height: 400px;
        margin: 0 auto 25px auto;
        border-radius: 10px;
        object-fit: cover;
        box-shadow: 0 3px 12px rgba(0,0,0,0.15);
    }
    .pet-info p {
        font-size: 1.1rem;
        margin-bottom: 12px;
    }
    .btn-back {
        display: block;
        margin-top: 30px;
    }
</style>

<div class="pet-container">
    <h2><?= htmlspecialchars($pet['name']) ?></h2>

    <?php 
    $image_filename = trim($pet['image']);
    // Debugging output (visible only in page source)
    echo "<!-- Debug: Image filename: " . htmlspecialchars($image_filename) . " -->\n";
    
    if (!empty($image_filename)): 
        // Use absolute URL assuming uploads is in web root
        $image_url = "/uploads/" . $image_filename;
    ?>
        <img src="<?= htmlspecialchars($image_url) ?>" alt="Pet Image" class="pet-image" />
    <?php else: ?>
        <p class="text-center text-muted">No image available for this pet.</p>
    <?php endif; ?>

    <div class="pet-info">
        <p><strong>Type:</strong> <?= htmlspecialchars($pet['type']) ?></p>
        <p><strong>Age:</strong> <?= htmlspecialchars($pet['age']) ?></p>
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($pet['description'])) ?></p>
        <p><strong>Sale Status:</strong> <?= htmlspecialchars($pet['sale_status']) ?></p>
        <p><strong>Adoption Center:</strong> <?= htmlspecialchars($pet['center_name']) ?> - Contact: <?= htmlspecialchars($pet['center_contact']) ?></p>
    </div>

    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'adopter'): ?>
        <form method="POST" class="mt-4">
            <button type="submit" class="btn btn-primary w-100">Send Adoption Inquiry</button>
        </form>
    <?php else: ?>
        <p class="text-muted mt-4 text-center">Please login as an adopter to send an adoption inquiry.</p>
    <?php endif; ?>

    <a href="../dashboard/center_dashboard.php" class="btn btn-outline-secondary btn-back">← Back to Dashboard</a>
</div>

<?php include('../includes/footer.php'); ?>
