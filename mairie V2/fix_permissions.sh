#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}Correction des permissions...${NC}"

# Créer les dossiers nécessaires
mkdir -p config
mkdir -p uploads
mkdir -p logs

# Donner les permissions
chmod 777 config/
chmod 777 uploads/
chmod 777 logs/

# Créer le fichier settings.json s'il n'existe pas
if [ ! -f config/settings.json ]; then
    echo '{
    "site_name": "Mairie Services",
    "email_contact": "contact@mairie.fr",
    "telephone": "01 23 45 67 89",
    "adresse": "Place de la Mairie, 75000 Paris",
    "horaires": "Lundi-Vendredi: 8h-17h, Samedi: 9h-12h",
    "max_file_size": 5,
    "allowed_file_types": "pdf,jpg,jpeg,png",
    "smtp_host": "",
    "smtp_port": 587,
    "smtp_user": "",
    "smtp_pass": "",
    "maintenance_mode": false,
    "registration_open": true
}' > config/settings.json
    echo -e "${GREEN}Fichier settings.json créé${NC}"
fi

chmod 666 config/settings.json

echo -e "${GREEN}Permissions corrigées avec succès !${NC}"
echo -e "${YELLOW}Résumé :${NC}"
ls -la config/
ls -la uploads/