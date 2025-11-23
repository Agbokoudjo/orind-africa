#!/bin/bash

################################################################################
# Setup Upload Directories - Script de configuration des permissions
# 
# Usage : ./setup-upload-directories.sh
# 
# Ce script :
# - Crée les répertoires s'ils n'existent pas
# - Vérifie s'ils contiennent déjà des fichiers
# - Configure les permissions et propriétaire
# - Teste l'écriture
#
# @author AGBOKOUDJO Franck
################################################################################

set -e  # Arrêter si une commande échoue

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DIRS=(
    "public/uploads"
    "public/uploads/avatars"
    "public/uploads/documents"
)

WEB_USER="www-data"  # À changer si nécessaire (nginx, apache, etc.)
PERMISSIONS="755"

################################################################################
# Fonctions utilitaires
################################################################################

log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

################################################################################
# Vérifier si le script est exécuté depuis le bon répertoire
################################################################################

if [ ! -d "public" ]; then
    log_error "Le répertoire 'public' n'existe pas"
    log_info "Assurez-vous d'exécuter ce script depuis la racine du projet Symfony"
    exit 1
fi

log_info "🚀 Démarrage de la configuration des répertoires d'upload"
echo ""

################################################################################
# Vérifier et créer les répertoires
################################################################################

log_info "📁 Vérification et création des répertoires..."
echo ""

for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        # Le répertoire existe
        file_count=$(find "$dir" -type f 2>/dev/null | wc -l)
        
        if [ "$file_count" -gt 0 ]; then
            log_warning "$dir existe déjà avec $file_count fichier(s) - Conservation"
        else
            log_info "$dir existe (vide)"
        fi
    else
        # Le répertoire n'existe pas, le créer
        mkdir -p "$dir"
        log_success "✨ $dir créé"
    fi
done

echo ""

################################################################################
# Configurer les permissions
################################################################################

log_info "🔐 Configuration des permissions ($PERMISSIONS)..."
echo ""

for dir in "${DIRS[@]}"; do
    sudo chmod  $PERMISSIONS "$dir"
    log_success "Permissions $PERMISSIONS sur $dir"
done

echo ""

################################################################################
# Changer le propriétaire
################################################################################

log_info "👤 Changement du propriétaire en $WEB_USER..."
echo ""

# Vérifier que l'utilisateur existe
if id "$WEB_USER" &>/dev/null; then
    for dir in "public/media" "public/uploads"; do
        sudo chown -R "$WEB_USER:$WEB_USER" "$dir"
        log_success "Propriétaire changé en $WEB_USER pour $dir"
    done
else
    log_warning "L'utilisateur '$WEB_USER' n'existe pas sur ce système"
    log_info "Utilisateurs web courants : www-data, nginx, apache"
    log_info "Modifiez WEB_USER=$WEB_USER dans le script si nécessaire"
fi

echo ""

################################################################################
# Afficher les permissions actuelles
################################################################################

log_info "📊 Vérification des permissions actuelles..."
echo ""

for dir in "${DIRS[@]}"; do
    echo "$(ls -ld "$dir")"
done

echo ""

################################################################################
# Tester l'écriture
################################################################################

log_info "🧪 Test d'écriture dans les répertoires..."
echo ""

test_write() {
    local dir="$1"
    local test_file="$dir/.write_test_$$"
    
    if touch "$test_file" 2>/dev/null; then
        rm -f "$test_file"
        log_success "Écriture OK dans $dir"
        return 0
    else
        log_error "Impossible d'écrire dans $dir"
        log_info "Vérifiez les permissions ou le propriétaire"
        return 1
    fi
}

test_write "public/uploads/avatars"
test_write "public/uploads/documents"

echo ""

################################################################################
# Résumé final
################################################################################

log_success "✨ Configuration terminée !"
echo ""

log_info "Résumé :"
echo "  • Répertoires : ${#DIRS[@]} configurés"
echo "  • Permissions : $PERMISSIONS"
echo "  • Propriétaire : $WEB_USER"
echo ""

log_info "Prochaines étapes :"
echo "  1. Redémarrer Nginx : sudo systemctl restart nginx"
echo "  2. Redémarrer PHP-FPM : sudo systemctl restart php-fpm (ou php8.x-fpm)"
echo "  3. Vider le cache Symfony : php bin/console cache:clear"
echo ""

log_success "✅ Tout est prêt pour les uploads !"