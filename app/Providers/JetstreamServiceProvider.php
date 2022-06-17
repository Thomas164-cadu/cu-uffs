<?php

namespace App\Providers;

use App\Enums\UserType;
use App\Services\AuthService;
use App\Services\BarcodeService;
use App\Services\MailjetService;
use App\Services\UserService;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use App\Actions\Jetstream\DeleteUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;
use PHPUnit\Util\Exception;

class JetstreamServiceProvider extends ServiceProvider
{
    private UserService $userService;
    private User $user;
    private AuthService $authService;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $userRepository = new UserRepository();
        $barcodeService = new BarcodeService();
        $mailJetService = new MailjetService();
        $this->userService = new UserService($userRepository, $barcodeService);
        $this->authService = new AuthService($this->userService, $mailJetService);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();
        $this->configureLogin();

        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     *
     *
     * @return void
     */
    protected function configureLogin()
    {
        Fortify::authenticateUsing(function (Request $request) {
            $this->user = $this->userService->getUserByUsername($request->input('email'), false);
            $password = $request->input('password');

            if (empty($this->user)) {
                return null;
            }

            switch ($this->user->type) {
                case UserType::RUEmployee->value:
                    try {
                        $data = $this->authService->authWithIdUFFS($this->user->uid, $password);
                        $this->user->update($data);
                        return $this->user;
                    } catch (Exception) {
                        return null;
                    }
                case UserType::ThirdPartyEmployee->value:
                    if (Hash::check($password, $this->user->password)) {
                        return $this->user;
                    }
                    return null;
                default:
                    return null;
            }
        });
    }

    /**
     * Configure the permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
