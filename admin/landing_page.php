<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Home</title>
  <link rel="icon" href="../assets/images/BG_logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #FFF8E1;
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Navbar */
    header {
      background: #343A40;
      color: #fff;
      padding: 15px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    header .logo img {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      border: 2px solid yellow;
    }

    header nav a {
      color: #ddd;
      margin-left: 20px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    header nav a:hover {
      color: #4a90e2;
    }

    /* Hero Section */
    .hero {
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../assets/images/BH.jpg') center/cover no-repeat;
      color: #fff;
      text-align: center;
      padding: 120px 20px;
    }

    .hero h1 {
      font-size: 50px;
      margin-bottom: 15px;
      color: #ffecb3;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
    }

    .hero p {
      font-size: 18px;
      max-width: 600px;
      margin: 0 auto 30px;
    }

    .hero .btn {
      background: #4a90e2;
      color: white;
      padding: 12px 25px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }

    .hero .btn:hover {
      background: #357ab7;
    }

    /* Info Section */
    .info-section {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      padding: 60px 30px;
      background: rgba(255, 255, 255, 0.8);
    }

    .info-card {
      background: #fff;
      width: 280px;
      border-radius: 10px;
      padding: 30px 20px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      text-align: center;
      transition: 0.3s;
    }

    .info-card:hover {
      transform: translateY(-5px);
      background: #f0f4ff;
    }

    .info-card i {
      font-size: 40px;
      color: #4a90e2;
      margin-bottom: 10px;
    }

    .info-card h3 {
      margin: 10px 0;
      font-size: 20px;
    }

    .info-card p {
      font-size: 15px;
      color: #555;
    }

    /* About Section */
    .about {
      background: #343A40;
      color: #fff;
      text-align: center;
      padding: 60px 20px;
    }

    .about h2 {
      color: #ffecb3;
      margin-bottom: 20px;
    }

    .about p {
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
      font-size: 17px;
    }

    footer {
  background: #222;
  color: #ccc;
  padding: 20px 30px;
  text-align: center;
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
  color: #ccc;
  font-size: 12px;
  text-align: center;
}

.footer-year {
  font-size: 12px;
  color: #aaa;
}


    @media (max-width: 768px) {
      header {
        flex-direction: column;
        text-align: center;
      }

      header nav {
        margin-top: 10px;
      }

      .hero h1 {
        font-size: 36px;
      }

      .info-card {
        width: 90%;
      }
    }
  </style>
</head>
<body>

  <header>
  
    <div class="logo">
      <img src="../assets/images/BG_logo.png" alt="Barangay Logo">
      <h2>Barangay Connect</h2>
    </div>
    <nav>
      <a href="#home">Home</a>
      <a href="#services">Services</a>
      <a href="#about">About</a>
      <a href="../login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero" id="home">
    <h1>Welcome to Barangay Connect</h1>
    <p>Empowering your barangay through digital access to complaints, document requests, and community services.</p>
    <a href="../login.php" class="btn"><i class="fa-solid fa-comments"></i> Complaints</a>
    <a href="../login.php" class="btn"><i class="fa-solid fa-file-lines"></i> Document Request</a>
    <a href="../track_transaction.php" class="btn"><i class="fa-solid fa-clipboard-list"></i> Track Status</a>
  </section>

  <!-- Info Section -->
  <section class="info-section" id="services">
    <div class="info-card">
      <i class="fa-solid fa-comments"></i>
      <h3>Submit Complaints</h3>
      <p>Report community concerns directly to your barangay officials online with ease.</p>
    </div>

    <div class="info-card">
      <i class="fa-solid fa-file-lines"></i>
      <h3>Request Documents</h3>
      <p>Apply for barangay certificates, clearances, and permits anytime, anywhere.</p>
    </div>

    <div class="info-card">
      <i class="fa-solid fa-users"></i>
      <h3>Community Connection</h3>
      <p>Stay informed about local initiatives, updates, and public announcements.</p>
    </div>

    <div class="info-card">
      <i class="fa-solid fa-user-shield"></i>
      <h3>Secure Platform</h3>
      <p>Your information is handled safely and securely by authorized barangay staff.</p>
    </div>
  </section>

  <!-- About Section -->
  <section class="about" id="about">
    <h2>About Barangay Connect</h2>
    <p>
      Barangay Connect is a digital platform designed to improve transparency, efficiency, and engagement
      between residents and barangay officials. With easy online complaint filing, document requests, and
      notifications, it brings the community closer together while reducing paperwork and delays.
    </p>
  </section>

  <!-- Footer with Three Logos -->
  <footer>
  <div class="footer-logos">
    <div class="logo-item">
      <img src="../assets/images/BG_logo.png" alt="Barangay Logo">
      <small>Barangay Connect</small>
    </div>
    <div class="logo-item">
      <img src="../assets/images/csab.png" alt="College Logo">
      <small>Colegio San Agustin - Bacolod </small>
    </div>
    <div class="logo-item">
      <img src="../assets/images/ghost_logo.png" alt="Designer Logo">
      <small>BSIT - Ghost Team </small>
    </div>
  </div>
  <div class="footer-year">
    &copy; <?php echo date('Y'); ?>
  </div>
</footer>


</body>
</html>
