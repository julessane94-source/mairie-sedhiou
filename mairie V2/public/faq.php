<?php
require_once '../config.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'public') {
    header('Location: ../login.php');
    exit();
}

// FAQ par catégories
$faq_categories = [
    'compte' => [
        'titre' => 'Compte et inscription',
        'icone' => 'fas fa-user',
        'questions' => [
            [
                'q' => 'Comment créer un compte ?',
                'r' => 'Cliquez sur "S\'inscrire" sur la page d\'accueil et remplissez le formulaire avec vos informations personnelles. Vous recevrez un numéro citoyen unique après validation.'
            ],
            [
                'q' => 'J\'ai oublié mon mot de passe',
                'r' => 'Utilisez la fonction "Mot de passe oublié" sur la page de connexion. Un email vous sera envoyé avec les instructions pour réinitialiser votre mot de passe.'
            ],
            [
                'q' => 'Comment modifier mes informations personnelles ?',
                'r' => 'Connectez-vous à votre espace, allez dans "Mon profil" et mettez à jour vos informations. N\'oubliez pas de sauvegarder vos modifications.'
            ],
            [
                'q' => 'Puis-je avoir plusieurs comptes ?',
                'r' => 'Non, un seul compte par personne est autorisé. Si vous avez des problèmes avec votre compte, contactez le support.'
            ]
        ]
    ],
    'demandes' => [
        'titre' => 'Demandes en ligne',
        'icone' => 'fas fa-file-alt',
        'questions' => [
            [
                'q' => 'Quels types de demandes puis-je faire en ligne ?',
                'r' => 'Vous pouvez demander : extraits de naissance, certificats de mariage, certificats de décès, certificats de résidence, et déclarer une naissance.'
            ],
            [
                'q' => 'Combien de temps faut-il pour traiter une demande ?',
                'r' => 'Les délais varient selon le type de demande : 2-3 jours ouvrés pour les demandes simples, 5-7 jours pour celles nécessitant des vérifications supplémentaires.'
            ],
            [
                'q' => 'Comment suivre l\'avancement de ma demande ?',
                'r' => 'Connectez-vous à votre espace et allez dans "Mes demandes". Vous verrez le statut de chaque demande (en attente, en cours, traité, rejeté).'
            ],
            [
                'q' => 'Que faire si ma demande est rejetée ?',
                'r' => 'Vous recevrez une notification avec la raison du rejet. Vous pouvez corriger les informations et soumettre une nouvelle demande, ou contacter le support pour plus d\'informations.'
            ],
            [
                'q' => 'Puis-je annuler une demande ?',
                'r' => 'Oui, tant que la demande est en statut "en attente", vous pouvez l\'annuler depuis la page "Mes demandes".'
            ]
        ]
    ],
    'documents' => [
        'titre' => 'Documents et pièces jointes',
        'icone' => 'fas fa-file-pdf',
        'questions' => [
            [
                'q' => 'Quels formats de fichiers sont acceptés ?',
                'r' => 'Les formats acceptés sont : PDF, JPG, JPEG, PNG. La taille maximale est de 5 Mo par fichier.'
            ],
            [
                'q' => 'Comment joindre un document à ma demande ?',
                'r' => 'Dans le formulaire de demande, utilisez le bouton "Choisir un fichier" pour sélectionner votre document. Assurez-vous qu\'il respecte les formats et la taille autorisés.'
            ],
            [
                'q' => 'Puisjoindre plusieurs fichiers ?',
                'r' => 'Pour le moment, un seul fichier par demande est accepté. Si vous avez plusieurs documents, regroupez-les en un seul fichier PDF.'
            ],
            [
                'q' => 'Mes documents sont-ils sécurisés ?',
                'r' => 'Oui, tous les documents sont stockés de façon sécurisée et ne sont accessibles que par vous et les agents traitant votre demande.'
            ]
        ]
    ],
    'technique' => [
        'titre' => 'Problèmes techniques',
        'icone' => 'fas fa-cog',
        'questions' => [
            [
                'q' => 'Le site ne répond pas correctement',
                'r' => 'Essayez de vider le cache de votre navigateur ou d\'utiliser un autre navigateur (Chrome, Firefox, Edge). Si le problème persiste, contactez le support.'
            ],
            [
                'q' => 'Je n\'arrive pas à me connecter',
                'r' => 'Vérifiez vos identifiants (email et mot de passe). Utilisez la fonction "Mot de passe oublié" si nécessaire. Assurez-vous que votre compte est bien activé.'
            ],
            [
                'q' => 'L\'upload de fichier échoue',
                'r' => 'Vérifiez que votre fichier ne dépasse pas 5 Mo et qu\'il est au format accepté (PDF, JPG, PNG). Réduisez la taille si nécessaire ou convertissez le format.'
            ],
            [
                'q' => 'Les pages mettent du temps à charger',
                'r' => 'Cela peut être dû à votre connexion internet. Essayez de rafraîchir la page ou de vous connecter plus tard. Si le problème est récurrent, contactez-nous.'
            ]
        ]
    ],
    'confidentialite' => [
        'titre' => 'Confidentialité et données',
        'icone' => 'fas fa-lock',
        'questions' => [
            [
                'q' => 'Mes données personnelles sont-elles protégées ?',
                'r' => 'Oui, toutes vos données sont confidentielles et protégées conformément au RGPD. Elles ne sont utilisées que dans le cadre de vos démarches administratives.'
            ],
            [
                'q' => 'Qui a accès à mes informations ?',
                'r' => 'Seuls vous et les agents municipaux habilités à traiter vos demandes ont accès à vos informations personnelles.'
            ],
            [
                'q' => 'Comment sont utilisées mes données ?',
                'r' => 'Vos données sont utilisées uniquement pour le traitement de vos demandes et l\'amélioration de nos services. Elles ne sont jamais vendues à des tiers.'
            ],
            [
                'q' => 'Puis-je demander la suppression de mes données ?',
                'r' => 'Oui, conformément au RGPD, vous pouvez demander la suppression de vos données. Contactez le support pour toute demande à ce sujet.'
            ]
        ]
    ]
];

