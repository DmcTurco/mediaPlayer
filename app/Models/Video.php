<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'title',
        'link',
        'description',
        'file_path',
        'thumbnail_path',
        'views',
        'likes'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'views' => 'integer',
        'likes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Obtener la URL completa del video
     *
     * @return string
     */
    public function getVideoUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Generar una imagen de miniatura del primer frame del video
     *
     * @return string
     */
    public function getThumbnailUrlAttribute()
    {
        // Primero verificar si tenemos una ruta de thumbnail guardada en la base de datos
        if (!empty($this->thumbnail_path)) {
            // Verificar si el archivo realmente existe
            if (file_exists(storage_path('app/' . $this->thumbnail_path))) {
                return asset('storage/' . str_replace('public/', '', $this->thumbnail_path));
            }
        }

        // Método de fallback: construir la ruta basada en el nombre del archivo de video
        $thumbnailPath = pathinfo($this->file_path, PATHINFO_DIRNAME) . '/thumbnails/' .
            pathinfo($this->file_path, PATHINFO_FILENAME) . '.jpg';

        if (file_exists(storage_path('app/public/' . $thumbnailPath))) {
            return asset('storage/' . $thumbnailPath);
        }

        // Si no hay miniatura, devolver una imagen por defecto
        return asset('assets/img/default.jpg');
    }

    /**
     * Incrementar el contador de vistas
     *
     * @return void
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Incrementar el contador de likes
     *
     * @return void
     */
    public function incrementLikes()
    {
        $this->increment('likes');
    }

    /**
     * Decrementar el contador de likes
     *
     * @return void
     */
    public function decrementLikes()
    {
        if ($this->likes > 0) {
            $this->decrement('likes');
        }
    }

    /**
     * Obtener videos relacionados basados en palabras clave en el título
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedVideos($limit = 4)
    {
        $titleWords = collect(explode(' ', $this->title))
            ->filter(function ($word) {
                return strlen($word) > 3; // Filtrar palabras cortas
            });

        if ($titleWords->isEmpty()) {
            // Si no hay palabras relevantes, obtener simplemente los últimos videos
            return self::where('id', '!=', $this->id)
                ->latest()
                ->limit($limit)
                ->get();
        }

        return self::where('id', '!=', $this->id)
            ->where(function ($query) use ($titleWords) {
                foreach ($titleWords as $word) {
                    $query->orWhere('title', 'like', "%{$word}%");
                }
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Devuelve si el video es reciente (menos de 7 días)
     *
     * @return bool
     */
    public function getIsNewAttribute()
    {
        return $this->created_at->diffInDays(now()) < 7;
    }

    /**
     * Tiempo transcurrido desde la publicación en formato legible
     *
     * @return string
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
