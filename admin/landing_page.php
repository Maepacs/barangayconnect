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

    .steps-section-horizontal {
  padding: 40px 20px;
  background-color: #f8f9fa;
  text-align: center;
}

.steps-section-horizontal h2 {
  font-size: 2rem;
  margin-bottom: 30px;
  color: #333;
}

.steps-container-horizontal {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap; /* allows stacking on smaller screens */
  padding-bottom: 10px;
}

.step-card-horizontal {
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  padding: 20px;
  flex: 0 0 200px; /* base width for desktop */
  transition: transform 0.3s, box-shadow 0.3s;
  display: flex;
  flex-direction: column;
  align-items: center; /* centers icon and content */
  text-align: center;
}

.step-card-horizontal:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}

.step-number-horizontal {
  font-weight: bold;
  color: #007bff;
  margin-bottom: 10px;
}

.step-icon-circle {
  background-color: #007bff;
  color: #fff;
  width: 60px;
  height: 60px;
  line-height: 60px;
  border-radius: 50%;
  font-size: 1.5rem;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step-card-horizontal h3 {
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: #333;
}

.step-card-horizontal p {
  font-size: 0.9rem;
  color: #555;
}

/* Responsive adjustments */
@media (max-width: 900px) {
  .steps-container-horizontal {
    gap: 15px;
  }

  .step-card-horizontal {
    flex: 1 1 45%; /* 2 cards per row */
  }
}

@media (max-width: 600px) {
  .step-card-horizontal {
    flex: 1 1 100%; /* full width on mobile */
  }
}

.announcements-section {
  padding: 50px 20px;
  background: #fff;
  text-align: center;
}

.announcements-section h2 {
  margin-bottom: 30px;
  color: #333333;
}

.carousel-container {
  position: relative;
  overflow: hidden;
  max-width: 900px;
  margin: 0 auto 30px;
}

.carousel-track {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

.carousel-slide {
  min-width: 100%;
  box-sizing: border-box;
}

.carousel-slide img {
  width: 100%;
  height: 300px;
  object-fit: cover;
  border-radius: 10px;
}

.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0,0,0,0.5);
  color: #fff;
  border: none;
  padding: 10px 15px;
  cursor: pointer;
  border-radius: 50%;
  z-index: 10;
}

.carousel-btn:hover {
  background: rgba(0,0,0,0.7);
}

.carousel-btn.prev { left: 10px; }
.carousel-btn.next { right: 10px; }

.announcement-form {
  margin: 15px 0;
}

.announcement-form input, .announcement-form select {
  padding: 8px 12px;
  margin-right: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.announcement-form button {
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  background: #007bff;
  color: #fff;
  cursor: pointer;
}

.announcement-form button:hover {
  background: #0056b3;
}

/* Emergency hotline contacts */
.emergency-section {
  text-align: center;
  margin: 30px auto;
  max-width: 1200px;
}

.emergency-section h2 {
  color: #222;
  font-size: 2rem;
  margin-bottom: 25px;
  letter-spacing: 1px;
}

/* Grid layout: 5 columns by default */
.contacts-container {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  margin-bottom: 30px;
  justify-items: center;
}

/* Card styling */
.contact-card {
  background: linear-gradient(to right, rgb(231, 138, 138),rgb(24, 42, 208));
  color: white;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 220px;
  height: 100px;
  padding: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.25);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.35);
}

/* Logo */
.contact-logo {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 15px;
  border: 2px solid #fff;
  flex-shrink: 0;
}

/* Info text */
.contact-info {
  text-align: left;
}

.contact-info h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: bold;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 5px;
}

.contact-info h3 i {
  color: #ffefb0;
  font-size: 1.1rem;
}

.contact-info p {
  margin: 3px 0 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #ffefb0;
  display: flex;
  align-items: center;
  gap: 5px;
}

.contact-info p i {
  color: #fff;
  font-size: 1rem;
}

/* Add / Delete form styling */
.contact-form {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  margin-top: 25px;
}

.contact-form input,
.contact-form select {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 260px;
}

.contact-form button {
  background-color: #007bff;
  color: white;
  padding: 10px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}

.contact-form button:hover {
  background-color: #0056b3;
}

