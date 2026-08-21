<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleMultipartPut
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->isMethod('PUT') &&
            str_contains($request->header('Content-Type', ''), 'multipart/form-data')
        ) {
            $this->parseMultipart($request);
        }

        return $next($request);
    }

    private function parseMultipart(Request $request)
    {
        $contentType = $request->header('Content-Type');

        preg_match('/boundary=(.*)$/', $contentType, $matches);

        if (!isset($matches[1])) {
            return;
        }

        $boundary = '--' . trim($matches[1], '"');

        $body = file_get_contents('php://input');

        $parts = preg_split(
            '/\R' . preg_quote($boundary, '/') . '(?:--)?\R?/',
            $body
        );

        $parameters = [];
        $files = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (!$part) {
                continue;
            }

            [$headers, $content] = preg_split(
                "/\R\R/",
                $part,
                2
            );

            $headers = $this->parseHeaders($headers);

            if (!isset($headers['content-disposition'])) {
                continue;
            }

            preg_match(
                '/name="([^"]+)"/',
                $headers['content-disposition'],
                $nameMatch
            );

            if (!isset($nameMatch[1])) {
                continue;
            }

            $name = $nameMatch[1];

            if (preg_match(
                '/filename="([^"]*)"/',
                $headers['content-disposition'],
                $fileMatch
            )) {
                $filename = $fileMatch[1];

                if ($filename === '') {
                    continue;
                }

                $tmpFile = tempnam(sys_get_temp_dir(), 'put_');

                file_put_contents($tmpFile, $content);

                $mimeType = $headers['content-type']
                    ?? 'application/octet-stream';

                $files[$name] = new \Illuminate\Http\UploadedFile(
                    $tmpFile,
                    $filename,
                    $mimeType,
                    null,
                    true
                );
            } else {
                $parameters[$name] = $content;
            }
        }

        $request->request->add($parameters);
        $request->files->add($files);
    }

    private function parseHeaders(string $headers): array
    {
        $result = [];

        foreach (preg_split('/\R/', $headers) as $header) {
            if (!str_contains($header, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $header, 2);

            $result[strtolower(trim($key))] = trim($value);
        }

        return $result;
    }
}
