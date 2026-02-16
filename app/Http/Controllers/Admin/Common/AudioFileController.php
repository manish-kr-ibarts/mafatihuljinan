<?php

namespace App\Http\Controllers\Admin\Common;

use App\Http\Controllers\Controller;
use App\Models\AudioFile;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class AudioFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language = strtolower($request->get('language', 'english'));
        $audioFiles = AudioFile::where('language', $language)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 25));
        return view('admin.audio.manage', compact('audioFiles', 'language'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,aac',
            'post_type' => 'required|string',
            'language' => 'required|string'
        ]);

        try {
            $audio = $request->file('audio');
            $language = strtolower($request->language);
            $postType = strtolower($request->post_type);

            $originalName = pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME);
            $originalName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalName);
            $extension = $audio->getClientOriginalExtension();
            $fileName = $originalName . '_' . uniqid() . '.' . $extension;

            // Target directory structure: language/post_type/
            $relativeDir = $language . '/' . $postType;
            $baseDir = env('AUDIO_DIRECTORY', public_path('uploads/audio'));
            $destination = rtrim($baseDir, '/') . '/' . $relativeDir;

            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            $audio->move($destination, $fileName);

            $filePath = $relativeDir . '/' . $fileName;
            $baseUrl = env('AUDIO_WEBURL', url('uploads/audio'));
            $url = rtrim($baseUrl, '/') . '/' . $filePath;

            AudioFile::create([
                'language' => $language,
                'post_type' => $postType,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'url' => $url,
                'user_id' => Auth::id()
            ]);
            logActivity(Auth::user(), 'Create', 'Uploaded audio : ' . $fileName);
            return back()->with('success', 'Audio uploaded and tracked successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AudioFile $audioFile)
    {
        try {
            $baseDir = env('AUDIO_DIRECTORY', public_path('uploads/audio'));
            $fullPath = rtrim($baseDir, '/') . '/' . $audioFile->file_path;

            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }

            $audioFile->delete();
            logActivity(Auth::user(), 'Delete', 'Deleted audio : ' . $audioFile->file_name);
            return back()->with('success', 'Audio file and record deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
