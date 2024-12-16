<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExcelUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ProcessExcelFile;


class ExcelUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.basic');
    }

    public function upload(ExcelUploadRequest $request)
    {
        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Сохраняем файл
            $path = Storage::disk('public')->putFileAs(
                'excel',
                $file,
                $fileName
            );

            // Запускаем обработку файла в фоновом режиме
            ProcessExcelFile::dispatch($fileName);

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно загружен и поставлен в очередь на обработку',
                'file_name' => $fileName,
                'path' => $path
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Ошибка при загрузке файла: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при загрузке файла',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProgress($fileName, $timestamp)
    {
        $fileKey = 'file_processing:' . $fileName . ':' . $timestamp;
        $progress = Redis::hgetall($fileKey);

        return response()->json([
            'processed' => $progress['processed'] ?? 0,
            'total' => $progress['total'] ?? 0
        ]);
    }
}
