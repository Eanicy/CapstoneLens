<?php

namespace App\Models;

use Database\Factories\ManuscriptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manuscript extends Model
{
    /** @use HasFactory<ManuscriptFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'arxiv_id',
        'authors',
        'published_year',
        'abstract',
        'keywords',
        'source_filename',
        'file_path',
        'checksum',
        'file_size',
        'imported_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'imported_at' => 'datetime',
        ];
    }
}
