<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Material;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index($materialId)
    {
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $material = Material::with([
                'units' => function ($query) {
                    $query->with('chapters');
                }
            ])->findOrFail($materialId);

            $chapters = Chapter::whereHas('unit', function ($query) use ($materialId) {
                $query->where('material_id', $materialId);
            })->whereIn('unit_id', $material->units->pluck('id'))
                ->where('material_id', $materialId) 
                ->get();

            return view('pages.student.unit.index', compact('material', 'chapters', 'userAuth'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }
    public function unitContent($unitId)
    {
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $unit = Unit::findOrFail($unitId);
            return view('pages.student.unit.unit_content', compact('unit', 'userAuth'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }

}
