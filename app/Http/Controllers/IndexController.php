<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TusPhp\Tus\Server;
use TusPhp\Cache\FileStore;
use Illuminate\Support\Facades\File;

class IndexController extends Controller
{
    public function __construct(Request $request)
    {
        $kac = $request->input('kac');
        $token = env('APP_TOKEN');

        if($kac != $token){
            abort(500);
        }
    }

    public function index()
    {
        return view('index');
    }

    public function upload()
    {
        $uploadDir = public_path('uploads');

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $server = new Server('file');

        $server->setApiPath('/tus')
            ->setUploadDir($uploadDir)
            ->setCache(FileStore::class, storage_path('tus'))
            ->setConcurrency(4);
        $server->enableStreaming(true);

        $response = $server->serve();

        $response->send();
    }

    public function list()
    {
        $directory = public_path('uploads'); // مسیر public/uploads
        $files = File::files($directory); // گرفتن لیست فایل‌ها

        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = $file->getFilename(); // فقط نام فایل
        }

        return view('files', ['files' => $fileNames]);
    }
}
