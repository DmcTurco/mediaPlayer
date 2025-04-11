<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * Mostrar lista de videos
     */
    public function index(Request $request)
    {
        $query = Video::query();

        // Buscar por título
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        $videos = $query->latest()->paginate(10);
        return view('company.pages.video.index', compact('videos'));
    }

    /**
     * Mostrar formulario para crear video (aunque usamos modal, puede ser útil tener esta ruta)
     */
    public function create()
    {
        return view('company.pages.video.create');
    }

    /**
     * Guardar un nuevo video
     */
    public function store(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'video_file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:102400', // 100MB
            ]);

            // Crear directorio si no existe
            $videoPath = "videos/" . date('Y/m');

            if (!Storage::exists("public/{$videoPath}")) {
                Storage::makeDirectory("public/{$videoPath}");
            }

            // Procesar y guardar el video
            $videoFile = $request->file('video_file');
            $videoFileName = Str::slug($request->title) . '-' . time() . '.' . $videoFile->getClientOriginalExtension();
            $videoFullPath = $videoFile->storeAs("public/{$videoPath}", $videoFileName);
            $videoDbPath = str_replace('public/', '', $videoFullPath);

            // Crear registro en base de datos
            $video = new Video();
            $video->title = $request->title;
            $video->description = $request->description;
            $video->file_path = $videoDbPath;
            $video->save();

            // Si la solicitud es AJAX, devolver respuesta JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Video subido correctamente',
                    'video' => $video
                ]);
            }

            // Redireccionar con mensaje de éxito
            return redirect()->route('company.video.index')
                ->with('success', 'Video subido correctamente');
        } catch (Exception $e) {
            // Registrar el error
            Log::error('Error al subir video: ' . $e->getMessage());

            // Si la solicitud es AJAX, devolver respuesta JSON con error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir el video: ' . $e->getMessage()
                ], 500);
            }

            // Redireccionar con mensaje de error
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al subir el video: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar un video específico
     */
    public function show($id)
    {
        $video = Video::findOrFail($id);

        // Incrementar contador de vistas
        $video->incrementViews();

        return view('company.pages.video.show', compact('video'));
    }

    /**
     * Mostrar formulario para editar video
     */
    public function edit($id)
    {
        $video = Video::findOrFail($id);
        return view('company.pages.video.edit', compact('video'));
    }

    /**
     * Actualizar un video existente
     */
    public function update(Request $request, $id)
    {
        // Encontrar el video
        $video = Video::findOrFail($id);

        // Validar la solicitud
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Actualizar los datos
        $video->title = $request->title;
        $video->description = $request->description;
        $video->save();

        return redirect()->route('company.video.index')
            ->with('success', 'Video actualizado correctamente');
    }

    /**
     * Eliminar un video
     */
    public function destroy($id)
    {
        // Encontrar el video
        $video = Video::findOrFail($id);

        // Eliminar el archivo físico
        if ($video->file_path && Storage::exists('public/' . $video->file_path)) {
            Storage::delete('public/' . $video->file_path);
        }

        // Eliminar el registro
        $video->delete();

        // Si la solicitud es AJAX, devolver respuesta JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Video eliminado correctamente'
            ]);
        }

        return redirect()->route('company.video.index')
            ->with('success', 'Video eliminado correctamente');
    }

    /**
     * Manejar "Me gusta" en un video
     */
    public function like($id)
    {
        $video = Video::findOrFail($id);
        $video->incrementLikes();

        return response()->json([
            'success' => true,
            'likes' => $video->likes
        ]);
    }
}
