<?php
require_once 'config.php';

// Redirection si déjà connecté
if (isLoggedIn()) {
    switch($_SESSION['user_role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'agent':
            header('Location: agent/dashboard.php');
            break;
        case 'public':
            header('Location: public/dashboard.php');
            break;
    }
    exit();
}

// Récupérer les paramètres
$settings = getSettings();

// Récupérer les informations de la mairie pour l'affichage
try {
    $stmt_infos = $pdo->query("
        SELECT i.*, u.nom, u.prenom 
        FROM infos_mairie i
        LEFT JOIN users u ON i.auteur_id = u.id
        ORDER BY i.date_publication DESC 
        LIMIT 3
    ");
    $infos_mairie = $stmt_infos->fetchAll();
} catch (Exception $e) {
    $infos_mairie = [];
}

// Statistiques pour l'affichage
try {
    $stats = [
        'citoyens' => $pdo->query("SELECT COUNT(*) FROM citoyens")->fetchColumn(),
        'demandes' => $pdo->query("SELECT COUNT(*) FROM demandes")->fetchColumn(),
        'agents' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='agent'")->fetchColumn()
    ];
} catch (Exception $e) {
    $stats = ['citoyens' => 0, 'demandes' => 0, 'agents' => 0];
}

// Vérifier si le site est en mode maintenance
if (getSetting('maintenance_mode', false) && basename($_SERVER['PHP_SELF']) != 'maintenance.php') {
    header('Location: maintenance.php');
    exit();
}

// Vérifier si les inscriptions sont ouvertes
$registration_open = getSetting('registration_open', true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(getSetting('site_name', 'Mairie Services')) ?> - Plateforme des Services Publics</title>
    <meta name="description" content="Plateforme en ligne pour vos démarches administratives municipales">
    
    <!-- Favicon -->
    <?php if (getSetting('favicon_url')): ?>
        <link rel="icon" href="<?= htmlspecialchars(getSetting('favicon_url')) ?>" type="image/x-icon">
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Google Analytics -->
    <?php if (getSetting('google_analytics_id')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(getSetting('google_analytics_id')) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars(getSetting('google_analytics_id')) ?>');
    </script>
    <?php endif; ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            color: #333;
        }

        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: white !important;
            letter-spacing: 1px;
        }

        .navbar-brand i {
            margin-right: 10px;
            color: #ffd700;
        }

        <?php if (getSetting('logo_url')): ?>
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        <?php endif; ?>

        .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            padding: 5px 0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #ffd700;
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            padding: 100px 0;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,170.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.1;
        }

        .hero-content {
            color: white;
            z-index: 1;
            position: relative;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-hero {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0 10px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-hero-primary {
            background: #ffd700;
            color: #333;
            border: 2px solid #ffd700;
        }

        .btn-hero-primary:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255,215,0,0.3);
        }

        .btn-hero-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-hero-secondary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }

        .hero-image {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

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

        /* Services Section */
        .services {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .section-title p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .service-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-icon {
            width: 100px;
            height: 100px;
            line-height: 100px;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 25px;
            color: white;
            font-size: 40px;
            transition: all 0.3s;
        }

        .service-card:hover .service-icon {
            transform: rotateY(360deg);
        }

        .service-card h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .service-card p {
            color: #6c757d;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .service-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }

        .service-link i {
            margin-left: 5px;
            transition: transform 0.3s;
        }

        .service-link:hover {
            color: #764ba2;
        }

        .service-link:hover i {
            transform: translateX(5px);
        }

        /* Statistiques */
        .stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0;
            color: white;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Informations */
        .infos {
            padding: 100px 0;
            background: white;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            height: 100%;
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .info-date {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: block;
        }

        .info-card h5 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .info-card p {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .info-author {
            color: #667eea;
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Footer */
        .footer {
            background: #1a1a2e;
            color: white;
            padding: 70px 0 20px;
        }

        .footer h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: #667eea;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #a0a0b0;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }

        .footer-contact p {
            color: #a0a0b0;
            margin-bottom: 10px;
        }

        .footer-contact i {
            color: #667eea;
            margin-right: 10px;
            width: 20px;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: white;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #667eea;
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 50px;
            padding-top: 20px;
            text-align: center;
            color: #a0a0b0;
        }

        /* Maintenance Mode Banner */
        .maintenance-banner {
            background: #dc3545;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }
            to {
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons .btn {
                display: block;
                margin: 10px 0;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
        }

        /* Loading animation */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Back to top */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s;
            z-index: 99;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .back-to-top.show {
            opacity: 1;
        }

        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
    </div>

    <!-- Maintenance Mode Banner -->
    <?php if (getSetting('maintenance_mode', false)): ?>
    <div class="maintenance-banner">
        <i class="fas fa-tools me-2"></i>
        Le site est actuellement en mode maintenance. Seuls les administrateurs peuvent y accéder.
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="navbar" style="<?= getSetting('maintenance_mode', false) ? 'margin-top: 40px;' : '' ?>">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php if (getSetting('logo_url')): ?>
                    <img src="<?= htmlspecialchars(getSetting('logo_url')) ?>" alt="<?= htmlspecialchars(getSetting('site_name')) ?>">
                <?php else: ?>
                    <i class="fas fa-city"></i>
                <?php endif; ?>
                <?= htmlspecialchars(getSetting('site_name')) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#informations">Informations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Connexion
                        </a>
                    </li>
                    <?php if (getSetting('registration_open', true)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="public/register.php">
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="accueil" class="hero d-flex align-items-center" style="<?= getSetting('maintenance_mode', false) ? 'padding-top: 140px;' : '' ?>">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <h1>Bienvenue à <?= htmlspecialchars(getSetting('site_name')) ?></h1>
                    <p class="lead">Simplifiez vos démarches administratives en ligne. Accédez à tous nos services 24h/24 et 7j/7 depuis chez vous.</p>
                    <div class="hero-buttons">
                        <?php if (getSetting('registration_open', true)): ?>
                        <a href="public/register.php" class="btn btn-hero btn-hero-primary">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire
                        </a>
                        <?php endif; ?>
                        <a href="login.php" class="btn btn-hero btn-hero-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                    </div>
                    <div class="row mt-5">
                        <div class="col-4">
                            <div class="text-center">
                                <h3 class="text-white mb-0"><?= number_format($stats['citoyens']) ?></h3>
                                <small>Citoyens</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h3 class="text-white mb-0"><?= number_format($stats['demandes']) ?></h3>
                                <small>Demandes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h3 class="text-white mb-0"><?= number_format($stats['agents']) ?></h3>
                                <small>Agents</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                    <img src="https://illustrations.popsy.co/amber/city-services.svg" alt="Illustration" class="img-fluid hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Nos Services en Ligne</h2>
                <p>Des services simples, rapides et sécurisés pour toutes vos démarches</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h4>Extrait de Naissance</h4>
                        <p>Obtenez rapidement vos copies d'actes de naissance en quelques clics</p>
                        <a href="public/nouvelle_demande.php?type=extrait_naissance" class="service-link">
                            Faire une demande <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Mariage</h4>
                        <p>Demandez vos certificats de mariage et informations associées</p>
                        <a href="public/nouvelle_demande.php?type=mariage" class="service-link">
                            Faire une demande <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-cross"></i>
                        </div>
                        <h4>Décès</h4>
                        <p>Obtenez les certificats de décès pour vos démarches</p>
                        <a href="public/nouvelle_demande.php?type=deces" class="service-link">
                            Faire une demande <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Certificat de Résidence</h4>
                        <p>Attestation de domicile avec pièce d'identité requise</p>
                        <a href="public/nouvelle_demande.php?type=residence" class="service-link">
                            Faire une demande <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h4>Déclaration de Naissance</h4>
                        <p>Déclarez une naissance auprès de la mairie</p>
                        <a href="public/nouvelle_demande.php?type=declaration_naissance" class="service-link">
                            Déclarer <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4>Contact & Questions</h4>
                        <p>Posez vos questions à nos agents, nous vous répondrons rapidement</p>
                        <a href="public/contact.php" class="service-link">
                            Nous contacter <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistiques -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-4" data-aos="zoom-in">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['citoyens']) ?></span>
                        <span class="stat-label">Citoyens inscrits</span>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['demandes']) ?></span>
                        <span class="stat-label">Demandes traitées</span>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['agents']) ?></span>
                        <span class="stat-label">Agents dédiés</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Informations Mairie -->
    <section id="informations" class="infos">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Informations de la Mairie</h2>
                <p>Restez informé des dernières actualités</p>
            </div>
            
            <div class="row">
                <?php if (!empty($infos_mairie)): ?>
                    <?php foreach($infos_mairie as $index => $info): ?>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                            <div class="info-card">
                                <span class="info-date">
                                    <i class="far fa-calendar-alt me-2"></i>
                                    <?= date('d/m/Y', strtotime($info['date_publication'])) ?>
                                </span>
                                <h5><?= htmlspecialchars($info['titre']) ?></h5>
                                <p><?= substr(htmlspecialchars($info['contenu']), 0, 150) ?>...</p>
                                <div class="info-author">
                                    <i class="fas fa-user me-2"></i>
                                    Publié par <?= htmlspecialchars($info['prenom'] . ' ' . $info['nom']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Aucune information disponible pour le moment</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($infos_mairie)): ?>
                <div class="text-center mt-4">
                    <a href="public/infos_mairie.php" class="btn btn-outline-primary">
                        Voir toutes les actualités <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4" data-aos="fade-right">
                    <h5><?= htmlspecialchars(getSetting('site_name')) ?></h5>
                    <p class="text-muted">Votre plateforme de services municipaux en ligne. Simplifiez vos démarches administratives avec notre service moderne et efficace.</p>
                    <div class="social-links">
                        <a href="<?= htmlspecialchars(getSetting('facebook_url', '#')) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= htmlspecialchars(getSetting('twitter_url', '#')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="<?= htmlspecialchars(getSetting('linkedin_url', '#')) ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <a href="<?= htmlspecialchars(getSetting('instagram_url', '#')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <h5>Liens rapides</h5>
                    <ul class="footer-links">
                        <li><a href="#accueil"><i class="fas fa-chevron-right me-2"></i>Accueil</a></li>
                        <li><a href="#services"><i class="fas fa-chevron-right me-2"></i>Services</a></li>
                        <?php if (getSetting('registration_open', true)): ?>
                        <li><a href="public/register.php"><i class="fas fa-chevron-right me-2"></i>Inscription</a></li>
                        <?php endif; ?>
                        <li><a href="login.php"><i class="fas fa-chevron-right me-2"></i>Connexion</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Mentions légales</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4" data-aos="fade-left">
                    <h5>Contact</h5>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(getSetting('adresse')) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars(getSetting('telephone')) ?></p>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars(getSetting('email_contact')) ?></p>
                        <p><i class="fas fa-clock"></i> <?= htmlspecialchars(getSetting('horaires')) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(getSetting('site_name')) ?> - Tous droits réservés. Version 1.0</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Loading spinner
        window.addEventListener('load', function() {
            document.getElementById('loading').style.display = 'none';
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Back to top button
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>