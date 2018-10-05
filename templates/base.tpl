<div id="presta-sms">
    <nav>
        <div class="container-fluid">
            <div class="nav-wrapper">
                <div id="brand-logo">
                    <a class="brand-logo hide-on-med-and-down" href="{$homepage|prestaSmsEscapeUrl}">
                    <img alt="prestasms" width="120" src="{$logo|prestaSmsEscapeUrl}" />
                    </a>
                </div>
                <ul class="controls">
                    <span id="react-app-panel-admin-buttons"></span>
                    <span id="react-app-info"></span>
                </ul>
                <div class="nav-h1">
                    <span class="h1-divider"></span>
                    <h1 class="truncate">{$title|prestaSmsEscapeHtml}<span id="react-app-h1-sub"></span></h1>
                </div>
            </div>
        </div>
    </nav>
    <div id="profile-tab"></div>
    <div{if isset($box) && $box} class="module-box"{/if}>
    <div id="react-snack-root"></div>
    <div id="react-app-root">
        <div class="loader loading">
            <div class="spinner"></div>
            <p>{'Loading content'|prestaSmsTranslate}</p>
        </div>
    </div>
    <div id="react-language-footer"></div>
    <script type="application/javascript">

        var _bg_client_config = {
            url: {
                authenticationService : 'ajax-tab.php',
            },
            actions: {
                authenticate: function () {
                    return {
                        data: {$authenticate|prestaSmsEscapeJs}
                    }
                }
            }
        };
    </script>
    <script src="{$widget_api_url|prestaSmsEscapeUrl}"></script>
    <script type="application/javascript">
        _bg_client.registerMiddleware(function (data)
        {
            if(data.init._generic)
            {
                data.init.env.homepage.logo_link = {$logo|prestaSmsEscapeJs};
                data.init._generic.scope.module_info = {$info|prestaSmsEscapeJs}
            }
        });

        var input = _bg_client.parseQuery(location.search);

        _bg_client.require({$application_id|prestaSmsEscapeJs}, {
            product: "ps",
            language: {$language|prestaSmsEscapeJs},
            salt: {$salt|prestaSmsEscapeJs},
            view: {
                presenter: {$presenter|prestaSmsEscapeJs},
                action: {$action|prestaSmsEscapeJs},
            },
            params: {
                id: {if isset($id) && $id}{$id|prestaSmsEscapeJs}{else}input["id"]{/if},
                key: {if isset($key) && $key}{$key|prestaSmsEscapeJs}{else}input["key"]{/if},
                type: {if isset($type) && $type}{$type|prestaSmsEscapeJs}{else}input["type"]{/if},
            },
            proxy: {$proxy|prestaSmsEscapeJs},
        });
    </script>
    </div>
</div>