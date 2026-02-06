<?php
session_start();
include 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$pet_id = intval($_GET['id']);
$message = "";

// Fetch pet info with center info
$sql = "SELECT pets.*, adoption_centers.center_name, adoption_centers.email AS center_email 
        FROM pets 
        LEFT JOIN adoption_centers ON pets.center_id = adoption_centers.id 
        WHERE pets.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Pet not found.";
    exit;
}

$pet = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'adopter') {
        $message = "You must be logged in as an adopter to request adoption.";
    } else {
        $adopter_id = $_SESSION['user_id'];
        // Insert adoption request
        $insert_sql = "INSERT INTO adoption_requests (pet_id, adopter_id) VALUES (?, ?)";
        $stmt2 = $conn->prepare($insert_sql);
        $stmt2->bind_param("ii", $pet_id, $adopter_id);
        if ($stmt2->execute()) {
            $message = "Adoption request sent successfully! The adoption center will contact you.";
            // TODO: Send email notification to center here (later steps)
        } else {
            $message = "Failed to send adoption request.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Pet Details - PawConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-5">

<h2><?php echo htmlspecialchars($pet['name']); ?></h2>

<?php if ($message): ?>
  <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-6">
    <?php if ($pet['photo'] && file_exists('uploads/' . basename($pet['photo']))): ?>
      <img src="<?php echo 'uploads/' . basename($pet['photo']); ?>" class="img-fluid" alt="Pet Image" />
    <?php else: ?>
      <img src="https://via.placeholder.com/300" class="img-fluid" alt="No Image" />
    <?php endif; ?>
  </div>
  <div class="col-md-6">
    <p><strong>Type:</strong> <?php echo htmlspecialchars($pet['type']); ?></p>
    <p><strong>Age:</strong> <?php echo htmlspecialchars($pet['age']); ?></p>
    <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
    <p><strong>Description:</strong><br> <?php echo nl2br(htmlspecialchars($pet['description'])); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($pet['status']); ?></p>
    <?php if ($pet['status'] == 'for_sale'): ?>
      <p><strong>Price:</strong> $<?php echo number_format($pet['price'], 2); ?></p>
    <?php endif; ?>
    <p><strong>Adoption Center:</strong> <?php echo htmlspecialchars($pet['center_name']); ?></p>

    <form method="POST">
      <button type="submit" class="btn btn-success">Request Adoption</button>
    </form>
  </div>
</div>

<p><a href="index.php">Back to Home</a></p>

</body>
</html>
