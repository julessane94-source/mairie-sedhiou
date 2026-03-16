<?php
require_once '../config.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'public') {
    header('Location: ../login.php');
    exit();
}

// Récupérer les informations du citoyen
$citoyen = null;
try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nom, u.prenom, u.email, u.telephone 
        FROM citoyens c 
        JOIN users u ON c.user_id = u.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $citoyen = $stmt->fetch();
} catch (Exception $e) {
    error_log("Erreur récupération citoyen: " . $e->getMessage());
}

$success = '';
$error = '';

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type = $_POST['type'] ?? 'general';
    $urgence = $_POST['urgence'] ?? 'normale';
    
    // Validation
    if (empty($sujet)) {
        $error = "Veuillez saisir un sujet";
    } elseif (empty($message)) {
        $error = "Veuillez saisir votre message";
    } elseif (strlen($message) < 10) {
        $error = "Votre message doit contenir au moins 10 caractères";
    } else {
        try {
            // Déterminer le destinataire en fonction du type
            $destinataire_id = 1; // Admin par défaut
            
            if ($type === 'technique') {
                // Envoyer au service technique (admin)
                $destinataire_id = 1;
            } elseif ($type === 'demande') {
                // Envoyer au service des demandes (premier agent disponible)
                $stmt = $pdo->query("SELECT id FROM users WHERE role = 'agent' LIMIT 1");
                $agent = $stmt->fetch();
                if ($agent) {
                    $destinataire_id = $agent['id'];
                }
            }
            
            // Préparer le sujet avec le niveau d'urgence
            $sujet_complet = "[$urgence] $sujet";
            
            // Ajouter les informations du citoyen au message
            $message_complet = "Message de : " . $citoyen['prenom'] . " " . $citoyen['nom'] . "\n";
            $message_complet .= "Email : " . $citoyen['email'] . "\n";
            $message_complet .= "Téléphone : " . ($citoyen['telephone'] ?? 'Non renseigné') . "\n";
            $message_complet .= "N° Citoyen : " . $citoyen['numero_citoyen'] . "\n";
            $message_complet .= "Type : " . $type . "\n";
            $message_complet .= "Urgence : " . $urgence . "\n";
            $message_complet .= "------------------------\n\n";
            $message_complet .= $message;
            
            // Envoyer le message
            $stmt = $pdo->prepare("
                INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $destinataire_id, $sujet_complet, $message_complet]);
            
            // Journaliser l'action
            logAction('contact', "Message envoyé - Type: $type, Sujet: $sujet");
            
            $success = "Votre message a été envoyé avec succès. Notre équipe vous répondra dans les plus brefs délais.";
            
            // Vider le formulaire
            $_POST = [];
            
        } catch (Exception $e) {
            error_log("Erreur envoi message contact: " . $e->getMessage());
            $error = "Une erreur est survenue lors de l'envoi de votre message. Veuillez réessayer.";
        }
    }
}

// Récupérer les messages récents de l'utilisateur
$messages_recents = [];
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.nom, u.prenom, u.role 
        FROM messages m
        JOIN users u ON m.expediteur_id = u.id
        WHERE m.destinataire_id = ? 
        ORDER BY m.date_envoi DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages_recents = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Erreur récupération messages: " . $e->getMessage());
}

