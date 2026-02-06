<?php
include('includes/db.php');
include('includes/header.php');
?>

<style>
/* Reset & Base */
body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4faff; }
/* Hero Section */
.hero-section {
    position: relative;
    background: url('/pawconnect/uploads/bc4.jpg') no-repeat center center;
    background-size: cover;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    height: 100vh;
    padding: 0 20px;
    color: #fff;
    overflow: hidden;
}
.hero-section::before {
    content: '';
    position: absolute; top:0; left:0; width:100%; height:100%;
    background: linear-gradient(to bottom right, rgba(0,0,0,0.7), rgba(0,0,0,0.4));
    z-index:0;
}
.hero-section h1, .hero-section p, .hero-section .cta-btn { position: relative; z-index:1; }
.hero-section h1 { font-size:62px; font-weight:900; color:#fff; margin-bottom:20px; text-shadow:2px2px12px rgba(0,0,0,0.6);}
.hero-section p { font-size:22px; color:#f0f0f0; max-width:700px; margin-bottom:40px; line-height:1.6; text-shadow:1px1px6px rgba(0,0,0,0.5);}
.hero-section .cta-btn { background-color:#1d3557; color:#fff; padding:16px 45px; border-radius:40px; font-weight:700; font-size:18px; text-decoration:none; transition:all 0.4s ease;}
.hero-section .cta-btn:hover { background-color:#457b9d; transform:scale(1.05); }
/* Section Titles */
.pet-section { max-width:1200px; margin:80px auto; padding:0 20px; }
.pet-section h2 { text-align:center; font-size:38px; font-weight:700; color:#1d3557; margin-bottom:50px; border-bottom:3px solid #1d3557; display:inline-block; padding-bottom:8px;}
.pet-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap:30px; }
/* Pet Card */
.pet-card { background:#fff; border-radius:25px; box-shadow:0 10px 25px rgba(0,0,0,0.12); overflow:hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; text-align:center; }
.pet-card:hover { transform:translateY(-8px) scale(1.02); box-shadow:0 15px 30px rgba(0,0,0,0.2);}
.pet-card img { width:100%; height:220px; object-fit:cover; border-bottom:3px solid #1d3557; }
.card-content { padding:20px; }
.pet-card h5 { font-size:21px; font-weight:700; color:#1d3557; margin-bottom:10px;}
.pet-card p { font-size:15px; color:#555; margin-bottom:15px;}
.btn-container { display:flex; justify-content:center; gap:12px; }
.btn-primary { background-color:#1d3557; color:#fff; padding:10px 22px; border-radius:20px; font-weight:600; text-decoration:none; }
.btn-primary:hover { background-color:#457b9d; }
.btn-secondary { background-color:#fff; border:2px solid #1d3557; color:#1d3557; padding:10px 22px; border-radius:20px; font-weight:600; text-decoration:none; }
.btn-secondary:hover { background-color:#1d3557; color:#fff; }
/* Sold (Adopted) Pets */
.pet-card.sold { opacity:0.8;}
.pet-card.sold h5 { color:#7a7a7a; }
/* Stats Section */
.stats-section { max-width:1100px; margin:80px auto; padding:40px 20px; display:flex; justify-content:space-around; gap:20px; flex-wrap:wrap;}
.stat-card { background:#1d3557; color:#fff; flex:1 1 220px; padding:30px 20px; border-radius:20px; text-align:center;}
.stat-card:hover { transform:translateY(-8px); box-shadow:0 10px 20px rgba(0,0,0,0.3);}
.stat-card h3 { font-size:32px; margin-bottom:10px; font-weight:700;}
.stat-card p { font-size:18px; font-weight:500;}
@media(max-width:768px){ .hero-section h1{ font-size:40px;} .hero-section p{ font-size:18px;} .hero-section .cta-btn{ font-size:16px; padding:14px 32px;} .stats-section{ flex-direction:column; align-items:center;} }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <h1>Welcome to PawConnect Web Platform</h1>
    <p>Connecting loving hearts with adorable pets. Find your next companion and give them a forever home filled with care and love.</p>
    <a href="#adopt" class="cta-btn">Explore Pets</a>
</section>

<!-- Stats Section -->
<?php
$total_pets = $conn->query("SELECT COUNT(*) as total FROM pets")->fetch_assoc()['total'];
$available_pets = $conn->query("SELECT COUNT(*) as total FROM pets p LEFT JOIN adoptions a ON p.id = a.pet_id AND a.status='approved' WHERE a.id IS NULL")->fetch_assoc()['total'];
$adopted_pets = $conn->query("SELECT COUNT(*) as total FROM adoptions WHERE status='approved'")->fetch_assoc()['total'];
$total_adopters = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type='adopter'")->fetch_assoc()['total'];
?>
<section class="stats-section">
    <div class="stat-card">
        <h3><?= $total_pets ?></h3>
        <p>Total Pets</p>
    </div>
    <div class="stat-card">
        <h3><?= $available_pets ?></h3>
        <p>Available Pets</p>
    </div>
    <div class="stat-card">
        <h3><?= $adopted_pets ?></h3>
        <p>Adopted Pets</p>
    </div>
    <div class="stat-card">
        <h3><?= $total_adopters ?></h3>
        <p>Total Adopters</p>
    </div>
</section>

<!-- Available Pets -->
<section id="adopt" class="pet-section">
    <h2>Available Pets</h2>
    <div class="pet-grid">
        <?php
        $pet_image_base_url = '/pawconnect/uploads/pets/';
        $sql_available = "
            SELECT p.*
            FROM pets p
            LEFT JOIN adoptions a ON p.id = a.pet_id AND a.status = 'approved'
            WHERE a.id IS NULL
            ORDER BY p.id DESC
        ";
        $result_available = $conn->query($sql_available);
        if ($result_available && $result_available->num_rows > 0) {
            while ($pet = $result_available->fetch_assoc()) {
                $imageUrl = $pet_image_base_url . htmlspecialchars($pet['image']);
                if (empty($pet['image']) || !file_exists($_SERVER['DOCUMENT_ROOT'] . $imageUrl)) {
                    $imageUrl = '/pawconnect/uploads/no-image.png';
                }
                ?>
                <div class="pet-card">
                    <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                    <div class="card-content">
                        <h5><?= htmlspecialchars($pet['name']) ?></h5>
                        <p><?= htmlspecialchars(mb_substr($pet['description'], 0, 80)) ?>...</p>
                        <div class="btn-container">
                            <a href="pets/view_pet.php?id=<?= urlencode($pet['id']) ?>" class="btn-primary">View Details</a>
                            <a href="login.php" class="btn-secondary">Book Pet</a>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p style="text-align:center; color:#1d3557;">No pets available at the moment.</p>';
        }
        ?>
    </div>
</section>

<!-- Adopted Pets -->
<section class="pet-section">
    <h2>Adopted Pets</h2>
    <div class="pet-grid">
        <?php
        $sql_sold = "
            SELECT p.*
            FROM pets p
            INNER JOIN adoptions a ON p.id = a.pet_id
            WHERE a.status = 'approved'
            ORDER BY p.id DESC
        ";
        $result_sold = $conn->query($sql_sold);
        if ($result_sold && $result_sold->num_rows > 0) {
            while ($pet = $result_sold->fetch_assoc()) {
                $imageUrl = $pet_image_base_url . htmlspecialchars($pet['image']);
                if (empty($pet['image']) || !file_exists($_SERVER['DOCUMENT_ROOT'] . $imageUrl)) {
                    $imageUrl = '/pawconnect/uploads/no-image.png';
                }
                ?>
                <div class="pet-card sold">
                    <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                    <div class="card-content">
                        <h5><?= htmlspecialchars($pet['name']) ?> (Adopted)</h5>
                        <p><?= htmlspecialchars(mb_substr($pet['description'], 0, 80)) ?>...</p>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p style="text-align:center; color:#1d3557;">No adopted pets to show yet.</p>';
        }
        ?>
    </div>
</section>

<?php include('includes/footer.php'); ?>
