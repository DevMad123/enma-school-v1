<?php

use App\Models\Level;
use App\Models\Subject;

echo "=== TEST DES RELATIONS MATIÈRES ===\n";

// Test 1 : Récupérer un niveau et ses matières
echo "\n1. Niveau CP1 et ses matières :\n";
$level = Level::where('name', 'CP1')->first();
if ($level) {
    echo "Niveau : {$level->name}\n";
    echo "Matières :\n";
    foreach ($level->subjects as $subject) {
        echo "  - {$subject->name} ({$subject->code})\n";
    }
} else {
    echo "Niveau CP1 non trouvé\n";
}

// Test 2 : Récupérer une matière et ses niveaux
echo "\n2. Matière Mathématiques et ses niveaux :\n";
$subject = Subject::where('code', 'MATH')->first();
if ($subject) {
    echo "Matière : {$subject->name} ({$subject->code})\n";
    echo "Coefficient : {$subject->coefficient}\n";
    echo "Niveaux :\n";
    foreach ($subject->levels as $level) {
        echo "  - {$level->name} ({$level->cycle->name})\n";
    }
} else {
    echo "Matière Mathématiques non trouvée\n";
}

// Test 3 : Compter les relations
echo "\n3. Statistiques :\n";
echo "Total matières : " . Subject::count() . "\n";
echo "Total niveaux : " . Level::count() . "\n";
echo "Total relations niveau-matière : " . \DB::table('level_subject')->count() . "\n";

echo "\n=== TEST TERMINÉ ===\n";