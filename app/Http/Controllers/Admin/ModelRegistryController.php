<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelRegistryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $models = MlModel::withCount('runs')->latest()->paginate(10);

        return view('admin.models.index', compact('models'));
    }

    /**
     * Display the specified resource.
     */
    public function show(MlModel $model): View
    {
        $model->load(['runs' => function ($query) {
            $query->latest();
        }]);

        return view('admin.models.show', compact('model'));
    }
}