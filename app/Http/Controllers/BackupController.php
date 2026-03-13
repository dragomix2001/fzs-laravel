<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->listBackups();
        return view('backup.index', compact('backups'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'full');
        
        $result = $this->backupService->createBackup($type);
        
        if ($result['success']) {
            return back()->with('success', 'Резервна копија креирана: ' . $result['filename']);
        }
        
        return back()->with('error', 'Грешка: ' . $result['error']);
    }

    public function download($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists($path)) {
            return back()->with('error', 'Фајл не постоји');
        }
        
        return response()->download($path);
    }

    public function delete($filename)
    {
        if ($this->backupService->deleteBackup($filename)) {
            return back()->with('success', 'Резервна копија обрисана');
        }
        
        return back()->with('error', 'Грешка при брисању');
    }
}
