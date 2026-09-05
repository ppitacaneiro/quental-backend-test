<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_swagger_ui_and_openapi_spec_are_available(): void
    {
        $this->get('/docs/api')
            ->assertOk()
            ->assertSee('swagger-ui', false)
            ->assertSee('docs\\/openapi.yaml', false);

        $this->get('/docs/openapi.yaml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/yaml')
            ->assertSee('openapi: 3.1.0', false)
            ->assertSee('/api/characters:', false)
            ->assertSee('/api/favorites/{character}:', false);
    }
}