// Page courante pour le menu actif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Espace Citoyen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        
        .info-item {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .info-item i {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .info-item-content {
            flex: 1;
        }
        
        .info-item-content strong {
            display: block;
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 3px;
        }
        
        .info-item-content span {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-send:active {
            transform: translateY(0);
        }
        
        .message-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        
        .message-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .message-item.unread {
            background: #e3f2fd;
            border-left-color: #2196F3;
        }
        
        .message-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .message-sujet {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .message-preview {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .urgence-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 10px;
        }
        
        .urgence-basse { background: #d4edda; color: #155724; }
        .urgence-normale { background: #fff3cd; color: #856404; }
        .urgence-haute { background: #f8d7da; color: #721c24; }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            border: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .character-count {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        
        .character-count.warning {
            color: #ffc107;
        }
        
        .character-count.danger {
            color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }
            
            .contact-card {
                padding: 20px;
            }
            
            .info-item i {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }
            
            .info-item-content span {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Inclusion de la sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- En-tête -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-2"><i class="fas fa-headset me-2"></i>Nous contacter</h2>
                        <p class="mb-0 opacity-75">Une question ? Besoin d'aide ? Notre équipe est là pour vous répondre</p>
                    </div>
                    <a href="faq.php" class="btn btn-light">
                        <i class="fas fa-question-circle me-2"></i>FAQ
                    </a>
                </div>
            </div>

            <!-- Messages de notification -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Formulaire de contact -->
                <div class="col-md-8">
                    <div class="contact-card">
                        <h4 class="mb-4"><i class="fas fa-pen me-2 text-primary"></i>Formulaire de contact</h4>
                        
                        <form method="POST" id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Type de demande</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="general" <?= ($_POST['type'] ?? '') == 'general' ? 'selected' : '' ?>>Question générale</option>
                                        <option value="demande" <?= ($_POST['type'] ?? '') == 'demande' ? 'selected' : '' ?>>Question sur une demande</option>
                                        <option value="technique" <?= ($_POST['type'] ?? '') == 'technique' ? 'selected' : '' ?>>Problème technique</option>
                                        <option value="suggestion" <?= ($_POST['type'] ?? '') == 'suggestion' ? 'selected' : '' ?>>Suggestion</option>
                                        <option value="reclamation" <?= ($_POST['type'] ?? '') == 'reclamation' ? 'selected' : '' ?>>Réclamation</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="urgence" class="form-label">Niveau d'urgence</label>
                                    <select class="form-select" id="urgence" name="urgence" required>
                                        <option value="basse" <?= ($_POST['urgence'] ?? '') == 'basse' ? 'selected' : '' ?>>Basse</option>
                                        <option value="normale" <?= ($_POST['urgence'] ?? 'normale') == 'normale' ? 'selected' : '' ?>>Normale</option>
                                        <option value="haute" <?= ($_POST['urgence'] ?? '') == 'haute' ? 'selected' : '' ?>>Haute</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="sujet" class="form-label">Sujet</label>
                                    <input type="text" class="form-control" id="sujet" name="sujet" 
                                           value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>" 
                                           placeholder="Résumez votre demande en quelques mots" 
                                           maxlength="100" required>
                                    <div class="character-count" id="sujetCount">0/100</div>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" 
                                              rows="6" placeholder="Décrivez votre demande en détail..." 
                                              minlength="10" maxlength="2000" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                    <div class="character-count" id="messageCount">0/2000</div>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="copie" name="copie" value="1" checked>
                                        <label class="form-check-label" for="copie">
                                            Recevoir une copie de ce message par email
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn-send">
                                        <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informations de contact -->
                <div class="col-md-4">
                    <!-- Coordonnées -->
                    <div class="info-card">
                        <h5 class="mb-4"><i class="fas fa-address-card me-2"></i>Coordonnées</h5>
                        
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="info-item-content">
                                <strong>Adresse</strong>
                                <span><?= htmlspecialchars(getSetting('adresse', 'Place de la Mairie, 75000 Paris')) ?></span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div class="info-item-content">
                                <strong>Téléphone</strong>
                                <span><?= htmlspecialchars(getSetting('telephone', '01 23 45 67 89')) ?></span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div class="info-item-content">
                                <strong>Email</strong>
                                <span><?= htmlspecialchars(getSetting('email_contact', 'contact@mairie.fr')) ?></span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div class="info-item-content">
                                <strong>Horaires</strong>
                                <span><?= nl2br(htmlspecialchars(getSetting('horaires', 'Lun-Ven: 8h-17h'))) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages récents -->
                    <div class="contact-card">
                        <h5 class="mb-3"><i class="fas fa-history me-2 text-primary"></i>Messages récents</h5>
                        
                        <?php if (!empty($messages_recents)): ?>
                            <?php foreach ($messages_recents as $msg): ?>
                                <div class="message-item <?= !$msg['lu'] ? 'unread' : '' ?>">
                                    <div class="message-date">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?>
                                    </div>
                                    <div class="message-sujet">
                                        <?= htmlspecialchars(substr($msg['sujet'], 0, 40)) ?>...
                                    </div>
                                    <div class="message-preview">
                                        <?= htmlspecialchars(substr($msg['contenu'], 0, 60)) ?>...
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="messagerie.php" class="btn btn-outline-primary btn-sm">
                                    Voir tous mes messages
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">
                                <i class="fas fa-inbox me-2"></i>
                                Aucun message récent
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Liens utiles -->
                    <div class="contact-card">
                        <h5 class="mb-3"><i class="fas fa-link me-2 text-primary"></i>Liens utiles</h5>
                        
                        <div class="d-grid gap-2">
                            <a href="faq.php" class="btn btn-outline-primary text-start">
                                <i class="fas fa-question-circle me-2"></i>Consulter la FAQ
                            </a>
                            <a href="nouvelle_demande.php" class="btn btn-outline-primary text-start">
                                <i class="fas fa-plus-circle me-2"></i>Faire une demande
                            </a>
                            <a href="mes_demandes.php" class="btn btn-outline-primary text-start">
                                <i class="fas fa-file-alt me-2"></i>Suivre mes demandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Compteur de caractères pour le sujet
        const sujetInput = document.getElementById('sujet');
        const sujetCount = document.getElementById('sujetCount');
        
        sujetInput.addEventListener('input', function() {
            const length = this.value.length;
            sujetCount.textContent = length + '/100';
            
            if (length > 90) {
                sujetCount.classList.add('warning');
            } else {
                sujetCount.classList.remove('warning');
            }
            
            if (length >= 100) {
                sujetCount.classList.add('danger');
            } else {
                sujetCount.classList.remove('danger');
            }
        });
        
        // Déclencher au chargement
        if (sujetInput.value) {
            sujetInput.dispatchEvent(new Event('input'));
        }
        
        // Compteur de caractères pour le message
        const messageInput = document.getElementById('message');
        const messageCount = document.getElementById('messageCount');
        
        messageInput.addEventListener('input', function() {
            const length = this.value.length;
            messageCount.textContent = length + '/2000';
            
            if (length > 1800) {
                messageCount.classList.add('warning');
            } else {
                messageCount.classList.remove('warning');
            }
            
            if (length >= 2000) {
                messageCount.classList.add('danger');
            } else {
                messageCount.classList.remove('danger');
            }
        });
        
        // Déclencher au chargement
        if (messageInput.value) {
            messageInput.dispatchEvent(new Event('input'));
        }
        
        // Validation du formulaire
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const sujet = sujetInput.value.trim();
            const message = messageInput.value.trim();
            
            if (sujet.length < 3) {
                e.preventDefault();
                alert('Le sujet doit contenir au moins 3 caractères');
                sujetInput.focus();
                return;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Le message doit contenir au moins 10 caractères');
                messageInput.focus();
                return;
            }
        });
        
        // Auto-sauvegarde du brouillon
        let timeout;
        messageInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                localStorage.setItem('contact_message', this.value);
                localStorage.setItem('contact_sujet', sujetInput.value);
                localStorage.setItem('contact_type', document.getElementById('type').value);
                localStorage.setItem('contact_urgence', document.getElementById('urgence').value);
                console.log('Brouillon sauvegardé');
            }, 1000);
        });
        
        // Restaurer le brouillon
        window.addEventListener('load', function() {
            if (!sujetInput.value && !messageInput.value) {
                const savedMessage = localStorage.getItem('contact_message');
                const savedSujet = localStorage.getItem('contact_sujet');
                const savedType = localStorage.getItem('contact_type');
                const savedUrgence = localStorage.getItem('contact_urgence');
                
                if (savedMessage && confirm('Un brouillon non envoyé a été trouvé. Voulez-vous le restaurer ?')) {
                    messageInput.value = savedMessage;
                    sujetInput.value = savedSujet || '';
                    if (savedType) document.getElementById('type').value = savedType;
                    if (savedUrgence) document.getElementById('urgence').value = savedUrgence;
                    
                    sujetInput.dispatchEvent(new Event('input'));
                    messageInput.dispatchEvent(new Event('input'));
                }
            }
        });
        
        // Effacer le brouillon après envoi réussi
        <?php if ($success): ?>
        localStorage.removeItem('contact_message');
        localStorage.removeItem('contact_sujet');
        localStorage.removeItem('contact_type');
        localStorage.removeItem('contact_urgence');
        <?php endif; ?>
    </script>
</body>
</html>