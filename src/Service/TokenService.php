<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class TokenService
{
    protected string $secret;
    protected int $issuedAt;
    
    protected int $expire;
    
    
    public function __construct()
    {
        $this->secret = "this_is_my_secret";
        date_default_timezone_set('Europe/Paris');
        $this->issuedAt = time();
        $this->expire = $this->issuedAt + 3600;
    }
    
    /**
     * @param string $userId
     * @param string $email
     * @return string
     */
    public function generateToken(string $userId, string $email): string
    {
        $encodedData = [
            'email' => $email,
            'userId' => $userId,
            'exp' => $this->expire
        ];
        
        return JWT::encode($encodedData, $this->secret, 'HS256');
    }
    
    /**
     * @param string $token
     * @return stdClass
     */
    public function decodeToken(string $token): stdClass
    {
        $decode = JWT::decode($token, new Key($this->secret, 'HS256'));
        return $decode;
    }
    
    /**
     * @param int $expire
     */
    public function setExpire(int $second): void
    {
        $this->expire = $this->issuedAt + $second;
    }
}