<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserResourceRoleFieldTest extends TestCase
{
    /** @test */
    public function role_field_is_not_dehydrated()
    {
        $path = base_path('app/Filament/Resources/UserResource.php');
        $this->assertFileExists($path, 'UserResource file missing');

        $content = file_get_contents($path);

        $this->assertStringNotContainsString('dehydrated(false)', $content, "The 'role' field should not be marked as dehydrated in UserResource.php");
    }
}
