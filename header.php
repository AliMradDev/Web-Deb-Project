<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['first_name'] ?? 'User';
$initials = urlencode($user_name[0]);
$user_avatar = "https://ui-avatars.com/api/?name=$initials&background=8b5cf6&color=ffffff";

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Header CSS Styles -->
<style>
/* Universal Header Styles */
header {
    background: white !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1000 !important;
    width: 100% !important;
    transition: all 0.3s ease !important;
}

header .container {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 1rem 2rem !important;
    max-width: 1200px !important;
    margin: 0 auto !important;
}

/* Logo Styles */
.logo h1 {
    font-size: 1.8rem !important;
    font-weight: bold !important;
    color: #8b5cf6 !important;
    margin: 0 !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
}

/* Navigation Styles */
nav {
    display: flex !important;
    align-items: center !important;
}

.main-menu {
    display: flex !important;
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    gap: 2rem !important;
    align-items: center !important;
}

.main-menu li {
    position: relative !important;
    list-style: none !important;
}

.main-menu > li > a {
    color: #1f2937 !important;
    text-decoration: none !important;
    font-weight: 500 !important;
    padding: 0.5rem 0 !important;
    transition: color 0.3s ease !important;
    display: block !important;
}

.main-menu > li > a:hover {
    color: #8b5cf6 !important;
}

/* Active page highlighting */
.main-menu li a.active {
    color: #8b5cf6 !important;
}

/* Dropdown Styles */
.dropdown {
    position: relative !important;
}

.dropdown-menu {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    background: white !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
    min-width: 200px !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transform: translateY(-10px) !important;
    transition: all 0.3s ease !important;
    z-index: 1001 !important;
    margin-top: 0.5rem !important;
    list-style: none !important;
    padding: 0.5rem 0 !important;
}

.dropdown:hover .dropdown-menu {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

.dropdown-menu li {
    list-style: none !important;
}

.dropdown-menu a {
    display: block !important;
    padding: 0.75rem 1rem !important;
    color: #1f2937 !important;
    text-decoration: none !important;
    transition: background 0.3s ease !important;
}

.dropdown-menu a:hover {
    background: #f8fafc !important;
    color: #8b5cf6 !important;
}

/* User Menu Styles */
.user-menu {
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
}

/* Search Box Styles */
.search-box {
    display: flex !important;
    align-items: center !important;
    background: #f8fafc !important;
    border-radius: 25px !important;
    padding: 0.5rem 1rem !important;
    border: 2px solid transparent !important;
    transition: all 0.3s ease !important;
}

.search-box:focus-within {
    border-color: #8b5cf6 !important;
    background: white !important;
}

.search-box input {
    border: none !important;
    background: transparent !important;
    outline: none !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 0.9rem !important;
    width: 200px !important;
}

.search-box button {
    background: none !important;
    border: none !important;
    color: #6b7280 !important;
    cursor: pointer !important;
    padding: 0.25rem !important;
    transition: color 0.3s ease !important;
}

.search-box button:hover {
    color: #8b5cf6 !important;
}

/* User Dropdown Styles */
.user-dropdown {
    position: relative !important;
}

.dropdown-toggle {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    text-decoration: none !important;
    color: #1f2937 !important;
    padding: 0.5rem 1rem !important;
    border-radius: 25px !important;
    transition: background 0.3s ease !important;
    cursor: pointer !important;
    border: none !important;
    background: transparent !important;
}

.dropdown-toggle:hover {
    background: #f3f4f6 !important;
    text-decoration: none !important;
}

.avatar {
    width: 40px !important;
    height: 40px !important;
    border-radius: 50% !important;
    border: 2px solid #e5e7eb !important;
    transition: border-color 0.3s ease !important;
}

.dropdown-toggle:hover .avatar {
    border-color: #8b5cf6 !important;
}

.username {
    font-weight: 500 !important;
    font-size: 0.9rem !important;
}

/* User dropdown menu - specific styles */
.user-dropdown .dropdown-menu {
    right: 0 !important;
    left: auto !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transform: translateY(-10px) !important;
    transition: all 0.3s ease !important;
    pointer-events: none !important;
}

.user-dropdown.show .dropdown-menu {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    pointer-events: auto !important;
}

.user-dropdown .dropdown-menu a {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.75rem 1rem !important;
    color: #1f2937 !important;
    text-decoration: none !important;
    transition: background 0.3s ease !important;
}

.user-dropdown .dropdown-menu a:hover {
    background: #f8fafc !important;
    color: #8b5cf6 !important;
}

/* Auth Buttons */
.auth-buttons {
    display: flex !important;
    gap: 0.5rem !important;
    align-items: center !important;
}

.btn {
    padding: 0.5rem 1rem !important;
    border-radius: 20px !important;
    text-decoration: none !important;
    font-weight: 500 !important;
    font-size: 0.9rem !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    border: 2px solid transparent !important;
}

.btn-primary {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
    color: white !important;
    border: 2px solid transparent !important;
}

.btn-primary:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4) !important;
    text-decoration: none !important;
    color: white !important;
}

