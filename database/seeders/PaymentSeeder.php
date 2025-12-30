<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Student;
use App\Models\SchoolFee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::with('user')->get();
        $schoolFees = SchoolFee::where('status', 'active')->get();
        $adminUser = User::first(); // Premier utilisateur comme admin

        if ($students->isEmpty()) {
            $this->command->warn('Aucun étudiant trouvé. Assurez-vous que les seeders d\'inscription ont été exécutés.');
            return;
        }

        if ($schoolFees->isEmpty()) {
            $this->command->warn('Aucun frais scolaire trouvé. Exécutez d\'abord SchoolFeeSeeder.');
            return;
        }

        $paymentMethods = ['cash', 'bank_transfer', 'check', 'card', 'mobile_money'];
        $statuses = ['confirmed', 'pending', 'confirmed', 'confirmed']; // Plus de paiements confirmés

        // Générer des paiements pour chaque étudiant
        foreach ($students as $student) {
            // Nombre aléatoire de paiements par étudiant (1 à 4)
            $numberOfPayments = rand(1, 4);
            
            for ($i = 0; $i < $numberOfPayments; $i++) {
                $schoolFee = $schoolFees->random();
                
                // Montant aléatoire (paiement partiel ou total)
                $isPartial = rand(0, 1); // 50% de chance d'être partiel
                if ($isPartial) {
                    $amount = round($schoolFee->amount * (rand(20, 80) / 100), 2);
                } else {
                    $amount = $schoolFee->amount;
                }
                
                $paymentDate = now()->subDays(rand(0, 90)); // Paiements dans les 90 derniers jours
                $status = $statuses[array_rand($statuses)];
                
                $payment = Payment::create([
                    'student_id' => $student->id,
                    'school_fee_id' => $schoolFee->id,
                    'amount' => $amount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'transaction_reference' => $this->generateTransactionReference(),
                    'payment_date' => $paymentDate,
                    'status' => $status,
                    'notes' => $this->generatePaymentNote(),
                    'processed_by' => $adminUser->id ?? null,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate
                ]);
                
                // Générer un reçu pour les paiements confirmés
                if ($status === 'confirmed') {
                    $payment->generateReceipt();
                }
            }
        }

        // Quelques paiements spéciaux pour tester différents scénarios
        if ($students->count() >= 3) {
            // Paiement complet d'un étudiant
            $student = $students->first();
            $inscriptionFee = $schoolFees->where('name', 'Frais d\'inscription')->first();
            
            if ($inscriptionFee) {
                $payment = Payment::create([
                    'student_id' => $student->id,
                    'school_fee_id' => $inscriptionFee->id,
                    'amount' => $inscriptionFee->amount,
                    'payment_method' => 'bank_transfer',
                    'transaction_reference' => 'VIR-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'payment_date' => now()->subDays(5),
                    'status' => 'confirmed',
                    'notes' => 'Paiement complet par virement bancaire',
                    'processed_by' => $adminUser->id ?? null
                ]);
                $payment->generateReceipt();
            }
            
            // Paiement en attente
            $student2 = $students->skip(1)->first();
            $scolariteFee = $schoolFees->where('name', 'Frais de scolarité')->first();
            
            if ($scolariteFee) {
                Payment::create([
                    'student_id' => $student2->id,
                    'school_fee_id' => $scolariteFee->id,
                    'amount' => $scolariteFee->amount / 2, // Paiement partiel
                    'payment_method' => 'cash',
                    'transaction_reference' => null,
                    'payment_date' => now()->subDays(1),
                    'status' => 'pending',
                    'notes' => 'Paiement partiel en espèces - en attente de confirmation',
                    'processed_by' => $adminUser->id ?? null
                ]);
            }
        }

        $this->command->info('Paiements de test créés avec succès.');
    }

    /**
     * Génère une référence de transaction aléatoire
     */
    private function generateTransactionReference(): ?string
    {
        $types = ['VIR', 'CHQ', 'CB', 'MM', null];
        $type = $types[array_rand($types)];
        
        if (!$type) {
            return null;
        }
        
        return $type . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Génère une note de paiement aléatoire
     */
    private function generatePaymentNote(): ?string
    {
        $notes = [
            'Paiement effectué en présence du parent',
            'Paiement par échéances convenues',
            'Remise commerciale appliquée',
            'Paiement pour étudiant boursier',
            null,
            null, // Plus de chance d'avoir des notes vides
            'Paiement anticipé avec remise',
            'Paiement en retard - pénalités appliquées'
        ];
        
        return $notes[array_rand($notes)];
    }
}
