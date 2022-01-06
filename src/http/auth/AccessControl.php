<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use PDO;
use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Http\Session\Session;
use Anskh\PhpWeb\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
            return my_app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_FILTER);
        }elseif($driver === Config::ACCESSCONTROL_DRIVER_DB){
            $connection = $this->config[Config::ACCESSCONTROL_DRIVER_DB];
            $results = my_app()->db($connection)->select(Config::ATTR_ACCESSCONTROL_FILTER);
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
            return my_app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_PERMISSION);
        }elseif($driver === Config::ACCESSCONTROL_DRIVER_DB){
            $connection = $this->config[Config::ACCESSCONTROL_DRIVER_DB];
            $results = my_app()->db($connection)->select(Config::ATTR_ACCESSCONTROL_PERMISSION, Config::ATTR_ACCESSCONTROL_PERMISSION_NAME,'',0,'',PDO::FETCH_COLUMN);
            return $results;
        }
        
        return [];
    }

    protected function getAssignmentConfig(): array
    {
        $driver = $this->config[Config::ATTR_ACCESSCONTROL_DRIVER];
        if($driver === Config::ACCESSCONTROL_DRIVER_FILE){
            return my_app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_ASSIGNMENT);
        }elseif($driver === Config::ACCESSCONTROL_DRIVER_DB){
            $connection = $this->config[Config::ACCESSCONTROL_DRIVER_DB];
            $results = my_app()->db($connection)->select(Config::ATTR_ACCESSCONTROL_ASSIGNMENT);
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

        $ip = my_client_ip();
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

        $user_agent =  my_user_agent();
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
        $session = my_app()->session();

        if ($session->has(Session::ATTR_SESSION_ID) && $session->has(Session::ATTR_SESSION_HASH)) {
            $userAgent =  my_user_agent();
            $modelClass = $this->config[Config::ATTR_ACCESSCONTROL_USERMODEL];
            if(!$modelClass){
                $modelClass = User::class;
            }
            $sid = $session->get(Session::ATTR_SESSION_ID);
            $user = $modelClass::getRow($sid);

            if($user){
                if($session->validateUserSessionHash($user[User::ATTR_PASSWORD], $user[User::ATTR_TOKEN], $userAgent))
                {
                    $roles = explode(Config::ACCESSCONTROL_SEPARATOR, $user[User::ATTR_ROLES]);
                    return new UserIdentity($sid, $user[User::ATTR_NAME], $roles);
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
        $unauthorized = my_app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_UNAUTHORIZED);
        if (is_null($unauthorized) || !is_callable($unauthorized)) {
            $response = new Response();
            return $response->withStatus($status);
        }

        return $unauthorized();
    }

    public function forbidden(int $status = 403): ResponseInterface
    {
        $forbidden = my_app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_FORBIDDEN);
        if (is_null($forbidden) || !is_callable($forbidden)) {
            $response = new Response();
            return $response->withStatus($status);
        }

        return $forbidden();
    }
}