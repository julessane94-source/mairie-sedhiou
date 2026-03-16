<?php
require_once 'config.php';

// Redirection si déjà connecté
if (isLoggedIn()) {
    switch($_SESSION['user_role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            exit();
        case 'agent':
            header('Location: agent/dashboard.php');
            exit();
        case 'public':
            header('Location: public/dashboard.php');
            exit();
    }
}

// Récupérer les informations de la mairie
$infos_mairie = [];
try {
    $stmt = $pdo->query("
        SELECT i.*, u.nom, u.prenom 
        FROM infos_mairie i
        LEFT JOIN users u ON i.auteur_id = u.id
        ORDER BY i.date_publication DESC 
        LIMIT 3
    ");
    $infos_mairie = $stmt->fetchAll();
} catch (Exception $e) {
    $infos_mairie = [];
}

// Récupérer les paramètres
$site_name = getSetting('site_name', 'Mairie');
$email_contact = getSetting('email_contact', 'contact@mairie.fr');
$telephone = getSetting('telephone', '01 23 45 67 89');
$adresse = getSetting('adresse', 'Place de la Mairie');
$horaires = getSetting('horaires', 'Lun-Ven: 8h-17h');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_name) ?> - Accueil</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background-color: #0056b3 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .hero {
            background: linear-gradient(135deg, #0056b3, #003d7a);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0056b3;
        }
        
        .section-title h2 {
            color: #333;
            font-weight: 600;
        }
        
        .service-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            height: 100%;
            border: 1px solid #e9ecef;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .service-icon {
            font-size: 2.5rem;
            color: #0056b3;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .service-card h4 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .service-card p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .btn-service {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-service:hover {
            background-color: #003d7a;
            color: white;
        }
        
        .btn-outline-service {
            border: 1px solid #0056b3;
            color: #0056b3;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .btn-outline-service:hover {
            background-color: #0056b3;
            color: white;
        }
        
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0056b3;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .info-date {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .info-card h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .footer {
            background-color: #343a40;
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        
        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .footer-contact p {
            margin-bottom: 10px;
            color: #adb5bd;
        }
        
        .footer-contact i {
            width: 25px;
            color: #0056b3;
        }
        
        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            line-height: 36px;
            text-align: center;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: white;
            margin-right: 5px;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        
        .footer-bottom {
            border-top: 1px solid #495057;
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
            color: #adb5bd;
        }
        
        .stats {
            background-color: #e9ecef;
            padding: 40px 0;
            margin: 40px 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #0056b3;
            display: block;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1rem;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .service-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-city me-2"></i>
                <?= htmlspecialchars($site_name) ?>
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
                    <li class="nav-item">
                        <a class="nav-link" href="public/register.php">
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="accueil" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1>Bienvenue à la <?= htmlspecialchars($site_name) ?></h1>
                    <p class="lead">Simplifiez vos démarches administratives en ligne. Accédez à tous nos services 24h/24 et 7j/7.</p>
                    <div class="mt-4">
                        <a href="public/register.php" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="section-title">
                <h2>Nos Services en Ligne</h2>
                <p class="text-muted">Des services simples et rapides pour toutes vos démarches</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h4>Extrait de Naissance</h4>
                        <p>Obtenez vos copies d'actes de naissance en ligne</p>
                        <a href="public/nouvelle_demande.php?type=extrait_naissance" class="btn-service">
                            Faire une demande
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Mariage</h4>
                        <p>Demandez vos certificats de mariage</p>
                        <a href="public/nouvelle_demande.php?type=mariage" class="btn-service">
                            Faire une demande
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-cross"></i>
                        </div>
                        <h4>Décès</h4>
                        <p>Obtenez les certificats de décès</p>
                        <a href="public/nouvelle_demande.php?type=deces" class="btn-service">
                            Faire une demande
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Certificat de Résidence</h4>
                        <p>Attestation de domicile en ligne</p>
                        <a href="public/nouvelle_demande.php?type=residence" class="btn-service">
                            Faire une demande
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h4>Déclaration de Naissance</h4>
                        <p>Déclarez une naissance</p>
                        <a href="public/nouvelle_demande.php?type=declaration_naissance" class="btn-service">
                            Déclarer
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4>Contact</h4>
                        <p>Posez vos questions</p>
                        <a href="public/contact.php" class="btn-service">
                            Nous contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Informations Mairie -->
    <section id="informations" class="py-5 bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Informations de la Mairie</h2>
                <p class="text-muted">Restez informé des dernières actualités</p>
            </div>
            
            <div class="row">
                <?php if (!empty($infos_mairie)): ?>
                    <?php foreach($infos_mairie as $info): ?>
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-date">
                                    <i class="far fa-calendar-alt me-2"></i>
                                    <?= date('d/m/Y', strtotime($info['date_publication'])) ?>
                                </div>
                                <h5><?= htmlspecialchars($info['titre']) ?></h5>
                                <p><?= substr(htmlspecialchars($info['contenu']), 0, 150) ?>...</p>
                                <small class="text-muted">
                                    Publié par <?= htmlspecialchars($info['prenom'] . ' ' . $info['nom']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Aucune information disponible pour le moment</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><?= htmlspecialchars($site_name) ?></h5>
                    <p class="text-muted">Votre plateforme de services municipaux en ligne.</p>
                    <div class="social-links">
                        <?php if (getSetting('facebook_url')): ?>
                            <a href="<?= htmlspecialchars(getSetting('facebook_url')) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (getSetting('twitter_url')): ?>
                            <a href="<?= htmlspecialchars(getSetting('twitter_url')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (getSetting('instagram_url')): ?>
                            <a href="<?= htmlspecialchars(getSetting('instagram_url')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (getSetting('linkedin_url')): ?>
                            <a href="<?= htmlspecialchars(getSetting('linkedin_url')) ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#accueil">Accueil</a></li>
                        <li class="mb-2"><a href="#services">Services</a></li>
                        <li class="mb-2"><a href="public/register.php">Inscription</a></li>
                        <li class="mb-2"><a href="login.php">Connexion</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5>Contact</h5>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($adresse) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($telephone) ?></p>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($email_contact) ?></p>
                        <p><i class="fas fa-clock"></i> <?= htmlspecialchars($horaires) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?> - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>