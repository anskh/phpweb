<?php

declare(strict_types=1);

namespace PhpWeb\Http\Auth;

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use PhpWeb\Http\Session\Session;
use PhpWeb\Model\DbModel;
use PhpWeb\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function PhpWeb\app;
use function PhpWeb\client_ip;
use function PhpWeb\user_agent;

class AccessControl implements AccessControlInterface
{
    public const ATTR_FILTER_IP = 'ip';
    public const ATTR_FILTER_USER_AGENT = 'ip';


    protected array $permissions;
    protected array $roles;
    protected array $assignments;
    protected array $filters;
    protected string $currentPermission;
    protected UserIdentityInterface $currentIdentity;
    protected ServerRequestInterface $currentRequest;
    protected string $model = User::class;

    public function __construct()
    {
        
    }

    public function filter(array $options): bool
    {
        if (empty($options)) {
            return true;
        }

        foreach ($options as $filter => $setting) {
            switch ($filter) {
                case self::ATTR_FILTER_IP:
                    if (!$this->filterIpAddress($setting)) {
                        return false;
                    } 
                    break;
                case self::ATTR_FILTER_USER_AGENT:
                    if (!$this->filterUserAgent($setting)) {
                        return false;
                    }
                    break;
                default:
                    throw new InvalidArgumentException('Available filter are ' . self::ATTR_FILTER_IP . ' and ' . self::ATTR_FILTER_USER_AGENT);
            }
        }

        return true;
    }

    protected function filterIpAddress(array $setting): bool
    {
        if (empty($setting)) {
            return true;
        }

        $ip = client_ip();
        if (empty($ip)) {
            return true;
        }

        return !in_array($ip, $setting);
    }

    protected function filterUserAgent(array $setting): bool
    {
        if (empty($setting)) {
            return true;
        }

        $user_agent =  user_agent();
        if (empty($user_agent)) {
            return true;
        }

        return !in_array($user_agent, $setting);
    }

    public function isAuthenticationRequired(string $permission, array $options): bool
    {
        if (empty($options) || empty($permission)) {
            return false;
        }

        return in_array($permission, $options);
    }

    public function authenticate(string $modelClass): UserIdentity
    {
        $session = app()->session();

        if ($session->has(Session::ATTR_SESSION_ID) && $session->has(Session::ATTR_SESSION_HASH)) {
            $userAgent =  user_agent();

            if(!$modelClass){
                $modelClass = $this->model;
            }

            $user = app()->dbModel($modelClass);

            $sid = $session->get(Session::ATTR_SESSION_ID);

            $user->get($sid);

            if($user){
                if (password_verify(sha1($user->password . $user->token) . ':' . $userAgent, $session->get(Session::ATTR_SESSION_HASH))) {
                    $roles = explode('|', $user->roles);
                    return new UserIdentity($sid, $user->name, $roles);
                }else{
                    $session->unset(Session::ATTR_SESSION_ID);
                    $session->unset(Session::ATTR_SESSION_HASH);
                }
            }
        }

        return new UserIdentity();
    }

    public function authorize(array $roles): bool
    {
        if (empty($this->permission)) {
            return false;
        }

        foreach ($roles as $role) {
            $permissions = $this->assignments[$role] ?? [];
            if (in_array($this->permission, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    public function unauthorized(int $status = 401): ResponseInterface
    {
        $unauthorized = app()->config('exception.unauthorized');
        if (is_null($unauthorized) || !is_callable($unauthorized)) {
            $response = new Response();
            return $response->withStatus($status);
        }

        return $unauthorized();
    }

    public function forbidden(int $status = 403): ResponseInterface
    {
        $forbidden = app()->config('exception.forbidden');
        if (is_null($forbidden) || !is_callable($forbidden)) {
            $response = new Response();
            return $response->withStatus($status);
        }

        return $forbidden();
    }
}
