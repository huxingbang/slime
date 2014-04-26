<?php
return array(
    'http' => array(
        '#^/v\d+/#' => array(array('Slime\Component\Route\Mode', 'slimeHttp_REST'), '{{{NS}}}\\ControllerApi\\', ''),
        array(array('Slime\Component\Route\Mode', 'slimeHttp_Page'), '{{{NS}}}\\ControllerPage\\C_', 'action')
    ),
    'cli'  => array(
        array(array('Slime\Component\Route\Mode', 'slimeCli'), '{{{NS}}}\\ControllerCli\\C_', 'action')
    )
);