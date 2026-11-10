<?php

namespace App\Http\Controllers;

use App\Models\MlModel;
use App\Models\ModelRun;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MlModelController extends Controller
{
    /**
     * عرض سجل نماذج الذكاء الاصطناعي والإصدارات المدربة
     */
    public function index(): View
    {
        $models = MlModel::with(['modelRuns' => function ($query) {
            $query->latest('trained_at')->latest('id');
        }])->get();

        // جلب أحدث تشغيلات النماذج مع المقاييس
        $recentRuns = ModelRun::with('mlModel')
            ->latest('trained_at')
            ->latest('id')
            ->paginate(10);

        return view('ml_models.index', compact('models', 'recentRuns'));
    }
}