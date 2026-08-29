<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User\User;
use App\Http\Controllers\Absen\KehadiranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AttendanceFaceSecurityTest extends TestCase
{
    private function generateDummy128Descriptor(): array
    {
        $desc = [];
        for ($i = 0; $i < 128; $i++) {
            $desc[] = round((sin($i) + 1) / 2, 6);
        }
        return $desc;
    }

    public function test_checkin_fails_when_face_descriptor_is_null()
    {
        $user = new User([
            'id' => 991,
            'name' => 'User No Face',
            'email' => 'noface@test.com',
        ]);
        $user->face_descriptor = null;

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-in', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => true,
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkIn($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Data biometrik wajah belum terdaftar', $data['message']);
        $this->assertArrayHasKey('face_descriptor', $data['errors']);
    }

    public function test_checkin_fails_when_face_descriptor_is_empty_array()
    {
        $user = new User([
            'id' => 992,
            'name' => 'User Empty Face',
            'email' => 'emptyface@test.com',
        ]);
        $user->face_descriptor = [];

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-in', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => true,
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkIn($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Data biometrik wajah belum terdaftar', $data['message']);
    }

    public function test_checkin_fails_when_face_descriptor_has_invalid_length()
    {
        $user = new User([
            'id' => 993,
            'name' => 'User Short Face',
            'email' => 'shortface@test.com',
        ]);
        $user->face_descriptor = [0.1, 0.2, 0.3]; // Not 128 floats

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-in', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => true,
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkIn($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Data biometrik wajah belum terdaftar', $data['message']);
    }

    public function test_checkin_fails_when_face_is_verified_flag_is_false()
    {
        $user = new User([
            'id' => 994,
            'name' => 'User Valid Face Not Matched',
            'email' => 'validface@test.com',
        ]);
        $user->face_descriptor = $this->generateDummy128Descriptor();

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-in', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => false,
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkIn($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Verifikasi biometrik wajah wajib berhasil', $data['message']);
    }

    public function test_checkin_redirects_with_errors_on_traditional_web_request()
    {
        $user = new User([
            'id' => 995,
            'name' => 'User Web Form',
            'email' => 'webform@test.com',
        ]);
        $user->face_descriptor = null;

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-in', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => 1,
        ]);
        // Do NOT set Accept: application/json
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkIn($request);

        $this->assertTrue($response->isRedirect());
        $errors = session('errors');
        $this->assertNotNull($errors);
        $this->assertTrue($errors->has('face_descriptor'));
        $this->assertStringContainsString('Data biometrik wajah belum terdaftar', $errors->first('face_descriptor'));
    }

    public function test_checkout_fails_when_face_descriptor_is_null()
    {
        $user = new User([
            'id' => 996,
            'name' => 'User Checkout No Face',
            'email' => 'checkoutnoface@test.com',
        ]);
        $user->face_descriptor = null;

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-out', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => true,
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkOut($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Data biometrik wajah belum terdaftar', $data['message']);
        $this->assertArrayHasKey('face_descriptor', $data['errors']);
    }

    public function test_checkout_fails_when_is_face_verified_is_false()
    {
        $user = new User([
            'id' => 997,
            'name' => 'User Checkout Face False',
            'email' => 'checkoutfalse@test.com',
        ]);
        $user->face_descriptor = $this->generateDummy128Descriptor();

        $controller = app(KehadiranController::class);

        $request = Request::create('/attendance/check-out', 'POST', [
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_face_verified' => false,
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $response = $controller->checkOut($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Verifikasi biometrik wajah wajib berhasil', $data['message']);
    }
}
