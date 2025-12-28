# Enumerations

Ce dossier contient les énumérations (Enums) de l'application Enma School.

## À propos
Les enums définissent des constantes typées et limitent les valeurs possibles 
pour certains champs de l'application.

## Avantages
- Type safety renforcée
- Valeurs prédéfinies et limitées  
- Auto-complétion dans l'IDE
- Facilite la maintenance du code

## Exemples futurs
- `StudentStatusEnum` : Statuts des étudiants (actif, inactif, diplômé, etc.)
- `GradeTypeEnum` : Types de notes (examen, contrôle, devoir, etc.)
- `UserRoleEnum` : Rôles utilisateurs (admin, professeur, étudiant, etc.)
- `ClassroomTypeEnum` : Types de salles (cours, laboratoire, amphithéâtre, etc.)

## Structure recommandée
```php
<?php

namespace App\Enums;

enum StudentStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive'; 
    case GRADUATED = 'graduated';
}
```

**Note :** Ce dossier est actuellement vide et sera rempli lors du développement des fonctionnalités métier d'Enma School.