.btn-outline {
    background: transparent !important;
    color: #8b5cf6 !important;
    border: 2px solid #8b5cf6 !important;
}

.btn-outline:hover {
    background: #8b5cf6 !important;
    color: white !important;
    text-decoration: none !important;
    transform: translateY(-1px) !important;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none !important;
    background: none !important;
    border: none !important;
    color: #1f2937 !important;
    font-size: 1.5rem !important;
    cursor: pointer !important;
    padding: 0.5rem !important;
}

/* Scroll Effect */
header.scrolled {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    header .container {
        padding: 1rem !important;
    }
    
    .logo h1 {
        font-size: 1.5rem !important;
    }
    
    .mobile-menu-toggle {
        display: block !important;
    }
    
    nav {
        display: none !important;
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        background: white !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
        padding: 1rem 0 !important;
    }
    
    nav.show {
        display: block !important;
    }
    
    .main-menu {
        flex-direction: column !important;
        gap: 0 !important;
        padding: 0 1rem !important;
    }
    
    .main-menu li {
        width: 100% !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }
    
    .main-menu li:last-child {
        border-bottom: none !important;
    }
    
    .main-menu > li > a {
        padding: 1rem 0 !important;
        display: block !important;
        width: 100% !important;
    }
    
    .dropdown-menu {
        position: static !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        box-shadow: none !important;
        background: #f8fafc !important;
        margin: 0 !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }
    
    .dropdown-menu a {
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    
    .search-box {
        display: none !important;
    }
    
    .user-menu {
        gap: 0.5rem !important;
    }
    
    .auth-buttons {
        flex-direction: column !important;
        gap: 0.25rem !important;
    }
    
    .btn {
        padding: 0.4rem 0.8rem !important;
        font-size: 0.8rem !important;
    }
    
    .username {
        display: none !important;
    }

    .user-dropdown .dropdown-menu {
        right: -50px !important;
        min-width: 150px !important;
    }
}
</style>

<header>
    <div class="container">
        <div class="logo">
            <h1>EduLearn Academy</h1>
        </div>
        <nav id="mainNav">
            <ul class="main-menu">
                <li><a href="Home.php" class="<?php echo $current_page === 'Home' ? 'active' : ''; ?>">Home</a></li>
                <li class="dropdown">
                    <a href="courses.php" class="<?php echo $current_page === 'courses' ? 'active' : ''; ?>">Courses</a>
                    <ul class="dropdown-menu">
                      <li><a href="courses.php?category=web">Web Development</a></li>
                      <li><a href="courses.php?category=data">Data Science</a></li>
                      <li><a href="courses.php?category=design">Design</a></li>
                    </ul>
                </li>
                <li><a href="teachers.php" class="<?php echo $current_page === 'teachers' ? 'active' : ''; ?>">Teachers</a></li>
                <li><a href="career.php" class="<?php echo $current_page === 'career' ? 'active' : ''; ?>">Career</a></li>
                <li><a href="pricing.php" class="<?php echo $current_page === 'pricing' ? 'active' : ''; ?>">Pricing</a></li>
                <li class="dropdown">
                    <a href="contact.php" class="<?php echo in_array($current_page, ['contact', 'about']) ? 'active' : ''; ?>">Contact</a>
                    <ul class="dropdown-menu">
                      <li><a href="about.php">About Us</a></li>
                      <li><a href="contact.php">Contact</a></li>
                    </ul>
                </li>

                <!-- Admin Dashboard Link for admin users -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php" style="color: #8b5cf6; font-weight: bold; margin-left: 10px;">Admin Dashboard</a></li>
                <?php endif; ?>
                <!--teacher dashboard-->
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher'): ?>
                    <li><a href="teacher_dashboard.php" style="color: #8b5cf6; font-weight: bold; margin-left: 10px;">Teacher Dashboard</a></li>
                <?php endif; ?>
                
            </ul>
        </nav>
        <div class="user-menu">
            <div class="search-box">
                <input type="text" placeholder="Search courses..." id="searchInput">
                <button type="submit" onclick="performSearch()"><i class="fas fa-search"></i></button>
            </div>
           
            <?php if ($is_logged_in): ?>
                <!-- Logged in user dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <button type="button" class="dropdown-toggle" onclick="toggleUserDropdown(event)">
                        <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="User Avatar" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="userDropdownMenu">
                        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="my-courses.php"><i class="fas fa-book"></i> My Courses</a>
                        <a href="my-subscriptions.php"><i class="fas fa-credit-card"></i> Subscription</a>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Login/Signup buttons for guests -->
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</header>

<!-- Header JavaScript -->
<script>
// Mobile menu toggle
function toggleMobileMenu() {
    const nav = document.getElementById('mainNav');
    const toggle = document.querySelector('.mobile-menu-toggle i');
    
    nav.classList.toggle('show');
    
    // Change icon
    if (nav.classList.contains('show')) {
        toggle.className = 'fas fa-times';
    } else {
        toggle.className = 'fas fa-bars';
    }
}

// User dropdown toggle
function toggleUserDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = document.getElementById('userDropdown');
    
    // Close all other dropdowns first
    document.querySelectorAll('.user-dropdown').forEach(dd => {
        if (dd !== dropdown) {
            dd.classList.remove('show');
        }
    });
    
    // Toggle current dropdown
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    
    if (userDropdown && !userDropdown.contains(event.target)) {
        userDropdown.classList.remove('show');
    }
});

