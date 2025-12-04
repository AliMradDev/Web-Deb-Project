<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn Academy</title>
    <link rel="stylesheet" href="Web3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Unlock Your Potential with EduLearn Academy</h1>
                <p>Access premium courses taught by industry experts. Learn at your own pace and boost your career.</p>
                <div class="hero-buttons">
                    <a href="courses.php" class="btn btn-primary">Explore Courses</a>
                    <a href="signup.php" class="btn btn-secondary">Join for Free</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://via.placeholder.com/600x400/8b5cf6/ffffff?text=Online+Learning" alt="Online Learning">
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose EduLearn Academy</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3>Expert Instructors</h3>
                    <p>Learn from industry professionals with years of experience.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-laptop"></i></div>
                    <h3>Live & Pre-recorded Classes</h3>
                    <p>Flexible learning options to fit your schedule.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                    <h3>Recognized Certificates</h3>
                    <p>Earn certificates upon successful course completion.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3>Student Community</h3>
                    <p>Connect with fellow learners and expand your network.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="popular-courses">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Popular Courses</h2>
                <a href="courses.php" class="view-all">View All Courses <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="course-filters">
                <button class="filter-btn active" data-filter="all">All Categories</button>
                <button class="filter-btn" data-filter="programming">Programming</button>
                <button class="filter-btn" data-filter="business">Business</button>
                <button class="filter-btn" data-filter="design">Design</button>
                <button class="filter-btn" data-filter="marketing">Marketing</button>
            </div>
            <div class="courses-grid" id="popular-courses-grid">
                <!-- Sample Course Card -->
                <div class="course-card" data-category="programming">
                    <div class="course-image">
                        <img src="https://via.placeholder.com/300x200/8b5cf6/ffffff?text=Web+Development" alt="Web Development Course">
                        <div class="course-overlay">
                            <a href="course-details.php?id=1" class="btn btn-primary btn-small">View Course</a>
                        </div>
                    </div>
                    <div class="course-content">
                        <div class="course-meta">
                            <span class="course-level level-beginner">Beginner</span>
                            <span class="course-duration"><i class="fas fa-clock"></i> 20h</span>
                        </div>
                        <h3 class="course-title">
                            <a href="course-details.php?id=1">Complete Web Development Bootcamp</a>
                        </h3>
                        <p class="course-description">
                            Learn HTML, CSS, JavaScript, and more. Build real projects and become a web developer.
                        </p>
                        <div class="course-instructor">
                            <img src="https://via.placeholder.com/40x40/7c3aed/ffffff?text=JS" alt="John Smith" class="instructor-avatar">
                            <span class="instructor-name">John Smith</span>
                        </div>
                        <div class="course-footer">
                            <div class="course-rating">
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="rating-text">(4.8)</span>
                            </div>
                       
                        </div>
                    </div>
                </div>

                <!-- More Course Cards -->
                <div class="course-card" data-category="design">
                    <div class="course-image">
                        <img src="https://via.placeholder.com/300x200/a855f7/ffffff?text=UI+Design" alt="UI Design Course">
                        <div class="course-overlay">
                            <a href="course-details.php?id=2" class="btn btn-primary btn-small">View Course</a>
                        </div>
                    </div>
                    <div class="course-content">
                        <div class="course-meta">
                            <span class="course-level level-intermediate">Intermediate</span>
                            <span class="course-duration"><i class="fas fa-clock"></i> 15h</span>
                        </div>
                        <h3 class="course-title">
                            <a href="course-details.php?id=2">UI/UX Design Masterclass</a>
                        </h3>
                        <p class="course-description">
                            Master the art of user interface and user experience design with Figma.
                        </p>
                        <div class="course-instructor">
                            <img src="https://via.placeholder.com/40x40/8b5cf6/ffffff?text=SD" alt="Sarah Davis" class="instructor-avatar">
                            <span class="instructor-name">Sarah Davis</span>
                        </div>
                        <div class="course-footer">
                            <div class="course-rating">
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="rating-text">(4.6)</span>
                            </div>
                        
                        </div>
                    </div>
                </div>

                <div class="course-card" data-category="business">
                    <div class="course-image">
                        <img src="https://via.placeholder.com/300x200/c084fc/ffffff?text=Marketing" alt="Digital Marketing">
                        <div class="course-overlay">
                            <a href="course-details.php?id=3" class="btn btn-primary btn-small">View Course</a>
                        </div>
                    </div>
                    <div class="course-content">
                        <div class="course-meta">
                            <span class="course-level level-beginner">Beginner</span>
                            <span class="course-duration"><i class="fas fa-clock"></i> 12h</span>
                        </div>
                        <h3 class="course-title">
                            <a href="course-details.php?id=3">Digital Marketing Fundamentals</a>
                        </h3>
                        <p class="course-description">
                            Learn social media marketing, SEO, Google Ads, and email marketing.
                        </p>
                        <div class="course-instructor">
                            <img src="https://via.placeholder.com/40x40/7c3aed/ffffff?text=MJ" alt="Mike Johnson" class="instructor-avatar">
                            <span class="instructor-name">Mike Johnson</span>
                        </div>
                        <div class="course-footer">
                            <div class="course-rating">
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="rating-text">(4.9)</span>
                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="top-teachers">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Top-Rated Teachers</h2>
                <a href="teachers.php" class="view-all">View All Teachers <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="teachers-grid" id="top-teachers-grid">
                <!-- Sample Teacher Cards -->
                <div class="teacher-card">
                    <div class="teacher-image">
                        <img src="https://via.placeholder.com/120x120/8b5cf6/ffffff?text=JS" alt="Hasan awada">
                        <div class="teacher-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    <div class="teacher-content">
                        <h3 class="teacher-name">Hasan awada</h3>
                        <p class="teacher-specialization">Full Stack Developer</p>
                        <p class="teacher-bio">
                            Senior developer with 8+ years of experience in web development.
                        </p>
                        <div class="teacher-stats">
                            <div class="stat">
                                <span class="stat-number">12</span>
                                <span class="stat-label">Courses</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2.5K</span>
                                <span class="stat-label">Students</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">8+</span>
                                <span class="stat-label">Years</span>
                            </div>
                        </div>
                        <a href="teacher-profile.php?id=1" class="btn btn-outline btn-small">View Profile</a>
                    </div>
                </div>

                <div class="teacher-card">
                    <div class="teacher-image">
                        <img src="https://via.placeholder.com/120x120/7c3aed/ffffff?text=SD" alt="Ali Kataya">
                        <div class="teacher-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    <div class="teacher-content">
                        <h3 class="teacher-name">Ali Katayaa</h3>
                        <p class="teacher-specialization">UI/UX Designer</p>
                        <p class="teacher-bio">
                            Award-winning designer with expertise in user interface design.
                        </p>
                        <div class="teacher-stats">
                            <div class="stat">
                                <span class="stat-number">8</span>
                                <span class="stat-label">Courses</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">1.8K</span>
                                <span class="stat-label">Students</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">6+</span>
                                <span class="stat-label">Years</span>
                            </div>
                        </div>
                        <a href="teacher-profile.php?id=2" class="btn btn-outline btn-small">View Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="subscription-plans">
        <div class="container">
            <h2 class="section-title">Subscription Plans</h2>
            <p class="section-desc">Choose the plan that fits your learning goals</p>
            <div class="plans-grid">
                <div class="plan-card">
                    <div class="plan-header">
                        <h3>Free Plan</h3>
                        <p class="price">$0<span>/month</span></p>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> Limited course catalog</li>
                            <li><i class="fas fa-check"></i> Course previews</li>
                            <li><i class="fas fa-check"></i> Community access</li>
                            <li><i class="fas fa-times"></i> Live classes</li>
                            <li><i class="fas fa-times"></i> Course certificates</li>
                            <li><i class="fas fa-times"></i> Priority support</li>
                        </ul>
                    </div>
                    <div class="plan-footer">
                        <a href="signup.php" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                <div class="plan-card featured">
                    <div class="plan-badge">Most Popular</div>
                    <div class="plan-header">
                        <h3>Premium Plan</h3>
                        <p class="price">$19.99<span>/month</span></p>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> Full course catalog</li>
                            <li><i class="fas fa-check"></i> Unlimited course access</li>
                            <li><i class="fas fa-check"></i> Live classes</li>
                            <li><i class="fas fa-check"></i> Course certificates</li>
                            <li><i class="fas fa-check"></i> Priority support</li>
                            <li><i class="fas fa-check"></i> Offline viewing</li>
                        </ul>
                    </div>
                    <div class="plan-footer">
                        <a href="signup.php?plan=premium" class="btn btn-primary">Subscribe Now</a>
                    </div>
                </div>
                <div class="plan-card">
                    <div class="plan-header">
                        <h3>University Student</h3>
                        <p class="price">$9.99<span>/month</span></p>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> Full course catalog</li>
                            <li><i class="fas fa-check"></i> Unlimited course access</li>
                            <li><i class="fas fa-check"></i> Live classes</li>
                            <li><i class="fas fa-check"></i> Course certificates</li>
                            <li><i class="fas fa-check"></i> Standard support</li>
                            <li><i class="fas fa-times"></i> Offline viewing</li>
                        </ul>
                    </div>
                    <div class="plan-footer">
                        <a href="signup.php?plan=student" class="btn btn-outline">Verify & Subscribe</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Students Say</h2>
            <div class="testimonial-slider">
                <div class="testimonial-slide">
                    <div class="testimonial-content">
                        <p>"EduLearn Academy transformed my career. The courses are comprehensive and the instructors are amazing. I landed my dream job after completing just two courses!"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://via.placeholder.com/80x80/8b5cf6/ffffff?text=HR" alt="Student Testimonial">
                        <div class="author-info">
                            <h4>Husen Rahal</h4>
                            <p>Web Developer</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-controls">
                <button class="prev-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="next-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Learning?</h2>
                <p>Join thousands of students and expand your knowledge today.</p>
                <a href="signup.php" class="btn btn-primary">Create Account</a>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Basic functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            const mobileNav = document.querySelector('.mobile-nav');
            
            if (mobileToggle && mobileNav) {
                mobileToggle.addEventListener('click', function() {
                    mobileNav.classList.toggle('active');
                });
            }

            // Course filter functionality
            const filterBtns = document.querySelectorAll('.filter-btn');
            const courseCards = document.querySelectorAll('.course-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');

                    courseCards.forEach(card => {
                        if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>