/* Responsive layout */
@media (max-width: 1100px) {
  .contacts-container {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 900px) {
  .contacts-container {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 700px) {
  .contacts-container {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 450px) {
  .contacts-container {
    grid-template-columns: 1fr;
  }
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

  <!-- Document and Complaint Step-by-Step -->
  <section class="steps-section-horizontal">
  <h2>How to Request a Document</h2>
  <div class="steps-container-horizontal">
    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 1</div>
      <div class="step-icon-circle"><i class="fa-solid fa-user-plus"></i></div>
      <h3>Create Account / Login</h3>
      <p>Sign up or log in to your Barangay Connect account to access document request services.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 2</div>
      <div class="step-icon-circle"><i class="fa-solid fa-file-lines"></i></div>
      <h3>Select Document</h3>
      <p>Choose the type of document you need, such as certificates, clearances, or permits.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 3</div>
      <div class="step-icon-circle"><i class="fa-solid fa-upload"></i></div>
      <h3>Submit Form & Upload Documents</h3>
      <p>Complete the request form and upload any required supporting documents.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 4</div>
      <div class="step-icon-circle"><i class="fa-solid fa-clipboard-check"></i></div>
      <h3>Track Status</h3>
      <p>Check the progress of your document request anytime through the Track Status feature.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 5</div>
      <div class="step-icon-circle"><i class="fa-solid fa-check-circle"></i></div>
      <h3>Receive Document</h3>
      <p>You will be notified once your document is ready for pickup or delivery.</p>
    </div>
  </div>
</section>

<section class="steps-section-horizontal">
  <h2>How to File a Complaint</h2>
  <div class="steps-container-horizontal">
    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 1</div>
      <div class="step-icon-circle"><i class="fa-solid fa-user-plus"></i></div>
      <h3>Create Account / Login</h3>
      <p>Sign up or log in to your Barangay Connect account to access complaint services.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 2</div>
      <div class="step-icon-circle"><i class="fa-solid fa-comments"></i></div>
      <h3>Submit Complaint</h3>
      <p>Go to the Complaints section, fill out the form, and provide all necessary details about your concern.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 3</div>
      <div class="step-icon-circle"><i class="fa-solid fa-clipboard-check"></i></div>
      <h3>Track Status</h3>
      <p>Check the progress of your complaint anytime through the Track Status feature.</p>
    </div>

    <div class="step-card-horizontal">
      <div class="step-number-horizontal">Step 4</div>
      <div class="step-icon-circle"><i class="fa-solid fa-check-circle"></i></div>
      <h3>Receive Updates</h3>
      <p>You will get notifications and updates once your complaint is processed by the barangay office.</p>
    </div>
  </div>
</section>

<!-- Announcements Section -->
<section class="announcements-section">
  <h2>Barangay Announcements</h2>

  <!-- Carousel Container -->
  <div class="carousel-container">
    <button class="carousel-btn prev"><i class="fa-solid fa-chevron-left"></i></button>
    <div class="carousel-track">
      <?php
      // Load announcement images from 'announcements' folder
      $announcement_dir = '../uploads/announcements/';
      $announcements = glob($announcement_dir . '*.{jpg,png,jpeg,gif}', GLOB_BRACE);
      foreach ($announcements as $img) {
        echo '<div class="carousel-slide"><img src="'. $img .'" alt="Announcement"></div>';
      }
      ?>
    </div>
    <button class="carousel-btn next"><i class="fa-solid fa-chevron-right"></i></button>
  </div>

  <!-- Upload Form (for admin) -->
  <form action="upload_announcement.php" method="POST" enctype="multipart/form-data">
  <input type="file" name="announcement_img" required>
  <button type="submit">Upload</button>
</form>


  <!-- Delete Form -->
  <form action="delete_announcement.php" method="POST" class="announcement-form">
    <select name="delete_img" required>
      <option value=""disabled select>Select Announcement to Delete</option>
      <?php
      foreach ($announcements as $img) {
        $filename = basename($img);
        echo '<option value="'. $filename .'">'. $filename .'</option>';
      }
      ?>
    </select>
    <button type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
  </form>
</section>

<section class="emergency-section">
  <h2>Emergency Contacts</h2>

  <div class="contacts-container">
    <?php
    $hotlines_file = '../data/hotlines.json';

    if (file_exists($hotlines_file)) {
      $contacts = json_decode(file_get_contents($hotlines_file), true);

      if (!empty($contacts)) {
        foreach ($contacts as $contact) {
          $logo = !empty($contact['logo']) ? '../uploads/hotlines/' . $contact['logo'] : '../assets/images/default_logo.png';
          echo '
          <div class="contact-card">
            <img src="'. htmlspecialchars($logo) .'" alt="Logo" class="contact-logo">
            <div class="contact-info">
              <h3><i class="fa-solid fa-building-shield"></i> '. htmlspecialchars($contact['name']) .'</h3>
<p><i class="fa-solid fa-phone"></i> '. htmlspecialchars($contact['number']) .'</p>

            </div>
          </div>';
        }
      } else {
        echo '<p>No emergency contacts available.</p>';
      }
    } else {
      echo '<p>No emergency contacts found.</p>';
    }
    ?>
  </div>

  <!-- Add Contact Form -->
  <form action="add_hotline.php" method="POST" enctype="multipart/form-data" class="contact-form">
    <input type="text" name="name" placeholder="Contact Name" required>
    <input type="text" name="number" placeholder="Phone Number" required>
    <label for="logo">Upload Logo (optional)</label>
    <input type="file" name="logo" accept="image/*">
    <button type="submit"><i class="fa-solid fa-plus"></i> Add Contact</button>
  </form>

  <!-- Delete Contact Form -->
  <form action="delete_hotline.php" method="POST" class="contact-form">
    <select name="delete_name" required>
      <option value="" disabled selected>Select contact to delete</option>
      <?php
      if (!empty($contacts)) {
        foreach ($contacts as $contact) {
          echo '<option value="'. htmlspecialchars($contact['name']) .'">'. htmlspecialchars($contact['name']) .'</option>';
        }
      }
      ?>
    </select>
    <button type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
  </form>
</section>


<br>

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
<script>
const track = document.querySelector('.carousel-track');
const slides = Array.from(track.children);
const nextBtn = document.querySelector('.carousel-btn.next');
const prevBtn = document.querySelector('.carousel-btn.prev');
let currentIndex = 0;

function updateCarousel() {
  const slideWidth = slides[0].getBoundingClientRect().width;
  track.style.transform = 'translateX(-' + currentIndex * slideWidth + 'px)';
}

nextBtn.addEventListener('click', () => {
  currentIndex = (currentIndex + 1) % slides.length;
  updateCarousel();
});

prevBtn.addEventListener('click', () => {
  currentIndex = (currentIndex - 1 + slides.length) % slides.length;
  updateCarousel();
});

window.addEventListener('resize', updateCarousel);



// Icon mapping by type
const hotlineIcons = {
    police: 'fa-shield-halved',
    fire: 'fa-fire',
    medical: 'fa-hospital',
    other: 'fa-phone'
};

// Load hotlines
function loadHotlines() {
    fetch('../data/hotlines.json')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('hotline-cards');
            const select = document.getElementById('delete-hotline-select');
            container.innerHTML = '';
            select.innerHTML = '<option value="" disabled selected>Select Hotline to Delete</option>';

            if(data.length === 0){
                container.innerHTML = '<p style="font-style:italic; color:#555;">No hotlines added yet.</p>';
            } else {
                data.forEach(h => {
                    const card = document.createElement('div');
                    card.className = `hotline-card hotline-${h.type || 'other'}`;
                    card.innerHTML = `<i class="fa-solid ${hotlineIcons[h.type] || hotlineIcons['other']}" style="font-size:2rem;margin-bottom:10px;"></i>
                                      <h3>${h.name}</h3>
                                      <p>${h.number}</p>`;
                    container.appendChild(card);

                    const option = document.createElement('option');
                    option.value = h.name;
                    option.textContent = `${h.name} - ${h.number}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(err => console.error("Error loading hotlines:", err));
}

// Add Hotline
document.getElementById('add-hotline-form').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('add_hotline.php', { method:'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) loadHotlines();
            else alert(res.message || 'Failed to add hotline.');
            this.reset();
        });
});

// Delete Hotline
document.getElementById('delete-hotline-form').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('delete_hotline.php', { method:'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success) loadHotlines();
            else alert(res.message || 'Failed to delete hotline.');
        });
});

// Initial load
loadHotlines();
</script>
</html>
