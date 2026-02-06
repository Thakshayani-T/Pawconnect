<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'center') {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');
include('../includes/header.php');

if (!isset($_GET['id'])) {
    echo '<div class="container mt-5"><p>Invalid Pet ID.</p></div>';
    include('../includes/footer.php');
    exit;
}

$pet_id = intval($_GET['id']);
$center_id = $_SESSION['user_id'];

// Fetch pet details and verify owner
$sql = "SELECT * FROM pets WHERE id = $pet_id AND added_by = $center_id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    echo '<div class="container mt-5"><p>Pet not found or you do not have permission to edit this pet.</p></div>';
    include('../includes/footer.php');
    exit;
}

$pet = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $age = intval($_POST['age']);
    $description = $conn->real_escape_string($_POST['description']);
    $sale_status = $conn->real_escape_string($_POST['sale_status']);

    // Handle image upload if new image provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $target_file = uniqid() . "." . $imageFileType;
        $upload_path = $target_dir . $target_file;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Delete old image
            if ($pet['image'] && file_exists($target_dir . $pet['image'])) {
                unlink($target_dir . $pet['image']);
            }
            $image_sql = ", image='$target_file'";
        } else {
            echo '<div class="alert alert-danger">Failed to upload new image.</div>';
            $image_sql = "";
        }
    } else {
        $image_sql = "";
    }

    $update_sql = "UPDATE pets SET
        name='$name',
        type='$type',
        age=$age,
        description='$description',
        sale_status='$sale_status'
        $image_sql
        WHERE id=$pet_id AND added_by=$center_id";

    if ($conn->query($update_sql)) {
        echo '<div class="alert alert-success">Pet updated successfully.</div>';
        // Refresh pet details after update
        $result = $conn->query($sql);
        $pet = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Error updating pet: ' . $conn->error . '</div>';
    }
}
?>

<div class="container mt-5" style="max-width: 600px;">
    <h2>Edit Pet</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3"><label>Pet Name</label><input type="text" name="name" required class="form-control" value="<?= htmlspecialchars($pet['name']) ?>" /></div>
        <div class="mb-3"><label>Type</label><input type="text" name="type" required class="form-control" value="<?= htmlspecialchars($pet['type']) ?>" /></div>
        <div class="mb-3"><label>Age</label><input type="number" name="age" required class="form-control" value="<?= htmlspecialchars($pet['age']) ?>" /></div>
        <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" required><?= htmlspecialchars($pet['description']) ?></textarea></div>
        <div class="mb-3">
            <label>Current Image</label><br>
            <img src="../uploads/<?= htmlspecialchars($pet['image']) ?>" alt="Pet Image" width="150" />
        </div>
        <div class="mb-3"><label>Change Image</label><input type="file" name="image" accept="image/*" class="form-control"/></div>
        <div class="mb-3">
            <label>Sale Status</label>
            <select name="sale_status" class="form-select" required>
                <option value="Available" <?= $pet['sale_status']=='Available' ? 'selected' : '' ?>>Available</option>
                <option value="Sold" <?= $pet['sale_status']=='Sold' ? 'selected' : '' ?>>Sold</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Pet</button>
        <a href="view_pet.php?id=<?= $pet['id'] ?>" class="btn btn-secondary ms-2">Cancel</a>
    </form>

    <a href="../dashboard/center_dashboard.php" class="btn btn-outline-secondary mt-3">Back to Dashboard</a>
</div>

<?php include('../includes/footer.php'); ?>
