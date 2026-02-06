<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'adopter') {
    header('Location: ../login.php');
    exit;
}
require_once('../includes/db.php');

/* ================= PHPMailer ================= */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

/* ================= USER ================= */
$adopter_id = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$adopter_id")->fetch_assoc();

/* ================= DATA ================= */
$available_pets = $conn->query("SELECT * FROM pets WHERE sale_status='available' ORDER BY id DESC");
$adoptions = $conn->query("
    SELECT a.*, p.name AS pet_name, p.type, p.age, p.image 
    FROM adoptions a 
    JOIN pets p ON a.pet_id = p.id 
    WHERE a.adopter_id = $adopter_id
    ORDER BY a.requested_at DESC
");
$my_feedbacks = $conn->query("SELECT * FROM feedback WHERE user_id=$adopter_id ORDER BY created_at DESC");

/* ================= DASHBOARD COUNTS ================= */
$total_pets = $available_pets->num_rows;
$total_requests = $adoptions->num_rows;
$total_feedbacks = $my_feedbacks->num_rows;

/* ================= CHART DATA ================= */
$status_q = $conn->query("SELECT status, COUNT(*) AS total FROM adoptions WHERE adopter_id=$adopter_id GROUP BY status");
$status_labels = $status_data = [];
while($r = $status_q->fetch_assoc()){
    $status_labels[] = ucfirst($r['status']);
    $status_data[] = $r['total'];
}

$type_q = $conn->query("SELECT p.type, COUNT(*) AS total FROM adoptions a JOIN pets p ON a.pet_id=p.id WHERE a.adopter_id=$adopter_id GROUP BY p.type");
$type_labels = $type_data = [];
while($r = $type_q->fetch_assoc()){
    $type_labels[] = $r['type'];
    $type_data[] = $r['total'];
}

/* ================= FEEDBACK HANDLING ================= */
$feedback_msg='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['feedback_message'])) {
    $msg = trim($_POST['feedback_message']);
    if ($msg !== '') {
        $esc = $conn->real_escape_string($msg);
        $conn->query("INSERT INTO feedback(user_id,message) VALUES($adopter_id,'$esc')");

        // Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'thakshayini1999@gmail.com';
            $mail->Password = 'aajj lfxh ilcc prik';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($user['email'], $user['name']);
            $mail->addAddress('thakshayini1999@gmail.com', 'PawConnect Admin');
            $mail->isHTML(true);
            $mail->Subject = 'New Feedback from Adopter';
            $mail->Body = nl2br(htmlspecialchars($msg));
            $mail->send();
        } catch (Exception $e) {}

        $feedback_msg = '<div class="alert alert-success">Feedback sent successfully.</div>';
        $my_feedbacks = $conn->query("SELECT * FROM feedback WHERE user_id=$adopter_id ORDER BY created_at DESC");
    } else {
        $feedback_msg = '<div class="alert alert-danger">Feedback cannot be empty.</div>';
    }
}

