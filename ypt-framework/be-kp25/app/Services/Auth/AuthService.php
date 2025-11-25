<?php

namespace App\Services\Auth;

use App\Helpers\ResponseHelper;

class AuthService
{
    public $responseHelper;

    public function __construct()
    {
        $this->responseHelper = new ResponseHelper();
    }

    /**
     * Service to do login. The login is by pass to Portal.
     *
     * @param string $loginRequest
     * @return array
     *
     * @throws \Exception
     */
    public function loginByPortal($loginRequest): array
    {
        try {
            $username = (string) $loginRequest['username'];
            $password = (string) $loginRequest['password'];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => env('ISSUE_AUTH_LOGIN'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "username={$username}&password={$password}",
            ]);

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $responseDecoded = json_decode($response);

            $token = $responseDecoded->token ?? null;
            $returned = ['status' => false, 'token' => ''];

            if (is_null($token) || $err) {
                return $returned;
            }

            $returned['status'] = true;
            $returned['token'] = $token;

            return $returned;

        } catch (\Throwable $th) {
            throw $this->responseHelper->catch_throw_exception_helper($th);
        }
    }

    /**
     * Service for verifying JWT token by getting profile user.
     *
     * @param string $token
     * @return mixed
     */
    public function getProfileByPortal($token): mixed
    {
        try {
            $token = (string) trim($token);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => env('ISSUE_PROFILE'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}"
                ],
            ]);

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            return json_decode($response);
        } catch (\Throwable $th) {
            throw $this->responseHelper->catch_throw_exception_helper($th);
        }
    }

    /**
     * Service for verifying JWT token by getting profile user.
     *
     * @param string $token
     * @return mixed
     */
    public function getRoleByPortal($token): mixed
    {
        try {
            $token = (string) trim($token);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => env('ISSUE_ROLE'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}"
                ],
            ]);

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            return json_decode($response);
        } catch (\Throwable $th) {
            throw $this->responseHelper->catch_throw_exception_helper($th);
        }
    }
}
