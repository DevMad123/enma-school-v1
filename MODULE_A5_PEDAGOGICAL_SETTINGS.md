# Module A5 - Paramètres Pédagogiques

## 📋 Vue d'ensemble

Le **Module A5 — Paramètres pédagogiques** permet la gestion centralisée des configurations académiques de l'établissement. Il gère les systèmes de notation, les seuils de validation, les règles de redoublement et les paramètres des bulletins.

## 🎯 Fonctionnalités implémentées

### 1. **Système de notation**
- Configuration du barème de notation (20, 100, ou personnalisé)
- Adaptation automatique selon le type d'établissement (secondaire/universitaire)
- Cohérence avec le système académique (trimestre/semestre)

### 2. **Seuils de validation**
- Définition des seuils globaux de validation et redoublement
- Configuration de seuils spécifiques par niveau (optionnel)
- Configuration de seuils spécifiques par matière (optionnel)
- Pourcentage minimum de matières à valider

### 3. **Règles de redoublement**
- Seuil de moyenne générale pour éviter le redoublement
- Promotion automatique basée sur les critères définis
- Flexibilité dans l'application des règles

### 4. **Paramètres des bulletins**
- Texte de pied de page personnalisable
- Activation/désactivation du système de mentions
- Configuration des messages automatiques

## 🏗️ Structure technique

### Contrôleur
- **PedagogicalSettingsController** : Gestion complète des paramètres pédagogiques
  - `index()` : Affichage de l'interface de configuration
  - `updateGlobal()` : Mise à jour des paramètres globaux
  - `updateLevelThreshold()` : Seuils spécifiques par niveau
  - `updateSubjectThreshold()` : Seuils spécifiques par matière
  - `resetLevelThreshold()` / `resetSubjectThreshold()` : Remise à zéro des seuils personnalisés

### Modèles
- **School** : Extension avec méthodes pour paramètres pédagogiques
  - `getLevelValidationThreshold()` : Récupère le seuil pour un niveau
  - `getSubjectValidationThreshold()` : Récupère le seuil pour une matière
  - `getPedagogicalSettings()` : Récupère tous les paramètres pédagogiques

### Base de données
- **schools** : Table principale avec champs `grading_system`, `academic_system`
- **school_settings** : Table key-value pour paramètres flexibles
- **Index optimisés** pour améliorer les performances

### Routes
- `GET /admin/pedagogy-settings` : Interface principale
- `PUT /admin/pedagogy-settings/global` : Mise à jour globale
- `PUT /admin/pedagogy-settings/level/{level}/threshold` : Seuil niveau
- `PUT /admin/pedagogy-settings/subject/{subject}/threshold` : Seuil matière
- `DELETE ...threshold.reset` : Remise à zéro des seuils personnalisés

## 🎨 Interface utilisateur

### Page principale
- **Paramètres globaux** : Configuration centrale du système
- **Seuils par niveau** : Table interactive avec modification en modal
- **Seuils par matière** : Table interactive avec gestion fine
- **Options avancées** : Promotion automatique, mentions, pied de page

### Caractéristiques UX
- Design cohérent avec le reste de l'application
- Modals pour édition rapide des seuils spécifiques
- Messages de confirmation pour les suppressions
- Validation côté client et serveur
- Navigation intuitive depuis la gouvernance

## ⚙️ Paramètres configurables

### Globaux
- `grading_system` : 20, 100, ou custom
- `validation_threshold` : Note minimale de validation
- `redoublement_threshold` : Moyenne minimale pour éviter redoublement
- `validation_subjects_required` : % de matières à valider
- `automatic_promotion` : Promotion automatique oui/non
- `mention_system` : Système de mentions activé/désactivé
- `bulletin_footer_text` : Texte personnalisé des bulletins

### Spécifiques
- `level_{id}_validation_threshold` : Seuil personnalisé par niveau
- `subject_{id}_validation_threshold` : Seuil personnalisé par matière

## 🔧 Installation et configuration

### 1. Migrations
```bash
php artisan migrate
```

### 2. Accès à l'interface
- URL : `/admin/pedagogy-settings`
- Permissions : `super_admin`, `admin`, `directeur`

### 3. Paramètres par défaut
Les paramètres par défaut sont initialisés lors du premier accès :
- Validation : 10/20 (ou 50/100)
- Redoublement : 8/20 (ou 40/100)
- Matières à valider : 80%
- Mentions : Activées
- Promotion automatique : Désactivée

## 🚀 Utilisation

### Configuration initiale
1. Accéder à "Gouvernance de l'Établissement"
2. Cliquer sur "Paramètres Pédagogiques"
3. Configurer le système de notation selon le type d'établissement
4. Définir les seuils globaux de validation et redoublement
5. Optionnellement, personnaliser les seuils par niveau/matière

### Gestion avancée
- **Seuils spécifiques** : Utiliser pour matières critiques ou niveaux particuliers
- **Promotion automatique** : Activer pour automatiser les décisions de passage
- **Système de mentions** : Personnaliser l'affichage des appréciations
- **Pied de page bulletins** : Ajouter informations légales ou motivationnelles

## 🔄 Intégration avec autres modules

### Module A1 - Gouvernance
- Lien direct depuis l'interface de gouvernance
- Cohérence avec les paramètres de l'école

### Module A3 - Structure académique
- Utilisation des niveaux et matières existants
- Seuils appliqués lors du calcul des moyennes

### Module A8 - Bulletins
- Application automatique des paramètres configurés
- Affichage du pied de page personnalisé
- Calcul des mentions selon les seuils

## 📊 Performances et optimisation

- **Index de base de données** optimisés pour les requêtes fréquentes
- **Cache des paramètres** via méthodes du modèle School
- **Validation côté client** pour réduire les requêtes serveur
- **Structure modulaire** permettant l'extension facile

## 🔒 Sécurité

- **Middleware d'authentification** obligatoire
- **Contrôle des rôles** (admin, directeur, super_admin uniquement)
- **Validation des données** côté serveur
- **Protection CSRF** sur tous les formulaires
- **Appartenance école** vérifiée pour tous les objets

## 🌟 Points forts

1. **Flexibilité maximale** : Paramétrage global avec surcharges spécifiques
2. **Interface intuitive** : Gestion simple des configurations complexes
3. **Performance optimisée** : Structure de données efficace
4. **Évolutivité** : Architecture extensible pour futurs besoins
5. **Cohérence système** : Intégration parfaite avec l'écosystème existant

## 📝 Notes techniques

- Compatible avec systèmes secondaire et universitaire
- Support multi-écoles prévu dans l'architecture
- Paramètres stockés en key-value pour flexibilité maximale
- Interface responsive et accessible
- Code documenté et maintenable