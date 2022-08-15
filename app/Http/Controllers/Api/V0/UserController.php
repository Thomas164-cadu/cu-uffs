<?php

namespace App\Http\Controllers\Api\V0;

use App\Enums\Operation;
use App\Interfaces\Services\IUserPayloadService;
use App\Interfaces\Services\IUserService;
use App\Jobs\StartCreateOrUpdateUserJob;
use App\Models\Api\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController
{
    private IUserService $service;

    private IUserPayloadService $userPayloadService;


    public function __construct(IUserService $userService, IUserPayloadService $userPayloadService)
    {
        $this->service = $userService;
        $this->userPayloadService = $userPayloadService;
    }

    public function createUserWithIdUFFS(Request $request)
    {
        try {
            $user = [
                "uid" => $request->uid,
                "password" => $request->password,
                "profile_photo" => $request->profile_photo,
                "enrollment_id" => $request->enrollment_id,
                "birth_date" => $request->birth_date
            ];

            $validation = Validator::make($user, $this->createUserWithIdUFFSRules());

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $created = $this->service->getUserByUsernameFirstOrDefault($user['uid']);

            if ($created) {
                return ApiResponse::conflict("User already has an account.");
            }

            $this->userPayloadService->create($user, Operation::UserCreationWithIdUFFS);

            StartCreateOrUpdateUserJob::dispatch($user["uid"]);

            return ApiResponse::accepted();
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }


    public function createUserWithoutIdUFFS(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = [
                "uid" => $request->uid,
                "email" => $request->email,
                "name" => $request->name,
                "password" => $request->password,
                "type" => ($request->type != 0) ? $request->type : null,
                "profile_photo" => $request->profile_photo,
                "birth_date" => $request->birth_date
            ];

            $validation = Validator::make($user, $this->createUserWitoutIdUFFSRules());

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $created = $this->service->getUserByUsernameFirstOrDefault($user['uid']);

            if ($created) {
                return ApiResponse::conflict("User already has an account.");
            }

            $this->userPayloadService->create($user, Operation::UserCreationWithoutIdUFFS);

            StartCreateOrUpdateUserJob::dispatch($user["uid"]);

            return ApiResponse::accepted();
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function getUserOperationStatus($uid)
    {
        try {
            $operation = $this->userPayloadService->getStatusAndMessageByUid($uid);

            if (empty($operation)) {

                $created = $this->service->getUserByUsernameFirstOrDefault($uid);

                if ($created) {
                    return ApiResponse::ok("User has no operation in progress.");
                }

                return ApiResponse::noContent(null);
            }

            return ApiResponse::Ok($operation);
        } catch (\Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function getUser(Request $request)
    {
        try {
            $user = $this->service->getStudentCard($request->user()->uid);

            return ApiResponse::ok($user);
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function changeUserActivity(Request $request)
    {
        try {
            $user = [
                "active" => $request->active,
            ];

            $validation = Validator::make($user, $this->changeUserActivityUserRules());

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $savedUser = $this->service->deactivateUser($request->user()->uid, $user);

            return ApiResponse::ok($savedUser);
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function updateUserWithIdUFFS(Request $request)
    {
        try {
            $user = [
                "enrollment_id" => $request->enrollment_id,
                "profile_photo" => $request->profile_photo,
                "birth_date" => $request->birth_date,
                "uid" => $request->user()->uid
            ];

            $user = array_filter($user);

            $validation = Validator::make($user, $this->updateUserWithIdUFFSRules($request->enrollment_id));

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $created = $this->service->getUserByUsernameFirstOrDefault($request->user()->uid);

            if (!$created) {
                return ApiResponse::conflict("User does not have an account.");
            }

            $this->userPayloadService->create($user, Operation::UserUpdateWithIdUFFS);

            StartCreateOrUpdateUserJob::dispatch($request->user()->uid);

            return ApiResponse::accepted();
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function updateUserWithoutIdUFFS(Request $request)
    {
        try {
            $user = [
                "email" => $request->email,
                "name" => $request->name,
                "profile_photo" => $request->profile_photo,
                "birth_date" => $request->birth_date,
                "uid" => $request->user()->uid
            ];

            $user = array_filter($user);

            $validation = Validator::make($user, $this->updateUserWithoutIdUFFSRules($request->email));

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            $created = $this->service->getUserByUsernameFirstOrDefault($request->user()->uid);

            if (!$created) {
                return ApiResponse::conflict("User does not have an account.");
            }

            $this->userPayloadService->create($user, Operation::UserUpdateWithoutIdUFFS);

            StartCreateOrUpdateUserJob::dispatch($request->user()->uid);

            return ApiResponse::accepted();
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    public function changeUserType(Request $request)
    {
        try {

            $data = [
                'uid' => $request->uid,
                "type" => $request->type,
            ];

            $validation = Validator::make($data, $this->changeUserTypeRules());

            if ($validation->fails()) {
                return ApiResponse::badRequest($validation->errors()->all());
            }

            unset($data['uid']);
            $savedUser = $this->service->changeUserType($request->uid, $data);

            return ApiResponse::ok($savedUser);
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    private static function changeUserActivityUserRules()
    {
        return [
            'active' => ['required', 'bool'],
        ];
    }

    private static function changeUserTypeRules()
    {
        return [
            'uid' => ['required', 'string'],
            'type' => ['required', 'int'],
        ];
    }

    private static function updateUserWithIdUFFSRules($enrollment_id): array
    {
        return [
            'profile_photo' => ['string'],
            'enrollment_id' => [Rule::unique('users')->ignore($enrollment_id, 'enrollment_id'), 'string'],
            'birth_date' => ['date']
        ];
    }

    private static function updateUserWithoutIdUFFSRules($email): array
    {
        return [
            'email' => [Rule::unique('users')->ignore($email, 'email'), 'email'],
            'name' => ['string', 'max:255'],
            'profile_photo' => ['string'],
            'birth_date' => ['date']
        ];
    }

    private static function createUserWithIdUFFSRules()
    {
        return [
            "uid" => ['required', 'string', 'unique:users'],
            'password' => ['required', 'string'],
            'profile_photo' => ['required', 'string'],
            'enrollment_id' => ['required', 'string', 'unique:users'],
            'birth_date' => ['required', 'date']
        ];
    }

    private static function createUserWitoutIdUFFSRules()
    {
        return [
            "uid" => ['required', 'string', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'profile_photo' => ['required', 'string'],
            'birth_date' => ['required', 'date']
        ];
    }
}
