<?php

namespace Canopy\Http;

use Canopy\CanopyTree;
use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Serves the Canopy explorer UI and the OpenAPI JSON document it consumes.
 *
 * The OpenAPI document is produced entirely by Scramble; Canopy only derives a
 * navigation tree from it. No Scramble core behaviour is modified.
 */
class DocumentationController
{
    public function __construct(
        private readonly Generator $generator,
        private readonly CanopyTree $tree,
    ) {}

    public function ui(): View
    {
        $spec = $this->spec();

        return view('canopy::docs', [
            'spec' => $spec,
            'tree' => $this->tree->build($spec),
            'branding' => config('canopy.branding', []),
            'documentUrl' => url(config('canopy.route.document', 'docs/canopy.json')),
        ]);
    }

    public function document(): JsonResponse
    {
        return response()->json($this->spec(), options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<string, mixed>
     */
    private function spec(): array
    {
        $this->raiseMemoryLimit();

        $api = (string) config('canopy.api', Scramble::DEFAULT_API);

        return ($this->generator)(Scramble::getGeneratorConfig($api));
    }

    /**
     * Optionally raise the memory limit before generating the document.
     *
     * Scramble's analysis can be memory intensive on large applications. When
     * `canopy.memory_limit` is set, Canopy applies it for this request only so
     * generation does not fail with a fatal "allowed memory exhausted" error.
     */
    private function raiseMemoryLimit(): void
    {
        $limit = config('canopy.memory_limit');

        if (is_string($limit) && $limit !== '') {
            @ini_set('memory_limit', $limit);
        }
    }
}
