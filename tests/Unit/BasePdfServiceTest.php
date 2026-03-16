<?php

namespace Tests\Unit;

use App\Services\BasePdfService;
use Tests\TestCase;

class BasePdfServiceTest extends TestCase
{
    public function test_base_pdf_service_can_be_instantiated()
    {
        $service = new BasePdfService;
        $this->assertInstanceOf(BasePdfService::class, $service);
    }
}