// Close dropdown when pressing Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.user-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        
        // Close mobile menu
        const nav = document.getElementById('mainNav');
        const toggle = document.querySelector('.mobile-menu-toggle i');
        if (nav && nav.classList.contains('show')) {
            nav.classList.remove('show');
            toggle.className = 'fas fa-bars';
        }
    }
});

// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    if (window.scrollY > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Search functionality
function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.trim();
    
    if (query) {
        // Redirect to courses page with search query
        window.location.href = `courses.php?search=${encodeURIComponent(query)}`;
    }
}

// Search on Enter key
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        });
    }
});

// Close mobile menu when clicking on links
document.querySelectorAll('.main-menu a').forEach(link => {
    link.addEventListener('click', function() {
        const nav = document.getElementById('mainNav');
        const toggle = document.querySelector('.mobile-menu-toggle i');
        
        nav.classList.remove('show');
        toggle.className = 'fas fa-bars';
    });
});

// Smooth dropdown animations for hover menus
document.querySelectorAll('.dropdown').forEach(dropdown => {
    const menu = dropdown.querySelector('.dropdown-menu');
    
    // Skip user dropdown as it uses click
    if (dropdown.classList.contains('user-dropdown')) return;
    
    dropdown.addEventListener('mouseenter', function() {
        menu.style.opacity = '1';
        menu.style.visibility = 'visible';
        menu.style.transform = 'translateY(0)';
    });
    
    dropdown.addEventListener('mouseleave', function() {
        menu.style.opacity = '0';
        menu.style.visibility = 'hidden';
        menu.style.transform = 'translateY(-10px)';
    });
});
</script>
