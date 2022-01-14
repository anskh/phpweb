<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

use Anskh\PhpWeb\Http\App;
use InvalidArgumentException;
use Laminas\Diactoros\Response;
use PDO;
use Anskh\PhpWeb\Http\Session\Session;
use Anskh\PhpWeb\Http\Auth\AccessControlInterface;
use Anskh\PhpWeb\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Access control class
 *
 * @package    Anskh\PhpWeb\Http\Auth
 * @author     Khaerul Anas <anasikova@gmail.com>
 * @copyright  2021-2022 Anskh Labs.
 * @version    1.0.0
 */
class AccessControl implements AccessControlInterface
{
    public const DRIVER_FILE          = 'from_file';
    public const DRIVER_DB            = 'from_db';
    public const FILTER_IP            = 'ip_filter';
    public const FILTER_USER_AGENT    = 'useragent_filter';
    public const ATTR_FILTER          = 'filter_config';
    public const ATTR_FILTER_TYPE     = 'filter_type';
    public const ATTR_FILTER_LIST     = 'filter_list';
    public const ATTR_PERMISSION      = 'permission_config';
    public const ATTR_PERMISSION_NAME = 'permission';
    public const ATTR_ASSIGNMENT      = 'assignment_config';
    public const ATTR_ROLE            = 'role_config';
    public const ATTR_ROLE_NAME       = 'role';
    public const SEPARATOR            = '|';

    protected string $driver;
    protected string $source;

    protected array $permissions;
    protected array $roles;
    protected array $assignments;
    protected array $filters;
    protected string $currentPermission;
    protected UserIdentityInterface $currentIdentity;
    protected ServerRequestInterface $currentRequest;
    protected string $model = User::class;

    /**
     * Constructor
     *
     * @param  array $source Configuration source, from DRIVER_FILE of DRIVER_DB
     * @return void 
     */
    public function __construct(string $source, string $driver = self::DRIVER_FILE)
    {
        $this->source = $source;
        $this->driver = $driver;
    }

    /**
     * @inheritdoc
     */
    public function filter(): bool
    {
        $filters = $this->getFilters();
        if (!$filters) {
            return true;
        }

        foreach ($filters as $filter => $setting) {
            switch ($filter) {
                case self::FILTER_IP:
                    if (!$this->filterIpAddress($setting)) {
                        return false;
                    }
                    break;
                case self::FILTER_USER_AGENT:
                    if (!$this->filterUserAgent($setting)) {
                        return false;
                    }
                    break;
                default:
                    throw new InvalidArgumentException("$filter not supported.");
            }
        }

        return true;
    }

    /**
     * Get filter configuration
     *
     * @return array filter configuration
     */
    protected function getFilters(): array
    {
        $filterAttribute = self::ATTR_FILTER;
        if ($this->driver === self::DRIVER_FILE) {
            return my_config()->get("{$this->source}.{$filterAttribute}");
        } elseif ($this->driver === self::DRIVER_DB) {
            $results = my_app()->db($this->source)->select($filterAttribute);
            if ($results) {
                $filters = [];
                foreach ($results as $result) {
                    if (empty($result[self::ATTR_FILTER_LIST])) {
                        $list = [];
                    } else {
                        $list = explode(self::SEPARATOR, $result[self::ATTR_FILTER_LIST]);
                    }
                    $filters[$result[self::ATTR_FILTER_TYPE]] = $list;
                }
                return $filters;
            }
        }

        return [];
    }

    /**
     * Get permission configuration
     *
     * @return array permission configuration
     */
    protected function getPermissions(): array
    {
        $permissionAttribute = self::ATTR_PERMISSION;
        if ($this->driver === self::DRIVER_FILE) {
            return my_config()->get("{$this->source}.{$permissionAttribute}");
        } elseif ($this->driver === self::DRIVER_DB) {
            $results = my_app()->db($this->source)->select(self::ATTR_PERMISSION, self::ATTR_PERMISSION_NAME, '', 0, '', PDO::FETCH_COLUMN);

            return $results;
        }

        return [];
    }

