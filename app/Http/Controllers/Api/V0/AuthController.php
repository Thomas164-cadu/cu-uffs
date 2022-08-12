<?php

namespace App\Http\Controllers\Api\V0;

use App\Interfaces\Services\IAuthService;
use App\Models\Api\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController
{
    private IAuthService $service;

    public function __construct(IAuthService $service)
    {
        $this->service = $service;
    }

    public function login(Request $request)
    {
        try {
            $token = $this->service->login($request->uid, $request->password);

            return ApiResponse::ok(["token" => $token]);
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validation = Validator::make(["uid" => $request->uid], $this->forgotPasswordRules());

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $this->service->forgotPassword($request->uid);

            return ApiResponse::ok(null);
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validation = Validator::make(["new_password" => $request->new_password], $this->resetPasswordRules());

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $this->service->resetPassword($request->user()->uid, $request->new_password);

            return ApiResponse::ok(null);
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    private static function resetPasswordRules()
    {
        return [
            'new_password' => [
                'required',
                'string',
            ]
        ];
    }

    private static function forgotPasswordRules()
    {
        return [
            'uid' => [
                'required',
                'string',
            ]
        ];
    }
}
