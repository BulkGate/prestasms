<?php
namespace BulkGate\PrestaSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class ProxyGenerator extends Extensions\Strict
{
    /** @var array */
    private $proxy = array();

    /** @var string */
    private $controller;

    /** @var string */
    private $token;

    public function __construct($controller, $token)
    {
        $this->controller = (string) $controller;
        $this->token = (string) $token;
    }

    public function add($action, $proxy_action, $reducer = '_generic', $url = 'ajax-tab.php')
    {
        $proxy = array(
            'url' => $url,
            'params' => array(
                'action' => $proxy_action,
                'ajax' => true,
                'controller' => $this->controller,
                'token' => $this->token
            )
        );

        if(isset($this->proxy[$reducer]))
        {
            $this->proxy[$reducer][$action] = $proxy;
        }
        else
        {
            $this->proxy[$reducer] = array($action => $proxy);
        }
    }

    public function get()
    {
        return $this->proxy;
    }
}