/* ================= REPORT GENERATION ================= */
$report_data = [];
if(isset($_GET['generate_report'])){
    $month = isset($_GET['month']) && $_GET['month']!='' ? (int)$_GET['month'] : 0;
    $year = isset($_GET['year']) && $_GET['year']!='' ? (int)$_GET['year'] : 0;

    // Adoptions
    if($month && $year){
        $start = "$year-".str_pad($month,2,'0',STR_PAD_LEFT)."-01";
        $end = date('Y-m-d', strtotime("$start +1 month"));
        $stmt = $conn->prepare("SELECT a.*, p.name AS pet_name, p.type FROM adoptions a JOIN pets p ON a.pet_id=p.id WHERE a.adopter_id=? AND a.requested_at>=? AND a.requested_at<?");
        $stmt->bind_param('iss',$adopter_id,$start,$end);
    } elseif($year){
        $start = "$year-01-01";
        $end = date('Y-m-d', strtotime("$start +1 year"));
        $stmt = $conn->prepare("SELECT a.*, p.name AS pet_name, p.type FROM adoptions a JOIN pets p ON a.pet_id=p.id WHERE a.adopter_id=? AND a.requested_at>=? AND a.requested_at<?");
        $stmt->bind_param('iss',$adopter_id,$start,$end);
    } else {
        $stmt = $conn->prepare("SELECT a.*, p.name AS pet_name, p.type FROM adoptions a JOIN pets p ON a.pet_id=p.id WHERE a.adopter_id=?");
        $stmt->bind_param('i',$adopter_id);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while($r = $res->fetch_assoc()){
        $report_data[] = [
            'section'=>'Adoption',
            'pet_name'=>$r['pet_name'],
            'type'=>$r['type'],
            'status'=>$r['status'],
            'date'=>$r['requested_at'],
            'feedback'=>'',
            'feedback_date'=>''
        ];
    }
    $stmt->close();

    // Feedback
    if($month && $year){
        $start = "$year-".str_pad($month,2,'0',STR_PAD_LEFT)."-01";
        $end = date('Y-m-d', strtotime("$start +1 month"));
        $stmt = $conn->prepare("SELECT message, created_at FROM feedback WHERE user_id=? AND created_at>=? AND created_at<?");
        $stmt->bind_param('iss',$adopter_id,$start,$end);
    } elseif($year){
        $start = "$year-01-01";
        $end = date('Y-m-d', strtotime("$start +1 year"));
        $stmt = $conn->prepare("SELECT message, created_at FROM feedback WHERE user_id=? AND created_at>=? AND created_at<?");
        $stmt->bind_param('iss',$adopter_id,$start,$end);
    } else {
        $stmt = $conn->prepare("SELECT message, created_at FROM feedback WHERE user_id=?");
        $stmt->bind_param('i',$adopter_id);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while($f = $res->fetch_assoc()){
        $report_data[] = [
            'section'=>'Feedback',
            'pet_name'=>'',
            'type'=>'',
            'status'=>'',
            'date'=>'',
            'feedback'=>$f['message'],
            'feedback_date'=>$f['created_at']
        ];
    }
    $stmt->close();
}

/* ================= CSV DOWNLOAD ================= */
if(isset($_GET['export_csv']) && $_GET['export_csv']==1){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="adopter_report.csv"');

    $output = fopen('php://output','w');
    fputcsv($output,['Section','Pet Name','Type','Status','Requested Date','Feedback','Feedback Date']);
    foreach($report_data as $row){
        fputcsv($output,$row);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Adopter Dashboard - PawConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#f1f5f9;font-family:Poppins}
.sidebar{position:fixed;width:220px;height:100%;background:#004aad;color:#fff;padding:20px}
.sidebar a{display:block;color:#fff;margin:12px 0;text-decoration:none;font-weight:500}
.sidebar a:hover{opacity:.8}
main{margin-left:240px;padding:25px}
.section{display:none}
.section.active{display:block}
.card-stat{text-align:center;border-radius:10px}
.pet-card{background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin-bottom:20px}
.pet-card img{width:100%;height:180px;object-fit:cover}
.pet-card .content{padding:15px}
</style>
</head>
<body>
<div class="sidebar">
<h4>PawConnect</h4>
<a onclick="show('dashboard')">Dashboard</a>
<a onclick="show('available')">Available Pets</a>
<a onclick="show('adoptions')">My Requests</a>
<a onclick="show('feedback')">Feedback</a>
<a onclick="show('report')">Reports</a>
<a href="../logout.php">Logout</a>
</div>
<main>
<?php
if(!empty($_SESSION['success'])){echo "<div class='alert alert-success'>".$_SESSION['success']."</div>"; unset($_SESSION['success']);}
if(!empty($_SESSION['error'])){echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>"; unset($_SESSION['error']);}
?>
<!-- DASHBOARD -->
<div id="dashboard" class="section active">
<h2>Welcome, <?=htmlspecialchars($user['name'])?> 🐾</h2>
<div class="row my-4">
<div class="col-md-4"><div class="card card-stat p-3"><h5>Available Pets</h5><h3><?=$total_pets?></h3></div></div>
<div class="col-md-4"><div class="card card-stat p-3"><h5>Adoption Requests</h5><h3><?=$total_requests?></h3></div></div>
<div class="col-md-4"><div class="card card-stat p-3"><h5>Feedback Given</h5><h3><?=$total_feedbacks?></h3></div></div>
</div>
<div class="row">
<div class="col-md-6"><canvas id="statusChart"></canvas></div>
<div class="col-md-6"><canvas id="typeChart"></canvas></div>
</div>
</div>

<!-- AVAILABLE PETS -->
<div id="available" class="section">
<h3>Available Pets</h3>
<div class="row">
<?php while($p=$available_pets->fetch_assoc()): 
    $img_path = '../uploads/pets/' . $p['image'];
    if(empty($p['image']) || !file_exists($img_path)) $img_path='../uploads/no-image.png';
?>
<div class="col-md-4">
<div class="pet-card">
<img src="<?=$img_path?>" alt="<?=htmlspecialchars($p['name'])?>">
<div class="content">
<h5><?=htmlspecialchars($p['name'])?></h5>
<p><?=htmlspecialchars($p['description'])?></p>
<div class="d-flex justify-content-between">
    <!-- View Details -->
    <a href="view_pet.php?id=<?=$p['id']?>" class="btn btn-info btn-sm">View Details</a>
    <!-- Request Adoption -->
    <form method="POST" action="adopt_pet.php" class="ms-2">
        <input type="hidden" name="pet_id" value="<?=$p['id']?>">
        <button class="btn btn-primary btn-sm">Request Adoption</button>
    </form>
</div>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
</div>

<!-- ADOPTIONS -->
<div id="adoptions" class="section">
<h3>My Adoption Requests</h3>
<div class="row">
<?php while($a=$adoptions->fetch_assoc()): 
    $img_path = '../uploads/pets/' . $a['image'];
    if(empty($a['image']) || !file_exists($img_path)) $img_path='../uploads/no-image.png';
?>
<div class="col-md-4">
<div class="pet-card">
<img src="<?=$img_path?>" alt="<?=htmlspecialchars($a['pet_name'])?>">
<div class="content">
<h5><?=htmlspecialchars($a['pet_name'])?></h5>
<p>Type: <?=$a['type']?><br>Status: <?=ucfirst($a['status'])?><br>Requested: <?=$a['requested_at']?></p>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
</div>

<!-- FEEDBACK -->
<div id="feedback" class="section">
<h3>Feedback</h3>
<?=$feedback_msg?>
<form method="POST" class="mb-3">
<textarea name="feedback_message" class="form-control mb-2" rows="3"></textarea>
<button class="btn btn-success">Send Feedback</button>
</form>
<h5>Feedback History</h5>
<table class="table table-bordered">
<thead><tr><th>Message</th><th>Date</th></tr></thead>
<tbody>
<?php if($my_feedbacks->num_rows>0): while($f=$my_feedbacks->fetch_assoc()): ?>
<tr><td><?=nl2br(htmlspecialchars($f['message']))?></td><td><?=$f['created_at']?></td></tr>
<?php endwhile; else: ?><tr><td colspan="2">No feedback yet.</td></tr><?php endif; ?>
</tbody>
</table>
</div>

<!-- REPORT -->
<div id="report" class="section">
<h3>Generate Report</h3>
<form method="GET" class="row g-2 align-items-center mb-3">
<div class="col-auto"><label>Month</label><select name="month" class="form-select"><option value="">All</option>
<?php for($m=1;$m<=12;$m++): $sel = (isset($_GET['month']) && $_GET['month']==$m)?'selected':''; ?>
<option value="<?=$m?>" <?=$sel?>><?=date('F',mktime(0,0,0,$m,1))?></option>
<?php endfor; ?></select></div>
<div class="col-auto"><label>Year</label><select name="year" class="form-select"><option value="">All</option>
<?php for($y=date('Y');$y>=2019;$y--): $sel=(isset($_GET['year'])&&$_GET['year']==$y)?'selected':''; ?>
<option value="<?=$y?>" <?=$sel?>><?=$y?></option>
<?php endfor; ?></select></div>
<div class="col-auto mt-4"><button class="btn btn-primary" type="submit" name="generate_report">Generate Report</button></div>
<div class="col-auto mt-4"><button class="btn btn-success" type="submit" name="export_csv" value="1">Download CSV</button></div>
</form>

<?php if(count($report_data)>0): ?>
<h5>Report History</h5>
<table class="table table-bordered">
<thead><tr><th>Section</th><th>Pet Name</th><th>Type</th><th>Status</th><th>Requested Date</th><th>Feedback</th><th>Feedback Date</th></tr></thead>
<tbody>
<?php foreach($report_data as $r): ?>
<tr>
<td><?=$r['section']?></td>
<td><?=$r['pet_name']?></td>
<td><?=$r['type']?></td>
<td><?=$r['status']?></td>
<td><?=$r['date']?></td>
<td><?=nl2br(htmlspecialchars($r['feedback']))?></td>
<td><?=$r['feedback_date']?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

</main>
<script>
function show(id){document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));document.getElementById(id).classList.add('active');}

new Chart(document.getElementById('statusChart'),{
    type:'doughnut',
    data:{labels:<?=json_encode($status_labels)?>,datasets:[{data:<?=json_encode($status_data)?>,backgroundColor:['#2563eb','#10b981','#facc15','#f87171']}]}
});

new Chart(document.getElementById('typeChart'),{
    type:'bar',
    data:{labels:<?=json_encode($type_labels)?>,datasets:[{label:'Pets by Type',data:<?=json_encode($type_data)?>,backgroundColor:'#2563eb'}]},
    options:{scales:{y:{beginAtZero:true}}}
});
</script>
</body>
</html>
