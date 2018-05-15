<?php 
namespace Kuink\Adapter\Authentication;

class PlainStrategy extends UsernamePasswordStrategy
{
    const PASSWORD_HASH_COST = 12;

    public function authenticate() : bool
    {
        return $this->username == 'admin' && $this->comparePasswordWithHash('$2y$12$hzSVsFCC37UzAJ8fj5IwOuFHzno7k7KDSdAutxZcK0MnTH/yGn0si', $this->password);
    }


    /**
     * Convert a password to a secure hash
     * @param string $password
     * @return string
     */
    protected function passwordToHash(string $password) : string{
        $options = ['cost' => self::PASSWORD_HASH_COST];
        return password_hash($password, PASSWORD_DEFAULT, $options);
    }

    /**
     * Compare a plain password with a has string
     * @param string $hash
     * @param string $plainPassword
     * @return bool
     */
    protected function comparePasswordWithHash(string $hash, string $plainPassword){
        return password_verify($plainPassword, $hash);
    }
}
