<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoLike;
use App\Models\VideoView;
use Illuminate\Http\Request;

class ApiVideoController extends Controller
{
    public function getVideo(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $search = $request->input('search');

        $query = Video::query()->orderBy('created_at', 'desc');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $videos = $query->skip($offset)->take($limit)->get();
        $transformed = $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'description' => $video->description,
                'thumbnail_url' => $video->thumbnailUrl,
                'video_url' => $video->videoUrl,
                'views' => $video->views,
                'likes' => $video->likes,
                'duration' => $this->getVideoDuration($video->file_path),
                'created_at' => $video->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $video->timeAgo,
                'is_new' => $video->isNew,
            ];
        });

        return response()->json([
            'videos' => $transformed,
            'has_more' => ($offset + $limit) < $total,
            'total' => $total,
            'next_offset' => ($offset + $limit) < $total ? ($offset + $limit) : null
        ]);
    }


    private function getVideoDuration($path)
    {
        // Implementación básica por ahora - esto se puede mejorar usando ffprobe
        return null;
    }


    public function incrementViews($id)
    {
        try {
            $video = Video::findOrFail($id);
            $user = auth()->user();

            // Registrar la vista
            VideoView::create([
                'user_id' => $user->id,
                'video_id' => $id,
                'viewed_at' => now()
            ]);

            // Incrementar contador directamente
            $video->increment('views');

            // Recargar el modelo para tener el valor actualizado
            $video->refresh();

            return response()->json([
                'success' => true,
                'views' => $video->views
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleLike(Request $request, $id)
    {
        try {
            $video = Video::findOrFail($id);
            $user = auth()->user();
            $action = $request->input('action', 'add'); // 'add' o 'remove'

            // Buscar si ya existe un like
            $like = VideoLike::where('user_id', $user->id)
                ->where('video_id', $id)
                ->first();

            if ($action === 'add' && !$like) {
                // Crear nuevo like
                VideoLike::create([
                    'user_id' => $user->id,
                    'video_id' => $id
                ]);

                // Incrementar contador directamente
                $video->increment('likes');
                $message = 'Like agregado correctamente';
            } elseif ($action === 'remove' && $like) {
                // Eliminar like
                $like->delete();

                // Decrementar contador directamente
                $video->decrement('likes');
                $message = 'Like eliminado correctamente';
            } else {
                // No cambió nada
                $message = 'No se requiere cambio';
            }

            // Recargar el modelo para tener el valor actualizado
            $video->refresh();

            return response()->json([
                'success' => true,
                'message' => $message,
                'likes' => $video->likes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para verificar si el usuario dio like
    public function getLikeStatus($id)
    {
        try {
            $user = auth()->user();

    
           $isLiked = VideoLike::where('user_id', $user->id)
                ->where('video_id', $id)
                ->exists();

            return response()->json([
                'success' => true,
                'isLiked' => $isLiked
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
