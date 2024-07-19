<?php return array(
    'root' => array(
        'name' => 'bulkgate/prestasms',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'prestashop-module',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'bulkgate/plugin' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'f1fbe7b48261462bfc87456c71d74c5824169377',
            'type' => 'library',
            'install_path' => __DIR__ . '/../bulkgate/plugin',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'bulkgate/prestasms' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'prestashop-module',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'cordoval/hamcrest-php' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'davedevelopment/hamcrest-php' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'hamcrest/hamcrest-php' => array(
            'pretty_version' => 'v2.0.1',
            'version' => '2.0.1.0',
            'reference' => '8c3d0a3f6af734494ad8f6fbbee0ba92422859f3',
            'type' => 'library',
            'install_path' => __DIR__ . '/../hamcrest/hamcrest-php',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'kodova/hamcrest-php' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'mockery/mockery' => array(
            'pretty_version' => '1.6.6',
            'version' => '1.6.6.0',
            'reference' => 'b8e0bb7d8c604046539c1115994632c74dcb361e',
            'type' => 'library',
            'install_path' => __DIR__ . '/../mockery/mockery',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'nette/tester' => array(
            'pretty_version' => 'v2.4.3',
            'version' => '2.4.3.0',
            'reference' => '451f6e97b117797e817446de8d19fe06e54fd33f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../nette/tester',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpstan/extension-installer' => array(
            'pretty_version' => '1.3.1',
            'version' => '1.3.1.0',
            'reference' => 'f45734bfb9984c6c56c4486b71230355f066a58a',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/../phpstan/extension-installer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpstan/phpstan' => array(
            'pretty_version' => '1.10.47',
            'version' => '1.10.47.0',
            'reference' => '84dbb33b520ea28b6cf5676a3941f4bae1c1ff39',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpstan/phpstan',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'tracy/tracy' => array(
            'pretty_version' => 'v2.9.8',
            'version' => '2.9.8.0',
            'reference' => 'd84fb3ca4e9fa5a9352e6d18f0b8cd767f25901e',
            'type' => 'library',
            'install_path' => __DIR__ . '/../tracy/tracy',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
    ),
);