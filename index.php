,<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker | Master Your Money</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    

    <style>
        /* CSS Variables */
        :root {
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --primary: #2b6777;
            --primary-light: #52ab98;
            --primary-dark: #1f4a56;
            --accent: #fbbf24;
            --gradient-1: rgba(82, 171, 152, 0.15);
            --gradient-2: rgba(43, 103, 119, 0.15);
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --text-light: #94a3b8;
            --border: #334155;
            --gradient-1: rgba(82, 171, 152, 0.1);
            --gradient-2: rgba(43, 103, 119, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        h1, h2, h3, .brand { font-family: 'Poppins', sans-serif; }
        a { text-decoration: none; color: inherit; }

        /* Background Effects */
        .bg-mesh {
            position: absolute;
            top: 0; left: 0; width: 100vw; height: 100vh;
            overflow: hidden; z-index: -1;
            background: radial-gradient(circle at 15% 50%, var(--gradient-1), transparent 40%),
                        radial-gradient(circle at 85% 30%, var(--gradient-2), transparent 40%);
            filter: blur(60px);
        }

        /* Navbar */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 24px 8%;
            position: fixed; width: 100%; top: 0; z-index: 100;
            background: rgba(var(--card-bg), 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        .brand { font-size: 1.4rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .nav-links { display: flex; gap: 24px; align-items: center; }
        .nav-links a { font-weight: 500; color: var(--text-light); transition: color 0.2s; }
        .nav-links a:hover { color: var(--primary); }
        .btn-outline { border: 2px solid var(--primary); color: var(--primary) !important; padding: 8px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .btn-outline:hover { background: var(--primary); color: white !important; }
        .btn-primary { background: var(--primary); color: white !important; padding: 10px 22px; border-radius: 8px; font-weight: 800; box-shadow: 0 4px 14px rgba(43,103,119,0.3); transition: 0.3s; }
        .btn-primary:hover { background: var(--primary-dark); color: white !important; transform: translateY(-2px); }

        /* Theme Toggle */
        .theme-toggle { background: none; border: none; color: var(--text-light); cursor: pointer; display: flex; padding: 6px; border-radius: 50%; transition: 0.2s; }
        .theme-toggle:hover { background: var(--border); color: var(--text); }

        /* Hero */
        .hero {
            display: flex; align-items: center; justify-content: space-between;
            min-height: 100vh; padding: 100px 8% 0; gap: 60px;
        }
        .hero-content { flex: 1; max-width: 600px; }
        .hero h1 { font-size: 3.8rem; line-height: 1.15; margin-bottom: 24px; color: var(--text); }
        .hero h1 span { color: var(--primary-light); }
        .hero p { font-size: 1.15rem; color: var(--text-light); margin-bottom: 40px; line-height: 1.6; }
        
        .hero-actions { display: flex; gap: 16px; align-items: center; }

        .hero-visual { flex: 1; position: relative; height: 500px; display: flex; justify-content: center; align-items: center; }
        
        /* Glass Floating Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
            position: absolute;
            transition: transform 0.3s ease;
        }
        [data-theme="dark"] .glass-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }

        .card-1 { width: 300px; top: 10%; left: 5%; animation: float 6s ease-in-out infinite; z-index: 2;}
        .card-2 { width: 260px; bottom: 15%; right: 5%; animation: float 8s ease-in-out infinite reverse; z-index: 1;}
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .metric { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .metric-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--gradient-1); color: var(--primary-light); display: flex; align-items: center; justify-content: center; }
        .metric-info h4 { font-size: 0.8rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .metric-info p { font-size: 1.4rem; font-weight: 700; color: var(--text); margin:0; }
        
        /* Features */
        .features { padding: 100px 8%; text-align: center; background: var(--card-bg); border-top: 1px solid var(--border); }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px; margin-top: 60px; }
        .feature-card { padding: 40px 30px; border-radius: 16px; background: var(--bg); border: 1px solid var(--border); transition: 0.3s; }
        .feature-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,0.05); border-color: var(--primary-light); }
        .feature-icon { width: 64px; height: 64px; border-radius: 16px; background: var(--gradient-2); color: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .feature-card h3 { font-size: 1.25rem; margin-bottom: 12px; }
        .feature-card p { color: var(--text-light); line-height: 1.6; }

        /* Footer */
        footer { padding: 40px 8%; text-align: center; border-top: 1px solid var(--border); color: var(--text-light); font-size: 0.9rem; }
        .footer-links { display: flex; justify-content: center; gap: 24px; margin-bottom: 20px; flex-wrap: wrap; }
        .footer-links a:hover { color: var(--primary); }

        /* Responsive */
        @media (max-width: 992px) {
            .hero { flex-direction: column; text-align: center; padding-top: 140px; }
            .hero-actions { justify-content: center; }
            .hero-visual { width: 100%; height: 400px; margin-top: 40px; }
            .card-1 { left: 50%; transform: translateX(-50%); top: 0; animation: none; }
            .card-2 { right: 50%; transform: translateX(50%); bottom: 0; animation: none; }
        }
        @media (max-width: 768px) {
            .nav-links .hide-mobile { display: none; }
            .hero h1 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>

    <nav>
        <div class="brand">
            <i data-lucide="wallet" width="28" height="28"></i>
            ExpenseTracker
        </div>
        <div class="nav-links">
            <a href="about_us.html" class="hide-mobile">About Us</a>
            <a href="ContactUs.html" class="hide-mobile">Contact</a>
            
            <a href="login.html" class="btn-outline">Log In</a>
            <a href="registrationform.html" class="btn-primary">Sign Up</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Master Your Money, <span>Effortlessly.</span></h1>
            <p>A simple, beautiful, and secure way to track your daily expenses, monitor your monthly budget, and achieve your financial goals.</p>
            <div class="hero-actions">
                <a href="registrationform.html" class="btn-primary" style="padding: 14px 28px; font-size: 1.1rem;">Start Tracking for Free</a>
                <a href="about_us.html" style="font-weight: 600; display: flex; align-items: center; gap: 6px; color: var(--text);">Learn More <i data-lucide="arrow-right" width="18" height="18"></i></a>
            </div>
        </div>
        <div class="hero-visual">
            <!-- Mock UI Card 1 -->
            <div class="glass-card card-1">
                <div class="metric">
                    <div class="metric-icon"><i data-lucide="pie-chart"></i></div>
                    <div class="metric-info">
                        <h4>Monthly Budget</h4>
                        <p>₹12,500</p>
                    </div>
                </div>
                <div style="height:6px; background:var(--border); border-radius:3px; overflow:hidden; margin-bottom:8px;">
                    <div style="height:100%; width:65%; background:var(--primary-light);"></div>
                </div>
                <p style="font-size:0.75rem; color:var(--text-light); text-align:right;">65% Used</p>
            </div>
            
            <!-- Mock UI Card 2 -->
            <div class="glass-card card-2">
                <div class="metric">
                    <div class="metric-icon" style="color:#d97706; background:rgba(217,119,6,0.15);"><i data-lucide="shopping-bag"></i></div>
                    <div class="metric-info">
                        <h4>Recent Expense</h4>
                        <p>₹840.00</p>
                    </div>
                </div>
                <p style="font-size:0.8rem; color:var(--text-light);"><i data-lucide="clock" width="12" height="12"></i> Just now • Groceries</p>
            </div>
        </div>
    </section>

    <section class="features">
        <h2 style="font-size: 2.2rem; margin-bottom: 16px;">Everything you need to succeed</h2>
        <p style="color: var(--text-light); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Powerful features wrapped in an incredibly easy-to-use interface.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="bar-chart-2" width="32" height="32"></i></div>
                <h3>Visual Analytics</h3>
                <p>Understand your spending habits instantly with beautiful charts, category breakdowns, and historical data exports.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="shield-check" width="32" height="32"></i></div>
                <h3>Secure & Private</h3>
                <p>Your financial data belongs to you. We use industry-standard security to ensure your information stays private and safe.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="zap" width="32" height="32"></i></div>
                <h3>Lightning Fast</h3>
                <p>Log expenses in seconds. Our streamlined dashboard gets out of your way so you can focus on what matters.</p>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-links">
            <a href="about_us.html">About Us</a>
            <a href="ContactUs.html">Contact</a>
            <a href="privacy_policy.html">Privacy Policy</a>
            <a href="terms.html">Terms of Service</a>
        </div>
        <p>&copy; <?php echo date('Y'); ?> Expense Tracker. All rights reserved.</p>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // System-based Theme Logic
        function applySystemTheme(e) {
            const isDark = e.matches;
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        // Apply on load
        applySystemTheme(mediaQuery);
        // Listen for system changes
        mediaQuery.addEventListener('change', applySystemTheme);
    </script>
</body>
</html>
