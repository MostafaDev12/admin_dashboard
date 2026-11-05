<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_admin_requires_authentication()
    {
        $response = $this->get('/admin/cms');

        $response->assertRedirect('/admin/login');
    }
}
