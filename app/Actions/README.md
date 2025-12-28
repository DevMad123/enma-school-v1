# Actions

Ce dossier contient les classes d'action de l'application Enma School.

## À propos
Les actions encapsulent une logique métier spécifique et atomique.
Elles suivent le principe de responsabilité unique (SRP) et sont facilement testables.

## Philosophie
- Une action = une responsabilité précise
- Interface simple et claire
- Facilement testable en isolation
- Réutilisable dans différents contextes

## Structure recommandée
```php
<?php

namespace App\Actions;

class CreateStudentAction
{
    public function execute(array $data): Student
    {
        // Logique de création d'un étudiant
    }
}
```

## Exemples futurs
- `CreateStudentAction` : Création d'un étudiant
- `UpdateStudentGradeAction` : Mise à jour d'une note
- `SendNotificationAction` : Envoi de notifications
- `GenerateReportAction` : Génération de rapports
- `ValidateEnrollmentAction` : Validation d'inscription

## Avantages
- Code plus lisible et maintenable
- Tests unitaires simplifiés
- Réutilisation facilitée
- Découplage des contrôleurs

**Note :** Ce dossier est actuellement vide et sera rempli lors du développement des fonctionnalités métier d'Enma School.
