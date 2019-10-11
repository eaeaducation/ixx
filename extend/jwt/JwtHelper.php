<?php
/**
 * User: Jasmine2
 * Date: 2018/5/21 17:53
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace jwt;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use think\exception\HttpResponseException;
use think\facade\Request;
use jwt\User;
use http\Exception\UnexpectedValueException;
use Exception;

class JwtHelper
{
    public static function fromUser(User $user, $jwt_ttl = 60)
    {
        $jwt_secret = config('jwt_secret');
        $payload = [
            'iss' => Request::instance()->domain(),
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + $jwt_ttl * 60,
            'sub' => encode($user->id)
        ];
        return JWT::encode($payload, $jwt_secret);
    }

    public static function checkToken($token)
    {
        $jwt_secret = config('jwt_secret');
        try {
            $payload = JWT::decode($token, $jwt_secret, ['HS256']);
            $payload->sub = decode($payload->sub);
            return $payload;
        } catch (ExpiredException $exception) {
            throw new HttpResponseException(json([
                'success' => false,
                'code' => -400,
                'msg' => lang('un login'),
            ]));
        } catch (UnexpectedValueException $exception) {
            throw new HttpResponseException(json([
                'success' => false,
                'code' => -400,
                'msg' => lang('un login'),
            ]));
        }
    }

    public static function checkTokenBool($token)
    {
        $jwt_secret = config('jwt_secret');
        try {
            $payload = JWT::decode($token, $jwt_secret, ['HS256']);
            $payload->sub = decode($payload->sub);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}