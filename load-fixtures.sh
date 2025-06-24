#!/bin/bash

# Script de chargement des fixtures optimisé - Studio Prunelle
# Utilisation: ./load-fixtures.sh [option]
# Options: complete | core | referential | users | test | demo | clean

set -e

# Configuration
COMPOSE_FILE="compose.yaml"
CONTAINER_NAME="app"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Fonction d'affichage
print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}🗃️  CHARGEMENT DES FIXTURES${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_debug() {
    echo -e "${PURPLE}🔍 $1${NC}"
}

print_data() {
    echo -e "${CYAN}📊 $1${NC}"
}

# Vérification des prérequis
check_prerequisites() {
    print_info "Vérification des prérequis..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker n'est pas installé"
        exit 1
    fi
    
    if ! docker compose ps | grep -q "Up"; then
        print_error "Les conteneurs Docker ne sont pas démarrés"
        print_info "Lancez: docker compose up -d"
        exit 1
    fi
    
    # Vérifier que les fixtures existent
    if [ ! -f "src/DataFixtures/AppFixtures.php" ]; then
        print_error "Les fixtures n'existent pas dans src/DataFixtures/"
        exit 1
    fi
    
    print_success "Prérequis vérifiés"
}

# Nettoyage de la base de données
clean_database() {
    print_info "Nettoyage de la base de données..."
    
    docker compose exec $CONTAINER_NAME php bin/console doctrine:schema:drop --force --quiet
    docker compose exec $CONTAINER_NAME php bin/console doctrine:schema:create --quiet
    
    print_success "Base de données nettoyée et recréée"
}

# Validation de la syntaxe Twig
validate_twig() {
    print_info "Validation de la syntaxe Twig..."
    
    if docker compose exec $CONTAINER_NAME php bin/console lint:twig templates --quiet; then
        print_success "Syntaxe Twig validée"
    else
        print_warning "Erreurs de syntaxe Twig détectées (non bloquant)"
    fi
}

# Chargement des fixtures par groupe
load_fixtures() {
    local group=$1
    local description=$2
    
    print_info "Chargement des fixtures: $description"
    
    if [ -z "$group" ]; then
        # Chargement complet
        docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --no-interaction
    else
        # Chargement par groupe
        docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --group=$group --no-interaction
    fi
    
    print_success "Fixtures $description chargées avec succès"
}

# Chargement ordonné des fixtures essentielles
load_core_fixtures() {
    print_info "Chargement ordonné des fixtures essentielles..."
    
    # 1. Utilisateurs d'abord
    print_debug "Étape 1/4: Chargement des utilisateurs"
    docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --group=user --no-interaction --append
    
    # 2. Écoles ensuite
    print_debug "Étape 2/4: Chargement des écoles"
    docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --group=ecole --no-interaction --append
    
    # 3. Énums référentiels
    print_debug "Étape 3/4: Chargement des enums"
    docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --group=enum --no-interaction --append
    
    # 4. Référentiels personnalisés
    print_debug "Étape 4/4: Chargement des référentiels personnalisés"
    docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --group=custom --no-interaction --append
    
    print_success "Fixtures essentielles chargées en mode incrémental"
}

# Validation des fixtures
validate_fixtures() {
    print_info "Validation des fixtures chargées..."
    
    # Compter les entités
    local users=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM user" --quiet | tail -1)
    local ecoles=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM ecole" --quiet | tail -1)
    local themes=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM theme" --quiet | tail -1)
    local type_prises=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM type_prise" --quiet | tail -1)
    local type_ventes=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM type_vente" --quiet | tail -1)
    local planches=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM planche" --quiet | tail -1)
    local pochette_indivs=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM pochette_indiv" --quiet | tail -1)
    local pochette_fratries=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM pochette_fratrie" --quiet | tail -1)
    local prises=$(docker compose exec $CONTAINER_NAME php bin/console doctrine:query:sql "SELECT COUNT(*) FROM prise_de_vue" --quiet | tail -1)
    
    echo ""
    echo -e "${GREEN}📊 RÉSUMÉ DES DONNÉES CHARGÉES:${NC}"
    echo -e "${CYAN}   👥 Utilisateurs: $users${NC}"
    echo -e "${CYAN}   🏫 Écoles: $ecoles${NC}"
    echo -e "${CYAN}   🎨 Thèmes: $themes${NC}"
    echo -e "${CYAN}   📷 Types de prise: $type_prises${NC}"
    echo -e "${CYAN}   💰 Types de vente: $type_ventes${NC}"
    echo -e "${CYAN}   🖼️  Planches: $planches${NC}"
    echo -e "${CYAN}   📦 Pochettes individuelles: $pochette_indivs${NC}"
    echo -e "${CYAN}   👨‍👩‍👧‍👦 Pochettes fratries: $pochette_fratries${NC}"
    echo -e "${CYAN}   📸 Prises de vue: $prises${NC}"
    echo ""
    
    # Validation métier
    local total_referentiels=$((themes + type_prises + type_ventes + planches + pochette_indivs + pochette_fratries))
    if [ $total_referentiels -gt 50 ]; then
        print_success "Référentiels complets: $total_referentiels éléments"
    else
        print_warning "Référentiels incomplets: seulement $total_referentiels éléments"
    fi
}

# Affichage des comptes de test
show_test_accounts() {
    echo ""
    echo -e "${GREEN}🔑 COMPTES DE TEST DISPONIBLES:${NC}"
    echo -e "${YELLOW}   SuperAdmin:${NC}"
    echo -e "     Utilisateur: ${CYAN}superadmin${NC}"
    echo -e "     Mot de passe: ${CYAN}SuperAdmin123!${NC}"
    echo -e "     URL: ${CYAN}http://localhost:8000/admin${NC}"
    echo ""
    echo -e "${YELLOW}   Admin Commercial:${NC}"
    echo -e "     Utilisateur: ${CYAN}commercial${NC}"
    echo -e "     Mot de passe: ${CYAN}Admin123!${NC}"
    echo ""
    echo -e "${YELLOW}   Photographe:${NC}"
    echo -e "     Utilisateur: ${CYAN}marie.durand${NC}"
    echo -e "     Mot de passe: ${CYAN}Photographe123!${NC}"
    echo ""
    echo -e "${YELLOW}   Test utilisateur:${NC}"
    echo -e "     Utilisateur: ${CYAN}test${NC}"
    echo -e "     Mot de passe: ${CYAN}Test123!${NC}"
    echo ""
}

# Mode démonstration avec données réalistes
load_demo_fixtures() {
    print_info "Chargement du mode démonstration..."
    
    clean_database
    validate_twig
    
    # Chargement complet pour démo
    docker compose exec $CONTAINER_NAME php bin/console doctrine:fixtures:load --no-interaction
    
    validate_fixtures
    show_test_accounts
    
    echo ""
    print_success "Mode démonstration prêt !"
    print_info "L'application contient des données réalistes pour présentation"
}

# Affichage de l'aide
show_help() {
    echo "Usage: $0 [option]"
    echo ""
    echo "Options disponibles:"
    echo -e "  ${GREEN}complete${NC}     - Charge toutes les fixtures (défaut)"
    echo -e "  ${GREEN}core${NC}         - Charge uniquement les données de base (users, écoles)"
    echo -e "  ${GREEN}referential${NC}  - Charge uniquement les référentiels"
    echo -e "  ${GREEN}users${NC}        - Charge uniquement les utilisateurs"
    echo -e "  ${GREEN}test${NC}         - Charge un jeu de données minimal pour les tests"
    echo -e "  ${GREEN}demo${NC}         - Mode démonstration avec données réalistes complètes"
    echo -e "  ${GREEN}clean${NC}        - Nettoie et recharge toutes les fixtures"
    echo -e "  ${GREEN}incremental${NC}  - Chargement incrémental des fixtures essentielles"
    echo -e "  ${GREEN}help${NC}         - Affiche cette aide"
    echo ""
    echo "Exemples:"
    echo "  $0                  # Charge toutes les fixtures"
    echo "  $0 core             # Charge les données de base"
    echo "  $0 demo             # Mode présentation complète"
    echo "  $0 clean            # Nettoie et recharge tout"
    echo ""
    echo "Données créées:"
    echo "  • 12 utilisateurs (tous rôles)"
    echo "  • 9 écoles représentatives"
    echo "  • 60+ référentiels (enum + personnalisés)"
    echo "  • 15 prises de vue avec relations complètes"
}

# Programme principal
main() {
    local option=${1:-complete}
    
    case $option in
        "help"|"-h"|"--help")
            show_help
            exit 0
            ;;
        "clean")
            print_header
            check_prerequisites
            clean_database
            validate_twig
            load_fixtures "" "complètes (après nettoyage)"
            validate_fixtures
            show_test_accounts
            ;;
        "complete"|"")
            print_header
            check_prerequisites
            validate_twig
            load_fixtures "" "complètes"
            validate_fixtures
            show_test_accounts
            ;;
        "demo")
            print_header
            check_prerequisites
            load_demo_fixtures
            ;;
        "core")
            print_header
            check_prerequisites
            load_fixtures "core" "de base"
            validate_fixtures
            ;;
        "incremental")
            print_header
            check_prerequisites
            clean_database
            load_core_fixtures
            validate_fixtures
            ;;
        "referential")
            print_header
            check_prerequisites
            load_fixtures "referential" "référentiels"
            validate_fixtures
            ;;
        "users")
            print_header
            check_prerequisites
            load_fixtures "user" "utilisateurs"
            validate_fixtures
            ;;
        "test")
            print_header
            check_prerequisites
            load_fixtures "test" "de test"
            validate_fixtures
            show_test_accounts
            ;;
        *)
            print_error "Option inconnue: $option"
            show_help
            exit 1
            ;;
    esac
    
    echo ""
    print_success "Opération terminée avec succès !"
    print_info "Studio Prunelle prêt pour les tests manuels"
}

# Exécution du script
main "$@" 