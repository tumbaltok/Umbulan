<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePipelineDistanceTest extends TestCase
{
    /**
     * Test bahwa landing page welcome menampilkan panjang pipa transmisi 97 KM dan tidak lagi menyebut 93 KM.
     */
    public function test_welcome_page_displays_correct_pipeline_length_of_97_km(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Memastikan penyebutan visual 97 KM ada pada halaman
        $response->assertSee('META Adhya Tirta Umbulan - Penyaluran Air Bersih 97 KM');
        $response->assertSee('Jalur Pipa 97 KM');
        $response->assertSee('sepanjang 97 KM');
        $response->assertSee('Pipa Transmisi ±97 KM');
        $response->assertSee('Lihat Aliran Pipa 97 KM');
        $response->assertSee('97 KM BEROPERASI');
        $response->assertSee('±97 KM');
        $response->assertSee('Jalur Pipa Transmisi 97 KM');
        $response->assertSee('sepanjang 97 kilometer');
        $response->assertSee('/ 97.0 KM');
        $response->assertSee('97.0 KM');
        $response->assertSee('transmisi sepanjang 97 km');

        // Memastikan tidak ada lagi teks 93 KM atau 93 kilometer untuk panjang pipa
        $response->assertDontSee('Penyaluran Air Bersih 93 KM');
        $response->assertDontSee('Jalur Pipa 93 KM');
        $response->assertDontSee('sepanjang 93 KM');
        $response->assertDontSee('Pipa Transmisi ±93 KM');
        $response->assertDontSee('Lihat Aliran Pipa 93 KM');
        $response->assertDontSee('93 KM BEROPERASI');
        $response->assertDontSee('±93 KM');
        $response->assertDontSee('Jalur Pipa Transmisi 93 KM');
        $response->assertDontSee('sepanjang 93 kilometer');
        $response->assertDontSee('/ 93.0 KM');
        $response->assertDontSee('>93.0 KM<');
        $response->assertDontSee('transmisi sepanjang 93 km');
        $response->assertDontSee('pipa transmisi 93 km');
        $response->assertDontSee('Hilir Transmisi 93 KM');
    }
}
