<?php

namespace App\Http\Controllers;

use App\Models\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExcelViewController extends Controller
{
    /**
     * Получение сгруппированных данных с пагинацией
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 50);
            $currentPage = $request->input('page', 1);

            // Получаем уникальные даты с пагинацией
            $dates = Excel::select('date')
                ->distinct()
                ->orderBy('date')
                ->paginate($perPage);

            if ($dates->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Данные отсутствуют'
                ]);
            }

            // Получаем данные только для дат на текущей странице
            $dateValues = $dates->pluck('date')->toArray();
            
            $groupedData = Excel::select('id', 'name', 'date')
                ->whereIn('date', $dateValues)
                ->orderBy('date')
                ->orderBy('id')
                ->chunk(1000, function ($records) use (&$result) {
                    foreach ($records as $record) {
                        $formattedDate = Carbon::parse($record->date)->format('d.m.Y');
                        $result[$formattedDate][] = [
                            'id' => $record->id,
                            'name' => $record->name,
                            'date' => $formattedDate
                        ];
                    }
                });

            return response()->json([
                'success' => true,
                'data' => $result ?? [],
                'pagination' => [
                    'total' => $dates->total(),
                    'per_page' => $dates->perPage(),
                    'current_page' => $dates->currentPage(),
                    'last_page' => $dates->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при получении данных: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении данных',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение данных за конкретную дату
     */
    public function getByDate(Request $request, $date)
    {
        try {
            $parsedDate = Carbon::createFromFormat('d.m.Y', $date)->format('Y-m-d');
            
            $result = [];
            Excel::select('id', 'name', 'date')
                ->whereDate('date', $parsedDate)
                ->orderBy('id')
                ->chunk(1000, function ($records) use (&$result, $date) {
                    foreach ($records as $record) {
                        $result[] = [
                            'id' => $record->id,
                            'name' => $record->name,
                            'date' => Carbon::parse($record->date)->format('d.m.Y')
                        ];
                    }
                });

            return response()->json([
                'success' => true,
                'data' => [
                    $date => $result
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при получении данных по дате: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении данных',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
