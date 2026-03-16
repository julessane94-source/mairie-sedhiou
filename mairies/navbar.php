<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/mairie/index.php">
            <i class="bi bi-bank"></i> Mairie Connect
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-item nav-link" href="/mairie/agent/dashboard.php">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link active" href="#">Traitement des demandes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link" href="/mairie/agent/liste_citoyens.php">Citoyens</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3 text-white">
                    <i class="bi bi-person-circle"></i> Agent Connecté
                </span>
                <a href="/mairie/logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">