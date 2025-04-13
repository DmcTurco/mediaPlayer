<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

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
    /**
     * Almacenar un nuevo video
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Definir reglas de validación
            $rules = [
                'title' => 'required|string|max:255',
                'link' => 'nullable|url',
                'description' => 'nullable|string',
                'video_file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:102400', // 100MB
            ];

            // Mensajes personalizados
            $messages = [
                'title.required' => 'El título del video es obligatorio',
                // 'description.required' => 'La descripción del video es obligatoria',
                'video_file.required' => 'Debes seleccionar un archivo de video',
                'video_file.file' => 'El archivo seleccionado no es válido',
                'video_file.mimetypes' => 'El formato del video debe ser MP4, MOV o AVI',
                'video_file.max' => 'El tamaño máximo permitido es 100MB',
            ];

            // Validar request
            $validator = Validator::make($request->all(), $rules, $messages);

            // Si la validación falla
            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Obtener datos validados
            $validated = $validator->validated();

            // Crear directorio si no existe
            $videoPath = "videos/" . date('Y/m');

            if (!Storage::exists("{$videoPath}")) {
                Storage::makeDirectory("{$videoPath}");
            }

            // Procesar y guardar el video
            $videoFile = $request->file('video_file');
            $videoFileName = Str::slug($validated['title']) . '-' . time() . '.' . $videoFile->getClientOriginalExtension();
            $videoFullPath = $videoFile->storeAs("{$videoPath}", $videoFileName);
            $videoDbPath = $videoFullPath;

            // Crear directorio para thumbnails si no existe
            $thumbnailPath = $videoPath . '/thumbnails';
            if (!Storage::exists($thumbnailPath)) {
                Storage::makeDirectory($thumbnailPath);
            }

            // Obtener rutas para procesamiento
            $videoStoragePath = Storage::path($videoFullPath);
            $thumbnailFileName = pathinfo($videoFileName, PATHINFO_FILENAME) . '.jpg';
            $thumbnailStoragePath = Storage::path($thumbnailPath . '/' . $thumbnailFileName);
            $thumbnailDbPath = $thumbnailPath . '/' . $thumbnailFileName;

            // Generar thumbnail con php-ffmpeg
            try {
                // Configurar FFMpeg con la ruta a los binarios incluidos en el proyecto
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => base_path('bin/ffmpeg/ffmpeg.exe'),
                    'ffprobe.binaries' => base_path('bin/ffmpeg/ffprobe.exe'),
                    'timeout' => 3600, // Tiempo máximo para procesar (en segundos)
                    'ffmpeg.threads' => 12, // Cantidad de hilos a usar
                ]);

                // Abrir el video
                $video_object = $ffmpeg->open($videoStoragePath);

                // Extraer un frame específico (a los 3 segundos)
                $frame = $video_object->frame(TimeCode::fromSeconds(3));

                // Guardar el frame como imagen JPG
                $frame->save($thumbnailStoragePath);

                // Registrar éxito
                Log::info('Thumbnail generado correctamente en: ' . $thumbnailStoragePath);
            } catch (\FFMpeg\Exception\ExecutableNotFoundException $e) {
                // Si no encuentra el ejecutable de FFmpeg
                Log::error('FFmpeg no encontrado: ' . $e->getMessage());
                $thumbnailDbPath = null;
            } catch (\Exception $e) {
                // Otros errores
                Log::error('Error al generar thumbnail con FFMpeg: ' . $e->getMessage());
                $thumbnailDbPath = null;
            }

            // Crear registro en base de datos
            $video = new Video();
            $video->company_id = auth()->id();
            $video->title = $validated['title'];
            $video->link = $validated['link'];
            $video->description = $validated['description'];
            $video->file_path = $videoDbPath;
            $video->thumbnail_path = $thumbnailDbPath;
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
