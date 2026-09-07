<?php

namespace Tests\Feature;

use App\Exports\AbrechnungExport;
use Tests\TestCase;

class AbrechnungExportTest extends TestCase
{
    public function test_abrechnung_export_has_all_expected_sheets(): void
    {
        $export = new AbrechnungExport();

        $this->assertCount(3, $export->sheets());
        $this->assertSame('Verkäufer Abrechnung', $export->sheets()[0]->title());
        $this->assertSame('Verkäufe', $export->sheets()[1]->title());
        $this->assertSame('verkaufte Artikel', $export->sheets()[2]->title());
    }

    public function test_abrechnung_export_collection_is_empty_and_safe(): void
    {
        $export = new AbrechnungExport();

        $this->assertNotNull($export->collection());
        $this->assertTrue($export->collection()->isEmpty());
    }
}
