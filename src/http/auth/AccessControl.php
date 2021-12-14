<?php

declare(strict_types=1);

namespace PhpWeb\Http\Auth;

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use PDO;
use PhpWeb\Config\Config;
use PhpWeb\Http\Session\Session;
use PhpWeb\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function PhpWeb\app;
use function PhpWeb\client_ip;
use function PhpWeb\current_route;
use function PhpWeb\user_agent;

class AccessControl implements AccessControlInterface
{
    protected array $permissions;
    protected array $roles;
    protected array $assignments;
    protected array $filters;
    protected string $currentPermission;
    protected UserIdentityInterface $currentIdentity;
    protected ServerRequestInterface $currentRequest;
    protected string $model = User::class;
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function filter(): bool
    {
        if (!isset($this->config[Config::ATTR_APP_ACCESSCONTROL])) {
            return true;
        }

        $filters = $this->getFilterConfig();
        foreach ($filters as $filter => $setting) {
            switch ($filter) {
                case Config::ACCESSCONTROL_FILTER_IP:
                    if (!$this->filterIpAddress($setting)) {
                        return false;
                    } 
                    break;
                case Config::ACCESSCONTROL_FILTER_USERAGENT:
                    if (!$this->filterUserAgent($setting)) {
                        return false;
                    }
                    break;
                default:
                    throw new InvalidArgumentException('Available filter are ' . Config::ACCESSCONTROL_FILTER_IP . ' and ' . Config::ACCESSCONTROL_FILTER_USERAGENT);
            }
        }

        return true;
    }

    protected function getFilterConfig(): array
    {
        $driver = $this->config[Config::ATTR_ACCESSCONTROL_DRIVER];
        if($driver === Config::ACCESSCONTROL_DRIVER_FILE){
            return app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_FILTER);
        }elseif($driver === Config::ACCESSCONTROL_DRIVER_DB){
            $connection = $this->config[Config::ACCESSCONTROL_DRIVER_DB];
            $results = app()->db($connection)->select(Config::ATTR_ACCESSCONTROL_FILTER);
            if($results){
                $filters = [];
                foreach($results as $result){
                    if(empty($result[Config::ATTR_ACCESSCONTROL_FILTER_LIST])){
                        $list = [];
                    }else{
                        $list = explode(Config::ACCESSCONTROL_SEPARATOR, $result[Config::ATTR_ACCESSCONTROL_FILTER_LIST]);
                    }
                    $filters[$result[Config::ATTR_ACCESSCONTROL_FILTER_TYPE]] = $list;
                }
                return $filters;
            }
        }
        
        return [];
    }

    protected function getPermissionConfig(): array
    {
        $driver = $this->config[Config::ATTR_ACCESSCONTROL_DRIVER];
        if($driver === Config::ACCESSCONTROL_DRIVER_FILE){
            return app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_PERMISSION);
        }elseif($driver === Config::ACCESSCONTROL_DRIVER_DB){
            $connection = $this->config[Config::ACCESSCONTROL_DRIVER_DB];
            $results = app()->db($connection)->select(Config::ATTR_ACCESSCONTROL_PERMISSION, Config::ATTR_ACCESSCONTROL_PERMISSION_NAME,'',0,'',PDO::FETCH_COLUMN);
            return $results;
        }
        
        return [];
    }

    protected function getAssignmentConfig(): array
    {
        $driver = $this->config[Config::ATTR_ACCESSCONTROL_DRIVER];
        if($driver === Config::ACCESSCONTROL_DRIVER_FILE){
            return app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_ASSIGNMENT);
        }elseif($driver === Config::ACCESSCONTROL_DRIVER_DB){
            $connection = $this->config[Config::ACCESSCONTROL_DRIVER_DB];
            $results = app()->db($connection)->select(Config::ATTR_ACCESSCONTROL_ASSIGNMENT);
            if($results){
                $assignments = [];
                foreach($results as $result){
                    $assignments[$result[Config::ATTR_ACCESSCONTROL_ROLE]] = explode(Config::ACCESSCONTROL_SEPARATOR, $result[Config::ATTR_ACCESSCONTROL_PERMISSION]);
                }
                return $assignments;
            }
            return $results;
        }
        
        return [];
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

    public function isAuthenticationRequired(string $permission): bool
    {
        if(!$permission){
            return false;
        }

        $permissions = $this->getPermissionConfig();

        if (!$permissions) {
            return false;
        }

        return in_array($permission, $permissions);
    }

    public function authenticate(): UserIdentity
    {
        $session = app()->session();

        if ($session->has(Session::ATTR_SESSION_ID) && $session->has(Session::ATTR_SESSION_HASH)) {
            $userAgent =  user_agent();
            $modelClass = $this->config[Config::ATTR_ACCESSCONTROL_USERMODEL];
            if(!$modelClass){
                $modelClass = User::class;
            }

            $user = app()->dbModel($modelClass);

            $sid = $session->get(Session::ATTR_SESSION_ID);

            $user->get($sid);

            if($user){
                if (password_verify(sha1($user->password . $user->token) . ':' . $userAgent, $session->get(Session::ATTR_SESSION_HASH))) {
                    $roles = explode(Config::ACCESSCONTROL_SEPARATOR, $user->roles);
                    return new UserIdentity($sid, $user->name, $roles);
                }else{
                    $session->unset(Session::ATTR_SESSION_ID);
                    $session->unset(Session::ATTR_SESSION_HASH);
                }
            }
        }

        return new UserIdentity();
    }

    public function authorize(array $roles, string $permission): bool
    {
        if (!$permission) {
            return false;
        }

        if(!$roles){
            return false;
        }

        $assignments = $this->getAssignmentConfig();
        foreach ($roles as $role) {
            $permissions = $assignments[$role] ?? [];
            if (in_array($permission, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    public function unauthorized(int $status = 401): ResponseInterface
    {
        $unauthorized = app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_UNAUTHORIZED);
        if (is_null($unauthorized) || !is_callable($unauthorized)) {
            $response = new Response();
            return $response->withStatus($status);
        }

        return $unauthorized();
    }

    public function forbidden(int $status = 403): ResponseInterface
    {
        $forbidden = app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_FORBIDDEN);
        if (is_null($forbidden) || !is_callable($forbidden)) {
            $response = new Response();
            return $response->withStatus($status);
        }

        return $forbidden();
    }
}
