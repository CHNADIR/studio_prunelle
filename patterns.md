# Principes et Design Patterns – Application Studio Prunelle

## **Connexion sécurisée des utilisateurs (Admin, Photographe, SuperAdmin)**

-   Principes appliqués :
-   • Sécurité (hashage des mots de passe)
-   • Contrôle d’accès via rôles
-   Design patterns recommandés :
-   • Security Bundle de Symfony
-   • Firewalls + Access Control dans security.yaml

## **Afficher ou modifier uniquement les prises de vue d’un utilisateur (photographe)**

-   Principes appliqués :
-   • Contrôle d’accès par entité
-   • Séparation des responsabilités
-   Design patterns recommandés :
-   • Voter Pattern
-   • Controller léger, logique déléguée au Voter

## **Gestion dynamique des planches, thèmes, types de vente, types de prise**

-   Principes appliqués :
-   • Responsabilité unique
-   • Évolutivité (modifiables dynamiquement)
-   Design patterns recommandés :
-   • Service Layer Pattern
-   • FormType personnalisé
-   • CRUD + Ajax (modularité)

## **Création d’une prise de vue avec logique complexe (sélection planches, thèmes...)**

-   Principes appliqués :
-   • Séparation logique métier vs présentation
-   • Validations strictes
-   Design patterns recommandés :
-   • FormType
-   • Service pour encapsuler la création
-   • Repository Pattern

## **Affichage d’une prise de vue calqué sur la fiche papier**

-   Principes appliqués :
-   • Responsabilité claire
-   • Respect du modèle de données
-   Design patterns recommandés :
-   • MVC
-   • Utilisation de Twig pour affichage structuré
-   • DTO (si nécessaire)

## **Gestion des utilisateurs par le SuperAdmin**

-   Principes appliqués :
-   • Sécurité et encapsulation
-   • Centralisation de la logique de gestion
-   Design patterns recommandés :
-   • CRUD avec rôle SuperAdmin
-   • FormType réutilisable
-   • Controller restreint

## **Partage de la logique métier (ex: calcul prix total, validation prise de vue)**

-   Principes appliqués :
-   • Réutilisabilité
-   • Testabilité
-   • Single Responsibility
-   Design patterns recommandés :
-   • Service Layer
-   • Utilisation de services injectés dans le contrôleur

## **Consultation rapide de la liste des prises de vue ou des écoles**

-   Principes appliqués :
-   • Performance
-   • Limitation du volume de données
-   Design patterns recommandés :
-   • Pagination (Pagerfanta, Doctrine)
-   • QueryBuilder optimisé
-   • Index SQL

## **Chargement conditionnel d’éléments dans les formulaires (ajout dynamique)**

-   Principes appliqués :
-   • UX fluide
-   • Asynchrone
-   • Responsabilité claire
-   Design patterns recommandés :
-   • Ajax + JavaScript léger
-   • FormType + gestion partielle Twig
-   • Modal Pattern