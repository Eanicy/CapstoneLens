<?php

namespace App\Http\Controllers;

use App\Models\Manuscript;
use App\Support\PdfPageRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManuscriptViewerController extends Controller
{
    public function show(Request $request, Manuscript $manuscript, PdfPageRenderer $renderer): View
    {
        $pageCount = $renderer->pageCount($manuscript);
        $page = max(1, $request->integer('page', 1));

        if ($pageCount !== null) {
            $page = min($page, $pageCount);
        }

        return view('repository.viewer', [
            'manuscript' => $manuscript,
            'page' => $page,
            'pageCount' => $pageCount,
        ]);
    }

    public function page(Manuscript $manuscript, int $page, PdfPageRenderer $renderer): BinaryFileResponse
    {
        abort_if($page < 1, 404);

        try {
            $pagePath = $renderer->render($manuscript, $page);
        } catch (RuntimeException) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($pagePath), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
