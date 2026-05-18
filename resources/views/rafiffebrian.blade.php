<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio | Rafif Febrian Putra</title>
    
    <link rel="stylesheet" href="{{ asset('css/stylerafif.css') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav>
        <div class="logo">PORTFOLIO<span>Rafif Febrian Putra</span></div>
        <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#projects">Projects</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>

    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Halo, Saya <span class="highlight">Rafif Febrian Putra</span></h1>
            <p>Mahasiswa Teknik Informatika | Network Security | Web Developer</p>
            <a href="#projects" class="btn">Lihat Portfolio</a>
        </div>
        <div class="hero-image">
            <img src="{{ asset('img/me2.png') }}" alt="Foto Profil Rafif Febrian">
        </div>
    </section>

    <section id="projects" class="projects">
        <h2 class="section-title">My <span>Projects</span></h2>
        <div class="project-grid">
            <div class="project-card">
                <i class="fas fa-server"></i>
                <h3>Network Monitoring</h3>
                <p>Monitoring server menggunakan Prometheus dan dashboard Grafana untuk analisis data real-time.</p>
            </div>
            <div class="project-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Cybersecurity</h3>
                <p>Eksplorasi tantangan CTF dan digital forensics untuk memperkuat keamanan sistem.</p>
            </div>
            <div class="project-card">
                <i class="fas fa-code"></i>
                <h3>Web Development</h3>
                <p>Membangun antarmuka web yang responsif dan fungsional menggunakan teknologi modern.</p>
            </div>
        </div>
    </section>

    <section id="contact" class="social-section">
        <h2 class="section-title">Let's <span>Connect</span></h2>
        <div class="social-links">
            <a href="https://github.com/rafiffebrian11" target="_blank" class="social-icon github">
                <i class="fab fa-github"></i> GitHub
            </a>
            <a href="https://www.linkedin.com/in/rafif-febrian-putra-65aba2356/?skipRedirect=true" target="_blank" class="social-icon linkedin">
                <i class="fab fa-linkedin"></i> LinkedIn
            </a>
            <a href="https://www.instagram.com/rafiffebrianp?igsh=MW1ienp3MnM4NThnYQ==" target="_blank" class="social-icon instagram">
                <i class="fab fa-instagram"></i> Instagram
            </a>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 Rafif Febrian. All rights reserved.</p>
    </footer>

</body>
</html>