<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin Scolaire - {{ $reportCard->student->full_name }}</title>
    <style>
        @page { 
            margin: 20px; 
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }

        .student-info {
            background-color: #f8fafc;
            padding: 15px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-info td {
            padding: 5px;
            border: none;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
            color: #4a5568;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .grades-table th,
        .grades-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: center;
        }

        .grades-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
        }

        .grades-table td:first-child {
            text-align: left;
            font-weight: 500;
        }

        .grade-passed {
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
        }

        .grade-failed {
            background-color: #fef2f2;
            color: #dc2626;
            font-weight: bold;
        }

        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .summary-box {
            width: 48%;
            border: 1px solid #d1d5db;
            padding: 15px;
        }

        .summary-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #1e40af;
        }

        .general-average {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .average-passed {
            color: #059669;
        }

        .average-failed {
            color: #dc2626;
        }

        .mention {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }

        .mention-excellent {
            color: #059669;
        }

        .mention-good {
            color: #0284c7;
        }

        .mention-fair {
            color: #ea580c;
        }

        .mention-poor {
            color: #dc2626;
        }

        .observations {
            border: 1px solid #d1d5db;
            padding: 15px;
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .signature-section {
            width: 45%;
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #d1d5db;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="school-name">ENMA SCHOOL</div>
        <div style="font-size: 12px; color: #6b7280;">Établissement d'Enseignement Privé</div>
        <div class="document-title">BULLETIN SCOLAIRE</div>
    </div>

    <!-- Informations de l'étudiant -->
    <div class="student-info">
        <table>
            <tr>
                <td class="info-label">Nom et Prénom :</td>
                <td>{{ $reportCard->student->full_name }}</td>
                <td class="info-label">Classe :</td>
                <td>{{ $reportCard->schoolClass->name }}</td>
            </tr>
            <tr>
                <td class="info-label">Niveau :</td>
                <td>{{ $reportCard->schoolClass->level->name }}</td>
                <td class="info-label">Cycle :</td>
                <td>{{ $reportCard->schoolClass->level->cycle->name }}</td>
            </tr>
            <tr>
                <td class="info-label">Période :</td>
                <td>{{ $reportCard->gradePeriod->name }}</td>
                <td class="info-label">Année scolaire :</td>
                <td>{{ $reportCard->academicYear->name }}</td>
            </tr>
            @if($reportCard->class_rank && $reportCard->total_students_in_class)
            <tr>
                <td class="info-label">Rang :</td>
                <td>{{ $reportCard->class_rank }}{{ $reportCard->class_rank == 1 ? 'er' : 'ème' }} sur {{ $reportCard->total_students_in_class }} élèves</td>
                <td class="info-label">Effectif :</td>
                <td>{{ $reportCard->total_students_in_class }} élèves</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Tableau des notes par matière -->
    @if($subjectAverages->count() > 0)
    <table class="grades-table">
        <thead>
            <tr>
                <th style="width: 40%;">MATIÈRES</th>
                <th style="width: 15%;">COEFFICIENT</th>
                <th style="width: 15%;">MOYENNE</th>
                <th style="width: 15%;">NB NOTES</th>
                <th style="width: 15%;">APPRÉCIATION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjectAverages as $subjectData)
            <tr>
                <td style="text-align: left;">{{ $subjectData['subject']->name }}</td>
                <td>{{ $subjectData['coefficient'] }}</td>
                <td class="{{ $subjectData['is_passed'] ? 'grade-passed' : 'grade-failed' }}">
                    {{ number_format($subjectData['average'], 2) }}/20
                </td>
                <td>{{ $subjectData['grades_count'] }}</td>
                <td style="font-size: 11px;">
                    @if($subjectData['average'] >= 16)
                        <span style="color: #059669;">Excellent</span>
                    @elseif($subjectData['average'] >= 14)
                        <span style="color: #0284c7;">Très bien</span>
                    @elseif($subjectData['average'] >= 12)
                        <span style="color: #f59e0b;">Bien</span>
                    @elseif($subjectData['average'] >= 10)
                        <span style="color: #ea580c;">Passable</span>
                    @else
                        <span style="color: #dc2626;">Insuffisant</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Résumé et statistiques -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <!-- Résultats généraux -->
        <div style="width: 48%; border: 1px solid #d1d5db; padding: 15px;">
            <div class="summary-title">RÉSULTATS GÉNÉRAUX</div>
            
            @if($reportCard->general_average !== null)
            <div class="general-average {{ $reportCard->general_average >= 10 ? 'average-passed' : 'average-failed' }}">
                {{ number_format($reportCard->general_average, 2) }}/20
            </div>
            
            <div style="text-align: center;">
                <div style="font-weight: bold; margin-bottom: 5px;">MENTION</div>
                <div class="mention mention-{{ 
                    $reportCard->general_average >= 16 ? 'excellent' : 
                    ($reportCard->general_average >= 14 ? 'good' : 
                    ($reportCard->general_average >= 10 ? 'fair' : 'poor')) }}">
                    {{ $reportCard->mention }}
                </div>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <div style="font-weight: bold; margin-bottom: 5px;">DÉCISION</div>
                <div style="font-weight: bold; color: {{ $reportCard->decision === 'admis' ? '#059669' : '#dc2626' }};">
                    {{ strtoupper($reportCard->decision) }}
                </div>
            </div>
            @else
            <div style="text-align: center; color: #6b7280; margin: 20px 0;">
                BULLETIN NON CALCULÉ
            </div>
            @endif
        </div>

        <!-- Statistiques -->
        <div style="width: 48%; border: 1px solid #d1d5db; padding: 15px;">
            <div class="summary-title">STATISTIQUES</div>
            
            <div class="stat-item">
                <span>Matières évaluées :</span>
                <strong>{{ $reportCard->total_subjects }}</strong>
            </div>
            
            <div class="stat-item">
                <span>Matières réussies :</span>
                <strong style="color: #059669;">{{ $reportCard->subjects_passed }}</strong>
            </div>

            <div class="stat-item">
                <span>Taux de réussite :</span>
                <strong>{{ $reportCard->passing_rate }}%</strong>
            </div>

            @if($reportCard->attendance_rate !== null)
            <div class="stat-item">
                <span>Assiduité :</span>
                <strong>{{ $reportCard->attendance_rate }}%</strong>
            </div>
            @endif

            @if($reportCard->class_rank && $reportCard->total_students_in_class)
            <div class="stat-item">
                <span>Classement :</span>
                <strong>{{ $reportCard->class_rank }}/{{ $reportCard->total_students_in_class }}</strong>
            </div>
            @endif
        </div>
    </div>

    <!-- Observations -->
    @if($reportCard->observations)
    <div class="observations">
        <div style="font-weight: bold; margin-bottom: 10px; color: #1e40af;">OBSERVATIONS</div>
        <div style="white-space: pre-line;">{{ $reportCard->observations }}</div>
    </div>
    @endif

    <!-- Pied de page avec signatures -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-title">Le Directeur</div>
            <div style="height: 40px;"></div>
            <div style="border-top: 1px solid #000; padding-top: 5px;">
                Signature et cachet
            </div>
        </div>

        <div style="width: 10%;"></div>

        <div class="signature-section">
            <div class="signature-title">Le Parent / Tuteur</div>
            <div style="height: 40px;"></div>
            <div style="border-top: 1px solid #000; padding-top: 5px;">
                Signature
            </div>
        </div>
    </div>

    <!-- Informations de génération -->
    <div style="margin-top: 20px; font-size: 10px; color: #6b7280; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px;">
        <div>Bulletin généré le {{ now()->format('d/m/Y à H:i') }}</div>
        @if($reportCard->generatedBy)
        <div>Par : {{ $reportCard->generatedBy->name }}</div>
        @endif
        <div style="margin-top: 5px;">
            Statut : {{ ucfirst($reportCard->status) }}
            @if($reportCard->is_final) - FINALISÉ @endif
        </div>
    </div>
</body>
</html>