<?php

namespace App\Services;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\Predmet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    public function predictStudentSuccess(int $kandidatId): array
    {
        try {
            $kandidat = Kandidat::find($kandidatId);
            
            if (!$kandidat) {
                return ['error' => 'Студент није пронађен'];
            }
            
            $stats = $this->getStudentStats($kandidatId);
            $riskLevel = $this->calculateRiskLevel($stats);
            $recommendations = $this->generateRecommendations($stats, $riskLevel);
            
            return [
                'student' => [
                    'id' => $kandidat->id,
                    'ime' => $kandidat->imeKandidata,
                    'prezime' => $kandidat->prezimeKandidata,
                    'email' => $kandidat->email,
                ],
                'statistics' => $stats,
                'risk_level' => $riskLevel,
                'recommendations' => $recommendations,
                'prediction' => $this->generatePrediction($stats, $riskLevel),
            ];
            
        } catch (\Exception $e) {
            Log::error('Prediction error: ' . $e->getMessage());
            return ['error' => 'Грешка при генерисању предикције'];
        }
    }
    
    protected function getStudentStats(int $kandidatId): array
    {
        $totalExams = PrijavaIspita::where('kandidat_id', $kandidatId)->count();
        $passedExams = PolozeniIspiti::where('kandidat_id', $kandidatId)->count();
        $failedExams = $totalExams - $passedExams;
        
        $avgGrade = PolozeniIspiti::where('kandidat_id', $kandidatId)
            ->avg('konacnaOcena') ?? 0;
        
        $recentExams = PrijavaIspita::where('kandidat_id', $kandidatId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->count();
        
        $recentPassed = PolozeniIspiti::where('kandidat_id', $kandidatId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->count();
        
        return [
            'total_exams' => $totalExams,
            'passed_exams' => $passedExams,
            'failed_exams' => $failedExams,
            'pass_rate' => $totalExams > 0 ? round(($passedExams / $totalExams) * 100, 2) : 0,
            'average_grade' => round($avgGrade, 2),
            'recent_exams_6m' => $recentExams,
            'recent_passed_6m' => $recentPassed,
            'recent_pass_rate' => $recentExams > 0 ? round(($recentPassed / $recentExams) * 100, 2) : 0,
        ];
    }
    
    protected function calculateRiskLevel(array $stats): array
    {
        $riskScore = 0;
        $factors = [];
        
        if ($stats['pass_rate'] < 50) {
            $riskScore += 30;
            $factors[] = 'Ниска пролазност (' . $stats['pass_rate'] . '%)';
        } elseif ($stats['pass_rate'] < 70) {
            $riskScore += 15;
            $factors[] = 'Умерена пролазност (' . $stats['pass_rate'] . '%)';
        }
        
        if ($stats['average_grade'] > 0 && $stats['average_grade'] < 7) {
            $riskScore += 20;
            $factors[] = 'Ниска просечна оцена (' . $stats['average_grade'] . ')';
        }
        
        if ($stats['recent_pass_rate'] < $stats['pass_rate']) {
            $riskScore += 15;
            $factors[] = 'Опадајући тренд у последњих 6 месеци';
        }
        
        if ($stats['failed_exams'] > 3) {
            $riskScore += 20;
            $factors[] = 'Више од 3 пала испита';
        }
        
        if ($riskScore >= 50) {
            $level = 'high';
            $label = 'Висок ризик';
            $color = 'danger';
        } elseif ($riskScore >= 25) {
            $level = 'medium';
            $label = 'Умерен ризик';
            $color = 'warning';
        } else {
            $level = 'low';
            $label = 'Низак ризик';
            $color = 'success';
        }
        
        return [
            'level' => $level,
            'label' => $label,
            'color' => $color,
            'score' => $riskScore,
            'factors' => $factors,
        ];
    }
    
    protected function generateRecommendations(array $stats, array $riskLevel): array
    {
        $recommendations = [];
        
        if ($riskLevel['level'] === 'high') {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Контактирати студента за индивидуални састанак',
                'reason' => 'Студент показује висок ризик од напуштања студија',
            ];
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Понудити менторство или додатну подршку',
                'reason' => 'Потребна је додатна помоћ у учењу',
            ];
        }
        
        if ($stats['pass_rate'] < 60) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Препоручити групно учење или вежбање',
                'reason' => 'Ниска пролазност указује на потешкоће са градивом',
            ];
        }
        
        if ($stats['recent_pass_rate'] < $stats['pass_rate']) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Проверити да ли студент има приватних проблема',
                'reason' => 'Опадајући тренд може указивати на ваннаставне проблеме',
            ];
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'low',
                'action' => 'Наставити са редовним праћењем',
                'reason' => 'Студент показује добре резултате',
            ];
        }
        
        return $recommendations;
    }
    
    protected function generatePrediction(array $stats, array $riskLevel): array
    {
        $graduationProbability = 100 - $riskLevel['score'];
        
        if ($stats['pass_rate'] > 80) {
            $graduationProbability = min(95, $graduationProbability + 10);
        } elseif ($stats['pass_rate'] < 40) {
            $graduationProbability = max(10, $graduationProbability - 20);
        }
        
        return [
            'graduation_probability' => $graduationProbability,
            'estimated_remaining_semesters' => $this->estimateRemainingSemesters($stats),
            'success_factors' => $this->identifySuccessFactors($stats),
        ];
    }
    
    protected function estimateRemainingSemesters(array $stats): int
    {
        $remainingExams = max(0, 40 - $stats['passed_exams']);
        $avgExamsPerSemester = max(1, $stats['passed_exams'] / 4);
        
        return ceil($remainingExams / $avgExamsPerSemester);
    }
    
    protected function identifySuccessFactors(array $stats): array
    {
        $factors = [];
        
        if ($stats['pass_rate'] >= 80) {
            $factors[] = 'Висока пролазност';
        }
        
        if ($stats['average_grade'] >= 8) {
            $factors[] = 'Висока просечна оцена';
        }
        
        if ($stats['recent_pass_rate'] >= $stats['pass_rate']) {
            $factors[] = 'Стабилан или растући тренд';
        }
        
        return $factors;
    }
    
    public function getClassStatistics(): array
    {
        try {
            $totalStudents = Kandidat::count();
            
            $examStats = DB::table('polozeni_ispiti')
                ->selectRaw('
                    COUNT(*) as total_passed,
                    AVG(konacnaOcena) as avg_grade,
                    COUNT(CASE WHEN konacnaOcena >= 9 THEN 1 END) as excellent,
                    COUNT(CASE WHEN konacnaOcena >= 8 AND konacnaOcena < 9 THEN 1 END) as very_good,
                    COUNT(CASE WHEN konacnaOcena >= 7 AND konacnaOcena < 8 THEN 1 END) as good,
                    COUNT(CASE WHEN konacnaOcena >= 6 AND konacnaOcena < 7 THEN 1 END) as sufficient
                ')
                ->first();
            
            $riskDistribution = [
                'high' => 0,
                'medium' => 0,
                'low' => 0,
            ];
            
            $students = Kandidat::all();
            foreach ($students as $student) {
                $stats = $this->getStudentStats($student->id);
                $risk = $this->calculateRiskLevel($stats);
                $riskDistribution[$risk['level']]++;
            }
            
            return [
                'total_students' => $totalStudents,
                'exam_statistics' => [
                    'total_passed' => $examStats->total_passed ?? 0,
                    'average_grade' => round($examStats->avg_grade ?? 0, 2),
                    'grade_distribution' => [
                        'excellent' => $examStats->excellent ?? 0,
                        'very_good' => $examStats->very_good ?? 0,
                        'good' => $examStats->good ?? 0,
                        'sufficient' => $examStats->sufficient ?? 0,
                    ],
                ],
                'risk_distribution' => $riskDistribution,
                'overall_pass_rate' => $this->calculateOverallPassRate(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Class statistics error: ' . $e->getMessage());
            return ['error' => 'Грешка при генерисању статистике'];
        }
    }
    
    protected function calculateOverallPassRate(): float
    {
        $totalRegistrations = PrijavaIspita::count();
        $totalPassed = PolozeniIspiti::count();
        
        return $totalRegistrations > 0 
            ? round(($totalPassed / $totalRegistrations) * 100, 2)
            : 0;
    }
}
