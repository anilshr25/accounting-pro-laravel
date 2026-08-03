<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Storage;

use App\Http\Controllers\Controller;
use App\Services\Infrastructure\ScopedStorageService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

final class TenantFileController extends Controller
{
    public function __invoke(Request $request, ScopedStorageService $storage): Response
    {
        try {
            $path = Crypt::decryptString((string) $request->query('file'));
        } catch (DecryptException) {
            abort(404);
        }

        return response($storage->get($path), 200, [
            'Content-Type' => $storage->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
