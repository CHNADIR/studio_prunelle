#!/bin/bash

# Script pour charger les fixtures des référentiels et planches
# Sprint 3 - Référentiels dynamiques + Sprint 4 - Gestion des planches

echo "🚀 Chargement des fixtures pour les référentiels et planches..."

# Vérifier si l'environnement Docker est actif
if [ -f "compose.yaml" ]; then
    echo "📦 Utilisation de Docker Compose..."
    docker compose exec app php bin/console doctrine:fixtures:load --group=referential --append
    docker compose exec app php bin/console doctrine:fixtures:load --group=planches --append
else
    echo "🐘 Utilisation de PHP local..."
    php bin/console doctrine:fixtures:load --group=referential --append
    php bin/console doctrine:fixtures:load --group=planches --append
fi

echo "✅ Fixtures chargées avec succès !"
echo ""
echo "📊 Référentiels disponibles :"
echo "   - Types de prise : Photo individuelle, Photo de classe, etc."
echo "   - Types de vente : Vente libre, Vente groupée, etc."  
echo "   - Thèmes : Automne, Hiver, Printemps, Noël, etc."
echo ""
echo "📋 Planches disponibles :"
echo "   - Planches individuelles : Portrait A4, Photo 10x15, etc."
echo "   - Planches fratries : Pack Fratrie A4, Photo Fratrie 13x18, etc."
echo "   - Planches seules : Poster A3, Canvas 20x30, etc."
echo ""
echo "🎯 Vous pouvez maintenant tester l'ajout dynamique dans les formulaires !" 