    /**
     * Get assignment configuration
     *
     * @return array assignment configuration
     */
    protected function getAssignments(): array
    {
        $assignmentAttribute = self::ATTR_ASSIGNMENT;
        if ($this->driver === self::DRIVER_FILE) {
            return my_config()->get("{$this->source}.{$assignmentAttribute}");
        } elseif ($this->driver === self::DRIVER_DB) {
            $results = my_app()->db($this->source)->select(self::ATTR_ASSIGNMENT);
            if ($results) {
                $assignments = [];
                foreach ($results as $result) {
                    $assignments[$result[self::ATTR_ROLE_NAME]] = explode(self::SEPARATOR, $result[self::ATTR_PERMISSION_NAME]);
                }
                return $assignments;
            }
        }

        return [];
    }

    protected function filterIpAddress(array $blockedIps): bool
    {
        if (!$blockedIps) {
            return true;
        }

        $ip = my_client_ip();
        if (!$ip) {
            return true;
        }

        foreach ($blockedIps as $blockedIp) {
            if ($this->stringEquals($ip, $blockedIp)) {
                return false;
            }
        }

        return true;
    }

    protected function filterUserAgent(array $blockedUserAgents): bool
    {
        if (!$blockedUserAgents) {
            return true;
        }

        $useragent =  my_user_agent();
        if (!$useragent) {
            return true;
        }

        foreach ($blockedUserAgents as $blockedUserAgent) {
            if($this->stringEquals($useragent, $blockedUserAgent)){
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticationRequired(string $route): bool
    {
        if (!$route) {
            return false;
        }

        $permissions = $this->getPermissions();

        if (!$permissions) {
            return false;
        }

        return in_array($route, $permissions);
    }

    /**
     * @inheritdoc
     */
    public function authenticate(): UserIdentity
    {
        $session = my_app()->session();
        $sid = $session->get(Session::ATTR_USER_ID);
        if ($sid) {
            $modelClass = my_app()->getAttribute(App::ATTR_USER_MODEL);

            if (!$modelClass) {
                $modelClass = User::class;
            }

            $user = $modelClass::getRow($sid);
            if ($user) {
                if ($session->validateUserSessionHash($user[User::ATTR_PASSWORD], $user[User::ATTR_TOKEN], my_user_agent())) {
                    $roles = explode(self::SEPARATOR, $user[User::ATTR_ROLES]);

                    return new UserIdentity($sid, $user[User::ATTR_NAME], $roles);
                } else {
                    $session->unset(Session::ATTR_USER_ID);
                    $session->unset(Session::ATTR_USER_HASH);
                }
            }
        }

        return new UserIdentity();
    }

    /**
     * @inheritdoc
     */
    public function authorize(UserIdentityInterface $user, string $route): bool
    {
        if (!$route) {
            return false;
        }

        $roles = $user->getRoles();
        if (!$roles) {
            return false;
        }

        $assignments = $this->getAssignments();
        foreach ($roles as $role) {
            $routes = $assignments[$role] ?? [];
            if (in_array($route, $routes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function unauthorized(int $status = 401): ResponseInterface
    {
        $unauthorized = my_app()->getAttribute(App::ATTR_UNAUTHORIZED);
        if (empty($unauthorized) || !is_callable($unauthorized)) {
            $response = new Response();

            return $response->withStatus($status);
        }

        return $unauthorized();
    }

    /**
     * @inheritdoc
     */
    public function forbidden(int $status = 403): ResponseInterface
    {
        $forbidden = my_app()->getAttribute(App::ATTR_FORBIDDEN);
        if (empty($forbidden) || !is_callable($forbidden)) {
            $response = new Response();

            return $response->withStatus($status);
        }

        return $forbidden();
    }

    /**
    * Check string is equals, based on '*' start or end in $needle
    *
    * @param  string $haystack string to search in
    * @param  string $needle   string to search, support start or end with '*' 
    * @return bool true if equals, otherwise false
    */
    protected function stringEquals(string $haystack, string $needle): bool
    {
        $start = substr($needle, 0, 1);
        $end = substr($needle, strlen($needle) - 1);
        if ($start === '*') {
            if ($end === '*') {
                return strpos($haystack, trim($needle, '*'));
            } else {
                return my_str_ends_with($haystack, ltrim($needle, '*'));
            }
        } elseif ($end === '*') {
            return my_str_starts_with($haystack, rtrim($needle, '*'));
        } else {
            return $haystack === $needle;
        }
    }
}