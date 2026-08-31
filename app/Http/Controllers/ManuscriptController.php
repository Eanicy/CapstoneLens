<?php

namespace App\Http\Controllers;

use App\Models\Manuscript;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ManuscriptController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $manuscripts = Manuscript::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('arxiv_id', 'like', "%{$search}%")
                        ->orWhere('authors', 'like', "%{$search}%")
                        ->orWhere('source_filename', 'like', "%{$search}%");
                });
            })
            ->latest('imported_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('repository.index', [
            'manuscripts' => $manuscripts,
            'search' => $search,
        ]);
    }

    public function show(Manuscript $manuscript): View
    {
        return view('repository.show', [
            'manuscript' => $manuscript,
        ]);
    }
}