// Recherche
$search_result = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = strtolower(trim($_GET['search']));
    $search_result = [];
    
    foreach ($faq_categories as $cat_key => $category) {
        foreach ($category['questions'] as $question) {
            if (strpos(strtolower($question['q']), $search_term) !== false || 
                strpos(strtolower($question['r']), $search_term) !== false) {
                $search_result[] = [
                    'categorie' => $category['titre'],
                    'question' => $question['q'],
                    'reponse' => $question['r']
                ];
            }
        }
    }
}

// Page courante pour le menu actif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Espace Citoyen</title>
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
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 5px 5px 5px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .search-box input {
            border: none;
            background: transparent;
            flex: 1;
            padding: 12px 0;
            outline: none;
            font-size: 1rem;
        }
        
        .search-box button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .faq-category {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .category-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .category-title i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .faq-item {
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .faq-question {
            background: #f8f9fa;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 500;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .faq-question:hover {
            background: #e9ecef;
        }
        
        .faq-question i {
            color: #667eea;
            transition: transform 0.3s;
        }
        
        .faq-question[aria-expanded="true"] i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 20px;
            background: white;
            color: #6c757d;
            line-height: 1.6;
            border-top: 1px solid #e9ecef;
        }
        
        .contact-support {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-top: 30px;
            text-align: center;
        }
        
        .contact-support .btn {
            background: white;
            color: #f5576c;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .contact-support .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .search-result-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .search-result-category {
            font-size: 0.85rem;
            color: #667eea;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .search-result-question {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .search-result-answer {
            color: #6c757d;
            line-height: 1.6;
        }
        
        .no-result {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .no-result i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .badge-category {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 10px;
        }
        
        .badge-compte { background: #e3f2fd; color: #1976d2; }
        .badge-demandes { background: #e8f5e9; color: #388e3c; }
        .badge-documents { background: #fff3e0; color: #f57c00; }
        .badge-technique { background: #ffebee; color: #d32f2f; }
        .badge-confidentialite { background: #f3e5f5; color: #7b1fa2; }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }
            
            .search-box {
                flex-direction: column;
                border-radius: 10px;
                padding: 10px;
            }
            
            .search-box input {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .search-box button {
                width: 100%;
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
                        <h2 class="mb-2"><i class="fas fa-question-circle me-2"></i>Foire Aux Questions</h2>
                        <p class="mb-0 opacity-75">Trouvez rapidement des réponses à vos questions</p>
                    </div>
                    <a href="contact.php" class="btn btn-light">
                        <i class="fas fa-headset me-2"></i>Contact
                    </a>
                </div>
            </div>

            <!-- Barre de recherche -->
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Rechercher une question..." 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit">
                    <i class="fas fa-search me-2"></i>Rechercher
                </button>
            </form>

            <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                <!-- Résultats de recherche -->
                <h4 class="mb-3">Résultats de recherche pour "<?= htmlspecialchars($_GET['search']) ?>"</h4>
                
                <?php if (!empty($search_result)): ?>
                    <?php foreach ($search_result as $result): ?>
                        <div class="search-result-item">
                            <div class="search-result-category">
                                <i class="fas fa-folder me-2"></i><?= htmlspecialchars($result['categorie']) ?>
                            </div>
                            <div class="search-result-question">
                                <?= htmlspecialchars($result['question']) ?>
                            </div>
                            <div class="search-result-answer">
                                <?= nl2br(htmlspecialchars($result['reponse'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-3">
                        <a href="faq.php" class="btn btn-outline-primary">
                            <i class="fas fa-undo me-2"></i>Voir toutes les questions
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-result">
                        <i class="fas fa-search"></i>
                        <h4>Aucun résultat trouvé</h4>
                        <p>Essayez avec d'autres mots-clés ou consultez notre FAQ complète</p>
                        <a href="faq.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la FAQ
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Affichage des catégories FAQ -->
                <?php foreach ($faq_categories as $cat_key => $category): ?>
                    <div class="faq-category">
                        <h4 class="category-title">
                            <i class="<?= $category['icone'] ?>"></i>
                            <?= htmlspecialchars($category['titre']) ?>
                        </h4>
                        
                        <?php foreach ($category['questions'] as $index => $faq): ?>
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" 
                                     data-bs-target="#answer-<?= $cat_key . '-' . $index ?>" 
                                     aria-expanded="false">
                                    <span>
                                        <span class="badge-category badge-<?= $cat_key ?>">
                                            <?= substr($category['titre'], 0, 1) ?>
                                        </span>
                                        <?= htmlspecialchars($faq['q']) ?>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="collapse" id="answer-<?= $cat_key . '-' . $index ?>">
                                    <div class="faq-answer">
                                        <?= nl2br(htmlspecialchars($faq['r'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Contact support -->
                <div class="contact-support">
                    <i class="fas fa-headset fa-3x mb-3"></i>
                    <h4 class="mb-3">Vous n'avez pas trouvé votre réponse ?</h4>
                    <p class="mb-4">Notre équipe est là pour vous aider personnellement</p>
                    <a href="contact.php" class="btn">
                        <i class="fas fa-envelope me-2"></i>Contacter le support
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Ouvrir la question si ancré dans l'URL
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const id = window.location.hash.substring(1);
                const element = document.getElementById(id);
                if (element) {
                    new bootstrap.Collapse(element, {show: true});
                    
                    // Scroll vers l'élément
                    setTimeout(() => {
                        element.scrollIntoView({behavior: 'smooth', block: 'center'});
                    }, 300);
                }
            }
        });

        // Animation de la recherche
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        }

        // Sauvegarder l'état ouvert/fermé des questions
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.collapse');
            
            if (question && answer) {
                question.addEventListener('click', function() {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    localStorage.setItem('faq-' + answer.id, !isExpanded);
                });
                
                // Restaurer l'état
                const savedState = localStorage.getItem('faq-' + answer.id);
                if (savedState === 'true') {
                    new bootstrap.Collapse(answer, {show: true});
                }
            }
        });
    </script>
</body>
</html>