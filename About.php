<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EduLearn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #8b5cf6;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: #1f2937;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #8b5cf6;
        }

        /* Hero Section */
        .hero-section {
            margin-top: 80px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
        }

        .hero-stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* About Section */
        .about-section {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 3rem;
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 4rem;
        }

        .about-text h3 {
            font-size: 1.8rem;
            color: #1f2937;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .about-text p {
            color: #6b7280;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .about-image {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 16px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }

        /* Mission Section */
        .mission-section {
            background: white;
            padding: 4rem 0;
        }

        .mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .mission-card {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .mission-card:hover {
            transform: translateY(-5px);
            border-color: #8b5cf6;
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
        }

        .mission-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }

        .mission-card h3 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .mission-card p {
            color: #6b7280;
        }

        /* Team Section */
        .team-section {
            padding: 4rem 0;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .team-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            transition: all 0.3s;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: white;
        }

        .team-card h4 {
            font-size: 1.3rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .team-role {
            color: #8b5cf6;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .team-bio {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(139, 92, 246, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8b5cf6;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-link:hover {
            background: #8b5cf6;
            color: white;
        }

        /* Timeline Section */
        .timeline-section {
            background: white;
            padding: 4rem 0;
        }

        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #8b5cf6;
            transform: translateX(-50%);
        }

        .timeline-item:nth-child(even) {
            left: 50%;
            padding-left: 2rem;
        }

        .timeline-content {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 16px;
            position: relative;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        }

        .timeline-year {
            font-size: 1.5rem;
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 1rem;
        }

        .timeline-title {
            font-size: 1.2rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .timeline-description {
            color: #6b7280;
        }

        .timeline-dot {
            position: absolute;
            top: 50%;
            width: 20px;
            height: 20px;
            background: #8b5cf6;
            border: 4px solid white;
            border-radius: 50%;
            transform: translateY(-50%);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
        }

        .timeline-item:nth-child(odd) .timeline-dot {
            right: -11px;
        }

        .timeline-item:nth-child(even) .timeline-dot {
            left: -11px;
        }

        /* Values Section */
        .values-section {
            padding: 4rem 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .value-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            transition: all 0.3s;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
        }

        .value-number {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0 auto 1rem;
        }

        .value-card h4 {
            font-size: 1.3rem;
            color: #1f2937;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .value-card p {
            color: #6b7280;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta {
            background: white;
            color: #8b5cf6;
            padding: 1rem 3rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: inline-block;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .btn-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: white;
            color: #8b5cf6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .about-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .about-image {
                height: 300px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .nav-links {
                display: none;
            }

            .cta-content h2 {
                font-size: 2rem;
            }

            .timeline::before {
                left: 20px;
            }

            .timeline-item {
                width: 100%;
                left: 0 !important;
                padding-left: 3rem !important;
                padding-right: 0 !important;
            }

            .timeline-dot {
                left: 11px !important;
            }

            .btn-cta {
                display: block;
                margin: 0.5rem auto;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .team-card, .mission-card, .value-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .team-card:nth-child(1) { animation-delay: 0s; }
        .team-card:nth-child(2) { animation-delay: 0.1s; }
        .team-card:nth-child(3) { animation-delay: 0.2s; }
        .team-card:nth-child(4) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>About EduLearn</h1>
                <p>We're passionate about making quality education accessible to everyone. Our mission is to empower learners worldwide with the skills and knowledge they need to succeed in today's rapidly evolving world.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-number">2018</span>
                        <span class="stat-label">Founded</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number">50,000+</span>
                        <span class="stat-label">Students Served</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfaction Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h3>Our Story</h3>
                    <p>EduLearn was born from a simple belief: that everyone deserves access to world-class education, regardless of their location, background, or circumstances. Founded in 2018 by a team of educators and technology enthusiasts, we set out to bridge the gap between traditional learning and the digital age.</p>
                    <p>What started as a small online tutoring platform has grown into a comprehensive learning ecosystem, serving students, professionals, and lifelong learners across the globe. We've helped thousands of people acquire new skills, advance their careers, and pursue their passions through our innovative courses and programs.</p>
                    <p>Today, we continue to push the boundaries of online education, incorporating cutting-edge technology, expert instruction, and community-driven learning to create an unparalleled educational experience.</p>
                </div>
                <div class="about-image">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="container">
            <h2 class="section-title">Our Mission & Vision</h2>
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To democratize education by providing accessible, high-quality learning experiences that empower individuals to achieve their personal and professional goals.</p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>To create a world where anyone can learn anything, anytime, anywhere - fostering a global community of lifelong learners and innovators.</p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Our Values</h3>
                    <p>Excellence, accessibility, innovation, and community drive everything we do. We believe in the transformative power of education to change lives and build a better future.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Sarah Johnson</h4>
                    <p class="team-role">Founder & CEO</p>
                    <p class="team-bio">Former educator with 15+ years of experience in curriculum development and educational technology. Passionate about making learning accessible to all.</p>
                    <div class="team-social">
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Michael Chen</h4>
                    <p class="team-role">CTO & Co-Founder</p>
                    <p class="team-bio">Software engineering expert with a background in machine learning and educational platforms. Leads our technical innovation and product development.</p>
                    <div class="team-social">
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Dr. Emily Rodriguez</h4>
                    <p class="team-role">Head of Academic Affairs</p>
                    <p class="team-bio">PhD in Educational Psychology with expertise in learning science and instructional design. Ensures our courses meet the highest academic standards.</p>
                    <div class="team-social">
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>David Kim</h4>
                    <p class="team-role">Head of Student Success</p>
                    <p class="team-bio">Dedicated student advocate with experience in online education support. Ensures every student has the resources and guidance they need to succeed.</p>
                    <div class="team-social">
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="timeline-section">
        <div class="container">
            <h2 class="section-title">Our Journey</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-year">2018</div>
                        <div class="timeline-title">The Beginning</div>
                        <div class="timeline-description">EduLearn was founded with just 5 courses and a vision to make quality education accessible to everyone.</div>
                    </div>
                    <div class="timeline-dot"></div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-year">2019</div>
                        <div class="timeline-title">First Milestone</div>
                        <div class="timeline-description">Reached 1,000 students and expanded our course catalog to include professional development and technical skills.</div>
                    </div>
                    <div class="timeline-dot"></div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-year">2020</div>
                        <div class="timeline-title">Global Expansion</div>
                        <div class="timeline-description">Launched multilingual support and partnerships with educational institutions worldwide during the pandemic.</div>
                    </div>
                    <div class="timeline-dot"></div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-year">2022</div>
                        <div class="timeline-title">Innovation Award</div>
                        <div class="timeline-description">Received the "Best Online Learning Platform" award and introduced AI-powered personalized learning paths.</div>
                    </div>
                    <div class="timeline-dot"></div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-year">2024</div>
                        <div class="timeline-title">50,000+ Students</div>
                        <div class="timeline-description">Celebrated serving over 50,000 students globally with 500+ expert-led courses and a 98% satisfaction rate.</div>
                    </div>
                    <div class="timeline-dot"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <h2 class="section-title">What Drives Us</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-number">1</div>
                    <h4>Excellence</h4>
                    <p>We maintain the highest standards in course content, instruction, and student support to ensure exceptional learning outcomes.</p>
                </div>
                <div class="value-card">
                    <div class="value-number">2</div>
                    <h4>Accessibility</h4>
                    <p>Education should be available to everyone. We work to remove barriers and make learning affordable and inclusive.</p>
                </div>
                <div class="value-card">
                    <div class="value-number">3</div>
                    <h4>Innovation</h4>
                    <p>We embrace new technologies and teaching methods to create engaging, effective, and personalized learning experiences.</p>
                </div>
                <div class="value-card">
                    <div class="value-number">4</div>
                    <h4>Community</h4>
                    <p>Learning is better together. We foster a supportive community where students and instructors can connect and grow.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Join Our Learning Community</h2>
                <p>Ready to start your learning journey? Join thousands of students who have transformed their careers and lives with EduLearn. Your future starts here.</p>
                <a href="signup.php" class="btn-cta">Start Learning Today</a>
                <a href="contact.php" class="btn-cta btn-outline">Get in Touch</a>
            </div>
        </div>
    </section>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else {
                header.style.background = 'white';
                header.style.backdropFilter = 'none';
            }
        });

        // Animate timeline items on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe timeline items
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(item);
        });
    </script>
</body>
</html>