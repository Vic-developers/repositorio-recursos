<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShareResourceRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Resource;
use App\Models\ResourceShare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    public function index(string $resourceId): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($resourceId);
        $shares = $resource->shares()->with('creator')->orderByDesc('created_at')->get();

        return ApiResponse::success($shares);
    }

    public function store(ShareResourceRequest $request, string $resourceId): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($resourceId);

        $share = ResourceShare::create([
            'resource_id' => $resource->id,
            'token' => Str::random(64),
            'type' => $request->type,
            'permission_level' => $request->permission_level,
            'is_active' => true,
            'expires_at' => $request->expires_at,
            'max_access_count' => $request->max_access_count,
            'access_count' => 0,
            'created_by' => $request->user()->id,
        ]);

        $share->load('creator');

        $shareUrls = $this->generateShareUrls($resource, $share);

        return ApiResponse::success([
            'share' => $share,
            'urls' => $shareUrls,
        ], 'Share link created', 201);
    }

    public function show(string $resourceId, string $shareId): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($resourceId);
        $share = $resource->shares()->with('creator')->findOrFail($shareId);

        $shareUrls = $this->generateShareUrls($resource, $share);

        return ApiResponse::success([
            'share' => $share,
            'urls' => $shareUrls,
        ]);
    }

    public function destroy(string $resourceId, string $shareId): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($resourceId);
        $share = $resource->shares()->findOrFail($shareId);
        $share->delete();

        return ApiResponse::success(null, 'Share link deleted');
    }

    public function access(string $token): JsonResponse
    {
        $share = ResourceShare::where('token', $token)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$share) {
            return ApiResponse::notFound('Invalid or expired share link');
        }

        if ($share->max_access_count && $share->access_count >= $share->max_access_count) {
            return ApiResponse::error('Access limit reached', 403);
        }

        $share->increment('access_count');
        $resource = $share->resource()->with(['categories', 'tags'])->first();

        return ApiResponse::success([
            'resource' => $resource,
            'embed_url' => url('/embed/' . $resource->uuid),
            'player_url' => url('/player/' . $resource->uuid),
        ]);
    }

    public function generateEmbedCode(string $resourceId): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($resourceId);
        $embedUrl = url('/embed/' . $resource->uuid);
        $iframeCode = '<iframe src="' . $embedUrl . '" width="100%" height="600" frameborder="0" allowfullscreen></iframe>';
        $moodleLink = '<a href="' . $embedUrl . '" target="_blank">' . e($resource->name) . '</a>';
        $htmlCode = '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">'
            . '<iframe src="' . $embedUrl . '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allowfullscreen></iframe>'
            . '</div>';

        $lmsInstructions = [
            'moodle' => [
                'label' => 'Moodle',
                'steps' => [
                    'Inicia sesión en tu curso de Moodle.',
                    'Activa la edición (botón "Activar edición" arriba a la derecha).',
                    'Haz clic en "Agregar una actividad o recurso".',
                    'Selecciona "Página" o "URL".',
                    'Pega el siguiente enlace en el campo URL:',
                    $embedUrl,
                    'Guarda los cambios.',
                ],
            ],
            'canvas' => [
                'label' => 'Canvas',
                'steps' => [
                    'Ve a tu curso en Canvas.',
                    'Haz clic en "Módulos" o "Páginas".',
                    'Crea un nuevo ítem o página.',
                    'En el editor de contenido, haz clic en "Insertar/Editar video" o "Insertar/Editar embed".',
                    'Pega el siguiente código iframe:',
                    $iframeCode,
                    'Guarda los cambios.',
                ],
            ],
            'classroom' => [
                'label' => 'Google Classroom',
                'steps' => [
                    'Ve a Google Classroom y selecciona tu clase.',
                    'Haz clic en "Trabajo en clase" y luego en "Crear" > "Material".',
                    'Pega el siguiente enlace en la descripción:',
                    $embedUrl,
                    'Haz clic en "Enviar".',
                ],
            ],
            'teams' => [
                'label' => 'Microsoft Teams',
                'steps' => [
                    'Ve a tu equipo y canal en Microsoft Teams.',
                    'Haz clic en "Agregar una pestaña" (+).',
                    'Selecciona "Sitio web".',
                    'Pega el siguiente enlace:',
                    $embedUrl,
                    'Asigna un nombre a la pestaña y guarda.',
                ],
            ],
            'schoology' => [
                'label' => 'Schoology',
                'steps' => [
                    'Ve a tu curso en Schoology.',
                    'Haz clic en "Agregar materiales" > "Agregar archivo/enlace/URL".',
                    'Pega el siguiente enlace:',
                    $embedUrl,
                    'Haz clic en "Agregar".',
                ],
            ],
        ];

        return ApiResponse::success([
            'embed_url' => $embedUrl,
            'iframe_code' => $iframeCode,
            'moodle_link' => $moodleLink,
            'html_code' => $htmlCode,
            'lms_instructions' => $lmsInstructions,
            'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($embedUrl),
        ]);
    }

    private function generateShareUrls(Resource $resource, ResourceShare $share): array
    {
        $shareUrl = url('/share/' . $share->token);

        return [
            'share_url' => $shareUrl,
            'embed_url' => url('/embed/' . $resource->uuid),
            'player_url' => url('/player/' . $resource->uuid),
            'direct_url' => $shareUrl,
        ];
    }
}
