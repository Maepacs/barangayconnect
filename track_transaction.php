

<?php
session_start();
require_once "cons/config.php";

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$tracking_number = trim($_GET['track'] ?? '');
$result = null;
$status_color = "default";
$type = '';
$category = '';
$progress_color = "#007bff"; // default blue
$date_filed = '';

if ($tracking_number) {
    $sql = "
        (SELECT tracking_number, status, complaint_type AS specific_type, date_filed AS date_filed, 'complaint' AS category FROM complaints WHERE tracking_number = ?)
        UNION
        (SELECT tracking_number, status, document_type AS specific_type, date_requested AS date_filed, 'document request' AS category FROM document_request WHERE tracking_number = ?)
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $tracking_number, $tracking_number);
    $stmt->execute();
    $query = $stmt->get_result();

    if ($query->num_rows > 0) {
        $result = $query->fetch_assoc();
        $status = strtolower(trim($result['status']));
        $type = strtolower(trim($result['specific_type']));
        $category = $result['category'];
        $date_filed = $result['date_filed'];

        // Status badge color
        switch ($status) {
            case 'pending': $status_color = 'pending'; break;
            case 'in progress': $status_color = 'progress'; break;
            case 'resolved':
            case 'approved': $status_color = 'resolved'; break;
            case 'rejected': $status_color = 'rejected'; break;
            case 'released': $status_color = 'released'; break;
            default: $status_color = 'default';
        }

        // Timeline line color based on status
        if ($category === 'complaint') {
            switch ($status) { 
                case 'pending': $progress_color = "#ffc107"; break;      // yellow
                case 'in progress': $progress_color = "#007bff"; break;  // blue
                case 'resolved': $progress_color = "#28a745"; break;     // green
                case 'rejected': $progress_color = "#dc3545"; break;     // 
                default: $progress_color = "#6c757d";                     // gray
            }
        } else { // document request
            switch ($status) {
                case 'pending': $progress_color = "#ffc107"; break;      // yellow
                case 'approved': $progress_color = "#007bff"; break;     // blue
                case 'released': $progress_color = "#28a745"; break;     // green
                case 'rejected': $progress_color = "#dc3545"; break;     // red
                default: $progress_color = "#6c757d";                     // gray
            }
        }
    }
}
?>



  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Track Request</title>
  <link rel="icon" href="assets/images/BG_logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;   background: url("assets/images/bg.png") no-repeat center center; margin:0; display:flex; flex-direction:column; min-height:100vh;}
  header { background:#343A40; color:#fff; padding:15px 50px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:100; box-shadow:0 2px 8px rgba(0,0,0,0.3);}
  header .logo { display:flex; align-items:center; gap:10px;}
  header .logo img { width:45px; height:45px; border-radius:50%; border:2px solid yellow;}
  header nav a { color:#ddd; margin-left:20px; text-decoration:none; font-weight:500; transition:0.3s;}
  header nav a:hover { color:#4a90e2;}
  .track-container { flex:1; display:flex; justify-content:center; align-items:center; flex-direction:column; padding:80px 20px;}
  .track-box { background:rgba(237, 238, 217, 0.83); padding:40px 30px; border-radius:15px; box-shadow:0 3px 10px rgba(0,0,0,0.1); width:100%; max-width:650px; text-align:center;}
  input[type="text"] { width:80%; padding:10px; border-radius:6px; border:1px solid #ccc; margin-bottom:20px; font-size:16px;}
  button { background:#4a90e2; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.3s;}
  button:hover { background:#357ab7;}
  .status-badge { display:inline-block; padding:6px 14px; border-radius:20px; color:white; font-weight:bold; font-size:14px;}
  .status-badge.pending { background:#ffc107; color:#333; }
  .status-badge.progress { background:#007bff; }
  .status-badge.resolved { background:#28a745; }
  .status-badge.rejected { background:#dc3545; }
  .status-badge.released { background:#6f42c1; }
  .status-badge.default { background:#6c757d; }

  /* Timeline */
  .timeline { display:flex; justify-content:space-between; position:relative; margin:40px 0; }
  .timeline::before { content:""; position:absolute; top:50%; left:0; width:100%; height:6px; background-color:#e0e0e0; border-radius:3px; transform:translateY(-50%); z-index:0; }
  .timeline-progress { 
    content:""; 
    position:absolute; 
    top:50%; 
    left:0; 
    height:6px; 
    background-color:<?= $progress_color ?>; 
    border-radius:3px; 
    transform:translateY(-50%); 
    z-index:1; 
    width:0; 
    animation: progressFill 1s forwards; 
}

  .timeline-step { position:relative; text-align:center; flex:1; z-index:2; }
  .timeline-step .circle { width:45px; height:45px; border-radius:50%; background:#ccc; margin:0 auto 8px; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:18px; transition:all 0.3s ease; }
  .timeline-step.completed .circle { background:<?= $progress_color ?>; box-shadow:0 0 10px <?= $progress_color ?>; }
  .timeline-step.active.rejected-step .circle { background:#dc3545; }
  .timeline-step small { display:block; font-weight:600; color:#333; }

  footer {
    background: #222;
    color: #ccc;
    padding: 25px 20px;
    text-align: center;
    margin-top: auto;
  }

  .footer-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 60px;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }

  .footer-logos .logo-item {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .footer-logos .logo-item img {
    width: 60px;
    height: 60px;
    object-fit: contain;
    margin-bottom: 5px;
  }

  .footer-logos .logo-item small {
    font-size: 12px;
    color: #ccc;
    text-align: center;
  }

  .footer-year {
    font-size: 12px;
    color: #aaa;
  }


  @keyframes progressFill { from { width:0; } to { width: var(--progress-width,0%); } }

  /* Loading */
  #loading { display:none; margin-top:20px; font-size:16px; color:#333; }
  </style>

  </head>
  <body>

  <header>
  <div class="logo">
  <img src="assets/images/BG_logo.png" alt="Barangay Logo">
  <h2>Barangay Connect</h2>
  </div>
  <nav>
  <a href="index.php">Home</a>
  <a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
  </nav>
  </header>

  <section class="track-container">
  <div class="track-box">
  <h1><i class="fa-solid fa-clipboard-list"></i> Track Your <?= ucfirst($result['type'] ?? 'Request') ?></h1>

  <form method="get" onsubmit="showLoading()">
  <input type="text" name="track" placeholder="Enter Tracking Number" value="<?= htmlspecialchars($tracking_number) ?>" required>
  <br>
  <button type="submit">Track</button>
  </form>

  <div id="loading">Searching for tracking number...</div>

  <?php if ($result): ?>
<div id="result-container" style="display:none; opacity:0; transition: opacity 0.5s;">
    <p><strong>Type:</strong> <?= htmlspecialchars(ucwords($type)) ?></p>
    <p><strong>Date Filed:</strong> <?= htmlspecialchars(date("F j, Y", strtotime($date_filed))) ?></p>
    

      <div class="timeline" style="--progress-width: 
<?php
    if ($category === 'complaint') {
        echo ($status === 'pending') ? '33%' : (($status === 'in progress') ? '66%' : '100%');
    } else { // document request
        if ($status === 'pending') echo '33%';
        elseif ($status === 'approved') echo '66%';
        else echo '100%';
    }
?>;">
    <div class="timeline-progress"></div>

    <?php if ($category === 'complaint'): ?>
    <div class="timeline-step <?= in_array($status,['pending','in progress','resolved'])?'completed':'' ?>">
        <div class="circle" style="background: <?= in_array($status,['pending','in progress']) ? '#007bff' : '#ccc' ?>;">
            <i class="fa-solid fa-file-circle-exclamation"></i>
        </div>
        <small>Pending</small>
    </div>
    <div class="timeline-step <?= in_array($status,['in progress','resolved'])?'completed':'' ?>">
        <div class="circle" style="background: <?= ($status==='in progress') ? '#007bff' : '#ccc' ?>;">
            <i class="fa-solid fa-gear"></i>
        </div>
        <small>In Progress</small>
    </div>
    <div class="timeline-step <?= $status==='resolved'?'completed':'' ?>">
        <div class="circle" style="background: <?= ($status==='resolved') ? '#28a745' : '#ccc' ?>;">
            <i class="fa-solid fa-check"></i>
        </div>
        <small>Resolved</small>
    </div>
<?php else: ?>
    <div class="timeline-step <?= in_array($status,['pending','approved','released','rejected'])?'completed':'' ?>">
        <div class="circle" style="background: <?= ($status==='pending' || $status==='approved') ? '#007bff' : '#ccc' ?>;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <small>Pending</small>
    </div>
    <div class="timeline-step <?= in_array($status,['approved','released'])?'completed':'' ?>">
        <div class="circle" style="background: <?= ($status==='approved') ? '#007bff' : '#ccc' ?>;">
            <i class="fa-solid fa-thumbs-up"></i>
        </div>
        <small>Approved</small>
    </div>
    <div class="timeline-step <?= in_array($status,['released','rejected'])?'completed':'' ?> <?= $status==='rejected'?'active rejected-step':'' ?>">
        <div class="circle" style="background: <?= ($status==='released') ? '#28a745' : ($status==='rejected' ? '#dc3545' : '#ccc') ?>;">
            <i class="fa-solid <?= $status==='rejected' ? 'fa-xmark' : 'fa-paper-plane' ?>"></i>
        </div>
        <small><?= $status==='rejected' ? 'Rejected' : 'Released' ?></small>
    </div>
<?php endif; ?>


</div>


<?php elseif ($tracking_number): ?>
<div id="result-container" style="display:none; opacity:0; transition: opacity 0.5s;">
    <p style="color:red; margin-top:15px;">No record found for tracking number <strong><?= htmlspecialchars($tracking_number) ?></strong>.</p>
</div>
<?php endif; ?>
  </div>
  </section>

<!-- Footer -->
<footer>
  <div class="footer-logos">
     
  <div class="logo-item"><img src="assets/images/csab.png" alt="College Logo"><small>Colegio San Agustin - Bacolod</small></div>
    <div class="logo-item"><img src="assets/images/BG_logo.png" alt="Barangay Logo"><small>Barangay Connect</small></div>
    <div class="logo-item"><img src="assets/images/ghost_logo.png" alt="Designer Logo"><small>Ghost Team</small></div>
    <div class="logo-item"><img src="assets/images/CABECS.png" alt="College Logo"><small>CABECS</small></div>
  </div>
  <div class="footer-year">&copy; <?php echo date('Y'); ?> | BSIT 4A </div>
</footer>


  </body>
  <script>
function showLoading() {
    const loading = document.getElementById('loading');
    const result = document.getElementById('result-container');

    loading.style.display = 'block';
    result.style.display = 'none';

    setTimeout(() => {
        loading.style.display = 'none';
        result.style.display = 'block';
        setTimeout(() => {
            result.style.opacity = 1; // fade in
        }, 50);
    }, 3000); // 3 seconds
}

// Automatically trigger loading effect if a tracking number is set
window.addEventListener('DOMContentLoaded', () => {
    const trackingNumber = "<?= htmlspecialchars($tracking_number) ?>";
    if(trackingNumber) {
        showLoading();
    }
});
</script>

  </html>
